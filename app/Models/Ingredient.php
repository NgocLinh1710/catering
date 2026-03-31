<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;
    // Các cột được phép thêm dữ liệu
    protected $fillable = [
        'company_id',
        'name',
        'unit',
        'calories',
        'protein',
        'fat',
        'carb',
        'fiber',
        'current_price',
        'tags'
    ];

    // Ép kiểu JSON (MySQL) thành Array (PHP)
    protected $casts = [
        'tags' => 'array',
    ];

    // Mối quan hệ: 1 thực phẩm có thể nằm trong nhiều món ăn
    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_ingredients')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
