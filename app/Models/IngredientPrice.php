<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientPrice extends Model
{
    protected $table = 'ingredient_prices';

    protected $fillable = [
        'ingredient_id',
        'price',
        'applied_date'
    ];
}