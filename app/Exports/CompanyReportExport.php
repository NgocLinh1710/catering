<?php

namespace App\Exports;

use App\Exports\Sheets\ClientsSheet;
use App\Exports\Sheets\IngredientsSheet;
use App\Exports\Sheets\MenusSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompanyReportExport implements WithMultipleSheets
{
    protected $companyId;
    protected $from;
    protected $to;
    protected $types;

    public function __construct($companyId, $from, $to, $types)
    {
        $this->companyId = $companyId;
        $this->from = $from;
        $this->to = $to;
        $this->types = $types;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Danh sách khách hàng + ngân sách + chi phí thực tế
        if (in_array('clients', $this->types)) {
            $sheets[] = new ClientsSheet(
                $this->companyId,
                $this->from,
                $this->to
            );
        }

        // Sheet 2: Lịch sử thay đổi giá nguyên liệu trong khoảng thời gian
        if (in_array('ingredients', $this->types)) {
            $sheets[] = new IngredientsSheet(
                $this->companyId,
                $this->from,
                $this->to
            );
        }

        // Sheet 3: Chi tiết thực đơn theo ngày
        if (in_array('menus', $this->types)) {
            $sheets[] = new MenusSheet(
                $this->companyId,
                $this->from,
                $this->to
            );
        }

        return $sheets;
    }
}