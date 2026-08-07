<?php

namespace App\Exports\Sheets;

use App\Models\DailyMenu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MenusSheet implements FromCollection, WithHeadings, WithTitle
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
        return DailyMenu::with([
            'unit',
            'targetAudience'
        ])

            ->whereHas('unit', function ($q) {
                $q->where(
                    'company_id',
                    $this->companyId
                );
            })

            ->whereBetween('date', [
                $this->from,
                $this->to
            ])
            ->orderBy('date')
            ->get()
            ->map(function ($menu) {
                $totalCost = (float) ($menu->actual_total_cost ?? 0);
                $servings = (int) ($menu->servings ?? 0);

                $averageCost = $servings > 0
                    ? round($totalCost / $servings, 2)
                    : 0;

                return [
                    $menu->date->format('d/m/Y'),
                    $menu->unit?->name ?? '',
                    $menu->targetAudience?->name ?? '',
                    $servings,
                    $totalCost,
                    $averageCost
                ];
            });
    }


    public function headings(): array
    {
        return [
            'Ngày',
            'Khách hàng',
            'Đối tượng',
            'Số suất',
            'Tổng chi phí',
            'Trung bình chi phí/suất'
        ];
    }

    public function title(): string
    {
        return 'Thực đơn';
    }
}