<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\User;

class UnitController extends Controller
{
    // Lấy danh sách đơn vị của công ty
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;

        $search = $request->search;

        $units = Unit::with('employees:id,name,status')
            ->where('company_id', $company_id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json($units);
    }

    // Thêm đơn vị mới
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'avg_meals_per_day' => 'nullable|integer',
            'employee_ids' => 'nullable|array', // Thêm dòng này
            'employee_ids.*' => 'exists:users,id'
        ]);

        $data['company_id'] = auth()->user()->company_id ?? auth()->user()->id;

        $unit = Unit::create($data);

        if (!empty($request->employee_ids)) {
            $unit->employees()->sync($request->employee_ids);
        }

        return response()->json($unit->load('employees'), 201);
    }

    // Cập nhật thông tin đơn vị
    public function update(Request $request, $id)
    {
        $company_id = auth()->user()->company_id ?? auth()->user()->id;
        $unit = Unit::where('company_id', $company_id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'avg_meals_per_day' => 'nullable|integer',
            'meal_price' => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);

        $unit->update($data);
        return response()->json($unit);
    }

    // Hàm phân công nhân viên vào đơn vị
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

        // Lấy danh sách đơn vị thông qua quan hệ n-n đã định nghĩa ở User Model
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
}