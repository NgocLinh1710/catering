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
        $mode = $request->input('mode', 'nutrition_analysis');
        $userMessage = trim($request->input('message', ''));
        $formContext = $request->input('form_context', []);
        $uiDescription = $request->input(
            'ui_description',
            'Giao diện chung của hệ thống quản lý suất ăn.'
        );

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

Bạn hỗ trợ:
- Tra cứu món ăn, giá và dinh dưỡng.
- Phân tích thực đơn.
- Kiểm tra Kcal, Protein, Fat, Glucid và chi phí.
- Hướng dẫn người dùng thao tác trên hệ thống.

MÔ TẢ GIAO DIỆN HIỆN TẠI:
==============================
{$uiDescription}
==============================

QUY TẮC THEO CHẾ ĐỘ:

1. nutrition_analysis
- Phân tích chỉ số thực tế so với mục tiêu.
- Đánh giá Kcal, chi phí, Protein, Fat và Glucid.
- Nếu người dùng yêu cầu gợi ý món, chỉ sử dụng món do tool trả về.
- Không tự bịa tên món, giá hoặc số liệu.
- Nếu thực đơn đang trống thì nói rõ chỉ số hiện tại bằng 0.
- Khi đề xuất nhiều món, phải tính tổng Kcal dựa đúng dữ liệu tool.

2. dish_lookup
- Khi hỏi về món ăn, giá, Kcal hoặc thống kê phải sử dụng tool phù hợp.
- Chỉ sử dụng dữ liệu từ hệ thống.
- Không tự tạo dữ liệu.

3. helpdesk
- Chỉ hướng dẫn dựa trên mô tả giao diện.
- Không được tự bịa nút, ô nhập, tab hoặc chức năng.

