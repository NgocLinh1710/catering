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
        'category',
        'price',
        'instructions',
        'total_calories',
        'total_protein',
        'lipid',
        'glucid',
        'dish_tags',
        'estimated_cost',
        'servings'
    ];

    protected $appends = [
        'allergy_tags',
        'cost_per_serving',
        'calories_per_serving',
        'protein_per_serving',
        'fat_per_serving',
        'glucid_per_serving',
    ];
    protected $casts = [
        'dish_tags' => 'array',
    ];

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

    public function getCostPerServingAttribute()
    {
        if (($this->servings ?? 1) <= 0) {
            return 0;
        }

        return round($this->estimated_cost / $this->servings, 2);
    }

    public function getCaloriesPerServingAttribute()
    {
        if (($this->servings ?? 1) <= 0) {
            return 0;
        }

        return round($this->total_calories / $this->servings, 2);
    }

    public function getProteinPerServingAttribute()
    {
        if (($this->servings ?? 1) <= 0) {
            return 0;
        }

        return round($this->total_protein / $this->servings, 2);
    }

    public function getFatPerServingAttribute()
    {
        if (($this->servings ?? 1) <= 0) {
            return 0;
        }

        return round($this->lipid / $this->servings, 1);
    }

    public function getGlucidPerServingAttribute()
    {
        if (($this->servings ?? 1) <= 0) {
            return 0;
        }

        return round($this->glucid / $this->servings, 1);
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

    // Tính toán lại dinh dưỡng tổng
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
            'lipid' => $lipid,
            'glucid' => $glucid,
        ]);
    }

    // Quan hệ n-n với bảng daily_menus

    public function dailyMenus()
    {
        return $this->belongsToMany(DailyMenu::class, 'daily_menu_dish', 'dish_id', 'daily_menu_id')
            ->withPivot('quantity', 'meal_type')
            ->withTimestamps();
    }
}