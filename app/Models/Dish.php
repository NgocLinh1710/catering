<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $fillable = ['name', 'company_id', 'created_by', 'total_calories', 'total_protein', 'estimated_cost'];

    public function ingredients()
    {
        // Quan hệ n-n với bảng ingredients thông qua bảng trung gian dish_ingredients
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients')
            ->withPivot('weight');
    }
}