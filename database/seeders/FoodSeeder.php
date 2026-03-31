<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use App\Models\Dish;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // Giả sử company_id = 1 (test)
        $companyId = 1;

        // 1. Tạo dữ liệu thực phẩm (Ingredients)
        $gao = Ingredient::create([
            'company_id' => $companyId,
            'name' => 'Gạo trắng',
            'unit' => 'kg',
            'calories' => 130,
            'protein' => 2.7,
            'fat' => 0.3,
            'carb' => 28,
            'fiber' => 0.4,
            'current_price' => 18000,
            'tags' => ['vegan', 'starch']
        ]);

        $thitGa = Ingredient::create([
            'company_id' => $companyId,
            'name' => 'Thịt gà công nghiệp (Ước lượng thịt lọc)',
            'unit' => 'kg',
            'calories' => 239,
            'protein' => 27,
            'fat' => 14,
            'carb' => 0,
            'fiber' => 0,
            'current_price' => 65000,
            'tags' => ['meat', 'halal']
        ]);

        // 2. Tạo món ăn (Dish)
        $comGa = Dish::create([
            'company_id' => $companyId,
            'name' => 'Cơm gà luộc',
            'instructions' => 'Nấu cơm chín, gà luộc thái miếng vừa ăn.',
            'total_calories' => 500, // Tạm tính thủ công, sau này sẽ viết code tự cộng
            'dish_tags' => ['meat', 'halal', 'starch']
        ]);

        // 3. Ghép thực phẩm vào món ăn (Bảng trung gian)
        // Ví dụ 1 suất cơm gà cần: 0.15kg gạo (150g) và 0.1kg thịt gà (100g)
        $comGa->ingredients()->attach([
            $gao->id => ['quantity' => 0.15],
            $thitGa->id => ['quantity' => 0.1],
        ]);

        $this->command->info('Đã tạo thành công dữ liệu Gạo, Thịt gà và món Cơm Gà!');
    }
}