<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Admin\CompanyApprovalController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\TargetAudienceController;
use App\Http\Controllers\Api\DailyMenuController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-company', [CompanyRegistrationController::class, 'store']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/ingredients/update-price', [IngredientController::class, 'updatePrice']);
    Route::apiResource('ingredients', IngredientController::class);

    // Quản lý món ăn
    Route::apiResource('dishes', DishController::class);
    Route::get('quan-ly-mon-an', [DishController::class, 'index']);

    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);

    Route::apiResource('units', UnitController::class);
    Route::post('units/{id}/assign-employees', [UnitController::class, 'assignEmployees']);
    Route::get('my-thiet-lap-tieu-chuan', [UnitController::class, 'getMyAssignedUnits']);
    Route::post('/units/{id}/toggle-status', [UnitController::class, 'toggleStatus']);

    // Quản lý Đối tượng ăn & Tiêu chuẩn
    Route::get('units/{unitId}/target-audiences', [TargetAudienceController::class, 'index']);
    Route::apiResource('target-audiences', TargetAudienceController::class)->except(['index']);

    // Quản lý Thực đơn mỗi ngày
    Route::get('daily-menus/by-date', [DailyMenuController::class, 'getMenuByDate']);
    Route::post('daily-menus', [DailyMenuController::class, 'store']);
});

// Admin Route
Route::post('/admin/approve-company/{id}', [CompanyApprovalController::class, 'approve']);