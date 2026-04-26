<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $dishes = Dish::with('ingredients')->where('company_id', $companyId)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách món ăn thành công',
            'data' => $dishes
        ], 200);
    }

    // Hàm thêm Món mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'total_calories' => 'numeric|min:0',
            'dish_tags' => 'nullable|array',
            'ingredients' => 'required|array',
        ]);

        $currentUser = auth()->user();
        $companyId = $currentUser->company_id ?? $currentUser->id;

        $dish = Dish::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'instructions' => $validated['instructions'] ?? '',
            'total_calories' => $validated['total_calories'] ?? 0,
            'dish_tags' => $validated['dish_tags'] ?? [],
        ]);

        if ($request->has('ingredients')) {
            $dish->ingredients()->attach($validated['ingredients']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo món ăn thành công',
            'data' => $dish->load('ingredients')
        ], 201);
    }
}