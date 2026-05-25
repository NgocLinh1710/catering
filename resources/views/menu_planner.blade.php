@extends('layouts.app')

@section('title', 'Lập Thực Đơn Mỗi Ngày')
@section('page_title', 'Hệ Thống Lập Thực Đơn Theo Cấu Hình Đối Tượng')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="selector-wrapper" class="hidden mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-layer-group text-green-500 mr-2"></i>Chọn Đơn
                vị & Nhóm đối tượng ăn mục tiêu</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">1. Chọn Khách hàng / Đơn vị</label>
                    <select id="select-unit" onchange="onUnitSelectorChange()"
                        class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-green-400 outline-none text-sm bg-gray-50 font-medium">
                        <option value="">-- Chọn khách hàng phụ trách --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">2. Chọn Nhóm đối tượng</label>
                    <select id="select-audience" onchange="onAudienceSelectorChange()" disabled
                        class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-green-400 outline-none text-sm bg-gray-50 font-medium">
                        <option value="">-- Vui lòng chọn đơn vị trước --</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="planner-main-content" class="hidden">
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-xl font-bold">
                        <i class="fas fa-school text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800" id="txt-display-unit-name">Đang xác định...</h2>
                        <p class="text-sm text-gray-500 font-medium">Đối tượng áp dụng: <span
                                class="text-green-600 font-bold" id="txt-display-audience-name">Chưa rõ</span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase">Ngày lập thực đơn</label>
                        <input type="date" id="menu-date"
                            class="border rounded-lg p-2 text-sm font-semibold text-gray-700 outline-none focus:border-green-500">
                    </div>
                    <button onclick="saveDailyMenu()"
                        class="h-10 px-5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg shadow-md transition flex items-center mt-4">
                        <i class="fas fa-save mr-2"></i> Lưu Thực Đơn
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-bold text-gray-800 text-sm uppercase mb-4 flex items-center border-b pb-2">
                            <i class="fas fa-users text-blue-500 mr-2"></i> Số lượng suất ăn hôm nay
                        </h3>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Dự kiến số lượng suất nấu:</label>
                            <input type="number" id="serving-quantity" value="100" min="1" oninput="reCalculateBudget()"
                                class="w-full text-center text-xl font-bold bg-gray-50 border rounded-lg p-2 text-gray-800 focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                        <h3 class="font-bold text-gray-800 text-sm uppercase flex items-center border-b pb-2">
                            <i class="fas fa-calculator text-amber-500 mr-2"></i> Tóm tắt ngân sách & Dinh dưỡng
                        </h3>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500 font-medium">Chi phí / suất ăn:</span>
                                <span class="font-bold text-gray-800"><span id="calc-current-cost">0</span> / <span
                                        id="target-budget-per">0</span> VNĐ</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div id="bar-budget" class="bg-green-500 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500 font-medium">Năng lượng (Calories):</span>
                                <span class="font-bold text-gray-800"><span id="calc-current-calories">0</span> / <span
                                        id="target-calories-per">0</span> Kcal</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div id="bar-calories" class="bg-amber-500 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs font-medium text-gray-600">
                            <div class="flex justify-between">
                                <span>Đạm (Protein):</span> <span id="calc-protein">0g</span> / <span
                                    id="target-protein">0g</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Béo (Lipid):</span> <span id="calc-fat">0g</span> / <span id="target-fat">0g</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Chất xơ (Glucid):</span> <span id="calc-fiber">0g</span> / <span
                                    id="target-fiber">0g</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm uppercase mb-3"><i
                                class="fas fa-plus-circle text-green-500 mr-1"></i>Thêm món ăn vào thực đơn ngày</h3>
                        <div class="flex gap-2">
                            <select id="select-dish-pool"
                                class="flex-1 p-2 border rounded-lg text-sm font-medium bg-gray-50 outline-none focus:ring-2 focus:ring-green-400">
                                <option value="">-- Đang tải kho món ăn... --</option>
                            </select>
                            <button onclick="addDishToMenu()"
                                class="px-5 bg-[#86efac] hover:bg-green-400 text-gray-900 font-bold rounded-lg text-sm transition shadow-md">
                                <i class="fas fa-plus mr-1"></i>
                                Thêm món
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-700 text-sm uppercase mb-3 flex justify-between">
                            <span>Danh sách món ăn được chọn</span>
                            <span class="text-xs text-gray-400 font-normal normal-case italic" id="count-dishes">Chưa có món
                                nào</span>
                        </h3>
                        <div class="border rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr
                                        class="bg-gray-50 border-b border-gray-100 text-gray-500 font-bold text-xs uppercase">
                                        <th class="p-3">Tên món ăn</th>
                                        <th class="p-3 text-center">Calories</th>
                                        <th class="p-3 text-right">Giá tham khảo</th>
                                        <th class="p-3 text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="selected-dish-tbody" class="divide-y divide-gray-100">
                                    <tr>
                                        <td colspan="4" class="p-8 text-center text-gray-400 italic">Chưa chọn món ăn nào
                                            cho ngày này. Hãy bấm thêm món ở trên!</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let selectedUnitId = null;
        let selectedAudienceId = null;
        let allDishesPool = [];
        let chosenDishes = [];
        let targetSpecs = { budget: 0, calories: 0, protein: 0, fat: 0, fiber: 0 };

        document.addEventListener('DOMContentLoaded', async () => {
            // Đặt ngày mặc định là hôm nay
            document.getElementById('menu-date').value = new Date().toISOString().split('T')[0];

            // Đổi ngày để tự động load lại thực đơn cũ của ngày đó (nếu có)
            document.getElementById('menu-date').addEventListener('change', fetchMenuOfSelectedDate);

            const urlParams = new URLSearchParams(window.location.search);
            selectedUnitId = urlParams.get('unit_id');
            selectedAudienceId = urlParams.get('audience_id');

            await fetchAllDishesPool();

            if (selectedUnitId && selectedAudienceId) {
                // TH1: Ấn nút "Thực đơn" ở giao diện thiết lập tiêu chuẩn (Đã truyền sẵn ID qua URL)
                document.getElementById('selector-wrapper').classList.add('hidden');
                await loadSpecificConfiguration(selectedUnitId, selectedAudienceId);
            } else {
                // TH2: Ấn từ Sidebar "Lập thực đơn" -> Hiển thị dropdown để chọn từng bước
                document.getElementById('selector-wrapper').classList.remove('hidden');
                await initUnitSelectorDropdown();
            }
        });

        async function fetchAllDishesPool() {
            try {
                const res = await fetch('/api/quan-ly-mon-an', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                    }
                });

                if (!res.ok) throw new Error("Không thể tải kho món ăn");

                const responseData = await res.json();
                const rawDishes = responseData.data || [];

                // Tính dữ liệu theo khẩu phần
                allDishesPool = rawDishes.map(d => {

                    const servings = parseFloat(d.servings || 1);

                    return {
                        ...d,

                        cost_per_serving:
                            parseFloat(d.estimated_cost || 0) / servings,

                        calories_per_serving:
                            parseFloat(d.total_calories || 0) / servings,

                        protein_per_serving:
                            parseFloat(d.total_protein || 0) / servings,

                        fat_per_serving:
                            parseFloat(d.lipid || 0) / servings,

                        glucid_per_serving:
                            parseFloat(d.glucid || 0) / servings
                    };
                });

                const selectPool = document.getElementById('select-dish-pool');

                if (allDishesPool.length === 0) {
                    selectPool.innerHTML =
                        '<option value="">Kho trống (Vui lòng tạo món ăn trước)</option>';
                    return;
                }

                selectPool.innerHTML =
                    '<option value="">-- Click để chọn món ăn phối hợp --</option>' +
                    allDishesPool.map(d =>
                        `<option value="${d.id}">
                                                                                ${d.name} (${Math.round(d.cost_per_serving || 0).toLocaleString()} VNĐ / suất)
                                                                                </option>`
                    ).join('');

            } catch (e) {
                console.error("Lỗi lấy kho món ăn:", e);

                document.getElementById('select-dish-pool').innerHTML =
                    '<option value="">Lỗi kết nối dữ liệu kho món ăn</option>';
            }
        }

        // TH1: Khởi tạo danh sách đơn vị phụ trách
        async function initUnitSelectorDropdown() {
            try {
                const res = await fetch('/api/my-thiet-lap-tieu-chuan', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                    }
                });
                if (!res.ok) throw new Error("Không thể tải danh sách khách hàng");

                const units = await res.json();
                const selectUnit = document.getElementById('select-unit');
                selectUnit.innerHTML = '<option value="">-- Chọn khách hàng phụ trách --</option>' +
                    units.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
            } catch (e) {
                console.error(e);
            }
        }

        async function onUnitSelectorChange() {
            const unitId = document.getElementById('select-unit').value;
            const selectAudience = document.getElementById('select-audience');

            if (!unitId) {
                selectAudience.disabled = true;
                selectAudience.innerHTML = '<option value="">-- Vui lòng chọn đơn vị trước --</option>';
                document.getElementById('planner-main-content').classList.add('hidden');
                return;
            }

            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                    }
                });
                const result = await res.json();
                const audiences = result.data || result || [];

                selectAudience.disabled = false;
                if (audiences.length === 0) {
                    selectAudience.innerHTML = '<option value="">Đơn vị này chưa cấu hình nhóm! Khởi tạo trước</option>';
                } else {
                    selectAudience.innerHTML = '<option value="">-- Chọn nhóm đối tượng nấu --</option>' +
                        audiences.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
                }
            } catch (e) {
                console.error(e);
            }
        }

        function onAudienceSelectorChange() {
            const unitId = document.getElementById('select-unit').value;
            const audienceId = document.getElementById('select-audience').value;
            if (unitId && audienceId) {
                loadSpecificConfiguration(unitId, audienceId);
            } else {
                document.getElementById('planner-main-content').classList.add('hidden');
            }
        }

        async function loadSpecificConfiguration(unitId, audienceId) {
            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                    }
                });
                const result = await res.json();
                const audiences = result.data || result || [];
                const currentAudience = audiences.find(item => item.id == audienceId);

                if (!currentAudience) {
                    alert("Không tìm thấy cấu hình định mức phù hợp cho đối tượng này!");
                    return;
                }

                selectedUnitId = unitId;
                selectedAudienceId = audienceId;

                targetSpecs.budget = parseFloat(currentAudience.budget_per_serving) || 0;
                targetSpecs.calories = parseFloat(currentAudience.target_calories) || 0;
                targetSpecs.protein = parseFloat(currentAudience.target_protein) || 0;
                targetSpecs.fat = parseFloat(currentAudience.target_fat) || 0;
                targetSpecs.fiber = parseFloat(currentAudience.target_fiber) || 0;

                document.getElementById('txt-display-unit-name').innerText = currentAudience.unit ? currentAudience.unit.name : (document.getElementById('select-unit').options[document.getElementById('select-unit').selectedIndex]?.text || "Đơn vị đang chọn");
                document.getElementById('txt-display-audience-name').innerText = currentAudience.name;

                document.getElementById('target-budget-per').innerText = targetSpecs.budget.toLocaleString();
                document.getElementById('target-calories-per').innerText = targetSpecs.calories;
                document.getElementById('target-protein').innerText = targetSpecs.protein + 'g';
                document.getElementById('target-fat').innerText = targetSpecs.fat + 'g';
                document.getElementById('target-fiber').innerText = targetSpecs.fiber + 'g';

                document.getElementById('planner-main-content').classList.remove('hidden');

                await fetchMenuOfSelectedDate();

            } catch (e) {
                console.error("Lỗi cấu hình giao diện chi tiết:", e);
            }
        }

        async function fetchMenuOfSelectedDate() {
            if (!selectedAudienceId) return;
            const date = document.getElementById('menu-date').value;

            try {
                const res = await fetch(`/api/daily-menus/by-date?target_audience_id=${selectedAudienceId}&date=${date}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                    }
                });
                const resData = await res.json();

                if (resData.status === 'success' && resData.data) {
                    chosenDishes = resData.data.dishes || [];
                    document.getElementById('serving-quantity').value = resData.data.servings;
                } else {
                    chosenDishes = [];
                    document.getElementById('serving-quantity').value = 100;
                }
                renderSelectedDishesTable();
                reCalculateBudget();
            } catch (error) {
                console.error("Lỗi đồng bộ dữ liệu ngày:", error);
            }
        }

        function addDishToMenu() {
            const selectPool = document.getElementById('select-dish-pool');
            const dishId = selectPool.value;
            if (!dishId) return;

            if (chosenDishes.some(d => d.id == dishId)) {
                alert("Món ăn này đã có sẵn trong danh sách thực đơn hôm nay rồi!");
                return;
            }

            const foundDish = allDishesPool.find(d => d.id == dishId);
            if (foundDish) {
                chosenDishes.push(foundDish);
                renderSelectedDishesTable();
                reCalculateBudget();
            }
            selectPool.value = "";
        }

        function removeDish(dishId) {
            chosenDishes = chosenDishes.filter(d => d.id != dishId);
            renderSelectedDishesTable();
            reCalculateBudget();
        }

        function renderSelectedDishesTable() {
            const tbody = document.getElementById('selected-dish-tbody');
            document.getElementById('count-dishes').innerText = `Đã chọn ${chosenDishes.length} món ăn`;

            if (chosenDishes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-gray-400 italic">Chưa chọn món ăn nào cho ngày này. Hãy bấm thêm món ở trên!</td></tr>`;
                return;
            }

            tbody.innerHTML = chosenDishes.map(d => `
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="p-3">
                                                    <div class="font-semibold text-gray-800">
                                                        ${d.name}
                                                    </div>

                                                    <div class="text-[11px] text-gray-400 mt-1">
                                                        ${d.servings || 1} suất / món
                                                    </div>
                                                </td>

                                                <td class="p-3 text-center font-medium text-gray-600">
                                                    ${parseFloat(d.calories_per_serving || 0).toFixed(1)} Kcal
                                                </td>

                                                <td class="p-3 text-right font-bold text-gray-700">
                                                    ${Math.round(d.cost_per_serving || 0).toLocaleString()}đ
                                                </td>

                                                <td class="p-3 text-center">
                                                    <button onclick="removeDish(${d.id})"
                                                        class="text-red-500 hover:text-red-700 p-1">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('');
        }

        function reCalculateBudget() {
            let totalCost = 0;
            let totalCalories = 0;
            let totalProtein = 0;
            let totalFat = 0;
            let totalFiber = 0;

            chosenDishes.forEach(d => {
                totalCost += parseFloat(d.cost_per_serving || 0);

                totalCalories += parseFloat(d.calories_per_serving || 0);

                totalProtein += parseFloat(d.protein_per_serving || 0);

                totalFat += parseFloat(d.fat_per_serving || 0);

                totalFiber += parseFloat(d.glucid_per_serving || 0);
            });

            document.getElementById('calc-current-cost').innerText =
                Math.round(totalCost).toLocaleString();

            document.getElementById('calc-current-calories').innerText =
                totalCalories.toFixed(1);

            document.getElementById('calc-protein').innerText =
                totalProtein.toFixed(1) + 'g';

            document.getElementById('calc-fat').innerText =
                totalFat.toFixed(1) + 'g';

            document.getElementById('calc-fiber').innerText =
                totalFiber.toFixed(1) + 'g';

            const budgetBar = document.getElementById('bar-budget');
            if (targetSpecs.budget > 0) {
                let pctBudget = (totalCost / targetSpecs.budget) * 100;
                budgetBar.style.width = Math.min(pctBudget, 100) + '%';
                budgetBar.className = totalCost > targetSpecs.budget ? "bg-red-500 h-2 rounded-full" : "bg-green-500 h-2 rounded-full";
            } else {
                budgetBar.style.width = '0%';
            }

            const caloriesBar = document.getElementById('bar-calories');
            if (targetSpecs.calories > 0) {
                let pctCalo = (totalCalories / targetSpecs.calories) * 100;
                caloriesBar.style.width = Math.min(pctCalo, 100) + '%';
                if (totalCalories > targetSpecs.calories + 200) {
                    caloriesBar.className = "bg-orange-500 h-2 rounded-full";
                } else if (totalCalories < targetSpecs.calories - 200) {
                    caloriesBar.className = "bg-amber-400 h-2 rounded-full";
                } else {
                    caloriesBar.className = "bg-green-500 h-2 rounded-full";
                }
            } else {
                caloriesBar.style.width = '0%';
            }
        }

        async function saveDailyMenu() {
            if (chosenDishes.length === 0) {
                alert("Vui lòng chọn ít nhất 1 món ăn vào thực đơn trước khi thực hiện lưu trữ dữ liệu!");
                return;
            }

            const uId = selectedUnitId || document.getElementById('select-unit').value;
            const aId = selectedAudienceId || document.getElementById('select-audience').value;

            if (!uId || !aId) {
                alert("Vui lòng xác định rõ Đơn vị và Đối tượng ăn trước khi lưu!");
                return;
            }

            const payload = {
                unit_id: parseInt(uId),
                target_audience_id: parseInt(aId),
                date: document.getElementById('menu-date').value,
                servings: parseInt(document.getElementById('serving-quantity').value) || 0,
                dish_ids: chosenDishes.map(d => d.id)
            };

            try {
                const res = await fetch('/api/daily-menus', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok) {
                    alert("Tuyệt vời! Thực đơn ngày hôm nay đã được phê duyệt và lưu trữ thành công!");
                } else {
                    alert("Thất bại: " + (data.message || "Lỗi lưu dữ liệu"));
                }
            } catch (e) {
                alert("Lỗi hệ thống mất kết nối máy chủ API.");
            }
        }
    </script>
@endsection