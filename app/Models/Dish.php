<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fiber',
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
        'fiber_per_serving',
    ];

    protected $casts = [
        'dish_tags' => 'array',
    ];

    // Quan hệ nguyên liệu
    public function ingredients()
    {
        return $this->belongsToMany(
            Ingredient::class,
            'dish_ingredients'
        )
            ->withPivot('weight')
            ->withTimestamps();
    }

    // Gom tag dị ứng
    public function getAllergyTagsAttribute()
    {
        return $this->ingredients
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    // Giá vốn / suất
    public function getCostPerServingAttribute()
    {
        if (($this->servings ?? 0) <= 0) {
            return 0;
        }

        $date = request()->get('date')
            ?? now('Asia/Ho_Chi_Minh')
                ->format('Y-m-d');

        return round(
            $this->calculateCostAtDate($date)
            /
            $this->servings,
            2
        );
    }

    // Dinh dưỡng / suất
    public function getCaloriesPerServingAttribute()
    {
        return $this->servings > 0
            ? round($this->total_calories / $this->servings, 2)
            : 0;
    }

    public function getProteinPerServingAttribute()
    {
        return $this->servings > 0
            ? round($this->total_protein / $this->servings, 2)
            : 0;
    }

    public function getFatPerServingAttribute()
    {
        return $this->servings > 0
            ? round($this->lipid / $this->servings, 1)
            : 0;
    }

    public function getGlucidPerServingAttribute()
    {
        return $this->servings > 0
            ? round($this->glucid / $this->servings, 1)
            : 0;
    }

    public function getFiberPerServingAttribute()
    {
        return $this->servings > 0
            ? round($this->fiber / $this->servings, 1)
            : 0;
    }

    // Tính giá món theo ngày
    public function calculateCostAtDate($date = null)
    {
        $date = $date
            ??
            now('Asia/Ho_Chi_Minh')
                ->format('Y-m-d');

        $totalCost = 0;

        foreach ($this->ingredients as $ingredient) {
            // lấy giá lịch sử mới nhất <= ngày chọn
            $unitPrice = $ingredient
                ->getPriceAtDate($date);

            // gram -> kg
            $weightKg =
                $ingredient->pivot->weight / 1000;

            $totalCost +=
                $weightKg * $unitPrice;
        }
        return round($totalCost, 2);
    }

    // Tính lại dinh dưỡng món
    public function recalculateNutrition()
    {
        $calories = 0;
        $protein = 0;
        $lipid = 0;
        $glucid = 0;
        $fiber = 0;
        $this->load('ingredients');

        foreach ($this->ingredients as $ingredient) {
            $weightKg = (float) $ingredient->pivot->weight / 1000;

            $calories += (float) $ingredient->calories * $weightKg;

            $protein += (float) $ingredient->protein * $weightKg;
            $lipid += (float) $ingredient->lipid * $weightKg;
            $glucid += (float) $ingredient->glucid * $weightKg;
            $fiber += (float) $ingredient->fiber * $weightKg;
        }

        $this->update([
            'total_calories' => $calories,
            'total_protein' => $protein,
            'lipid' => $lipid,
            'glucid' => $glucid,
            'fiber' => $fiber,

            // lưu giá vốn tại thời điểm tạo/sửa
            'estimated_cost' =>
                $this->calculateCostAtDate(
                    now('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d')
                ),

        ]);

    }

    // Daily menu

    public function dailyMenus()
    {
        return $this->belongsToMany(
            DailyMenu::class,
            'daily_menu_dish',
            'dish_id',
            'daily_menu_id'
        )
            ->withPivot(
                'quantity',
                'meal_type'
            )
            ->withTimestamps();
    }
}