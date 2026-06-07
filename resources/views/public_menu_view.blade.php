<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn Hôm Nay</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">

    <div class="max-w-7xl mx-auto min-h-screen bg-white shadow-lg">

        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-b-3xl text-center shadow-md">

            <h1 class="text-2xl font-black">
                <i class="fas fa-utensils mr-2"></i>
                THỰC ĐƠN HÔM NAY
            </h1>

            <p class="mt-2 text-sm opacity-90">
                {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </p>

            <div class="mt-3 inline-block bg-white/20 px-4 py-1 rounded-full text-xs font-bold">
                {{ $audience->name ?? 'Đối tượng công khai' }}
            </div>

        </div>

        <div class="p-4">

            <div class="flex gap-4 overflow-x-auto pb-4">

                // Suất thường
                @php
                    $normalDishes = $menu->dishes->where('pivot.meal_type', 'normal');
                @endphp

                @if($normalDishes->count())
                    <div class="min-w-[280px] bg-white border rounded-2xl shadow-sm flex-shrink-0">

                        <div class="bg-green-50 border-b p-4">
                            <h3 class="font-black text-green-700 text-lg">
                                🟢 Suất Thường
                            </h3>
                        </div>

                        <div class="p-3 space-y-2">

                            @foreach($normalDishes as $dish)

                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">

                                    <div class="font-semibold text-sm text-gray-800">
                                        {{ $dish->name }}
                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>
                @endif


                // Suất chay
                @php
                    $vegDishes = $menu->dishes->where('pivot.meal_type', 'vegetarian');
                @endphp

                @if($vegDishes->count())

                    <div class="min-w-[280px] bg-white border rounded-2xl shadow-sm flex-shrink-0">

                        <div class="bg-amber-50 border-b p-4">
                            <h3 class="font-black text-amber-700 text-lg">
                                🟡 Suất Chay
                            </h3>
                        </div>

                        <div class="p-3 space-y-2">

                            @foreach($vegDishes as $dish)

                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">

                                    <div class="font-semibold text-sm text-gray-800">
                                        {{ $dish->name }}
                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif


                // Nhóm dị ứng
                @if(!empty($menu->allergy_notes))

                    @foreach($menu->allergy_notes as $index => $group)

                        @php
                            $allergyDishes = $menu->dishes->where(
                                'pivot.meal_type',
                                'allergy_nhom_' . $index,
                            );
                        @endphp

                        @if($allergyDishes->count())

                            <div class="min-w-[280px] bg-white border rounded-2xl shadow-sm flex-shrink-0">

                                <div class="bg-red-50 border-b p-4">

                                    <h3 class="font-black text-red-700 text-lg">
                                        🔴 {{ $group['name'] ?? ('Nhóm dị ứng ' . ($index + 1)) }}
                                    </h3>

                                </div>

                                <div class="p-3 space-y-2">

                                    @foreach($allergyDishes as $dish)

                                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">

                                            <div class="font-semibold text-sm text-gray-800">
                                                {{ $dish->name }}
                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endif

                    @endforeach

                @endif

            </div>

        </div>

        // Phần ghi chú
        @if($menu->allergy_servings > 0)

            <div class="px-4 pb-6">

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">

                    <div class="font-bold text-amber-800 mb-2">
                        <i class="fas fa-triangle-exclamation mr-1"></i>
                        Lưu ý dị ứng
                    </div>

                    <div class="text-sm text-amber-700">
                        Các suất ăn dành cho nhóm dị ứng đã được tách riêng. Vui lòng nhận đúng phần ăn được chỉ định.
                    </div>

                </div>

            </div>

        @endif

    </div>

</body>

</html>