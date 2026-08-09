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

        $allowedModes = [
            'nutrition_analysis',
            'dish_lookup',
            'helpdesk'
        ];

        if (!in_array($mode, $allowedModes, true)) {
            $mode = 'nutrition_analysis';
        }

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

        // Chặn các yêu cầu rõ ràng thuộc mode khác.
        $modeMismatch = $this->checkModeMismatch($mode, $userMessage);

        if ($modeMismatch !== null) {
            return response()->json([
                'status' => 'success',
                'data' => $modeMismatch
            ]);
        }

        $systemInstruction = <<<PROMPT
Bạn là "Trợ lý Thực đơn & Dinh dưỡng AI" cho phần mềm quản lý suất ăn công nghiệp.

CHẾ ĐỘ HIỆN TẠI:
{$mode}

MÔ TẢ GIAO DIỆN HIỆN TẠI:
==============================
{$uiDescription}
==============================

NGUYÊN TẮC QUAN TRỌNG:

Chế độ hiện tại là phạm vi chức năng của cuộc hội thoại.
Không được tự ý thực hiện nhiệm vụ thuộc chế độ khác.

Nếu câu hỏi thuộc chế độ khác:
- Không gọi tool.
- Không tự trả lời nội dung thuộc chế độ khác.
- Yêu cầu người dùng chuyển sang đúng chế độ.
- Phải nêu rõ tên chế độ cần chuyển sang.

Câu hỏi về "giao diện hiện tại", "màn hình hiện tại", "chế độ hiện tại dùng để làm gì"
được phép trả lời trong mọi chế độ dựa trên mô tả giao diện được cung cấp.

==============================
1. KIỂM DUYỆT THỰC ĐƠN - nutrition_analysis
==============================

Được phép:
- Phân tích thực đơn hiện tại.
- Kiểm tra Kcal, Protein, Fat, Glucid và chi phí.
- So sánh số liệu thực tế với mục tiêu.
- Đánh giá thực đơn thiếu hoặc vượt mục tiêu.
- Gợi ý thêm, bớt hoặc thay đổi món.
- Khi gợi ý món, chỉ sử dụng món do tool search_dishes trả về.
- Tính tổng dinh dưỡng dựa đúng dữ liệu tool.
- Giải thích mục đích của giao diện hiện tại.

Không được phép:
- Tra cứu món đắt nhất hoặc rẻ nhất.
- Tra cứu món nhiều hoặc ít Kcal nhất.
- Tra cứu giá món độc lập.
- Liệt kê kho món chỉ nhằm mục đích tra cứu.
- Thực hiện chức năng thuộc "Tra cứu món & giá".
- Hướng dẫn thao tác chi tiết thuộc "Hướng dẫn điền form / Thao tác".

Nếu người dùng yêu cầu tra cứu món, giá hoặc thống kê món:
"Bạn đang ở chế độ Kiểm duyệt thực đơn. Vui lòng chuyển sang chế độ Tra cứu món & giá để thực hiện yêu cầu này."

Nếu thực đơn đang trống:
- Nói rõ các chỉ số thực tế hiện tại bằng 0.
- Nếu người dùng yêu cầu gợi ý món, sử dụng tool để lấy món từ hệ thống.

==============================
2. TRA CỨU MÓN & GIÁ - dish_lookup
==============================

Được phép:
- Tìm kiếm món ăn trong kho.
- Tra cứu giá món.
- Tìm món đắt nhất hoặc rẻ nhất.
- Tìm món nhiều hoặc ít Kcal nhất.
- Lọc món theo loại.
- Tìm món theo tên.
- Tìm món theo dị ứng.
- Sử dụng tool để lấy dữ liệu thực tế.

Không được phép:
- Tự bịa tên món.
- Tự bịa giá hoặc số liệu dinh dưỡng.
- Tự ý thay đổi thực đơn.
- Hướng dẫn thao tác chi tiết trên giao diện.

Nếu người dùng yêu cầu hướng dẫn thao tác:
"Bạn đang ở chế độ Tra cứu món & giá. Vui lòng chuyển sang chế độ Hướng dẫn điền form / Thao tác để được hướng dẫn."

==============================
3. HƯỚNG DẪN ĐIỀN FORM / THAO TÁC - helpdesk
==============================

