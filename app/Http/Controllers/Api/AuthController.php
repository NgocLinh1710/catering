<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CompanyRegistration;

class AuthController extends Controller
{
    // Hàm login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Chưa có tài khoản chính thức
        if (!$user) {

            // Đang chờ duyệt
            $pendingRequest = CompanyRegistration::where('email', $request->email)
                ->where('status', 'pending')
                ->exists();

            if ($pendingRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Yêu cầu đăng ký của bạn đang chờ quản trị viên phê duyệt.'
                ], 403);
            }

            // Đã bị từ chối
            $rejectedRequest = CompanyRegistration::where('email', $request->email)
                ->where('status', 'rejected')
                ->exists();

            if ($rejectedRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Yêu cầu đăng ký của bạn đã bị từ chối. Vui lòng đăng ký lại hoặc liên hệ quản trị viên.'
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác!'
            ], 401);
        }

        // Sai mật khẩu
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác!'
            ], 401);
        }

        // Tài khoản bị khóa
        if (isset($user->status) && $user->status === 'locked') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị khóa! Vui lòng liên hệ Quản lý hoặc Admin để biết thêm chi tiết.'
            ], 403);
        }

        // Nhân viên thuộc công ty bị khóa
        if (!in_array($user->role, ['admin', 'company', 'company_admin'])) {

            if (!empty($user->company_id)) {

                $companyOwner = User::find($user->company_id);

                if ($companyOwner && $companyOwner->status === 'locked') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Hệ thống tạm thời gián đoạn do tài khoản Doanh nghiệp chủ quản đã bị khóa! Vui lòng liên hệ đại diện công ty của bạn.'
                    ], 403);
                }
            }
        }

        $token = $user->createToken('CateringAppToken')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'access_token' => $token
        ]);
    }

    // Hàm logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã đăng xuất thành công'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->password_change_deadline = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Đổi mật khẩu thành công.'
        ]);
    }
}