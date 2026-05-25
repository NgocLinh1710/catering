<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMenu;
use Illuminate\Support\Facades\DB;

class DailyMenuController extends Controller
{
    // Lấy thực đơn của một ngày cụ thể (nếu có) để hiển thị lên Form khi đổi ngày
    public function getMenuByDate(Request $request)
    {
        $request->validate([
            'target_audience_id' => 'required|exists:target_audiences,id',
            'date' => 'required|date'
        ]);

        $menu = DailyMenu::where('target_audience_id', $request->target_audience_id)
            ->where('date', $request->date)
            ->with('dishes')
            ->first();

        if (!$menu) {
            return response()->json(['status' => 'empty', 'data' => null]);
        }

        return response()->json(['status' => 'success', 'data' => $menu]);
    }

    // Lưu / Cập nhật thực đơn ngày
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'target_audience_id' => 'required|exists:target_audiences,id',
            'date' => 'required|date',
            'servings' => 'required|integer|min:1',
            'dish_ids' => 'required|array',
            'dish_ids.*' => 'exists:dishes,id'
        ]);

        try {
            DB::beginTransaction();

            $menu = DailyMenu::updateOrCreate(
                [
                    'target_audience_id' => $data['target_audience_id'],
                    'date' => $data['date']
                ],
                [
                    'unit_id' => $data['unit_id'],
                    'servings' => $data['servings']
                ]
            );

            $menu->dishes()->sync($data['dish_ids']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu dữ liệu thực đơn ngày thành công!',
                'data' => $menu->load('dishes')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}