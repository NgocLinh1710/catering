<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    // 1. Lấy danh sách món ăn (GET)
    public function index()
    {
        // Lấy tất cả món ăn, kèm theo danh sách nguyên liệu (ingredients) bên trong nó
        // Giả sử đang lấy cho company_id = 1
        $dishes = Dish::with('ingredients')->where('company_id', 1)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách món ăn thành công',
            'data' => $dishes
        ], 200);
    }

    // 2. Thêm món ăn mới (POST)
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'total_calories' => 'numeric|min:0',
            'dish_tags' => 'nullable|array',
            'ingredients' => 'required|array', // Mảng các nguyên liệu và định lượng
        ]);

        // Tạo món ăn mới lưu vào DB
        $dish = Dish::create([
            'company_id' => 1, // Fix cứng tạm thời, sau này lấy theo user đăng nhập
            'name' => $validated['name'],
            'instructions' => $validated['instructions'] ?? '',
            'total_calories' => $validated['total_calories'] ?? 0,
            'dish_tags' => $validated['dish_tags'] ?? [],
        ]);

        // Gắn nguyên liệu vào món ăn (Lưu vào bảng trung gian dish_ingredients)
        // Dữ liệu mẫu gửi lên sẽ có dạng: ['ingredients' => [ 1 => ['quantity' => 0.5], 2 => ['quantity' => 0.2] ]]
        if ($request->has('ingredients')) {
            $dish->ingredients()->attach($validated['ingredients']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo món ăn thành công',
            'data' => $dish->load('ingredients') // Load lại data trả về cho FE
        ], 201);
    }
}