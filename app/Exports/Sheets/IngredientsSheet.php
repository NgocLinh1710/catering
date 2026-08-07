<?php

namespace App\Exports\Sheets;

use App\Models\Ingredient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class IngredientsSheet implements FromCollection, WithHeadings, WithTitle
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
        $rows = collect();

        $ingredients = Ingredient::where(
            'company_id',
            $this->companyId
        )
            ->with('prices')
            ->get();

        foreach ($ingredients as $ingredient) {
            $priceAtStart = $ingredient->prices()
                ->where(
                    'applied_date',
                    '<=',
                    $this->from
                )
                ->orderByDesc('applied_date')
                ->orderByDesc('id')
                ->first();

            $rows->push([
                $ingredient->name,
                $ingredient->unit,
                $this->from,
                $priceAtStart
                ? $priceAtStart->price
                : $ingredient->price_per_kg
            ]);

            /*
             * Lấy toàn bộ lịch sử thay đổi giá
             * trong khoảng thời gian xuất
             */
            $priceChanges = $ingredient->prices()
                ->whereBetween(
                    'applied_date',
                    [
                        $this->from,
                        $this->to
                    ]
                )
                ->orderBy('applied_date')
                ->orderBy('id')
                ->get();

            foreach ($priceChanges as $change) {
                if ($change->applied_date == $this->from) {
                    continue;
                }

                $rows->push([
                    $ingredient->name,
                    $ingredient->unit,
                    $change->applied_date,
                    $change->price
                ]);
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tên nguyên liệu',
            'Đơn vị',
            'Ngày áp dụng',
            'Giá'
        ];
    }

    public function title(): string
    {
        return 'Nguyên liệu';
    }
}