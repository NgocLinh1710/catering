<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TargetAudience;
use App\Models\Unit;
use App\Models\TargetAudienceRestriction;
use Illuminate\Support\Facades\DB;

class TargetAudienceController extends Controller
{
    // Lấy danh sách nhóm đối tượng của một Khách hàng cụ thể
    public function index(Request $request, $unitId)
    {
        // Kiểm tra xem nhân viên có quyền quản lý đơn vị này không
        $user = auth()->user();
        $unit = $user->units()->findOrFail($unitId);

        $audiences = TargetAudience::with('restrictions')
            ->where('unit_id', $unitId)
            ->get();

        return response()->json([
            'status' => 'success',
            'unit_name' => $unit->name,
            'data' => $audiences
        ]);
    }

    // Lưu nhóm đối tượng mới + Thiết lập tiêu chuẩn (Cấu hình giới hạn gói Free)
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'allergy_tags' => 'nullable|string',
            'religion_tags' => 'nullable|string',
            'target_calories' => 'numeric|min:0',
            'target_protein' => 'numeric|min:0',
            'target_fat' => 'numeric|min:0',
            'target_fiber' => 'numeric|min:0',
            'budget_per_serving' => 'numeric|min:0',
            'required_foods' => 'nullable|string',
            'restrictions' => 'nullable|array',
            'restrictions.*.tag' => 'required|string',
            'restrictions.*.type' => 'required|in:allergy,religion',
            'restrictions.*.quantity' => 'required|integer|min:1'
        ]);

        $user = auth()->user();
        $registration = \App\Models\CompanyRegistration::where('email', $user->email)->first();

        if ($registration) {
            // Kiểm tra xem chuỗi thông tin người đại diện có chứa từ khóa đã nâng cấp chưa
            $isExpanded = str_contains($registration->contact_person, '(Đã mở rộng Quy mô)');

            if (!$isExpanded) {
                // Nếu chưa được mở rộng (Đang dùng gói Free) -> Tiến hành đếm số khách hàng đã có
                $unitIds = $user->units()->pluck('units.id')->toArray();
                $currentAudienceCount = TargetAudience::whereIn('unit_id', $unitIds)->count();

                // Thiết lập giới hạn gói Free (Tối đa 5 khách hàng)
                if ($currentAudienceCount >= 5) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tài khoản Gói Free chỉ được tạo tối đa 3 khách hàng! Vui lòng liên hệ Admin để được Mở rộng quy mô.'
                    ], 403);
                }
            }
        }

        $audience = TargetAudience::create($data);

        if (!empty($request->restrictions)) {
            foreach ($request->restrictions as $r) {
                TargetAudienceRestriction::create([
                    'target_audience_id' => $audience->id,
                    'tag' => $r['tag'],
                    'type' => $r['type'],
                    'quantity' => $r['quantity'],
                ]);
            }
        }

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
            'unit_id' => 'required|integer',
            'name' => 'string|max:255',
            'allergy_tags' => 'nullable|string',
            'religion_tags' => 'nullable|string',
            'target_calories' => 'numeric',
            'target_protein' => 'numeric',
            'target_fat' => 'numeric',
            'target_fiber' => 'numeric',
            'budget_per_serving' => 'numeric',
            'required_foods' => 'nullable|string'
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