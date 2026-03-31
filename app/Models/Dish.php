<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'instructions',
        'total_calories',
        'dish_tags'
    ];

    protected $casts = [
        'dish_tags' => 'array',
    ];

    // Mối quan hệ: 1 món ăn bao gồm nhiều thực phẩm
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients')
            ->withPivot('quantity') // Lấy thêm cột định lượng ở bảng trung gian
            ->withTimestamps();
    }
}
