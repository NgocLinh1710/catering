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

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-company', [CompanyRegistrationController::class, 'store']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('ingredients', IngredientController::class);

    Route::apiResource('dishes', DishController::class);

    Route::apiResource('employees', EmployeeController::class);
    Route::patch('employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);

    Route::apiResource('units', UnitController::class);
    Route::post('units/{id}/assign-employees', [UnitController::class, 'assignEmployees']);
    Route::get('my-assigned-units', [UnitController::class, 'getMyAssignedUnits']);
});

// Admin Route
Route::post('/admin/approve-company/{id}', [CompanyApprovalController::class, 'approve']);