<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employees = User::where('role', 'employee')
            ->where('company_id', $companyId)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $employees
        ]);
    }

    // Lưu Nhân viên mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $currentUser = auth()->user();

        $companyId = $currentUser->company_id ?? $currentUser->id;

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'company_id' => $companyId
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm nhân viên thành công',
            'data' => $employee
        ]);
    }
}