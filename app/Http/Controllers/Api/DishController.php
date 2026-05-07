<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;

class DishController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        return DB::transaction(function () use ($request, $user) {
            // Tạo món ăn mới
            $dish = Dish::create([
                'name' => $request->name,
                'company_id' => $user->company_id ?? $user->id,
                'created_by' => $user->id,
                'total_calories' => $request->total_calories,
                'estimated_cost' => $request->total_cost,
            ]);

            // Lưu chi tiết nguyên liệu
            foreach ($request->ingredients as $item) {
                $dish->ingredients()->attach($item['id'], ['weight' => $item['weight']]);
            }

            return response()->json(['message' => 'Lưu món ăn thành công!', 'dish' => $dish]);
        });
    }

    public function index()
    {
        // Nhân viên chỉ thấy món ăn của công ty mình
        return Dish::with('ingredients')->where('company_id', auth()->user()->company_id)->get();
    }
}