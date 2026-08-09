<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    private $apiKey;

    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    private $model = 'qwen/qwen3.6-27b';

    public function chat(Request $request)
    {
        // Lấy dữ liệu từ Frontend
        $mode = $request->input('mode', 'nutrition_analysis');
        $userMessage = trim($request->input('message', ''));
        $formContext = $request->input('form_context', []);
        $uiDescription = $request->input(
            'ui_description',
            'Giao diện chung của hệ thống quản lý suất ăn.'
        );

        // Xác định company_id
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
            ], 401);
        }

        $companyId = $user->company_id ?? $user->id;

        $this->apiKey = config('services.grok.api_key');

        if (empty($this->apiKey)) {
            Log::error('Groq API Key is missing.');

            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cấu hình API Key.'
            ], 500);
        }

        if ($userMessage === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng nhập câu hỏi.'
            ], 422);
        }

        $systemInstruction = <<<PROMPT
Bạn là "Trợ lý Thực đơn & Dinh dưỡng AI" cho phần mềm quản lý suất ăn công nghiệp.

NHIỆM VỤ:
Hỗ trợ người dùng tra cứu món ăn, phân tích thực đơn, kiểm tra dinh dưỡng,
chi phí và hướng dẫn sử dụng hệ thống.

==============================
MÔ TẢ GIAO DIỆN HIỆN TẠI
==============================

{$uiDescription}

==============================
QUY TẮC THEO CHẾ ĐỘ
==============================

1. nutrition_analysis
- Phân tích các chỉ số thực tế so với mục tiêu.
- Đánh giá Kcal, chi phí, Protein, Fat và Glucid.
- Nếu người dùng yêu cầu gợi ý món, chỉ được sử dụng các món có trong dữ liệu hệ thống được cung cấp bởi tool.
- Không được tự bịa món ăn, giá hoặc số liệu dinh dưỡng.
- Có thể đề xuất tăng hoặc giảm món để tiến gần mục tiêu.
- Nếu chưa có món nào trong thực đơn, nói rõ rằng các chỉ số hiện tại đang bằng 0.

2. dish_lookup
- Khi người dùng hỏi về món ăn, giá, Kcal hoặc thống kê món ăn, phải sử dụng tool phù hợp.
- Chỉ sử dụng dữ liệu do hệ thống trả về.
- Không tự tạo dữ liệu không có trong hệ thống.

3. helpdesk
- Đọc kỹ mô tả giao diện hiện tại.
- Hướng dẫn từng bước dựa trên đúng giao diện được cung cấp.
- Không được tự bịa nút, ô nhập, tab hoặc chức năng không xuất hiện trong mô tả giao diện.

==============================
QUY TẮC TRẢ LỜI
==============================