QUY TẮC BẮT BUỘC VỀ CÂU TRẢ LỜI:
- Chỉ trả về câu trả lời cuối cùng cho người dùng.
- KHÔNG được hiển thị suy luận nội bộ.
- KHÔNG được hiển thị reasoning, chain-of-thought hoặc quá trình tự kiểm tra.
- KHÔNG được viết các câu như "The user wants...", "I need to...", "Let's calculate...", "Draft:", "Self-Correction:", "I will...", "Proceeds..." hoặc tương tự.
- KHÔNG được trả lời bằng tiếng Anh.
- Chỉ trả lời bằng tiếng Việt.
- Không lặp lại câu hỏi của người dùng.
- Ngắn gọn, rõ ràng, thân thiện.
- Ưu tiên gạch đầu dòng.
- Có thể sử dụng emoji phù hợp.
- Chỉ đưa ra kết quả và lời giải thích cần thiết cho người dùng.
PROMPT;

        $contextString = "NGỮ CẢNH DỮ LIỆU THỰC TẾ:\n";

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

        $contextString .= "- Glucid thực tế: "
            . ($formContext['current_glucid'] ?? '0')
            . " g\n";

        if (!empty($formContext['chosen_dishes'])) {
            $contextString .= "- Các món đang có: "
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
                    . "\nChế độ hiện tại: {$mode}"
                    . "\nCâu hỏi người dùng: {$userMessage}"
            ]
        ];

        try {
            $response = $this->sendRequest(
                $messages,
                $this->defineTools(),
                true
            );
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
                'message' => 'Lỗi kết nối hệ thống AI.'
            ], 500);
        }

        $responseData = $response->json();
        $aiMessage = $responseData['choices'][0]['message'] ?? null;

        if (!$aiMessage) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI không trả về dữ liệu hợp lệ.'
            ], 500);
        }

        if (!empty($aiMessage['tool_calls'])) {
            $messages[] = $aiMessage;

            foreach ($aiMessage['tool_calls'] as $toolCall) {
                $functionName = $toolCall['function']['name'] ?? null;
                $rawArguments = $toolCall['function']['arguments'] ?? '{}';

                if (!$functionName) {
                    continue;
                }

                $functionArgs = json_decode($rawArguments, true);

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

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $functionName,
                    'content' => json_encode(
                        $localResult,
                        JSON_UNESCAPED_UNICODE
                    )
                ];
            }

            try {
                $finalResponse = $this->sendRequest(
                    $messages,
                    [],
                    false
                );
            } catch (\Throwable $e) {
                Log::error('Groq Step 2 Exception', [
                    'message' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể tổng hợp kết quả từ hệ thống AI.'
                ], 500);
            }

            if (!$finalResponse->successful()) {
                Log::error('Groq API Step 2 Error', [
                    'status' => $finalResponse->status(),
                    'body' => $finalResponse->body()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi xử lý tổng hợp dữ liệu từ AI.'
                ], 500);
            }

            $finalData = $finalResponse->json();

            $aiReply = $finalData['choices'][0]['message']['content'] ?? '';

            $aiReply = $this->cleanAiResponse($aiReply);

            if ($aiReply === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'AI không tạo được câu trả lời.'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'data' => $aiReply
            ]);
        }

        $aiReply = $this->cleanAiResponse(
            $aiMessage['content'] ?? ''
        );

        if ($aiReply === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'AI không trả về nội dung phản hồi.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $aiReply
        ]);
    }

    private function sendRequest($messages, $tools = [], $allowTools = true)
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.1,
            'top_p' => 0.8,
            'max_tokens' => 1200
        ];

        if ($allowTools && !empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        } else {
            $payload['tool_choice'] = 'none';
        }

        return Http::withToken($this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->timeout(45)
            ->connectTimeout(15)
            ->post($this->apiUrl, $payload);
    }

    private function cleanAiResponse($text)
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $patterns = [
            '/<think>.*?<\/think>/is',
            '/<analysis>.*?<\/analysis>/is',
            '/<reasoning>.*?<\/reasoning>/is'
        ];

        $text = preg_replace($patterns, '', $text);

        $markers = [
            'The user wants',
            'The user is asking',
            'I need to',
            'I should',
            'Let\'s calculate',
            'Let\'s look',
            'I will',
            'Draft:',
            'Self-Correction:',
            'Self-Correction/Verification',
            '[Output Generation]',
            'Proceeds to output response',
            'Done.',
            'All steps verified.'
        ];

        $lines = preg_split('/\R/', $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            $skip = false;

            foreach ($markers as $marker) {
                if (stripos($trimmed, $marker) === 0) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                $cleanLines[] = $line;
            }
        }

        $text = trim(implode("\n", $cleanLines));

        $text = preg_replace(
            '/```(?:text|markdown)?\s*(.*?)```/is',
            '$1',
            $text
        );

        return trim($text);
    }

    private function defineTools()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_dishes',
                    'description' =>
                        'Tìm kiếm món ăn trong cơ sở dữ liệu của doanh nghiệp theo tên hoặc loại món. Dùng khi người dùng hỏi danh sách món hoặc cần gợi ý món.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' =>
                                    'Từ khóa tên món. Để trống nếu muốn lấy danh sách món.'
                            ],
                            'category' => [
                                'type' => 'string',
                                'description' =>
                                    'Loại món: món chính, món phụ, canh, khai vị hoặc tráng miệng.'
                            ]
                        ],
                        'required' => [],
                        'additionalProperties' => false
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_dish_statistics',
                    'description' =>
                        'Tìm một món ăn duy nhất theo tiêu chí đắt nhất, rẻ nhất, nhiều calo nhất hoặc ít calo nhất.',
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
                                ]
                            ],
                            'category' => [
                                'type' => 'string',
                                'enum' => [
                                    'món chính',
                                    'món phụ',
                                    'canh',
                                    'khai vị',
                                    'tráng miệng'
                                ]
                            ]
                        ],
                        'required' => ['criteria'],
                        'additionalProperties' => false
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'filter_dishes_by_allergy',
                    'description' =>
                        'Tìm các món ăn có chứa thành phần gây dị ứng dựa trên dữ liệu nguyên liệu.',
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
                                ]
                            ]
                        ],
                        'required' => ['allergy_tag'],
                        'additionalProperties' => false
                    ]
                ]
            ]
        ];
    }

    private function executeTargetFunction($name, $args, $companyId)
    {
        switch ($name) {
            case 'search_dishes':

                $query = Dish::where('company_id', $companyId);

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
                            'name' => $dish->name,
                            'category' => $dish->category ?? 'Chưa phân loại',
                            'cost' => $dish->cost_per_serving,
                            'calories' => $dish->calories_per_serving,
                            'protein' => $dish->protein_per_serving,
                            'fat' => $dish->fat_per_serving,
                            'glucid' => $dish->glucid_per_serving,
                            'allergy_tags' => $dish->allergy_tags
                        ];
                    })
                    ->toArray();

            case 'get_dish_statistics':

                $query = Dish::where('company_id', $companyId);

                if (!empty($args['category'])) {
                    $query->where(
                        'category',
                        'LIKE',
                        '%' . $args['category'] . '%'
                    );
                }

                $criteria = $args['criteria'] ?? null;
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
                    'name' => $dish->name,
                    'category' => $dish->category ?? 'Chưa phân loại',
                    'cost' => $dish->cost_per_serving,
                    'calories' => $dish->calories_per_serving,
                    'protein' => $dish->protein_per_serving,
                    'fat' => $dish->fat_per_serving,
                    'glucid' => $dish->glucid_per_serving
                ];

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

                if ($matchedDishes->isEmpty()) {
                    $allDishes = Dish::where(
                        'company_id',
                        $companyId
                    )
                        ->limit(100)
                        ->get();

                    $matchedDishes = $allDishes->filter(
                        function ($dish) use ($tag) {
                            if (empty($dish->allergy_tags)) {
                                return false;
                            }

                            $tags = is_array($dish->allergy_tags)
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
                            'name' => $dish->name,
                            'category' => $dish->category ?? 'Chưa phân loại',
                            'cost' => $dish->cost_per_serving,
                            'calories' => $dish->calories_per_serving,
                            'protein' => $dish->protein_per_serving,
                            'fat' => $dish->fat_per_serving,
                            'glucid' => $dish->glucid_per_serving
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