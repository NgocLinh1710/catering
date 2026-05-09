<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;

class DishController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;

        // Lấy danh sách món ăn kèm nguyên liệu và tự động tính thêm allergy_tags
        $dishes = Dish::with('ingredients')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($dishes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.weight' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyId = $user->company_id ?? $user->id;

        return DB::transaction(function () use ($request, $user, $companyId) {
            // Tạo món ăn 
            $dish = Dish::create([
                'name' => $request->name,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'total_calories' => 0,
                'total_protein' => 0,
                'estimated_cost' => 0,
            ]);

            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach($item['id'], ['weight' => $item['weight']]);
            }

            // Sử dụng hàm 'thông minh' ở Model Dish để tính toán chính xác
            $dish->recalculateNutrition();

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu món ăn và tính toán dinh dưỡng thành công!',
                'dish' => $dish->load('ingredients')
            ]);
        });
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $dish = Dish::with('ingredients')->where('company_id', $companyId)->findOrFail($id);

        return response()->json($dish);
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $dish = Dish::where('company_id', $companyId)->findOrFail($id);

        // Xóa quan hệ trong bảng trung gian trước khi xóa món
        $dish->ingredients()->detach();
        $dish->delete();

        return response()->json(['message' => 'Xóa món ăn thành công']);
    }

    public function update(Request $request, $id)
    {
        $dish = Dish::findOrFail($id);

        return DB::transaction(function () use ($request, $dish) {
            $dish->update(['name' => $request->name]);

            $dish->ingredients()->detach();

            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach($item['id'], ['weight' => $item['weight']]);
            }

            $dish->recalculateNutrition();

            return response()->json(['status' => 'success', 'message' => 'Cập nhật món thành công']);
        });
    }
}