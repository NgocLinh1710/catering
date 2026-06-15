<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Sử dụng Join để lấy dữ liệu kết hợp từ cả 2 bảng thông qua Email trùng nhau
        $companies = User::whereIn('users.role', ['company', 'company_admin'])
            ->join('company_registrations', 'users.email', '=', 'company_registrations.email')
            ->select(
                'users.id as id',
                'company_registrations.company_name as company_name',
                'company_registrations.contact_person as contact_person',
                'users.email as email',
                'company_registrations.phone as phone',
                'users.status as status'
            )
            ->get();

        return view('admin.dashboard', compact('companies'));
    }
}