<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dish;
use Illuminate\Support\Facades\Auth;

class DishController extends Controller
{
    /**
     * Lấy danh sách món ăn của công ty
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id ?? $user->id;

        $query = Dish::where('company_id', $companyId);
        // Tìm kiếm theo tên 
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // Lọc theo loại (Món chính, canh...) 
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        $dishes = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $dishes
        ]);
    }

    /**
     * Lưu món ăn mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'calories' => 'required|numeric|min:0',
            'category' => 'required|string',
        ]);

        $user = Auth::user();
        $companyId = $user->company_id ?? $user->id;

        $dish = Dish::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'calories' => $request->calories,
            'protein' => $request->protein ?? 0,
            'lipid' => $request->lipid ?? 0,
            'glucid' => $request->glucid ?? 0,
            'instructions' => $request->instructions,
            'dish_tags' => $request->dish_tags,
            'image_url' => $request->image_url,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm món ăn thành công!',
            'data' => $dish
        ]);
    }

    // Cập nhật món ăn
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = $user->company_id ?? $user->id;

        $dish = Dish::where('id', $id)->where('company_id', $companyId)->firstOrFail();

        $dish->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật món ăn thành công!'
        ]);
    }

    // Xóa món ăn
    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = $user->company_id ?? $user->id;

        $dish = Dish::where('id', $id)->where('company_id', $companyId)->firstOrFail();
        $dish->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa món ăn thành công.'
        ]);
    }
}