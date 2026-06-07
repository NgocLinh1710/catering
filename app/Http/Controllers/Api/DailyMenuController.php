<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMenu;
use App\Models\TargetAudience;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;

class DailyMenuController extends Controller
{
    /**
     * Lấy thực đơn của ngày được chọn
     */
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

    /**
     * Lưu / cập nhật thực đơn tách biệt suất ăn (CẤU TRÚC JSON PHÂN NHÓM DỊ ỨNG)
     */
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

    /**
     * MODULE 7: TỰ ĐỘNG TỐI ƯU HÓA THỰC ĐƠN BẰNG QUY HOẠCH TUYẾN TÍNH
     */
    public function autoGenerateMenu(Request $request)
    {
        $request->validate([
            'target_audience_id' => 'required|exists:target_audiences,id',
            'forbidden_keywords' => 'nullable|array',
            'all_dishes' => 'required|array'
        ]);

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

        // Tạo và ghi dữ liệu ra file tạm để tránh lỗi Broken Pipe trên Windows XAMPP
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
}
