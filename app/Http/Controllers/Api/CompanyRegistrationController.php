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
        $validator = Validator::make(
            $request->all(),
            [
                'company_name' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|digits:10',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $emailExists = CompanyRegistration::where('email', $request->email)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($emailExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email đã được đăng ký.'
            ], 400);
        }

        $phoneExists = CompanyRegistration::where('phone', $request->phone)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($phoneExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số điện thoại đã được đăng ký.'
            ], 400);
        }

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

    public function getPendingCompanies(Request $request)
    {
        $search = $request->search;

        $pending = CompanyRegistration::query()
            ->where('status', 'pending')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $pending->items(),
            'current_page' => $pending->currentPage(),
            'last_page' => $pending->lastPage(),
            'total' => $pending->total()
        ]);
    }
}