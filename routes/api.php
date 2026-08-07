<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\TargetAudienceController;
use App\Http\Controllers\Api\DailyMenuController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Admin\CompanyApprovalController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Company\ExcelReportController;

// PUBLIC ROUTES (Không cần đăng nhập)

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-company', [CompanyRegistrationController::class, 'store']);

// Route Thống kê Tổng quan đa xác thực 
Route::get('/company/dashboard-stats', [DailyMenuController::class, 'getDashboardStats']);

Route::middleware('auth:sanctum')->group(function () {

    // Lấy thông tin tài khoản đang đăng nhập
    Route::get('/user', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
            'must_change_password' => $request->user()->must_change_password,
            'password_change_deadline' => $request->user()->password_change_deadline,
        ]);
    });

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);

    // Quản lý Nguyên liệu (Ingredients)
    Route::get('/ingredients/all', [IngredientController::class, 'all']);
    Route::post('/ingredients/update-price', [IngredientController::class, 'updatePrice']);
    Route::apiResource('ingredients', IngredientController::class);

    // Quản lý Món ăn (Dishes)
    Route::get('/quan-ly-mon-an/all', [DishController::class, 'all']);
    Route::apiResource('dishes', DishController::class);

    // Quản lý Nhân viên (Employees)
    Route::patch('employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);
    Route::apiResource('employees', EmployeeController::class);

    // Quản lý Khách hàng (Units)
    Route::get('my-thiet-lap-tieu-chuan', [UnitController::class, 'getMyAssignedUnits']);
    Route::post('units/{id}/assign-employees', [UnitController::class, 'assignEmployees']);
    Route::post('/units/{id}/toggle-status', [UnitController::class, 'toggleStatus']);
    Route::apiResource('units', UnitController::class);

    // Quản lý Đối tượng ăn và Tiêu chuẩn (Target Audiences)
    Route::get('units/{unitId}/target-audiences', [TargetAudienceController::class, 'index']);
    Route::apiResource('target-audiences', TargetAudienceController::class)->except(['index']);

    // Quản lý Thực đơn (Daily Menus)
    Route::get('daily-menus/by-date', [DailyMenuController::class, 'getMenuByDate']);
    Route::post('daily-menus', [DailyMenuController::class, 'store']);
    Route::post('/daily-menus/auto-generate', [DailyMenuController::class, 'autoGenerateMenu']);

    // ADMIN
    Route::prefix('admin')->group(function () {

        // Duyệt đơn đăng ký doanh nghiệp mới
        Route::get('/pending-companies', [CompanyRegistrationController::class, 'getPendingCompanies']); // Phân trang + Tìm kiếm yêu cầu mới
        Route::post('/approve-company/{id}', [CompanyApprovalController::class, 'approve']); // Kích hoạt cấp tài khoản
        Route::post('/reject-company/{id}', [CompanyApprovalController::class, 'reject']);

        // Quản lý các doanh nghiệp đang hoạt động trên hệ thống
        Route::get('/companies', [AdminController::class, 'getCompanies']);
        Route::post('/companies/{id}/toggle-lock', [AdminController::class, 'toggleLock']);
        Route::delete('/companies/{id}', [AdminController::class, 'deleteCompany']);
        Route::post('/companies/{id}/upgrade-scale', [AdminController::class, 'upgradeScale']);

        // Duyệt các yêu cầu chỉnh sửa thông tin gửi từ Doanh nghiệp
        Route::get('/company-update-requests', [AdminController::class, 'getUpdateRequests']);
        Route::post('/company-update-requests/{id}/{action}', [AdminController::class, 'handleUpdateRequest']);
    });

    Route::post('/ai/chat', [AiChatController::class, 'chat']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Xuất báo cáo Excel
    Route::post('/company/export-report', [ExcelReportController::class, 'export']);
});