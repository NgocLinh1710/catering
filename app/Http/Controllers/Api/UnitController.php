<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    // Lấy danh sách khách hàng của công ty kèm tổng chi tiêu động dựa trên bộ lọc và năm nhỏ nhất
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $search = $request->search;
        $year = $request->year;
        $month = $request->month;

        $query = Unit::with('employees:id,name,status')
            ->where('company_id', $company_id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });

        $units = $query->orderBy('created_at', 'desc')->paginate(10);

        // Biến đổi tập hợp dữ liệu để tính toán chi tiêu động cho từng khách hàng
        $units->getCollection()->transform(function ($unit) use ($year, $month) {
            $consumptionQuery = DB::table('daily_menus')
                ->join(
                    'target_audiences',
                    'target_audiences.id',
                    '=',
                    'daily_menus.target_audience_id'
                )
                ->where('daily_menus.unit_id', $unit->id);

            if (!empty($year)) {
                $consumptionQuery->whereYear('daily_menus.date', $year);
            }

            if (!empty($month)) {
                $consumptionQuery->whereMonth('daily_menus.date', $month);
            }

            $result = $consumptionQuery->selectRaw("
    SUM(
        (daily_menus.normal_servings
        + daily_menus.vegetarian_servings
        + daily_menus.allergy_servings)
        * target_audiences.budget_per_serving
    ) as budget_total,

    SUM(daily_menus.actual_total_cost) as actual_total
")->first();

            $unit->total_consumption = (float) ($result->budget_total ?? 0);

            $unit->actual_consumption = (float) ($result->actual_total ?? 0);

            return $unit;
        });

        // Tự động tìm năm nhỏ nhất trong Database: Quét bảng daily_menus xem năm xa nhất là năm nào
        $earliestYear = DB::table('daily_menus')->min(DB::raw('YEAR(date)'));
        // Nếu database chưa có dữ liệu thực đơn nào, mặc định lấy năm hiện tại lùi lại 5 năm
        $minYear = $earliestYear ? (int) $earliestYear : ((int) date('Y') - 5);

        return response()->json([
            'status' => 'success',
            'data' => $units->items(),
            'current_page' => $units->currentPage(),
            'last_page' => $units->lastPage(),
            'total' => $units->total(),
            'min_year' => $minYear
        ]);
    }

    // Thêm khách hàng mới (Chặn giới hạn tối đa 5 khách hàng cho gói Free)
    public function store(Request $request)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $user = auth()->user();

        $registration = \App\Models\CompanyRegistration::where('email', $user->email)->first();

        if ($registration) {
            // Kiểm tra xem đã được Admin bấm nút "Mở rộng" chưa
            $isExpanded = str_contains($registration->contact_person, '(Đã mở rộng Quy mô)');

            if (!$isExpanded) {
                // Nếu là gói FREE -> Đếm chuẩn số lượng Khách hàng (Units) mà công ty đã tạo trong DB
                $currentUnitCount = Unit::where('company_id', $company_id)->count();

                // Nếu đã đạt tới hoặc vượt quá giới hạn 5 khách hàng thì CHẶN NGAY lập tức
                if ($currentUnitCount >= 5) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tài khoản Gói Free chỉ được phép tạo tối đa 5 Khách hàng! Vui lòng liên hệ cho Admin để được kích hoạt Mở rộng quy mô.'
                    ], 403);
                }
            }
        }

        // Nếu hợp lệ (Chưa đủ 5 khách hàng hoặc đã được Mở rộng) -> Tiếp tục tạo mới 
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'avg_meals_per_day' => 'nullable|integer',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:users,id'
        ]);

        $data['company_id'] = $company_id;

        $unit = Unit::create($data);

        if (!empty($request->employee_ids)) {
            $unit->employees()->sync($request->employee_ids);
        }

        return response()->json($unit->load('employees'), 201);
    }

    // Cập nhật thông tin khách hàng
    public function update(Request $request, $id)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $unit = Unit::where('company_id', $company_id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:users,id'
        ]);

        $unit->update([
            'name' => $data['name'],
            'address' => $data['address']
        ]);

        if (isset($request->employee_ids)) {
            $unit->employees()->sync($request->employee_ids);
        }

        return response()->json($unit->load('employees'));
    }

    // Hàm phân công nhân viên vào khách hàng
    public function assignEmployees(Request $request, $id)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $unit = Unit::where('company_id', $company_id)->findOrFail($id);

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:users,id'
        ]);

        // Cập nhật danh sách nhân viên (xóa cũ thêm mới)
        $unit->employees()->sync($request->employee_ids);

        return response()->json([
            'message' => 'Phân công nhân sự thành công!',
            'data' => $unit->load('employees:id,name')
        ]);
    }

    public function getMyAssignedUnits()
    {
        $user = auth()->user();

        // Lấy danh sách khách hàng thông qua quan hệ n-n 
        $assignedUnits = $user->units()->where('status', 'active')->get();
        return response()->json($assignedUnits);
    }

    public function destroy($id)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $unit = Unit::where('company_id', $company_id)->findOrFail($id);
        $unit->delete();
        return response()->json(['message' => 'Xóa đơn vị thành công']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $unit = Unit::where('company_id', $company_id)->findOrFail($id);

        $unit->update([
            'status' => $request->status // 'active' hoặc 'inactive'
        ]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'status' => $unit->status
        ]);
    }
}