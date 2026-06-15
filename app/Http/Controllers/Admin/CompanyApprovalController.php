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
            return response()->json(['status' => 'error', 'message' => 'Yêu cầu không tồn tại hoặc đã được xử lý!'], 400);
        }

        // Sinh mật khẩu ngẫu nhiên (8 ký tự)
        $randomPassword = Str::random(8);

        // Tạo tài khoản chính thức cho Công ty
        User::create([
            'name' => $company->contact_person,
            'email' => $company->email,
            'password' => Hash::make($randomPassword),
            'role' => 'company', // Cấp quyền Admin của Công ty Catering
        ]);

        // Đổi trạng thái thành Đã duyệt
        $company->status = 'approved';
        $company->save();

        // Trả kết quả về cho FE
        return response()->json([
            'status' => 'success',
            'email' => $company->email,
            'password' => $randomPassword
        ]);
    }

    // Hàm từ chối yêu cầu đăng ký
    public function reject($id)
    {
        // Tìm doanh nghiệp đang ở trạng thái pending
        $company = \App\Models\CompanyRegistration::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Cập nhật trạng thái thành 'rejected'
        $company->update(['status' => 'rejected']);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã từ chối yêu cầu đăng ký của doanh nghiệp này thành công!'
        ]);
    }
}