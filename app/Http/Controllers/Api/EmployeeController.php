<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $query = User::where('role', 'employee')
            ->where('company_id', $companyId);

        // Tìm kiếm:
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Phân trang: Mỗi trang 10 nhân viên
        $employees = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $employees->items(), // Lấy danh sách nhân viên
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'total' => $employees->total(),
        ]);
    }

    // Lưu Nhân viên mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'company_id' => $companyId
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm nhân viên thành công',
            'data' => $employee
        ]);
    }

    // Cập nhật thông tin nhân viên
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        // Tìm đúng nhân viên thuộc công ty mình
        $employee = User::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            // Email duy nhất nhưng ngoại trừ ID hiện tại
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $employee->name = $request->name;
        $employee->email = $request->email;

        // Nếu có nhập mật khẩu mới thì mới đổi
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }

        $employee->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật nhân viên thành công'
        ]);
    }

    // Hàm Khóa/Mở khóa (Cập nhật status)
    public function toggleStatus($id)
    {
        $employee = User::where('id', $id)->firstOrFail();
        $employee->status = ($employee->status === 'inactive') ? 'active' : 'inactive';
        $employee->save();

        return response()->json([
            'status' => 'success',
            'message' => $employee->status === 'active' ? 'Đã mở khóa' : 'Đã khóa tài khoản'
        ]);
    }

    // Xóa tài khoản nhân viên vĩnh viễn
    public function destroy($id)
    {
        $employee = User::where('id', $id)->firstOrFail();
        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa nhân viên vĩnh viễn'
        ]);
    }
}