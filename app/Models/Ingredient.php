<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'unit',
        'calories',
        'protein',
        'lipid',
        'glucid',
        'fiber',
        'price_per_kg', // Đây là giá hiện hành
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    // Mối quan hệ: 1 thực phẩm có thể nằm trong nhiều món ăn
    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_ingredients')
            ->withPivot('weight')
            ->withTimestamps();
    }

    public function prices()
    {
        return $this->hasMany(IngredientPrice::class);
    }

    public function getPriceAtDate($date = null)
    {
        $date = $date ?: now('Asia/Ho_Chi_Minh')->toDateString();

        return $this->prices()
            ->where('applied_date', '<=', $date)
            ->orderByDesc('applied_date')
            ->orderByDesc('id')
            ->value('price')
            ?? $this->price_per_kg;
    }

    public function getCurrentPrice()
    {
        return $this->getPriceAtDate(
            now()
                ->timezone('Asia/Ho_Chi_Minh')
                ->format('Y-m-d')
        );
    }

    public function priceHistory()
    {
        return $this->hasMany(
            IngredientPrice::class
        );
    }
}