<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use App\Models\Dish;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 3;

        // Tạo dữ liệu thực phẩm (Ingredients)
        $gao = Ingredient::updateOrCreate(
            ['name' => 'Gạo trắng', 'company_id' => $companyId],
            [
                'unit' => 'kg',
                'calories' => 130,
                'protein' => 2.7,
                'lipid' => 0.3,
                'glucid' => 28,
                'fiber' => 0.4,
                'current_price' => 18000,
                'tags' => ['vegan', 'starch']
            ]
        );

        $thitGa = Ingredient::updateOrCreate(
            ['name' => 'Thịt gà công nghiệp', 'company_id' => $companyId],
            [
                'unit' => 'kg',
                'calories' => 239,
                'protein' => 27,
                'lipid' => 14,
                'glucid' => 0,
                'fiber' => 0,
                'current_price' => 65000,
                'tags' => ['meat', 'halal']
            ]
        );

        // Tạo món ăn (Dish)
        $comGa = Dish::create([
            'company_id' => $companyId,
            'name' => 'Cơm gà luộc',
            'category' => 'Món chính',
            'price' => 35000,
            'instructions' => 'Nấu cơm chín, gà luộc thái miếng vừa ăn.',
            'calories' => 500,
            'protein' => 30.0,
            'lipid' => 15.0,
            'glucid' => 45.0,
            'dish_tags' => ['meat', 'halal', 'starch']
        ]);

        // Ghép thực phẩm vào món ăn (Bảng trung gian)
        if (method_exists($comGa, 'ingredients')) {
            $comGa->ingredients()->attach([
                $gao->id => ['quantity' => 0.15],
                $thitGa->id => ['quantity' => 0.1],
            ]);
        }

        $this->command->info('Đã tạo thành công dữ liệu Gạo, Thịt gà và món Cơm Gà với đầy đủ thông tin dinh dưỡng!');
    }
}