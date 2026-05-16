<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TargetAudience;
use App\Models\Unit;

class TargetAudienceController extends Controller
{
    // Lấy danh sách nhóm đối tượng của một Đơn vị cụ thể
    public function index(Request $request, $unitId)
    {
        // Kiểm tra xem nhân viên có quyền quản lý đơn vị này không
        $user = auth()->user();
        $unit = $user->units()->findOrFail($unitId);

        $audiences = TargetAudience::where('unit_id', $unitId)->get();

        return response()->json([
            'status' => 'success',
            'unit_name' => $unit->name,
            'data' => $audiences
        ]);
    }

    // Lưu nhóm đối tượng mới + Thiết lập tiêu chuẩn
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'allergy_tags' => 'nullable|array',
            'religion_tags' => 'nullable|array',
            'target_calories' => 'numeric|min:0',
            'target_protein' => 'numeric|min:0',
            'target_fat' => 'numeric|min:0',
            'target_fiber' => 'numeric|min:0',
            'budget_per_serving' => 'numeric|min:0',
            'required_foods' => 'nullable|array' // Ví dụ: ["Sữa tươi buổi xế"]
        ]);

        $audience = TargetAudience::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Thiết lập đối tượng và tiêu chuẩn thành công!',
            'data' => $audience
        ], 201);
    }

    // Cập nhật tiêu chuẩn
    public function update(Request $request, $id)
    {
        $audience = TargetAudience::findOrFail($id);

        $data = $request->validate([
            'name' => 'string|max:255',
            'allergy_tags' => 'nullable|array',
            'religion_tags' => 'nullable|array',
            'target_calories' => 'numeric',
            'target_protein' => 'numeric',
            'target_fat' => 'numeric',
            'target_fiber' => 'numeric',
            'budget_per_serving' => 'numeric',
            'required_foods' => 'nullable|array'
        ]);

        $audience->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật tiêu chuẩn thành công!',
            'data' => $audience
        ]);
    }

    public function destroy($id)
    {
        TargetAudience::findOrFail($id)->delete();
        return response()->json(['message' => 'Xóa nhóm đối tượng thành công']);
    }
}