Được phép:
- Giải thích giao diện hiện tại.
- Hướng dẫn từng bước thao tác.
- Chỉ dẫn nút bấm, trường nhập liệu, tab và thứ tự thực hiện.
- Chỉ sử dụng thông tin có trong mô tả giao diện.

Không được phép:
- Tra cứu món bằng tool.
- Tra cứu giá.
- Tìm món đắt nhất hoặc rẻ nhất.
- Phân tích thực đơn.
- Tự tạo dữ liệu không có trong mô tả giao diện.

Nếu người dùng yêu cầu tra cứu món, giá hoặc thống kê:
"Bạn đang ở chế độ Hướng dẫn điền form / Thao tác. Vui lòng chuyển sang chế độ Tra cứu món & giá để thực hiện yêu cầu này."

==============================
QUY TẮC DỮ LIỆU
==============================

- Chỉ sử dụng dữ liệu do hệ thống hoặc tool cung cấp.
- Không tự bịa tên món, giá, Kcal hoặc thông tin dinh dưỡng.
- Khi đề xuất nhiều món, phải tính tổng dựa đúng dữ liệu tool.
- Không trình bày các phương án thử nhưng không phù hợp.
- Không trình bày quá trình suy luận hoặc tính toán trung gian.
- Chỉ đưa kết quả cuối cùng cần thiết cho người dùng.

==============================
QUY TẮC OUTPUT
==============================

- Chỉ trả về câu trả lời cuối cùng cho người dùng.
- Chỉ sử dụng tiếng Việt.
- Không được trả lời bằng tiếng Anh.
- Không hiển thị reasoning.
- Không hiển thị chain-of-thought.
- Không mô tả quá trình AI suy nghĩ.
- Không viết:
  "The user wants..."
  "The user is asking..."
  "I need to..."
  "I should..."
  "I will..."
  "Let's..."
  "Let's try..."
  "Let's calculate..."
  "Let's verify..."
  "Draft:"
  "Self-Correction:"
  "Check rules:"
  "Output Generation:"
  "Proceeds..."
  "All steps verified..."
