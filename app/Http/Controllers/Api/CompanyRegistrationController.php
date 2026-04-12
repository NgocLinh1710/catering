<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyRegistration;
use Illuminate\Support\Facades\Validator;

class CompanyRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:company_registrations,email',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        //  Lưu bảng vào "chờ duyệt"
        CompanyRegistration::create([
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã gửi yêu cầu thành công!'
        ]);
    }
}