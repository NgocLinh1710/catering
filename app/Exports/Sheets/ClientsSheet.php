<?php

namespace App\Exports\Sheets;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $companyId;
    protected $from;
    protected $to;

    public function __construct($companyId, $from, $to)
    {
        $this->companyId = $companyId;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $units = Unit::where('company_id', $this->companyId)->get();

        return $units->map(function ($unit) {

            $result = DB::table('daily_menus')
                ->join(
                    'target_audiences',
                    'target_audiences.id',
                    '=',
                    'daily_menus.target_audience_id'
                )
                ->where('daily_menus.unit_id', $unit->id)
                ->whereBetween('daily_menus.date', [
                    $this->from,
                    $this->to
                ])
                ->selectRaw("
                    SUM(
                        (
                            daily_menus.normal_servings +
                            daily_menus.vegetarian_servings +
                            daily_menus.allergy_servings
                        ) * target_audiences.budget_per_serving
                    ) as budget_total,

                    SUM(daily_menus.actual_total_cost) as actual_total
                ")
                ->first();

            return [
                $unit->name,
                $unit->address,
                number_format($result->budget_total ?? 0, 0, ',', '.'),
                number_format($result->actual_total ?? 0, 0, ',', '.'),
                $unit->status === 'active'
                ? 'Đang hợp tác'
                : 'Tạm ngưng hợp tác',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tên đơn vị',
            'Địa chỉ',
            'Tổng ngân sách',
            'Tổng chi phí thực tế',
            'Trạng thái'
        ];
    }

    public function title(): string
    {
        return 'Khách hàng';
    }
}