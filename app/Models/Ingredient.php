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

    public function getPriceAtDate($date)
    {
        // Tìm bản ghi giá có ngày áp dụng gần nhất nhưng không vượt quá ngày cần tra cứu
        $historicalPrice = $this->prices()
            ->where('applied_date', '<=', $date)
            ->orderBy('applied_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Nếu tìm thấy giá trong lịch sử thì lấy, không thì lấy giá hiện hành (price_per_kg)
        return $historicalPrice ? $historicalPrice->price : $this->price_per_kg;
    }
}