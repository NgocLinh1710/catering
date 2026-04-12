<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyRegistrationController;

Route::post('/login', [AuthController::class, 'login']);

// API để Công ty Đăng ký
Route::post('/register-company', [CompanyRegistrationController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Quản lý món ăn (Chỉ user đã đăng nhập mới được dùng API này)
    Route::apiResource('dishes', DishController::class);

});