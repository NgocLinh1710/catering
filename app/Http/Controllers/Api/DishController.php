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
        $query = Dish::with([
            'ingredients.prices'
        ])
            ->where('company_id', $companyId);

        if (!empty($search)) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $dishes = $query
            ->orderBy('id', 'desc')
            ->paginate(12);

        $dishes->getCollection()->transform(function ($dish) use ($request) {

            // Tag dị ứng từ nguyên liệu
            $dish->warning_tags = $dish->allergy_tags ?? [];
            $dish->allergy_tags = $dish->allergy_tags;

            // Giá món theo ngày được truyền vào
            // nếu không có thì lấy ngày hiện tại
            $date = $request->query('date')
                ?? now('Asia/Ho_Chi_Minh')->format('Y-m-d');

            $dish->cost_per_serving =
                round(
                    $dish->calculateCostAtDate($date)
                    /
                    max($dish->servings, 1),
                    2
                );

            $dish->calories_per_serving =
                $dish->calories_per_serving;

            $dish->protein_per_serving =
                $dish->protein_per_serving;

            return $dish;
        });

        return response()->json($dishes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'servings' => 'required|integer|min:1',

            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.weight' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyId = $user->company_id ?? $user->id;

        return DB::transaction(function () use ($request, $user, $companyId) {
            $dish = Dish::create([
                'name' => $request->name,
                'category' => $request->category,
                'servings' => $request->servings,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'total_calories' => 0,
                'total_protein' => 0,
                'estimated_cost' => 0,
            ]);

            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach(
                    $item['id'],
                    [
                        'weight' => $item['weight']
                    ]
                );
            }

            // Tính dinh dưỡng
            $dish->refresh();
            $dish->recalculateNutrition();

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu món ăn và tính toán dinh dưỡng thành công!',
                'dish' => $dish
                    ->load('ingredients.prices')
            ]);
        });
    }

    public function show($id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $dish = Dish::with([
            'ingredients.prices'
        ])
            ->where('company_id', $companyId)
            ->findOrFail($id);
        return response()->json($dish);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'servings' => 'required|integer|min:1',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id'
            => 'required|exists:ingredients,id',
            'ingredients.*.weight'
            => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyId = $user->company_id ?? $user->id;

        $dish = Dish::where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();
        return DB::transaction(function () use ($request, $dish) {
            $dish->update([
                'name' => $request->name,
                'category' => $request->category,
                'servings' => $request->servings,
            ]);

            $dish->ingredients()->detach();
            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach(
                    $item['id'],
                    [
                        'weight' => $item['weight']
                    ]
                );
            }
            $dish->refresh();
            $dish->recalculateNutrition();
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật món thành công',
                'dish' => $dish
                    ->load('ingredients.prices')
            ]);
        });
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;
        $dish = Dish::where('company_id', $companyId)
            ->findOrFail($id);
        $dish->ingredients()->detach();
        $dish->delete();
        return response()->json([
            'message' => 'Xóa món ăn thành công'
        ]);
    }

    public function all(Request $request)
    {
        $companyId = auth()->user()->company_id ?? auth()->user()->id;

        $date = $request->query('date')
            ?? now('Asia/Ho_Chi_Minh')->format('Y-m-d');

        $dishes = Dish::with([
            'ingredients.prices'
        ])
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->get();

        $dishes->transform(function ($dish) use ($date) {

            $dish->warning_tags = $dish->allergy_tags ?? [];
            $dish->allergy_tags = $dish->allergy_tags;

            // Giá thực tế

            $actualCost = round(
                $dish->calculateCostAtDate($date),
                2
            );

            $dish->actual_cost = $actualCost;

            $dish->cost_per_serving = round(
                $actualCost / max($dish->servings, 1),
                2
            );

            // Dinh dưỡng

            $dish->calories_per_serving =
                $dish->calories_per_serving;

            $dish->protein_per_serving =
                $dish->protein_per_serving;

            $dish->fat_per_serving =
                $dish->fat_per_serving;

            $dish->glucid_per_serving =
                $dish->glucid_per_serving;

            $dish->fiber_per_serving =
                $dish->fiber_per_serving;

            return $dish;
        });

        return response()->json([
            'status' => 'success',
            'data' => $dishes
        ]);
    }
}