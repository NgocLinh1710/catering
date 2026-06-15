<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác!'
            ], 401);
        }

        // Kiểm tra trạng thái tài khoản của chính user đó
        if (isset($user->status) && $user->status === 'locked') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị khóa! Vui lòng liên hệ Quản lý hoặc Admin để biết thêm chi tiết.'
            ], 403);
        }

        // Nếu là nhân viên, kiểm tra xem Doanh nghiệp chủ quản có đang bị khóa không -> Nếu có thì khóa cả tài khoản Nhân viên
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
}