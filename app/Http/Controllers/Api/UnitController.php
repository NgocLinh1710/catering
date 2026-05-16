<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\User;

class UnitController extends Controller
{
    // Lấy danh sách khách hàng của công ty
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $search = $request->search;

        $query = Unit::with('employees:id,name,status')
            ->where('company_id', $company_id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });

        // Phân trang 10 items
        $units = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $units->items(),
            'current_page' => $units->currentPage(),
            'last_page' => $units->lastPage(),
            'total' => $units->total(),
        ]);
    }

    // Thêm khách hàng mới
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'avg_meals_per_day' => 'nullable|integer',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:users,id'
        ]);

        $data['company_id'] = auth()->user()->company_id ?? auth()->user()->id;

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

        // Lấy danh sách khách hàng thông qua quan hệ n-n đã định nghĩa ở User Model
        $units = $user->units()->where('status', 'active')->get();

        return response()->json($units);
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