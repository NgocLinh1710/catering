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

        // Kiểm tra user có tồn tại và pass có khớp không
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác!'
            ], 401);
        }

        // Tạo token
        $token = $user->createToken('CateringAppToken')->plainTextToken;

        // Trả về cho FE
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'user' => $user, // Frontend sẽ dùng cái này để check user.role
            'access_token' => $token
        ]);
    }

    // Hàm logout
    public function logout(Request $request)
    {
        // Hủy toàn bộ token hiện tại của User
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã đăng xuất thành công'
        ]);
    }
}