- Không lặp lại câu hỏi của người dùng.
- Ngắn gọn, rõ ràng, tự nhiên.
- Ưu tiên gạch đầu dòng.
- Có thể sử dụng emoji phù hợp.
PROMPT;

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

        $contextString .= "- Glucid thực tế: "
            . ($formContext['current_glucid'] ?? '0')
            . " g\n";

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
                    . "\nChế độ hiện tại: {$mode}"
                    . "\nCâu hỏi người dùng: {$userMessage}"
            ]
        ];

        // Chỉ cấp tool phù hợp với mode hiện tại.
        $tools = $this->getToolsForMode($mode);

        try {
            $response = $this->sendRequest(
                $messages,
                $tools,
                !empty($tools)
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
                $functionName = $toolCall['function']['name'] ?? null;
                $rawArguments = $toolCall['function']['arguments'] ?? '{}';

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

            $aiReply =
                $finalData['choices'][0]['message']['content']
                ?? '';

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

    private function sendRequest(
        array $messages,
        array $tools = [],
        bool $allowTools = true
    ) {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'reasoning_effort' => 'none',
            'reasoning_format' => 'hidden',
            'temperature' => 0.1,
            'top_p' => 0.8,
            'max_completion_tokens' => 700
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

    private function getToolsForMode($mode)
    {
        if ($mode === 'nutrition_analysis') {
            return $this->defineNutritionTools();
        }

        if ($mode === 'dish_lookup') {
            return $this->defineTools();
        }

        return [];
    }

    private function defineNutritionTools()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_dishes',
                    'description' =>
                        'Tìm kiếm món ăn trong cơ sở dữ liệu của doanh nghiệp để phục vụ phân tích và gợi ý thực đơn. Có thể lọc theo tên hoặc loại món.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' =>
                                    'Từ khóa tên món. Để trống nếu cần danh sách món.'
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
            ]
        ];
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
                                ],
                                'description' =>
                                    'Loại dị ứng cần tìm.'
                            ]
                        ],
                        'required' => ['allergy_tag'],
                        'additionalProperties' => false
                    ]
                ]
            ]
        ];
    }

    private function checkModeMismatch($mode, $message)
    {
        $text = mb_strtolower(
            trim($message),
            'UTF-8'
        );

        $lookupPatterns = [
            'đắt nhất',
            'rẻ nhất',
            'giá món',
            'giá của món',
            'món nào đắt',
            'món nào rẻ',
            'nhiều kcal nhất',
            'ít kcal nhất',
            'nhiều calo nhất',
            'ít calo nhất',
            'món nào nhiều calo',
            'món nào ít calo',
            'trong kho',
            'kho món',
            'danh sách món',
            'tìm món',
            'tra cứu món',
            'món ăn nào'
        ];

        $helpdeskPatterns = [
            'hướng dẫn',
            'làm sao để',
            'làm thế nào để',
            'cách thêm',
            'cách xóa',
            'cách sửa',
            'cách tạo',
            'cách nhập',
            'bấm nút nào',
            'ấn nút nào',
            'điền vào đâu',
            'thao tác',
            'sử dụng chức năng',
            'sử dụng hệ thống'
        ];

        $interfacePatterns = [
            'giao diện hiện tại dùng để làm gì',
            'giao diện này dùng để làm gì',
            'màn hình hiện tại dùng để làm gì',
            'màn hình này dùng để làm gì',
            'chế độ hiện tại dùng để làm gì',
            'đang ở chế độ gì',
            'đây là giao diện gì'
        ];

        foreach ($interfacePatterns as $pattern) {
            if (mb_stripos($text, $pattern, 0, 'UTF-8') !== false) {
                return null;
            }
        }

        if ($mode !== 'dish_lookup') {
            foreach ($lookupPatterns as $pattern) {
                if (mb_stripos($text, $pattern, 0, 'UTF-8') !== false) {
                    return 'Bạn đang ở chế độ ' .
                        $this->getModeName($mode) .
                        '. Vui lòng chuyển sang chế độ Tra cứu món & giá để thực hiện yêu cầu này.';
                }
            }
        }

        if ($mode !== 'helpdesk') {
            foreach ($helpdeskPatterns as $pattern) {
                if (mb_stripos($text, $pattern, 0, 'UTF-8') !== false) {
                    return 'Bạn đang ở chế độ ' .
                        $this->getModeName($mode) .
                        '. Vui lòng chuyển sang chế độ Hướng dẫn điền form / Thao tác để được hướng dẫn.';
                }
            }
        }

        return null;
    }

    private function getModeName($mode)
    {
        return match ($mode) {
            'nutrition_analysis' => 'Kiểm duyệt thực đơn',
            'dish_lookup' => 'Tra cứu món & giá',
            'helpdesk' => 'Hướng dẫn điền form / Thao tác',
            default => 'hiện tại'
        };
    }

    private function executeTargetFunction(
        $name,
        $args,
        $companyId
    ) {
        switch ($name) {
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

    private function cleanAiResponse($text)
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $text = preg_replace(
            '/<think>.*?<\/think>/is',
            '',
            $text
        );

        $text = preg_replace(
            '/<analysis>.*?<\/analysis>/is',
            '',
            $text
        );

        $text = preg_replace(
            '/<reasoning>.*?<\/reasoning>/is',
            '',
            $text
        );

        $blockedPrefixes = [
            'The user wants',
            'The user is asking',
            'The user needs',
            'I need to',
            'I should',
            'I will',
            "Let's calculate",
            "Let's look",
            "Let's try",
            "Let's aim",
            "Let's verify",
            'Draft:',
            'Self-Correction:',
            'Self-Correction/Verification',
            'Check rules:',
            'Output Generation',
            'Proceeds to output',
            'All steps verified',
            'Done.'
        ];

        $lines = preg_split('/\R/', $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if (!empty($cleanLines)) {
                    $cleanLines[] = '';
                }

                continue;
            }

            $skip = false;

            foreach ($blockedPrefixes as $prefix) {
                if (stripos($trimmed, $prefix) === 0) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                $cleanLines[] = $line;
            }
        }

        $text = trim(
            implode("\n", $cleanLines)
        );

        $text = preg_replace(
            '/```(?:text|markdown)?\s*(.*?)```/is',
            '$1',
            $text
        );

        return trim($text);
    }
}