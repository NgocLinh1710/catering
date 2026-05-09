<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dish extends Model
{
    protected $fillable = [
        'name',
        'company_id',
        'created_by',
        'total_calories',
        'total_protein',
        'estimated_cost',
        'calories',
        'protein',
        'lipid',
        'glucid'
    ];

    protected $appends = ['allergy_tags'];

    /**
     * Quan hệ n-n với bảng ingredients
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients')
            ->withPivot('weight')
            ->withTimestamps();
    }

    /**
     * TỰ ĐỘNG GOM TAGS DỊ ỨNG
     * Hàm này sẽ quét tất cả nguyên liệu trong món và trả về danh sách tag không trùng lặp.
     * Ví dụ: Món có Tôm (tag: Hải sản) và Đậu phụ (tag: Chay) -> Món sẽ có tags: ['Hải sản', 'Chay']
     */
    public function getAllergyTagsAttribute()
    {
        return $this->ingredients->pluck('tags')
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * TÍNH GIÁ MÓN ĂN THEO THỜI ĐIỂM
     * @param string|null $date (Định dạng Y-m-d)
     * Hàm này cực kỳ quan trọng để lập kế hoạch tài chính.
     */
    public function calculateCostAtDate($date = null)
    {
        $date = $date ?: now()->format('Y-m-d');
        $totalCost = 0;

        foreach ($this->ingredients as $ingredient) {
            // Tìm giá nguyên liệu (ưu tiên bảng biến động giá)
            $priceRecord = DB::table('ingredient_prices')
                ->where('ingredient_id', $ingredient->id)
                ->where('applied_date', '<=', $date)
                ->orderBy('applied_date', 'desc')
                ->first();

            $unitPrice = $priceRecord ? $priceRecord->price : $ingredient->price_per_kg;

            $weightInKg = $ingredient->pivot->weight / 1000;
            $totalCost += ($weightInKg * $unitPrice);
        }

        return round($totalCost, 2);
    }

    /**
     * TÍNH TOÁN LẠI DINH DƯỠNG TỔNG
     */
    public function recalculateNutrition()
    {
        $calories = 0;
        $protein = 0;
        $lipid = 0;
        $glucid = 0;

        $this->load('ingredients');

        foreach ($this->ingredients as $ingredient) {
            $weightInKg = $ingredient->pivot->weight / 1000;

            $calories += ($ingredient->calories * $weightInKg);
            $protein += ($ingredient->protein * $weightInKg);
            $lipid += ($ingredient->lipid * $weightInKg);
            $glucid += ($ingredient->glucid * $weightInKg);
        }

        $this->update([
            'total_calories' => $calories,
            'total_protein' => $protein,
            'estimated_cost' => $this->calculateCostAtDate(),
            'calories' => $calories,
            'protein' => $protein,
            'lipid' => $lipid,
            'glucid' => $glucid,
        ]);
    }
}