- Chỉ trả về câu trả lời cuối cùng cho người dùng.
- KHÔNG hiển thị suy luận nội bộ, reasoning, chain-of-thought hoặc quá trình phân tích.
- KHÔNG viết các câu như "I need to...", "Let's calculate...", "Draft:", "Self-Correction:", "Proceeds..." hoặc nội dung tương tự.
- Trả lời bằng tiếng Việt.
- Ngắn gọn, rõ ràng.
- Ưu tiên gạch đầu dòng.
- Có thể sử dụng emoji phù hợp.
- Không lặp lại câu hỏi của người dùng.
- Nếu dữ liệu không đủ để kết luận, nói rõ dữ liệu còn thiếu.
PROMPT;

        // Xây dựng context dinh dưỡng
        $contextString = "NGỮ CẢNH DỮ LIỆU THỰC TẾ TRÊN MÀN HÌNH:\n";

        $contextString .= "- Kcal mục tiêu: "
            . ($formContext['target_calories'] ?? 'Chưa rõ')
            . " Kcal\n";

        $contextString .= "- Ngân sách mục tiêu: "
            . ($formContext['target_budget'] ?? 'Chưa rõ')
            . " VNĐ\n";

        $contextString .= "- Kcal thực tế: "
            . ($formContext['current_calories'] ?? '0')
            . " Kcal\n";

        $contextString .= "- Chi phí thực tế: "
            . ($formContext['current_cost'] ?? '0')
            . " VNĐ\n";

        $contextString .= "- Protein thực tế: "
            . ($formContext['current_protein'] ?? '0')
            . " g\n";

        $contextString .= "- Fat thực tế: "
            . ($formContext['current_fat'] ?? '0')
            . " g\n";

        $contextString .= "- Glucid/Xơ thực tế: "
            . ($formContext['current_glucid'] ?? '0')
            . " g\n";

        // Danh sách món hiện tại
        if (!empty($formContext['chosen_dishes'])) {
            $contextString .= "- Các món đang có trong thực đơn: "
                . json_encode(
                    $formContext['chosen_dishes'],
                    JSON_UNESCAPED_UNICODE
                )
                . "\n";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $systemInstruction
            ],
            [
                'role' => 'user',
                'content' =>
                    $contextString
                    . "\nChế độ hiện tại: "
                    . $mode
                    . "\nCâu hỏi người dùng: "
                    . $userMessage
            ]
        ];

        $tools = $this->defineTools();

        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->timeout(45)
                ->connectTimeout(15)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $messages,
                    'tools' => $tools,
                    'tool_choice' => 'auto',
                    'temperature' => 0.2,
                    'top_p' => 0.8,
                    'max_tokens' => 1200
                ]);

        } catch (\Throwable $e) {

            Log::error('Groq Step 1 Exception', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể kết nối đến hệ thống AI. Vui lòng thử lại.'
            ], 500);
        }

        if (!$response->successful()) {

            Log::error('Groq API Step 1 Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi kết nối hệ thống AI tầng 1.'
            ], 500);
        }

        $responseData = $response->json();

        $aiMessage = $responseData['choices'][0]['message'] ?? null;

        if (!$aiMessage) {

            Log::error('Groq response does not contain message', [
                'response' => $responseData
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'AI không trả về dữ liệu hợp lệ.'
            ], 500);
        }

        if (!empty($aiMessage['tool_calls'])) {

            $messages[] = $aiMessage;

            foreach ($aiMessage['tool_calls'] as $toolCall) {
                $functionName =
                    $toolCall['function']['name'] ?? null;
                $rawArguments =
                    $toolCall['function']['arguments'] ?? '{}';
                if (!$functionName) {
                    continue;
                }

                $functionArgs = json_decode(
                    $rawArguments,
                    true
                );

                if (!is_array($functionArgs)) {
                    $functionArgs = [];
                }

                try {

                    $localResult = $this->executeTargetFunction(
                        $functionName,
                        $functionArgs,
                        $companyId
                    );

                } catch (\Throwable $e) {

                    Log::error('Tool execution error', [
                        'function' => $functionName,
                        'error' => $e->getMessage()
                    ]);

                    $localResult = [
                        'error' => 'Không thể truy vấn dữ liệu hệ thống.'
                    ];
                }

                $toolContent = json_encode(
                    $localResult,
                    JSON_UNESCAPED_UNICODE
                );

                if (strlen($toolContent) > 12000) {
                    $toolContent = substr(
                        $toolContent,
                        0,
                        12000
                    );
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $functionName,
                    'content' => $toolContent
                ];
            }

            try {

                $finalResponse = Http::withToken($this->apiKey)
                    ->withHeaders([
                        'Content-Type' => 'application/json'
                    ])
                    ->timeout(45)
                    ->connectTimeout(15)
                    ->post($this->apiUrl, [
                        'model' => $this->model,
                        'messages' => $messages,
                        'tool_choice' => 'none',
                        'temperature' => 0.2,
                        'top_p' => 0.8,
                        'max_tokens' => 1200
                    ]);

            } catch (\Throwable $e) {
                Log::error('Groq Step 2 Exception', [
                    'message' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Không thể tổng hợp kết quả từ hệ thống AI.'
                ], 500);
            }

            if (!$finalResponse->successful()) {
                Log::error('Groq API Step 2 Error', [
                    'status' => $finalResponse->status(),
                    'body' => $finalResponse->body()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Lỗi xử lý tổng hợp dữ liệu từ AI.'
                ], 500);
            }

            $finalData = $finalResponse->json();

            $aiReply =
                $finalData['choices'][0]['message']['content']
                ?? '';

            if (empty(trim($aiReply))) {

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'AI không tạo được câu trả lời. Vui lòng thử lại.'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'data' => trim($aiReply)
            ]);
        }

        $aiReply = $aiMessage['content'] ?? '';

        if (empty(trim($aiReply))) {

            return response()->json([
                'status' => 'error',
                'message' =>
                    'AI không trả về nội dung phản hồi.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => trim($aiReply)
        ]);
    }

    private function defineTools()
    {
        return [
            // Search dishes
            [
                'type' => 'function',

                'function' => [
                    'name' => 'search_dishes',

                    'description' =>
                        'Tìm kiếm món ăn trong cơ sở dữ liệu của doanh nghiệp '
                        . 'theo tên hoặc loại món ăn. Dùng khi người dùng hỏi '
                        . 'danh sách món, món có tên cụ thể hoặc muốn gợi ý món.',

                    'parameters' => [
                        'type' => 'object',

                        'properties' => [

                            'keyword' => [
                                'type' => 'string',
                                'description' =>
                                    'Từ khóa tên món. '
                                    . 'Để trống nếu muốn lấy danh sách món.'
                            ],

                            'category' => [
                                'type' => 'string',
                                'description' =>
                                    'Loại món: món chính, món phụ, canh, '
                                    . 'khai vị hoặc tráng miệng.'
                            ]
                        ],

                        'required' => [],

                        'additionalProperties' => false
                    ]
                ]
            ],

            // Dish statistics
            [
                'type' => 'function',

                'function' => [
                    'name' => 'get_dish_statistics',

                    'description' =>
                        'Tìm một món ăn duy nhất theo tiêu chí '
                        . 'đắt nhất, rẻ nhất, nhiều calo nhất hoặc ít calo nhất.',

                    'parameters' => [
                        'type' => 'object',

                        'properties' => [

                            'criteria' => [
                                'type' => 'string',

                                'enum' => [
                                    'most_expensive',
                                    'cheapest',
                                    'highest_calories',
                                    'lowest_calories'
                                ],

                                'description' =>
                                    'Tiêu chí thống kê.'
                            ],

                            'category' => [
                                'type' => 'string',

                                'enum' => [
                                    'món chính',
                                    'món phụ',
                                    'canh',
                                    'khai vị',
                                    'tráng miệng'
                                ],

                                'description' =>
                                    'Giới hạn loại món nếu người dùng yêu cầu.'
                            ]
                        ],

                        'required' => [
                            'criteria'
                        ],

                        'additionalProperties' => false
                    ]
                ]
            ],

            // Allergy search
            [
                'type' => 'function',

                'function' => [
                    'name' => 'filter_dishes_by_allergy',

                    'description' =>
                        'Tìm các món ăn có chứa thành phần gây dị ứng '
                        . 'dựa trên dữ liệu nguyên liệu của doanh nghiệp.',

                    'parameters' => [
                        'type' => 'object',

                        'properties' => [

                            'allergy_tag' => [
                                'type' => 'string',

                                'enum' => [
                                    'đậu nành',
                                    'hải sản',
                                    'gluten',
                                    'sữa',
                                    'trứng'
                                ],

                                'description' =>
                                    'Loại dị ứng cần tìm.'
                            ]
                        ],

                        'required' => [
                            'allergy_tag'
                        ],

                        'additionalProperties' => false
                    ]
                ]
            ]
        ];
    }

    private function executeTargetFunction(
        $name,
        $args,
        $companyId
    ) {
        switch ($name) {
            // Search dishes
            case 'search_dishes':

                $query = Dish::where(
                    'company_id',
                    $companyId
                );

                if (!empty($args['category'])) {

                    $query->where(
                        'category',
                        'LIKE',
                        '%' . $args['category'] . '%'
                    );
                }

                if (!empty($args['keyword'])) {

                    $query->where(
                        'name',
                        'LIKE',
                        '%' . $args['keyword'] . '%'
                    );
                }

                return $query
                    ->limit(25)
                    ->get()
                    ->map(function ($dish) {

                        return [
                            'name' =>
                                $dish->name,

                            'category' =>
                                $dish->category
                                ?? 'Chưa phân loại',

                            'cost' =>
                                $dish->cost_per_serving,

                            'calories' =>
                                $dish->calories_per_serving,

                            'protein' =>
                                $dish->protein_per_serving,

                            'fat' =>
                                $dish->fat_per_serving,

                            'glucid' =>
                                $dish->glucid_per_serving,

                            'allergy_tags' =>
                                $dish->allergy_tags
                        ];
                    })
                    ->toArray();

            // Dish statistics
            case 'get_dish_statistics':

                $query = Dish::where(
                    'company_id',
                    $companyId
                );

                if (!empty($args['category'])) {

                    $query->where(
                        'category',
                        'LIKE',
                        '%' . $args['category'] . '%'
                    );
                }

                $criteria =
                    $args['criteria']
                    ?? null;

                $dish = null;

                switch ($criteria) {

                    case 'most_expensive':

                        $dish = $query
                            ->where('estimated_cost', '>', 0)
                            ->orderByRaw(
                                '(estimated_cost / IF(servings > 0, servings, 1)) DESC'
                            )
                            ->first();

                        break;

                    case 'cheapest':

                        $dish = $query
                            ->where('estimated_cost', '>', 0)
                            ->orderByRaw(
                                '(estimated_cost / IF(servings > 0, servings, 1)) ASC'
                            )
                            ->first();

                        break;

                    case 'highest_calories':

                        $dish = $query
                            ->where('total_calories', '>', 0)
                            ->orderByRaw(
                                '(total_calories / IF(servings > 0, servings, 1)) DESC'
                            )
                            ->first();

                        break;

                    case 'lowest_calories':

                        $dish = $query
                            ->where('total_calories', '>', 0)
                            ->orderByRaw(
                                '(total_calories / IF(servings > 0, servings, 1)) ASC'
                            )
                            ->first();

                        break;
                }

                if (!$dish) {

                    return [
                        'message' =>
                            'Không tìm thấy món ăn phù hợp trong hệ thống.'
                    ];
                }

                return [

                    'name' =>
                        $dish->name,

                    'category' =>
                        $dish->category
                        ?? 'Chưa phân loại',

                    'cost' =>
                        $dish->cost_per_serving,

                    'calories' =>
                        $dish->calories_per_serving,

                    'protein' =>
                        $dish->protein_per_serving,

                    'fat' =>
                        $dish->fat_per_serving,

                    'glucid' =>
                        $dish->glucid_per_serving
                ];

            // Allergy
            case 'filter_dishes_by_allergy':

                $tag = mb_strtolower(
                    trim($args['allergy_tag'] ?? ''),
                    'UTF-8'
                );

                if ($tag === '') {

                    return [
                        'message' =>
                            'Chưa xác định loại dị ứng cần tìm.'
                    ];
                }

                $matchedDishes = Dish::where(
                    'company_id',
                    $companyId
                )
                    ->whereHas(
                        'ingredients',
                        function ($q) use ($tag) {

                            $q->whereRaw(
                                'LOWER(allergy_tags) LIKE ?',
                                ['%' . $tag . '%']
                            );
                        }
                    )
                    ->limit(25)
                    ->get();


                /*
                 * Nếu không tìm thấy từ ingredients,
                 * kiểm tra allergy_tags của dish.
                 */

                if ($matchedDishes->isEmpty()) {

                    $allDishes = Dish::where(
                        'company_id',
                        $companyId
                    )
                        ->limit(100)
                        ->get();

                    $matchedDishes =
                        $allDishes->filter(
                            function ($dish) use ($tag) {

                                if (empty($dish->allergy_tags)) {
                                    return false;
                                }

                                $tags = is_array(
                                    $dish->allergy_tags
                                )
                                    ? $dish->allergy_tags
                                    : [$dish->allergy_tags];

                                foreach ($tags as $item) {

                                    if (
                                        mb_strtolower(
                                            trim($item),
                                            'UTF-8'
                                        ) === $tag
                                    ) {
                                        return true;
                                    }
                                }

                                return false;
                            }
                        );
                }


                if ($matchedDishes->isEmpty()) {

                    return [
                        'message' =>
                            "Không có món ăn nào chứa cảnh báo dị ứng '{$tag}' trong hệ thống."
                    ];
                }


                return $matchedDishes
                    ->map(function ($dish) {

                        return [

                            'name' =>
                                $dish->name,

                            'category' =>
                                $dish->category
                                ?? 'Chưa phân loại',

                            'cost' =>
                                $dish->cost_per_serving,

                            'calories' =>
                                $dish->calories_per_serving,

                            'protein' =>
                                $dish->protein_per_serving,

                            'fat' =>
                                $dish->fat_per_serving,

                            'glucid' =>
                                $dish->glucid_per_serving
                        ];
                    })
                    ->values()
                    ->toArray();

            default:

                return [
                    'error' =>
                        'Hàm yêu cầu không tồn tại trên hệ thống.'
                ];
        }
    }
}