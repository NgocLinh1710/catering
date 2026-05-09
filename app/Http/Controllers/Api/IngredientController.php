<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\IngredientPrice;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $query = Ingredient::where('company_id', $companyId);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Phân trang
        $ingredients = $query
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $ingredients->items(),
            'current_page' => $ingredients->currentPage(),
            'last_page' => $ingredients->lastPage(),
            'total' => $ingredients->total(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'lipid' => 'nullable|numeric|min:0',
            'glucid' => 'nullable|numeric|min:0',
            'fiber' => 'nullable|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'unit' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string'
        ]);

        $data['company_id'] = auth()->user()->company_id ?? auth()->user()->id;

        return DB::transaction(function () use ($data) {
            $ingredient = Ingredient::create($data);

            // Tự động lưu giá vào lịch sử ngay khi tạo mới
            IngredientPrice::create([
                'ingredient_id' => $ingredient->id,
                'price' => $data['price_per_kg'],
                'applied_date' => now()->format('Y-m-d'),
            ]);

            return response()->json($ingredient, 201);
        });
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $ingredient = Ingredient::where('company_id', $companyId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'lipid' => 'nullable|numeric|min:0',
            'glucid' => 'nullable|numeric|min:0',
            'fiber' => 'nullable|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string'
        ]);
        return DB::transaction(function () use ($ingredient, $data) {
            // Kiểm tra nếu giá thay đổi thì mới lưu vào lịch sử
            if ($ingredient->price_per_kg != $data['price_per_kg']) {
                IngredientPrice::create([
                    'ingredient_id' => $ingredient->id,
                    'price' => $data['price_per_kg'],
                    'applied_date' => now()->format('Y-m-d'),
                ]);
            }

            $ingredient->update($data);
            return response()->json($ingredient);
        });
    }

    /**
     * API chuyên dụng để cập nhật giá theo định kỳ (Tuần/Tháng)
     */
    public function updatePrice(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'price' => 'required|numeric|min:0',
            'applied_date' => 'required|date',
        ]);

        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $ingredient = Ingredient::where('company_id', $companyId)
            ->findOrFail($request->ingredient_id);

        return DB::transaction(function () use ($request, $ingredient) {
            // Lưu lịch sử giá
            IngredientPrice::create([
                'ingredient_id' => $ingredient->id,
                'price' => $request->price,
                'applied_date' => $request->applied_date
            ]);

            // Cập nhật giá hiện hành
            $ingredient->update(['price_per_kg' => $request->price]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật giá thời vụ thành công!'
            ]);
        });
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $ingredient = Ingredient::where('company_id', $companyId)->findOrFail($id);
        $ingredient->delete();

        return response()->json(['message' => 'Xóa thành công']);
    }
}