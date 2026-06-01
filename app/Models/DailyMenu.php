<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyMenu extends Model
{
    protected $fillable = [
        'unit_id',
        'target_audience_id',
        'date',
        'servings',
        'normal_servings',
        'vegetarian_servings',
        'allergy_servings',
        'allergy_notes'
    ];

    protected $casts = [
        'allergy_notes' => 'array',
        'date' => 'date'
    ];

    // Mối quan hệ n - n với Dish (Món ăn) 
    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'daily_menu_dish', 'daily_menu_id', 'dish_id')
            ->withPivot('quantity', 'meal_type')
            ->withTimestamps();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function targetAudience()
    {
        return $this->belongsTo(TargetAudience::class);
    }
}