<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\CompanyReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelReportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'reports' => 'required|array|min:1',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $companyId = auth()->user()->company_id ?? auth()->id();

        try {

            $fileName = 'BaoCao_' .
                date('d-m-Y', strtotime($request->from)) .
                '_den_' .
                date('d-m-Y', strtotime($request->to)) .
                '.xlsx';

            return Excel::download(
                new CompanyReportExport(
                    $companyId,
                    $request->from,
                    $request->to,
                    $request->reports
                ),
                $fileName
            );

        } catch (\Throwable $e) {

            return response()->json([
                'message' => 'Không thể xuất báo cáo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}