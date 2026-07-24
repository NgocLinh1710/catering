<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompanyApprovedMail;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();

        try {

            CompanyRegistration::where('email', $company->email)
                ->where('status', 'rejected')
                ->delete();

            $randomPassword = Str::random(8);

            $user = User::create([
                'name' => $company->contact_person,
                'email' => $company->email,
                'password' => Hash::make($randomPassword),
                'role' => 'company',
                'status' => 'active',
                'must_change_password' => true,
                'password_change_deadline' => now()->addHours(48),
            ]);

            try {
                Mail::to($user->email)->send(
                    new CompanyApprovedMail($user, $randomPassword)
                );
            } catch (\Throwable $e) {

                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }

            $company->status = 'active';
            $company->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'email' => $company->email,
                'password' => $randomPassword
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('Approve company failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể gửi email phê duyệt: ' . $e->getMessage(),
            ], 500);
        }
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