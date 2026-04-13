<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CompanyApprovalController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/quan-ly-mon-an', function () {
    return view('dishes');
});

Route::get('/tong-quan', function () {
    return "Đây là trang Tổng quan dành cho Admin!";
});

Route::get('/dang-ky', function () {
    return view('register');
});

Route::get('/admin/duyet-cong-ty', [CompanyApprovalController::class, 'index']);