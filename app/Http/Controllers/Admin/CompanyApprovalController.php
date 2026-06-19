<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyApprovalController extends Controller
{
    public function index()
    {
        // Lấy tất cả các công ty đang chờ duyệt, sắp xếp mới nhất lên đầu
        $pendingCompanies = CompanyRegistration::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.company_approvals.index', compact('pendingCompanies'));
    }

    public function approve($id)
    {
        $company = CompanyRegistration::find($id);

        if (!$company || $company->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Yêu cầu không tồn tại hoặc đã được xử lý!'
            ], 400);
        }

        // Xóa tất cả bản ghi rejected cùng email
        CompanyRegistration::where('email', $company->email)
            ->where('status', 'rejected')
            ->delete();

        // Sinh mật khẩu ngẫu nhiên
        $randomPassword = Str::random(8);

        // Tạo tài khoản doanh nghiệp
        User::create([
            'name' => $company->contact_person,
            'email' => $company->email,
            'password' => Hash::make($randomPassword),
            'role' => 'company',
            'status' => 'active'
        ]);

        // Chuyển bản ghi hiện tại sang active
        $company->status = 'active';
        $company->save();

        return response()->json([
            'status' => 'success',
            'email' => $company->email,
            'password' => $randomPassword
        ]);
    }

    // Hàm từ chối yêu cầu đăng ký
    public function reject($id)
    {
        $company = CompanyRegistration::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Yêu cầu không tồn tại hoặc đã được xử lý!'
            ], 400);
        }

        // Xóa khỏi danh sách đăng ký
        $company->status = 'rejected';
        $company->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã từ chối và xóa yêu cầu đăng ký!'
        ]);
    }
}