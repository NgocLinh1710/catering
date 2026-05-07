<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingredient;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::where('company_id', auth()->user()->id);

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
        ]);

        $data['company_id'] = auth()->user()->id;
        $ingredient = Ingredient::create($data);

        return response()->json($ingredient, 201);
    }

    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::where('company_id', auth()->user()->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'lipid' => 'nullable|numeric|min:0',
            'glucid' => 'nullable|numeric|min:0',
            'fiber' => 'nullable|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
        ]);

        $ingredient->update($data);
        return response()->json($ingredient);
    }

    public function destroy($id)
    {
        // Chỉ cho phép xóa nguyên liệu thuộc công ty mình
        $ingredient = Ingredient::where('company_id', auth()->user()->id)->findOrFail($id);
        $ingredient->delete();

        return response()->json(['message' => 'Xóa thành công']);
    }
}