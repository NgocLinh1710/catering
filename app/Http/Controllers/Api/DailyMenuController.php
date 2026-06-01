<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMenu;
use App\Models\TargetAudience;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;

class DailyMenuController extends Controller
{
    /**
     * Lấy thực đơn của ngày được chọn
     */
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
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa cấu hình thực đơn cho ngày này.',
                'data' => null
            ]);
        }

        $menu->dishes = $menu->dishes->map(function ($d) {
            $d->quantity = $d->pivot->quantity ?? 1;
            $d->meal_type = $d->pivot->meal_type ?? 'normal';
            return $d;
        });

        return response()->json([
            'status' => 'success',
            'data' => $menu
        ]);
    }

    /**
     * Lưu / cập nhật thực đơn tách biệt suất ăn (CẤU TRÚC JSON PHÂN NHÓM DỊ ỨNG)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'target_audience_id' => 'required|exists:target_audiences,id',
            'date' => 'required|date',
            'servings' => 'required|integer|min:1',

            'normal_servings' => 'nullable|integer|min:0',
            'vegetarian_servings' => 'nullable|integer|min:0',
            'allergy_servings' => 'nullable|integer|min:0',
            'allergy_notes' => 'nullable|array',

            'dishes' => 'required|array|min:1',
            'dishes.*.id' => 'required|exists:dishes,id',
            'dishes.*.quantity' => 'required|integer|min:1',
            'dishes.*.meal_type' => 'required|string'
        ]);

        // Kiểm tra Dị ứng 
        if (($data['allergy_servings'] ?? 0) > 0 && !empty($data['allergy_notes'])) {

            foreach ($data['dishes'] as $dishItem) {
                if (str_starts_with($dishItem['meal_type'], 'allergy_nhom_')) {
                    $groupIndex = (int) str_replace('allergy_nhom_', '', $dishItem['meal_type']);

                    if (isset($data['allergy_notes'][$groupIndex])) {
                        $allergyGroup = $data['allergy_notes'][$groupIndex];
                        $rawKeyword = trim(mb_strtolower($allergyGroup['keyword'] ?? '', 'UTF-8'));

                        if (!empty($rawKeyword)) {
                            $forbiddenKeywords = array_map('trim', explode(',', $rawKeyword));
                            $forbiddenKeywords = array_filter($forbiddenKeywords);

                            if (count($forbiddenKeywords) > 0) {
                                $dish = Dish::with('ingredients')->find($dishItem['id']);

                                // Gom góp toàn bộ các tags của món ăn từ nguyên liệu và thông tin gốc
                                $dishTags = [];
                                if ($dish && $dish->ingredients) {
                                    foreach ($dish->ingredients as $ingredient) {
                                        if (!empty($ingredient->tags) && is_array($ingredient->tags)) {
                                            foreach ($ingredient->tags as $t) {
                                                $dishTags[] = trim(mb_strtolower($t, 'UTF-8'));
                                            }
                                        }
                                    }
                                }

                                if (!empty($dish->dish_tags) && is_array($dish->dish_tags)) {
                                    foreach ($dish->dish_tags as $t) {
                                        $dishTags[] = trim(mb_strtolower($t, 'UTF-8'));
                                    }
                                }

                                $dishTags = array_unique(array_filter($dishTags));

                                foreach ($forbiddenKeywords as $singleKeyword) {
                                    if (empty($singleKeyword))
                                        continue;

                                    foreach ($dishTags as $cleanDishTag) {
                                        if (!empty($cleanDishTag) && (str_contains($cleanDishTag, $singleKeyword) || str_contains($singleKeyword, $cleanDishTag))) {
                                            return response()->json([
                                                'status' => 'error',
                                                'message' => "Không thể lưu! Bạn xếp món [{$dish->name}] vào nhóm [{$allergyGroup['name']}]. Món này chứa thành phần cấm: [{$cleanDishTag}] (Thuộc từ khóa chặn: {$singleKeyword})!"
                                            ], 422);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            $menu = DailyMenu::updateOrCreate(
                [
                    'target_audience_id' => $data['target_audience_id'],
                    'date' => $data['date']
                ],
                [
                    'unit_id' => $data['unit_id'],
                    'servings' => $data['servings'],
                    'normal_servings' => $data['normal_servings'] ?? 0,
                    'vegetarian_servings' => $data['vegetarian_servings'] ?? 0,
                    'allergy_servings' => $data['allergy_servings'] ?? 0,
                    'allergy_notes' => $data['allergy_notes'] ?? null,
                ]
            );

            $menu->dishes()->detach();

            foreach ($data['dishes'] as $dish) {
                $menu->dishes()->attach($dish['id'], [
                    'quantity' => $dish['quantity'],
                    'meal_type' => $dish['meal_type']
                ]);
            }

            DB::commit();

            $updatedMenu = DailyMenu::with('dishes')->find($menu->id);
            $updatedMenu->dishes = $updatedMenu->dishes->map(function ($d) {
                $d->quantity = $d->pivot->quantity;
                $d->meal_type = $d->pivot->meal_type;
                return $d;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu thực đơn thành công!',
                'data' => $updatedMenu
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autoGenerateMenu(Request $request)
    {
        $request->validate([
            'target_audience_id' => 'required|exists:target_audiences,id',
            'allergy_notes' => 'nullable|array'
        ]);

        $audience = TargetAudience::find($request->target_audience_id);
        if (!$audience) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy nhóm đối tượng'], 404);
        }

        $targetBudget = parseFloat($audience->budget_per_serving ?? 0);
        $targetCalories = parseFloat($audience->target_calories ?? 0);

        $dishesPool = Dish::with('ingredients')->get();

        $processedPool = $dishesPool->map(function ($dish) {
            $tags = [];
            if ($dish->ingredients) {
                foreach ($dish->ingredients as $ing) {
                    if (!empty($ing->tags) && is_array($ing->tags)) {
                        foreach ($ing->tags as $t) {
                            $tags[] = trim(mb_strtolower($t, 'UTF-8'));
                        }
                    }
                }
            }
            if (!empty($dish->dish_tags) && is_array($dish->dish_tags)) {
                foreach ($dish->dish_tags as $t) {
                    $tags[] = trim(mb_strtolower($t, 'UTF-8'));
                }
            }
            if (!empty($dish->allergy_tags) && is_array($dish->allergy_tags)) {
                foreach ($dish->allergy_tags as $t) {
                    $tags[] = trim(mb_strtolower($t, 'UTF-8'));
                }
            }

            $servings = (float) ($dish->servings ?? 1);
            if ($servings <= 0)
                $servings = 1;

            return [
                'id' => $dish->id,
                'name' => $dish->name,
                'cost_per_serving' => (float) ($dish->estimated_cost ?? 0) / $servings,
                'calories_per_serving' => (float) ($dish->total_calories ?? 0) / $servings,
                'tags' => array_unique(array_filter($tags))
            ];
        });

        // Phân loại món ăn tag Ăn Chay
        $vegetarianPool = $processedPool->filter(function ($d) {
            return !in_array('thịt', $d['tags']) && !in_array('hải sản', $d['tags']);
        })->values();

        $picker = function ($pool, $tBudget, $tCalories) {
            if ($pool->isEmpty())
                return [];

            $shuffled = $pool->shuffle();
            $selected = [];
            $currentBudget = 0;
            $currentCalories = 0;

            foreach ($shuffled as $dish) {
                if (count($selected) >= 4)
                    break;

                if ($currentBudget + $dish['cost_per_serving'] <= $tBudget * 1.3) {
                    $selected[] = [
                        'id' => $dish['id'],
                        'name' => $dish['name'],
                        'quantity' => 1,
                        'cost_per_serving' => $dish['cost_per_serving'],
                        'calories_per_serving' => $dish['calories_per_serving']
                    ];
                    $currentBudget += $dish['cost_per_serving'];
                    $currentCalories += $dish['calories_per_serving'];
                }
            }
            return $selected;
        };

        $suggestedDishes = [];

        // Hàng 1: Suất thường (Normal)
        $normalDishes = $picker($processedPool, $targetBudget, $targetCalories);
        foreach ($normalDishes as $d) {
            $d['meal_type'] = 'normal';
            $suggestedDishes[] = $d;
        }

        // Hàng 2: Suất chay (Vegetarian)
        $vegDishes = $picker($vegetarianPool, $targetBudget, $targetCalories);
        foreach ($vegDishes as $d) {
            $d['meal_type'] = 'vegetarian';
            $suggestedDishes[] = $d;
        }

        // Hàng 3: từng Nhóm dị ứng con 
        $allergyNotes = $request->allergy_notes ?? [];
        foreach ($allergyNotes as $index => $group) {
            $keyword = trim(mb_strtolower($group['keyword'] ?? '', 'UTF-8'));

            $allergySafePool = $processedPool->filter(function ($d) use ($keyword) {
                if (empty($keyword))
                    return true;
                foreach ($d['tags'] as $tag) {
                    if (str_contains($tag, $keyword) || str_contains($keyword, $tag))
                        return false;
                }
                return true;
            })->values();

            $allergyDishes = $picker($allergySafePool, $targetBudget, $targetCalories);
            foreach ($allergyDishes as $d) {
                $d['meal_type'] = "allergy_nhom_{$index}";
                $suggestedDishes[] = $d;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $suggestedDishes
        ]);
    }
}