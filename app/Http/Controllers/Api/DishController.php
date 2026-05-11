<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;

class DishController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $search = $request->query('search');

        $query = Dish::with('ingredients')
            ->where('company_id', $companyId);

        // Tìm kiếm theo tên món
        if (!empty($search)) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        // PHÂN TRANG
        $dishes = $query
            ->orderBy('id', 'desc')
            ->paginate(12);

        // append allergy_tags
        $dishes->getCollection()->transform(function ($dish) {
            $dish->allergy_tags = $dish->allergy_tags;
            return $dish;
        });

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

    public function update(Request $request, $id)
    {
        $dish = Dish::findOrFail($id);

        return DB::transaction(function () use ($request, $dish) {
            // Cập nhật tên món
            $dish->update(['name' => $request->name]);

            // Làm mới bảng trung gian
            $dish->ingredients()->detach();

            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach($item['id'], ['weight' => $item['weight']]);
            }

            // Tính toán lại toàn bộ chỉ số sau khi sửa
            $dish->recalculateNutrition();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật món thành công',
                'dish' => $dish->load('ingredients')
            ]);
        });
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
}