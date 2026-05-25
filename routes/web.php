<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CompanyApprovalController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/tong-quan', function () {
    return "Đây là trang Tổng quan dành cho Admin!";
});

Route::get('/dang-ky', function () {
    return view('register');
});

Route::get('/admin/duyet-cong-ty', [CompanyApprovalController::class, 'index']);

Route::get('/cong-ty/tong-quan', function () {
    return view('company.dashboard');
});

Route::get('/quan-ly-khach-hang', function () {
    return view('units');
})->name('customer.index');

Route::get('/quan-ly-nguyen-lieu', function () {
    return view('ingredients');
});

Route::get('/quan-ly-nhan-vien', function () {
    return view('employees');
});

Route::get('/quan-ly-mon-an', function () {
    return view('dishes');
});

Route::get('/thiet-lap-tieu-chuan', function () {
    return view('assigned_units');
});

Route::get('/lap-thuc-don', function () {
    return view('menu_planner');
});
