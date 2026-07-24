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
    private $apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    private $model = "llama-3.3-70b-versatile";

    public function chat(Request $request)
    {
        $mode = $request->input('mode', 'nutrition_analysis');
        $userMessage = $request->input('message', '');
        $formContext = $request->input('form_context', []);
        $companyId = auth()->user()->company_id ?? auth()->id();

        $uiDescription = $request->input('ui_description', 'Giao diện chung của hệ thống quản lý suất ăn.');

        $this->apiKey = config('services.grok.api_key');
        if (!$this->apiKey) {
            return response()->json(['status' => 'error', 'message' => 'Lỗi cấu hình API Key.'], 500);
        }

        // Ép AI tuân thủ nghiêm ngặt cấu trúc giao diện thực tế
        $systemInstruction = "Bạn là 'Trợ lý Thực đơn & Dinh dưỡng AI' chuyên sâu cho phần mềm quản lý suất ăn công nghiệp.\n";
        $systemInstruction .= "Dưới đây là thông tin BẮT BUỘC và MÔ TẢ THỰC TẾ về màn hình giao diện mà người dùng đang nhìn thấy (Frontend cấp động):\n";
        $systemInstruction .= "==============================\n";
        $systemInstruction .= $uiDescription . "\n";
        $systemInstruction .= "==============================\n\n";
        $systemInstruction .= "QUY TẮC VÀ NHIỆM VỤ CỦA BẠN (Dựa trên Chế độ hiện tại):\n";
        $systemInstruction .= "1. Chế độ 'Kiểm duyệt thực đơn' (nutrition_analysis): Phân tích, đánh giá xem các chỉ số thực tế đang đạt được đã khớp với chỉ số tiêu chuẩn/mục tiêu chưa. Đưa ra lời khuyên tăng/giảm món hợp lý.\n";
        $systemInstruction .= "2. Chế độ 'Tra cứu món & giá' (dish_lookup): Bắt buộc sử dụng hệ thống công cụ (tools) được cấp bên dưới để truy vấn cơ sở dữ liệu món ăn, giá vốn, calo khi người dùng hỏi.\n";
        $systemInstruction .= "3. Chế độ 'Hướng dẫn điền form / Thao tác' (helpdesk):\n";
        $systemInstruction .= "   - ĐỌC KỸ phần mô tả giao diện động và 'THỨ TỰ THỰC HIỆN' được cung cấp ở trên.\n";
        $systemInstruction .= "   - Chỉ dẫn rành mạch, chính xác từng bước bấm nút gì, điền ô nào, tab nào theo đúng thực tế.\n";
        $systemInstruction .= "   - ⚠️ TUYỆT ĐỐI KHÔNG ĐƯỢC tự bịa ra các nút bấm, ô nhập liệu hoặc luồng xử lý không có trong mô tả giao diện động (Ví dụ: Nếu mô tả bảo Kcal/Ngân sách mục tiêu chỉ để hiển thị, KHÔNG ĐƯỢC bảo người dùng nhập tay vào).\n\n";
        $systemInstruction .= "⚠️ YÊU CẦU TRẢ LỜI: Ngắn gọn, súc tích, gạch đầu dòng rõ ràng, sử dụng icon biểu tượng phù hợp, phong cách thân thiện như một người đồng nghiệp am hiểu hệ thống.";

        // Gom toàn bộ ngữ cảnh dữ liệu dinh dưỡng thực tế 

        $contextString = "Ngữ cảnh số liệu thực tế trên màn hình hiện tại:\n";
        $contextString .= "- Kcal mục tiêu: " . ($formContext['target_calories'] ?? 'Chưa rõ') . " Kcal\n";
        $contextString .= "- Ngân sách mục tiêu: " . ($formContext['target_budget'] ?? 'Chưa rõ') . " VNĐ\n";
        $contextString .= "- Kcal thực tế đang đạt: " . ($formContext['current_calories'] ?? '0') . " Kcal\n";
        $contextString .= "- Chi phí thực tế đang đạt: " . ($formContext['current_cost'] ?? '0') . " VNĐ\n";
        $contextString .= "- Đạm (Protein) thực tế: " . ($formContext['current_protein'] ?? '0') . " g\n";
        $contextString .= "- Béo (Lipid) thực tế: " . ($formContext['current_fat'] ?? '0') . " g\n";
        $contextString .= "- Xơ (Glucid) thực tế: " . ($formContext['current_glucid'] ?? '0') . " g\n";

        if (!empty($formContext['chosen_dishes'])) {
            $contextString .= "- Danh sách các món đã thêm vào thực đơn: " . json_encode($formContext['chosen_dishes'], JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Khai báo danh mục các Tools cấp cho AI
        $tools = $this->defineTools();

        // Thiết lập cấu trúc tin nhắn chuẩn gửi cho Groq API
        $messages = [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => $contextString . "\nChế độ Chat đang chọn: " . $mode . "\nCâu hỏi của người dùng: " . $userMessage]
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $messages,
                    'tools' => $tools,
                    'tool_choice' => 'auto',
                    'temperature' => 0.15 // Hạ thấp nhiệt độ để AI bám sát khống chế, chống tự suy diễn tối đa
                ]);

            if (!$response->successful()) {
                Log::error("Groq API Step 1 Error: " . $response->body());
                return response()->json(['status' => 'error', 'message' => 'Lỗi kết nối hệ thống AI tầng 1.'], 500);
            }

            $responseData = $response->json();
            $aiMessage = $responseData['choices'][0]['message'] ?? null;

            // Kiểm tra và xử lý nếu AI kích hoạt chức năng Gọi hàm (Tool Calls)
            if (!empty($aiMessage['tool_calls'])) {
                $messages[] = $aiMessage;

                foreach ($aiMessage['tool_calls'] as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $functionArgs = json_decode($toolCall['function']['arguments'], true) ?? [];

                    $localResult = $this->executeTargetFunction(
                        $functionName,
                        $functionArgs,
                        $companyId
                    );

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name' => $functionName,
                        'content' => json_encode($localResult, JSON_UNESCAPED_UNICODE)
                    ];
                }

                // Gửi ngược kết quả từ DB lên để AI tổng hợp câu trả lời cuối cùng
                $finalResponse = Http::withToken($this->apiKey)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($this->apiUrl, [
                        'model' => $this->model,
                        'messages' => $messages,
                        'temperature' => 0.15
                    ]);

                if ($finalResponse->successful()) {
                    $finalData = $finalResponse->json();
                    $aiReply = $finalData['choices'][0]['message']['content'] ?? 'Không nhận được câu trả lời từ tổng đài AI.';
                    return response()->json(['status' => 'success', 'data' => trim($aiReply)]);
                }

                Log::error("Groq API Step 3 Error: " . $finalResponse->body());
                return response()->json(['status' => 'error', 'message' => 'Lỗi xử lý tổng hợp dữ liệu sau gọi hàm.'], 500);
            }

            return response()->json([
                'status' => 'success',
                'data' => trim($aiMessage['content'] ?? 'AI không phản hồi dữ liệu.')
            ]);

        } catch (\Exception $e) {
            Log::error("Exception in Function Calling: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Hệ thống AI gặp sự cố trục trặc: ' . $e->getMessage()], 500);
        }
    }

    // Định nghĩa danh sách các hàm (Tools) hỗ trợ AI tra cứu DB
    private function defineTools()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_dishes',
                    'description' => 'Tìm kiếm danh sách món ăn theo tên từ khóa hoặc lấy toàn bộ danh sách món ăn, lọc theo thể loại nếu có.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => ['type' => 'string', 'description' => 'Từ khóa tên món ăn (ví dụ: bò, cá, gà). Để trống nếu muốn lấy toàn bộ.'],
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_dish_statistics',
                    'description' => 'Thống kê tìm ra một món ăn duy nhất theo tiêu chí: đắt nhất, rẻ nhất, nhiều calo nhất, hoặc ít calo nhất.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'criteria' => ['type' => 'string', 'enum' => ['most_expensive', 'cheapest', 'highest_calories', 'lowest_calories'], 'description' => 'Tiêu chí thống kê cần tìm.'],
                            'category' => ['type' => 'string', 'enum' => ['món chính', 'món phụ', 'canh', 'khai vị', 'tráng miệng'], 'description' => 'Giới hạn trong phân loại món ăn cụ thể nào đó.']
                        ],
                        'required' => ['criteria']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'filter_dishes_by_allergy',
                    'description' => 'Lọc ra toàn bộ các món ăn có chứa thành phần gây dị ứng hoặc cảnh báo nguy hiểm từ bảng nguyên liệu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'allergy_tag' => ['type' => 'string', 'enum' => ['đậu nành', 'hải sản', 'gluten', 'sữa', 'trứng'], 'description' => 'Tên tag dị ứng cần dò tìm tận gốc.']
                        ],
                        'required' => ['allergy_tag']
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
                    $query->where('category', 'LIKE', '%' . $args['category'] . '%');
                }
                if (!empty($args['keyword'])) {
                    $query->where('name', 'LIKE', '%' . $args['keyword'] . '%');
                }
                return $query->limit(25)->get()->map(function ($dish) {
                    return [
                        'name' => $dish->name,
                        'category' => $dish->category ?? 'Chưa phân loại',
                        'cost' => $dish->cost_per_serving,
                        'calories' => $dish->calories_per_serving,
                        'allergy_tags' => $dish->allergy_tags
                    ];
                })->toArray();

            case 'get_dish_statistics':
                $query = Dish::where('company_id', $companyId);
                if (!empty($args['category'])) {
                    $query->where('category', 'LIKE', '%' . $args['category'] . '%');
                }

                $criteria = $args['criteria'];
                if ($criteria === 'most_expensive') {
                    $dish = $query->orderByRaw('(estimated_cost / IF(servings > 0, servings, 1)) DESC')->first();
                } elseif ($criteria === 'cheapest') {
                    $dish = $query->where('estimated_cost', '>', 0)->orderByRaw('(estimated_cost / IF(servings > 0, servings, 1)) ASC')->first();
                } elseif ($criteria === 'highest_calories') {
                    $dish = $query->orderByRaw('(total_calories / IF(servings > 0, servings, 1)) DESC')->first();
                } elseif ($criteria === 'lowest_calories') {
                    $dish = $query->where('total_calories', '>', 0)->orderByRaw('(total_calories / IF(servings > 0, servings, 1)) ASC')->first();
                }

                if (!$dish) {
                    return ['message' => 'Không tìm thấy món ăn nào phù hợp tiêu chí này trong hệ thống.'];
                }

                return [
                    'name' => $dish->name,
                    'category' => $dish->category,
                    'cost' => $dish->cost_per_serving,
                    'calories' => $dish->calories_per_serving,
                    'protein' => $dish->protein_per_serving,
                    'fat' => $dish->fat_per_serving,
                    'glucid' => $dish->glucid_per_serving
                ];

            case 'filter_dishes_by_allergy':
                $tag = mb_strtolower($args['allergy_tag'], 'UTF-8');

                $matchedDishes = Dish::where('company_id', $companyId)
                    ->whereHas('ingredients', function ($q) use ($tag) {
                        $q->whereRaw('LOWER(allergy_tags) LIKE ?', ['%' . $tag . '%']);
                    })
                    ->get();

                if ($matchedDishes->isEmpty()) {
                    $allDishes = Dish::where('company_id', $companyId)
                        ->with('ingredients')
                        ->get();
                    $matchedDishes = $allDishes->filter(function ($dish) use ($tag) {
                        if (empty($dish->allergy_tags)) {
                            return false;
                        }
                        $tagsLower = array_map('mb_strtolower', (array) $dish->allergy_tags);
                        return in_array($tag, $tagsLower);
                    });
                }

                if ($matchedDishes->isEmpty()) {
                    return ['message' => "Không có món ăn nào chứa cảnh báo dị ứng '$tag' trong hệ thống."];
                }

                return $matchedDishes->map(function ($dish) {
                    return [
                        'name' => $dish->name,
                        'category' => $dish->category ?? 'Chưa phân loại',
                        'cost' => $dish->cost_per_serving,
                        'calories' => $dish->calories_per_serving
                    ];
                })->values()->toArray();

            default:
                return ['error' => 'Hàm yêu cầu không tồn tại trên hệ thống.'];
        }
    }
}