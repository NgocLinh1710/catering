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

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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
            'data' => $employees->items(),
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'total' => $employees->total(),
        ]);
    }

    // Lưu Nhân viên mới
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'min:6',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
                ]
            ],
            [
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
                'password.regex' => 'Mật khẩu phải chứa cả chữ cái và số.'
            ]
        );

        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'company_id' => $companyId,
            'status' => 'active'
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

        $employee = User::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => [
                'nullable',
                'min:6',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
            ]
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa cả chữ cái và số.'
        ]);

        $employee->name = $request->name;
        $employee->email = $request->email;

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
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employee = User::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

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
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employee = User::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        try {

            \DB::beginTransaction();

            \DB::table('unit_user')
                ->where('user_id', $employee->id)
                ->delete();

            \App\Models\Dish::where('created_by', $employee->id)
                ->delete();

            \App\Models\Dish::where('company_id', $employee->id)
                ->delete();

            $employee->delete();

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa nhân viên và toàn bộ dữ liệu liên quan.'
            ]);

        } catch (\Exception $e) {

            \DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}