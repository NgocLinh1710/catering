<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CompanyRegistration;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Hàm xử lý KHÓA / MỞ KHÓA tài khoản Doanh nghiệp
    public function toggleLock($id)
    {
        $user = User::findOrFail($id);
        $newStatus = ($user->status === 'locked') ? 'active' : 'locked';
        $user->update(['status' => $newStatus]);

        $registration = CompanyRegistration::where('email', $user->email)->first();
        if ($registration) {
            $registration->update(['status' => $newStatus]);
        }

        if ($newStatus === 'locked') {
            $user->tokens()->delete();

            // Tìm tất cả user có company_id bằng với ID của Doanh nghiệp vừa bị khóa -> khóa tài khoản Nhân viên
            $employeeIds = User::where('company_id', $user->id)->pluck('id');

            if ($employeeIds->count() > 0) {
                \DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\Models\User')
                    ->whereIn('tokenable_id', $employeeIds)
                    ->delete();
            }
        }

        $message = $newStatus === 'locked' ? 'Đã KHÓA tài khoản doanh nghiệp và toàn bộ nhân viên liên quan thành công!' : 'Đã MỞ KHÓA tài khoản doanh nghiệp thành công!';
        return response()->json(['success' => true, 'message' => $message]);
    }

    // Hàm xử lý XÓA doanh nghiệp
    public function deleteCompany($id)
    {
        $user = User::findOrFail($id);
        CompanyRegistration::where('email', $user->email)->delete();
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa vĩnh viễn doanh nghiệp khỏi hệ thống!']);
    }

    // Hàm xử lý NÂNG CẤP / MỞ RỘNG QUY MÔ Khách hàng
    public function upgradeScale($id)
    {
        $user = User::findOrFail($id);
        $registration = CompanyRegistration::where('email', $user->email)->first();

        if ($registration) {
            $registration->update(['contact_person' => $registration->contact_person . ' (Đã mở rộng Quy mô)']);

            return response()->json(['success' => true, 'message' => 'Đã kích hoạt mở rộng quy mô Khách hàng thành công cho doanh nghiệp!']);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu doanh nghiệp.'], 404);
    }

    public function getCompanies(Request $request)
    {
        $search = $request->search;

        $companies = User::query()
            ->select('users.*', 'company_registrations.company_name', 'company_registrations.contact_person')
            ->leftJoin('company_registrations', 'company_registrations.email', '=', 'users.email')
            ->whereIn('users.role', ['company', 'company_admin'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_registrations.company_name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('company_registrations.contact_person', 'like', "%{$search}%");
                });
            })
            ->orderBy('users.created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $companies->items(),
            'current_page' => $companies->currentPage(),
            'last_page' => $companies->lastPage(),
            'total' => $companies->total()
        ]);
    }
}