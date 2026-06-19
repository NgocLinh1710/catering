<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMenu;
use App\Models\TargetAudience;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DailyMenuController extends Controller
{
    // Lấy thực đơn của ngày được chọn
    public function getMenuByDate(Request $request)
    {
        $request->validate([
            'target_audience_id' => 'required|exists:target_audiences,id',
            'date' => 'required|date'
        ]);

        $menu = DailyMenu::where('target_audience_id', $request->target_audience_id)
            ->where('date', $request->date)
            ->with('dishes')
            ->first();

        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa cấu hình thực đơn cho ngày này.',
                'data' => null
            ]);
        }

        $menu->dishes = $menu->dishes->map(function ($d) {
            $d->quantity = $d->pivot->quantity ?? 1;
            $d->meal_type = $d->pivot->meal_type ?? 'normal';
            return $d;
        });

        return response()->json([
            'status' => 'success',
            'data' => $menu
        ]);
    }

    // Lưu / cập nhật thực đơn 
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'target_audience_id' => 'required|exists:target_audiences,id',
            'date' => 'required|date',
            'servings' => 'required|integer|min:1',

            'normal_servings' => 'nullable|integer|min:0',
            'vegetarian_servings' => 'nullable|integer|min:0',
            'allergy_servings' => 'nullable|integer|min:0',
            'allergy_notes' => 'nullable|array',

            'dishes' => 'required|array|min:1',
            'dishes.*.id' => 'required|exists:dishes,id',
            'dishes.*.quantity' => 'required|integer|min:1',
            'dishes.*.meal_type' => 'required|string'
        ]);

        // Kiểm tra Dị ứng 
        if (($data['allergy_servings'] ?? 0) > 0 && !empty($data['allergy_notes'])) {

            foreach ($data['dishes'] as $dishItem) {
                if (str_starts_with($dishItem['meal_type'], 'allergy_nhom_')) {
                    $groupIndex = (int) str_replace('allergy_nhom_', '', $dishItem['meal_type']);

                    if (isset($data['allergy_notes'][$groupIndex])) {
                        $allergyGroup = $data['allergy_notes'][$groupIndex];
                        $rawKeyword = trim(mb_strtolower($allergyGroup['keyword'] ?? '', 'UTF-8'));

                        if (!empty($rawKeyword)) {
                            $forbiddenKeywords = array_map('trim', explode(',', $rawKeyword));
                            $forbiddenKeywords = array_filter($forbiddenKeywords);

                            if (count($forbiddenKeywords) > 0) {
                                $dish = Dish::with('ingredients')->find($dishItem['id']);

                                // Gom góp toàn bộ các tags của món ăn từ nguyên liệu và thông tin gốc
                                $dishTags = [];
                                if ($dish && $dish->ingredients) {
                                    foreach ($dish->ingredients as $ingredient) {
                                        if (!empty($ingredient->tags) && is_array($ingredient->tags)) {
                                            foreach ($ingredient->tags as $t) {
                                                $dishTags[] = trim(mb_strtolower($t, 'UTF-8'));
                                            }
                                        }
                                    }
                                }

                                if (!empty($dish->dish_tags) && is_array($dish->dish_tags)) {
                                    foreach ($dish->dish_tags as $t) {
                                        $dishTags[] = trim(mb_strtolower($t, 'UTF-8'));
                                    }
                                }

                                $dishTags = array_unique(array_filter($dishTags));

                                foreach ($forbiddenKeywords as $singleKeyword) {
                                    if (empty($singleKeyword))
                                        continue;

                                    foreach ($dishTags as $cleanDishTag) {
                                        if (!empty($cleanDishTag) && (str_contains($cleanDishTag, $singleKeyword) || str_contains($singleKeyword, $cleanDishTag))) {
                                            return response()->json([
                                                'status' => 'error',
                                                'message' => "Không thể lưu! Bạn xếp món [{$dish->name}] vào nhóm [{$allergyGroup['name']}]. Món này chứa thành phần cấm: [{$cleanDishTag}] (Thuộc từ khóa chặn: {$singleKeyword})!"
                                            ], 422);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            $menu = DailyMenu::updateOrCreate(
                [
                    'target_audience_id' => $data['target_audience_id'],
                    'date' => $data['date']
                ],
                [
                    'unit_id' => $data['unit_id'],
                    'servings' => $data['servings'],
                    'normal_servings' => $data['normal_servings'] ?? 0,
                    'vegetarian_servings' => $data['vegetarian_servings'] ?? 0,
                    'allergy_servings' => $data['allergy_servings'] ?? 0,
                    'allergy_notes' => $data['allergy_notes'] ?? null,
                ]
            );

            $menu->dishes()->detach();

            foreach ($data['dishes'] as $dish) {
                $menu->dishes()->attach($dish['id'], [
                    'quantity' => $dish['quantity'],
                    'meal_type' => $dish['meal_type']
                ]);
            }

            DB::commit();

            $updatedMenu = DailyMenu::with('dishes')->find($menu->id);
            $updatedMenu->dishes = $updatedMenu->dishes->map(function ($d) {
                $d->quantity = $d->pivot->quantity;
                $d->meal_type = $d->pivot->meal_type;
                return $d;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu thực đơn thành công!',
                'data' => $updatedMenu
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tự động TỐI ƯU HÓA thực đơn
    public function autoGenerateMenu(Request $request)
    {
        if (
            !$request->has('all_dishes') ||
            !is_array($request->all_dishes) ||
            count($request->all_dishes) === 0
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng thêm ít nhất 1 món ăn trước khi tối ưu thực đơn.'
            ], 422);
        }

        $request->validate(
            [
                'target_audience_id' => 'required|exists:target_audiences,id',
                'forbidden_keywords' => 'nullable|array',
                'all_dishes' => 'array'
            ],
            [
                'target_audience_id.required' => 'Vui lòng chọn đối tượng ăn.',
                'target_audience_id.exists' => 'Đối tượng ăn không tồn tại.'
            ]
        );

        // Lấy thông tin định mức dinh dưỡng của đối tượng mục tiêu
        $audience = TargetAudience::find($request->target_audience_id);

        $payload = [
            'target' => [
                'budget' => (float) ($audience->budget_per_serving ?? 0),
                'calories' => (float) ($audience->target_calories ?? 0),
                'protein' => (float) ($audience->target_protein ?? 0),
                'fat' => (float) ($audience->target_fat ?? 0),
                'fiber' => (float) ($audience->target_fiber ?? 0),
            ],
            'forbidden_keywords' => $request->forbidden_keywords ?? [],
            'dishes' => $request->all_dishes
        ];

        $tempInputFile = storage_path('app/baimat_input_' . time() . '.json');
        file_put_contents(
            storage_path('app/debug_payload.json'),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        file_put_contents($tempInputFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $scriptPath = base_path('optimizer.py');
        $pythonPath = "C:\\Users\\ngocl\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";

        $command = "\"{$pythonPath}\" \"{$scriptPath}\" \"{$tempInputFile}\" 2>&1";
        $output = shell_exec($command);

        if (file_exists($tempInputFile)) {
            @unlink($tempInputFile);
        }

        if (empty($output)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Script Python không phản hồi hoặc không trả về kết quả.'
            ], 500);
        }

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi phát sinh từ môi trường Python hoặc thiếu thư viện: ' . $output
            ], 500);
        }

        return response()->json($result);
    }

    // Hiển thị thực đơn công khai cho khách hàng xem khi quét mã QR
    public function showPublicMenu(Request $request)
    {
        $date = $request->query('date');
        $targetAudienceId = $request->query('target_audience_id');

        if (!$date || !$targetAudienceId) {
            return "<h3>Thiếu tham số dữ liệu thực đơn!</h3>";
        }

        // Lấy thực đơn kèm các món ăn
        $menu = DailyMenu::where('target_audience_id', $targetAudienceId)
            ->where('date', $date)
            ->with('dishes')
            ->first();

        if (!$menu) {
            return "<h3>Thực đơn ngày {$date} hiện chưa được chuẩn bị. Vui lòng quay lại sau!</h3>";
        }

        $audience = TargetAudience::find($targetAudienceId);

        return view('public_menu_view', compact('menu', 'audience', 'date'));
    }

    // Lấy số liệu thống kê cho trang Dashboard của công ty 
    public function getDashboardStats(Request $request)
    {
        try {
            // Hỗ trợ linh hoạt cả Web Session và API Token
            $currentUser = auth('sanctum')->user() ?? auth('web')->user() ?? auth()->user();

            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Yêu cầu đăng nhập hệ thống.'
                ], 401);
            }

            $companyId = $currentUser->company_id ?? $currentUser->id;

            $cardUnitQuery = DB::table('units');
            if (Schema::hasColumn('units', 'company_id')) {
                $cardUnitQuery->where('company_id', $companyId);
            }
            $cardUnitQuery->where(function ($q) {
                $q->where('status', '!=', 'inactive')
                    ->where('status', '!=', '0')
                    ->orWhereNull('status');
            });
            $countClients = $cardUnitQuery->count();


            $tableUnitQuery = DB::table('units');
            if (Schema::hasColumn('units', 'company_id')) {
                $tableUnitQuery->where('company_id', $companyId);
            }
            $clients = $tableUnitQuery->select('id', 'name', 'address', 'status')->get();

            $countEmployees = DB::table('users')
                ->where('role', 'employee')
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->count();

            $countIngredients = DB::table('ingredients')
                ->where('company_id', $companyId)
                ->count();

            // Tính tổng số suất ăn từ bảng thực đơn ngày của công ty
            $servingsQuery = DB::table('daily_menus');
            if (Schema::hasColumn('units', 'company_id')) {
                $servingsQuery->join('units', 'units.id', '=', 'daily_menus.unit_id')
                    ->where('units.company_id', $companyId);
            }

            $servings = $servingsQuery->select(
                DB::raw('SUM(normal_servings) as normal'),
                DB::raw('SUM(vegetarian_servings) as veg'),
                DB::raw('SUM(allergy_servings) as allergy')
            )->first();

            $normal = $servings ? ($servings->normal ?: 0) : 0;
            $veg = $servings ? ($servings->veg ?: 0) : 0;
            $allergy = $servings ? ($servings->allergy ?: 0) : 0;

            $chartData = [
                'labels' => ['Suất bình thường', 'Suất ăn chay', 'Suất ăn dị ứng'],
                'values' => [(int) $normal, (int) $veg, (int) $allergy]
            ];

            return response()->json([
                'status' => 'success',
                'counts' => [
                    'clients' => $countClients, // Số lượng active
                    'employees' => $countEmployees,
                    'ingredients' => $countIngredients
                ],
                'clients' => $clients, // Đầy đủ danh sách kể cả tạm ngưng
                'chart' => $chartData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi kết xuất dữ liệu thống kê: ' . $e->getMessage(),
                'counts' => [
                    'clients' => 0,
                    'employees' => 0,
                    'ingredients' => 0
                ],
                'clients' => [],
                'chart' => [
                    'labels' => ['Suất bình thường', 'Suất ăn chay', 'Suất ăn dị ứng'],
                    'values' => [0, 0, 0]
                ]
            ]);
        }
    }
}