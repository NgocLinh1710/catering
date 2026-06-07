@extends('layouts.app')

@section('title', 'Lập Thực Đơn Hàng Ngày')
@section('page_title', 'Hệ Thống Lập Thực Đơn Theo Từng Đối Tượng')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div id="selector-wrapper" class="hidden mb-6 bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-black text-gray-800 mb-5">
                <i class="fas fa-layer-group text-green-500 mr-2"></i> Chọn Khách hàng & Nhóm đối tượng
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-2">1. Chọn khách hàng</label>
                    <select id="select-unit" onchange="onUnitSelectorChange()"
                        class="w-full p-3 rounded-2xl border border-gray-200 bg-gray-50 outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">-- Chọn khách hàng --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-2">2. Chọn nhóm đối tượng</label>
                    <select id="select-audience" onchange="onAudienceSelectorChange()" disabled
                        class="w-full p-3 rounded-2xl border border-gray-200 bg-gray-50 outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">-- Chọn khách hàng trước --</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="planner-main-content" class="hidden">

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex flex-wrap gap-4 justify-between items-center">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center">
                            <i class="fas fa-utensils text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800" id="txt-display-unit-name">Đang tải thông tin...
                            </h2>
                            <p class="text-sm text-gray-500">Nhóm: <span class="font-black text-green-600"
                                    id="txt-display-audience-name">---</span></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Ngày áp dụng thực
                                đơn</label>
                            <input type="date" id="menu-date"
                                class="border border-gray-200 rounded-2xl p-2.5 outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                        <button onclick="saveDailyMenu()"
                            class="h-11 px-5 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-black shadow transition">
                            <i class="fas fa-save mr-2"></i> Lưu thực đơn ngày
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <div class="lg:col-span-4 space-y-6">

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 space-y-4">
                        <h3 class="font-black text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-users text-blue-500 mr-2"></i> Số lượng suất
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Suất thường (SL)</label>
                                <input type="number" id="normal-servings" min="0" value="100"
                                    oninput="reCalculateNutrition()"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 font-black text-gray-800 focus:ring-2 focus:ring-green-300 outline-none bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Suất chay (SL)</label>
                                <input type="number" id="vegetarian-servings" min="0" value="0"
                                    oninput="reCalculateNutrition()"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 font-black text-gray-800 focus:ring-2 focus:ring-green-300 outline-none bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-red-700 mb-1">Tổng suất dị ứng</label>
                            <input type="number" id="allergy-servings" value="0" readonly
                                class="w-full rounded-xl border border-red-200 px-3 py-2.5 font-black text-red-800 bg-red-50/50 outline-none cursor-not-allowed">
                        </div>

                        <div class="border border-red-100 bg-red-50/20 rounded-2xl p-3 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-black text-red-800 uppercase">Cấu hình nhóm dị ứng</span>
                                <button type="button" onclick="addNewAllergyGroup()"
                                    class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-black px-2.5 py-1 rounded-lg transition">
                                    ➕ Thêm nhóm
                                </button>
                            </div>
                            <div id="allergy-groups-container" class="space-y-2">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                        <h3 class="font-black text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-chart-pie text-amber-500 mr-2"></i> Giám sát tiêu chuẩn thực đơn
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1 text-xs font-bold">
                                    <span class="text-gray-500">Chi phí / suất thực tế</span>
                                    <span class="font-black"><span id="calc-current-cost">0</span> / <span
                                            id="target-budget-per">0</span>đ</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div id="bar-budget" class="h-2.5 bg-green-500 rounded-full transition-all duration-300"
                                        style="width:0%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1 text-xs font-bold">
                                    <span class="text-gray-500">Calories / suất thực tế</span>
                                    <span class="font-black"><span id="calc-current-calories">0</span> / <span
                                            id="target-calories-per">0</span> kcal</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div id="bar-calories"
                                        class="h-2.5 bg-amber-500 rounded-full transition-all duration-300"
                                        style="width:0%"></div>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-3 text-xs space-y-2 font-medium">
                                <div class="flex justify-between"><span>Đạm (Protein):</span><span class="font-bold"><span
                                            id="calc-protein">0g</span> / <span id="target-protein">0g</span></span></div>
                                <div class="flex justify-between"><span>Béo (Fat):</span><span class="font-bold"><span
                                            id="calc-fat">0g</span> / <span id="target-fat">0g</span></span></div>
                                <div class="flex justify-between"><span>Xơ (Glucid):</span><span class="font-bold"><span
                                            id="calc-fiber">0g</span> / <span id="target-fiber">0g</span></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 flex flex-col">
                    <div class="flex flex-wrap border-b border-gray-100 mb-5 gap-1" id="meal-tabs-wrapper">
                    </div>

                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-xs font-black uppercase text-gray-400 mb-2">
                                Thêm món vào: <span id="txt-current-tab-label" class="text-green-600">Suất bình
                                    thường</span>
                            </div>
                            <button type="button" onclick="triggerAutoOptimizeMenu()"
                                class="text-xs bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black px-3 py-1.5 rounded-xl shadow-sm transition flex items-center gap-1">
                                <i class="fas fa-bolt animate-bounce"></i>Tự động tối ưu
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-8">
                                <select id="select-dish-pool"
                                    class="w-full border border-gray-200 rounded-xl p-2.5 bg-white outline-none focus:ring-2 focus:ring-green-300 text-sm">
                                    <option value="">-- Đang tải món ăn --</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <input type="number" id="dish-quantity" min="1" value="1"
                                    class="w-full border border-gray-200 rounded-xl p-2.5 bg-white text-center font-black outline-none focus:ring-2 focus:ring-green-300 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <button onclick="addDishToMenu()"
                                    class="w-full h-full bg-green-500 hover:bg-green-600 text-white font-black rounded-xl text-sm transition">Thêm</button>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-100 rounded-2xl overflow-hidden flex-1">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="p-3 text-left">Tên món ăn</th>
                                    <th class="p-3 text-center w-24">SL ĐL</th>
                                    <th class="p-3 text-center">Calories / Suất</th>
                                    <th class="p-3 text-right">Chi phí / Món</th>
                                    <th class="p-3 text-center w-16">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="selected-dish-tbody">
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-400 italic">Chưa có món ăn nào cho
                                        loại suất này</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div id="qr-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 animate-fade-in">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl border border-gray-100">
            <h3 class="text-lg font-black text-gray-800 mb-2">
                <i class="fas fa-qrcode text-green-500 mr-1"></i> Mã QR Thực Đơn Hôm Nay
            </h3>
            <p class="text-xs text-gray-400 mb-4">Quét mã dưới đây để xem thực đơn trên thiết bị di động</p>

            <div
                class="flex justify-center p-3 bg-gray-50 rounded-2xl inline-block mx-auto border border-gray-200 shadow-inner">
                <img id="qr-image" src="" alt="Mã QR Thực đơn" class="w-48 h-48">
            </div>

            <div class="grid grid-cols-2 gap-3 mt-5">
                <button onclick="window.open(document.getElementById('qr-image').src, '_blank')"
                    class="p-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                    <i class="fas fa-download mr-1"></i> Tải ảnh QR
                </button>
                <button onclick="document.getElementById('qr-modal').classList.add('hidden')"
                    class="p-3 bg-green-500 hover:bg-green-600 text-white font-bold text-xs rounded-xl transition shadow-md shadow-green-200">
                    Đóng lại
                </button>
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
        let currentActiveTab = 'normal'; // 'normal', 'vegetarian', 'allergy_nhom_0', 'allergy_nhom_1'...

        // Mảng lưu danh sách các phân nhóm dị ứng động 
        let allergyGroups = [];
        let targetSpecs = { budget: 0, calories: 0, protein: 0, fat: 0, fiber: 0 };

        document.addEventListener('DOMContentLoaded', async () => {
            const style = document.createElement('style');
            style.innerHTML = `
                            .select2-results__options{
                                max-height:250px !important;
                                overflow-y:auto !important;
                            }

                            .select2-container{
                                width:100% !important;
                            }

                            .select2-container--default .select2-selection--single{
                                height:42px !important;
                                border:1px solid #e5e7eb !important;
                                border-radius:12px !important;
                            }

                            .select2-selection__rendered{
                                line-height:42px !important;
                            }

                            .select2-selection__arrow{
                                height:42px !important;
                            }
                        `;
            document.head.appendChild(style);

            const token = localStorage.getItem('access_token');
            if (!token) {
                alert("Phiên đăng nhập hết hạn!");
                window.location.href = "/login";
                return;
            }

            document.getElementById('menu-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('menu-date').addEventListener('change', fetchMenuOfSelectedDate);

            const params = new URLSearchParams(window.location.search);
            selectedUnitId = params.get('unit_id');
            selectedAudienceId = params.get('audience_id');

            await fetchAllDishesPool();

            if (selectedUnitId && selectedAudienceId) {
                await loadSpecificConfiguration(selectedUnitId, selectedAudienceId);
            } else {
                document.getElementById('selector-wrapper').classList.remove('hidden');
                await initUnitSelectorDropdown();
            }
        });

        function addNewAllergyGroup(name = '', servings = 0, keyword = '') {
            allergyGroups.push({ name: name, servings: parseInt(servings || 0), keyword: keyword });
            renderAllergyGroupsAndTabs();
            reCalculateNutrition();
        }

        // Xóa nhóm dị ứng
        function removeAllergyGroup(index) {
            chosenDishes = chosenDishes.filter(d => d.meal_type !== `allergy_nhom_${index}`);
            chosenDishes.forEach(d => {
                if (d.meal_type.startsWith('allergy_nhom_')) {
                    let currentIdx = parseInt(d.meal_type.replace('allergy_nhom_', ''));
                    if (currentIdx > index) {
                        d.meal_type = `allergy_nhom_${currentIdx - 1}`;
                    }
                }
            });

            allergyGroups.splice(index, 1);
            if (currentActiveTab === `allergy_nhom_${index}` || currentActiveTab.startsWith('allergy_nhom_')) {
                currentActiveTab = 'normal';
            }

            renderAllergyGroupsAndTabs();
            reCalculateNutrition();
        }

        function updateAllergyGroupValue(index, field, value) {
            if (field === 'servings') {
                allergyGroups[index][field] = parseInt(value) || 0;
            } else {
                allergyGroups[index][field] = value;
            }

            // Tính tổng số suất dị ứng hiển thị lên giao diện
            let totalAllergy = allergyGroups.reduce((sum, g) => sum + (parseInt(g.servings) || 0), 0);
            document.getElementById('allergy-servings').value = totalAllergy;

            renderTabsWrapperOnly();
            renderDishPool();
            reCalculateNutrition();
        }

        function renderAllergyGroupsAndTabs() {
            const container = document.getElementById('allergy-groups-container');
            let totalAllergy = 0;

            container.innerHTML = allergyGroups.map((g, i) => {
                totalAllergy += (parseInt(g.servings) || 0);
                return `
                                                                                                                                                                            <div class="space-y-1 bg-white p-2 rounded-xl border border-red-100 shadow-sm relative">
                                                                                                                                                                                <input type="text" placeholder="Tên nhóm (VD: Khách dị ứng tôm)" value="${g.name}" 
                                                                                                                                                                                    oninput="updateAllergyGroupValue(${i}, 'name', this.value)"
                                                                                                                                                                                    class="w-full text-xs p-1.5 rounded border font-bold text-gray-700 outline-none">
                                                                                                                                                                                <div class="grid grid-cols-2 gap-2">
                                                                                                                                                                                    <input type="number" placeholder="Số suất" value="${g.servings || ''}" 
                                                                                                                                                                                        oninput="updateAllergyGroupValue(${i}, 'servings', this.value)"
                                                                                                                                                                                        class="w-full text-xs p-1.5 rounded border text-center font-bold outline-none">
                                                                                                                                                                                    <input type="text" placeholder="Từ khóa (VD: tôm)" value="${g.keyword}" 
                                                                                                                                                                                        oninput="updateAllergyGroupValue(${i}, 'keyword', this.value)"
                                                                                                                                                                                        class="w-full text-xs p-1.5 rounded border text-red-600 bg-red-50/50 font-black outline-none">
                                                                                                                                                                                </div>
                                                                                                                                                                                <button type="button" onclick="removeAllergyGroup(${i})" class="absolute top-1 right-2 text-red-400 hover:text-red-600 text-[10px]">
                                                                                                                                                                                    <i class="fas fa-times"></i>
                                                                                                                                                                                </button>
                                                                                                                                                                            </div>
                                                                                                                                                                        `;
            }).join('');

            document.getElementById('allergy-servings').value = totalAllergy;
            renderTabsWrapperOnly();
        }

        function renderTabsWrapperOnly() {
            const wrapper = document.getElementById('meal-tabs-wrapper');
            let countNormal = chosenDishes.filter(d => d.meal_type === 'normal').length;
            let countVeg = chosenDishes.filter(d => d.meal_type === 'vegetarian').length;

            let tabsHtml = `
                                                                                                                                                                        <button onclick="switchMealTab('normal')" id="tab-btn-normal" class="py-3 px-4 text-sm font-bold border-b-2 focus:outline-none flex items-center gap-2">
                                                                                                                                                                            🟢 Suất Thường <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold">${countNormal}</span>
                                                                                                                                                                        </button>
                                                                                                                                                                        <button onclick="switchMealTab('vegetarian')" id="tab-btn-vegetarian" class="py-3 px-4 text-sm font-bold border-b-2 focus:outline-none flex items-center gap-2">
                                                                                                                                                                            🟡 Suất Chay <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold">${countVeg}</span>
                                                                                                                                                                        </button>
                                                                                                                                                                    `;

            allergyGroups.forEach((g, i) => {
                let countGroup = chosenDishes.filter(d => d.meal_type === `allergy_nhom_${i}`).length;
                tabsHtml += `
                                                                                                                                                                            <button onclick="switchMealTab('allergy_nhom_${i}')" id="tab-btn-allergy_nhom_${i}" class="py-3 px-4 text-sm font-bold border-b-2 focus:outline-none flex items-center gap-2">
                                                                                                                                                                                🔴 ${g.name || 'Nhóm dị ứng ' + (i + 1)} (${g.servings || 0}s) 
                                                                                                                                                                                <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-bold">${countGroup}</span>
                                                                                                                                                                            </button>
                                                                                                                                                                        `;
            });

            wrapper.innerHTML = tabsHtml;
            highlightTabsUI();
        }

        function highlightTabsUI() {
            ['normal', 'vegetarian'].forEach(t => {
                const btn = document.getElementById(`tab-btn-${t}`);
                if (!btn) return;
                if (t === currentActiveTab) {
                    btn.className = "py-3 px-4 text-sm font-black border-b-2 border-green-500 text-green-600 focus:outline-none flex items-center gap-2";
                } else {
                    btn.className = "py-3 px-4 text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-gray-600 focus:outline-none flex items-center gap-2";
                }
            });

            allergyGroups.forEach((g, i) => {
                const btn = document.getElementById(`tab-btn-allergy_nhom_${i}`);
                if (!btn) return;
                if (`allergy_nhom_${i}` === currentActiveTab) {
                    btn.className = "py-3 px-4 text-sm font-black border-b-2 border-red-500 text-red-600 bg-red-50/40 focus:outline-none flex items-center gap-2";
                } else {
                    btn.className = "py-3 px-4 text-sm font-bold border-b-2 border-transparent text-gray-400 hover:text-red-500 focus:outline-none flex items-center gap-2";
                }
            });

            // Cập nhật text nhãn khu vực thêm món ăn
            let txtLabel = document.getElementById('txt-current-tab-label');
            if (currentActiveTab === 'normal') { txtLabel.innerText = "Suất bình thường"; txtLabel.className = "text-green-600 font-bold"; }
            else if (currentActiveTab === 'vegetarian') { txtLabel.innerText = "Suất ăn chay"; txtLabel.className = "text-amber-500 font-bold"; }
            else {
                let idx = parseInt(currentActiveTab.replace('allergy_nhom_', ''));
                txtLabel.innerText = allergyGroups[idx]?.name || "Nhóm suất dị ứng";
                txtLabel.className = "text-red-600 font-black animate-pulse";
            }
        }

        // Bộ lọc khóa Dropdown món ăn theo đúng tất cả các tag
        function renderDishPool() {
            const select = document.getElementById('select-dish-pool');
            if (!select) return;

            select.innerHTML = '<option value="">-- Chọn món ăn đưa vào thực đơn --</option>' +
                allDishesPool.map(d => {
                    let isBlocked = false;
                    let blockedReason = '';

                    let dishTagsText = '';
                    if (d.warning_tags && typeof d.warning_tags === 'string') dishTagsText += d.warning_tags.toLowerCase() + ' ';
                    if (d.dish_tags && Array.isArray(d.dish_tags)) dishTagsText += d.dish_tags.join(' ').toLowerCase() + ' ';
                    if (d.allergy_tags && Array.isArray(d.allergy_tags)) dishTagsText += d.allergy_tags.join(' ').toLowerCase() + ' ';

                    // Chặn tag suất chay: Chặn tuyệt đối món chứa Thịt, Hải sản, Cá, Thân mềm, Giáp xác
                    if (currentActiveTab === 'vegetarian') {
                        if (dishTagsText.includes('thịt')) { isBlocked = true; blockedReason = 'MÓN MẶN / Thịt'; }
                        else if (dishTagsText.includes('hải sản')) { isBlocked = true; blockedReason = 'MÓN MẶN / Hải sản'; }
                        else if (dishTagsText.includes('cá')) { isBlocked = true; blockedReason = 'MÓN MẶN / Cá'; }
                        else if (dishTagsText.includes('thân mềm')) { isBlocked = true; blockedReason = 'MÓN MẶN / Thân mềm'; }
                        else if (dishTagsText.includes('giáp xác')) { isBlocked = true; blockedReason = 'MÓN MẶN / Giáp xác'; }
                    }

                    // Chặn tag dị ứng
                    if (currentActiveTab.startsWith('allergy_nhom_')) {
                        let idx = parseInt(currentActiveTab.replace('allergy_nhom_', ''));
                        let rawKeyword = allergyGroups[idx]?.keyword?.trim()?.toLowerCase();

                        if (rawKeyword) {
                            let keywordsArray = rawKeyword.split(',').map(k => k.trim()).filter(k => k !== '');

                            // Duyệt qua từng từ khóa
                            for (let i = 0; i < keywordsArray.length; i++) {
                                let singleKey = keywordsArray[i];
                                if (singleKey && dishTagsText.includes(singleKey)) {
                                    isBlocked = true;
                                    blockedReason = `DỊ ỨNG: ${singleKey.toUpperCase()}`;
                                    break;
                                }
                            }
                        }
                    }

                    if (isBlocked) {
                        return `<option value="${d.id}" disabled style="color: #dc2626; background-color: #fef2f2; font-weight: bold;">
                                                                                                                                                        ⚠️ [${blockedReason}] ${d.name} (${Math.round(d.cost_per_serving).toLocaleString()}đ)
                                                                                                                                                    </option>`;
                    }

                    return `<option value="${d.id}">${d.name} (${Math.round(d.cost_per_serving).toLocaleString()}đ)</option>`;
                }).join('');

            if ($('#select-dish-pool').hasClass("select2-hidden-accessible")) {
                $('#select-dish-pool').select2('destroy');
            }

            $('#select-dish-pool').select2({
                placeholder: '-- Chọn món ăn đưa vào thực đơn --',
                width: '100%',
                allowClear: true,
                dropdownParent: $('#planner-main-content')
            });
        }

        function switchMealTab(tabName) {
            currentActiveTab = tabName;
            highlightTabsUI();
            renderDishPool();
            renderSelectedDishesTable();
            reCalculateNutrition();
        }

        async function fetchAllDishesPool() {
            try {
                const res = await fetch('/api/quan-ly-mon-an/all', {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' }
                });
                const json = await res.json();
                const raw = json.data || [];

                allDishesPool = raw.map(d => {
                    const servings = parseFloat(d.servings || 1);
                    return {
                        ...d,
                        cost_per_serving: parseFloat(d.estimated_cost || 0) / servings,
                        calories_per_serving: parseFloat(d.total_calories || 0) / servings,
                        protein_per_serving: parseFloat(d.total_protein || 0) / servings,
                        fat_per_serving: parseFloat(d.lipid || 0) / servings,
                        glucid_per_serving: parseFloat(d.glucid || 0) / servings
                    };
                });
                renderDishPool();
            } catch (e) {
                console.error(e);
                document.getElementById('select-dish-pool').innerHTML = '<option>Lỗi tải món ăn</option>';
            }
        }

        async function initUnitSelectorDropdown() {
            try {
                const res = await fetch('/api/my-thiet-lap-tieu-chuan', {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' }
                });
                const units = await res.json();
                document.getElementById('select-unit').innerHTML = '<option value="">-- Chọn khách hàng --</option>' +
                    units.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
            } catch (e) { console.error(e); }
        }

        async function onUnitSelectorChange() {
            const unitId = document.getElementById('select-unit').value;
            const audienceSelect = document.getElementById('select-audience');
            if (!unitId) {
                audienceSelect.disabled = true;
                audienceSelect.innerHTML = '<option value="">-- Chọn khách hàng trước --</option>';
                return;
            }
            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' }
                });
                const json = await res.json();
                const audiences = json.data || [];
                audienceSelect.disabled = false;
                audienceSelect.innerHTML = '<option value="">-- Chọn nhóm đối tượng --</option>' +
                    audiences.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
            } catch (e) { console.error(e); }
        }

        function onAudienceSelectorChange() {
            const unitId = document.getElementById('select-unit').value;
            const audienceId = document.getElementById('select-audience').value;
            if (unitId && audienceId) loadSpecificConfiguration(unitId, audienceId);
        }

        async function loadSpecificConfiguration(unitId, audienceId) {
            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' }
                });
                const json = await res.json();
                const audiences = json.data || [];
                const audience = audiences.find(a => a.id == audienceId);

                if (!audience) { alert("Không tìm thấy cấu hình!"); return; }

                selectedUnitId = unitId;
                selectedAudienceId = audienceId;

                targetSpecs = {
                    budget: parseFloat(audience.budget_per_serving || 0),
                    calories: parseFloat(audience.target_calories || 0),
                    protein: parseFloat(audience.target_protein || 0),
                    fat: parseFloat(audience.target_fat || 0),
                    fiber: parseFloat(audience.target_fiber || 0)
                };

                document.getElementById('planner-main-content').classList.remove('hidden');
                document.getElementById('txt-display-unit-name').innerText = audience.unit?.name || 'Khách hàng';
                document.getElementById('txt-display-audience-name').innerText = audience.name;

                document.getElementById('target-budget-per').innerText = targetSpecs.budget.toLocaleString();
                document.getElementById('target-calories-per').innerText = targetSpecs.calories;
                document.getElementById('target-protein').innerText = targetSpecs.protein + 'g';
                document.getElementById('target-fat').innerText = targetSpecs.fat + 'g';
                document.getElementById('target-fiber').innerText = targetSpecs.fiber + 'g';

                renderAllergyGroupsAndTabs();
                renderDishPool();
                await fetchMenuOfSelectedDate();
            } catch (e) { console.error(e); }
        }

        async function fetchMenuOfSelectedDate() {
            if (!selectedAudienceId) return;
            const date = document.getElementById('menu-date').value;

            try {
                const res = await fetch(`/api/daily-menus/by-date?target_audience_id=${selectedAudienceId}&date=${date}`, {
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' }
                });
                const json = await res.json();

                if (json.status === 'success' && json.data) {
                    allergyGroups = Array.isArray(json.data.allergy_notes) ? json.data.allergy_notes : [];

                    chosenDishes = (json.data.dishes || []).map(d => {
                        const fullDish = allDishesPool.find(x => x.id == d.id);
                        return {
                            ...(fullDish || d),
                            quantity: d.quantity || 1,
                            meal_type: d.pivot?.meal_type || d.meal_type || 'normal'
                        };
                    });

                    document.getElementById('normal-servings').value = json.data.normal_servings || 0;
                    document.getElementById('vegetarian-servings').value = json.data.vegetarian_servings || 0;
                } else {
                    chosenDishes = [];
                    allergyGroups = [];
                    document.getElementById('normal-servings').value = 100;
                    document.getElementById('vegetarian-servings').value = 0;
                }

                renderAllergyGroupsAndTabs();
                renderSelectedDishesTable();
                reCalculateNutrition();
            } catch (e) { console.error(e); }
        }

        function addDishToMenu() {
            const select = document.getElementById('select-dish-pool');
            const dishId = select.value;
            const quantity = parseInt(document.getElementById('dish-quantity').value || 1);

            if (!dishId) { alert("Vui lòng chọn món ăn!"); return; }

            if (chosenDishes.some(d => d.id == dishId && d.meal_type === currentActiveTab)) {
                alert("Món ăn này đã được thêm vào nhóm suất ăn này rồi!");
                return;
            }

            const dish = allDishesPool.find(d => d.id == dishId);
            if (!dish) { alert("Không tìm thấy món ăn!"); return; }

            chosenDishes.push({ ...dish, quantity, meal_type: currentActiveTab });

            renderSelectedDishesTable();
            renderTabsWrapperOnly(); // Cập nhật lại số lượng badge đếm trên tab
            reCalculateNutrition();

            select.value = '';
            document.getElementById('dish-quantity').value = 1;
        }

        function removeDish(id) {
            chosenDishes = chosenDishes.filter(d => !(d.id == id && d.meal_type === currentActiveTab));
            renderSelectedDishesTable();
            renderTabsWrapperOnly();
            reCalculateNutrition();
        }

        function updateDishQuantity(id, quantity) {
            const dish = chosenDishes.find(d => d.id == id && d.meal_type === currentActiveTab);
            if (!dish) return;
            dish.quantity = parseInt(quantity || 1);
            reCalculateNutrition();
        }

        function renderSelectedDishesTable() {
            const tbody = document.getElementById('selected-dish-tbody');
            const filteredDishes = chosenDishes.filter(d => d.meal_type === currentActiveTab);

            if (filteredDishes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-10 text-center text-gray-400 italic">Chưa có món ăn nào trong nhóm suất ăn này.</td></tr>`;
                return;
            }

            tbody.innerHTML = filteredDishes.map(d => `
                                                                                                                                                                        <tr class="border-t hover:bg-gray-50 transition">
                                                                                                                                                                            <td class="p-4">
                                                                                                                                                                                <div class="font-bold text-gray-800">${d.name}</div>
                                                                                                                                                                            </td>
                                                                                                                                                                            <td class="p-4 text-center">
                                                                                                                                                                                <input type="number" min="1" value="${d.quantity || 1}"
                                                                                                                                                                                    onchange="updateDishQuantity(${d.id}, this.value)"
                                                                                                                                                                                    class="w-20 text-center border border-gray-200 rounded-xl px-2 py-1">
                                                                                                                                                                            </td>
                                                                                                                                                                            <td class="p-4 text-center">${(navigator_calories(d) * Number(d.quantity || 1)).toFixed(1)}</td>
                                                                                                                                                                            <td class="p-4 text-right font-black">${Math.round((d.cost_per_serving || 0) * (d.quantity || 1)).toLocaleString()}đ</td>
                                                                                                                                                                            <td class="p-4 text-center">
                                                                                                                                                                                <button onclick="removeDish(${d.id})" class="text-red-500 hover:text-red-700 transition">
                                                                                                                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                                                                                                                </button>
                                                                                                                                                                            </td>
                                                                                                                                                                        </tr>
                                                                                                                                                                    `).join('');
        }

        function navigator_calories(d) {
            return parseFloat(d.calories_per_serving || d.total_calories || 0);
        }

        function reCalculateNutrition() {
            const filteredDishes = chosenDishes.filter(d => d.meal_type === currentActiveTab);
            let totalCost = 0; let totalCalories = 0; let totalProtein = 0; let totalFat = 0; let totalFiber = 0;

            filteredDishes.forEach(d => {
                const quantity = Number(d.quantity || 1);
                totalCost += parseFloat(d.cost_per_serving || 0) * quantity;
                totalCalories += navigator_calories(d) * quantity;
                totalProtein += parseFloat(d.protein_per_serving || d.total_protein || 0) * quantity;
                totalFat += parseFloat(d.fat_per_serving || d.lipid || 0) * quantity;
                totalFiber += parseFloat(d.glucid_per_serving || d.glucid || 0) * quantity;
            });

            document.getElementById('calc-current-cost').innerText = Math.round(totalCost).toLocaleString();
            document.getElementById('calc-current-calories').innerText = totalCalories.toFixed(1);
            document.getElementById('calc-protein').innerText = totalProtein.toFixed(1) + 'g';
            document.getElementById('calc-fat').innerText = totalFat.toFixed(1) + 'g';
            document.getElementById('calc-fiber').innerText = totalFiber.toFixed(1) + 'g';

            updateProgressBar('bar-budget', totalCost, targetSpecs.budget);
            updateProgressBar('bar-calories', totalCalories, targetSpecs.calories);
        }

        function updateProgressBar(id, current, target) {
            const bar = document.getElementById(id);
            if (!target || target <= 0) { bar.style.width = '0%'; return; }
            let percent = Math.min((current / target) * 100, 100);
            bar.style.width = percent + '%';

            if (current > target) {
                bar.classList.remove('bg-green-500', 'bg-amber-500');
                bar.classList.add('bg-red-500');
            } else {
                if (id === 'bar-budget') { bar.classList.remove('bg-red-500'); bar.classList.add('bg-green-500'); }
                else { bar.classList.remove('bg-red-500'); bar.classList.add('bg-amber-500'); }
            }
        }

        async function saveDailyMenu() {
            if (!selectedUnitId || !selectedAudienceId) {
                alert("Vui lòng chọn khách hàng!"); return;
            }

            const normalServings = parseInt(document.getElementById('normal-servings').value) || 0;
            const vegetarianServings = parseInt(document.getElementById('vegetarian-servings').value) || 0;
            const totalAllergyServings = allergyGroups.reduce((sum, g) => sum + (parseInt(g.servings) || 0), 0);

            const totalServings = normalServings + vegetarianServings + totalAllergyServings;

            if (chosenDishes.length === 0) {
                alert("Vui lòng chọn ít nhất một món ăn!"); return;
            }

            const payload = {
                unit_id: Number(selectedUnitId),
                target_audience_id: Number(selectedAudienceId),
                date: document.getElementById('menu-date').value,
                servings: Number(totalServings),
                normal_servings: Number(normalServings),
                vegetarian_servings: Number(vegetarianServings),
                allergy_servings: Number(totalAllergyServings),
                allergy_notes: allergyGroups,
                dishes: chosenDishes.map(d => ({
                    id: d.id,
                    quantity: Number(d.quantity || 1),
                    meal_type: d.meal_type
                }))
            };

            try {
                const res = await fetch('/api/daily-menus', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (res.ok) {
                    // Hiển thị QR
                    const menuDate = document.getElementById('menu-date').value;
                    const publicMenuUrl = `${window.location.origin}/public/menu?date=${menuDate}&target_audience_id=${selectedAudienceId}`;
                    const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(publicMenuUrl)}`;
                    const qrImage = document.getElementById('qr-image');
                    const qrModal = document.getElementById('qr-modal');

                    if (qrImage && qrModal) {
                        qrImage.src = qrApiUrl;
                        qrModal.classList.remove('hidden');
                    }

                    alert("Hệ thống đã lưu thực đơn và tự động khởi tạo mã QR phân phối thành công!");

                    await fetchMenuOfSelectedDate();
                } else {
                    alert(json.message || 'Lưu thất bại!');
                }
            } catch (e) {
                console.error(e);
                alert("Lỗi hệ thống kết nối Server!");
            }
        }
        // Tối ưu Thực đơn
        async function triggerAutoOptimizeMenu() {
            if (!selectedAudienceId) { alert("Vui lòng cấu hình Khách hàng và Đối tượng trước!"); return; }

            // Xác định mảng từ khóa cấm dựa trên Tab hiện tại đang đứng để gửi sang Python lọc
            let forbiddenKeywords = [];
            if (currentActiveTab === 'vegetarian') {
                forbiddenKeywords = ['thịt', 'hải sản'];
            } else if (currentActiveTab.startsWith('allergy_nhom_')) {
                let idx = parseInt(currentActiveTab.replace('allergy_nhom_', ''));
                let rawKeyword = allergyGroups[idx]?.keyword?.trim()?.toLowerCase();
                if (rawKeyword) {
                    forbiddenKeywords = rawKeyword.split(',').map(k => k.trim()).filter(k => k !== '');
                }
            }

            if (confirm(`Hệ thống AI sẽ tự động quét toàn bộ kho ${allDishesPool.length} món ăn để thiết lập thực đơn nháp tối ưu chi phí thấp nhất và đạt chuẩn chỉ số dinh dưỡng cho riêng suất này. Bạn có muốn tiếp tục?`)) {

                try {
                    // Hiển thị trạng thái chờ 
                    document.getElementById('selected-dish-tbody').innerHTML = `
                                                                                                                                        <tr><td colspan="5" class="p-10 text-center text-indigo-600 font-bold">
                                                                                                                                            <i class="fas fa-spinner fa-spin mr-2"></i> Đang tính toán...
                                                                                                                                        </td></tr>`;

                    const res = await fetch('/api/daily-menus/auto-generate', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('access_token'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            target_audience_id: Number(selectedAudienceId),
                            forbidden_keywords: forbiddenKeywords,
                            all_dishes: allDishesPool // Gửi tất cả món ăn có sẵn cho Python tính toán
                        })
                    });

                    const json = await res.json();

                    if (json.status === 'success') {
                        // Xóa bỏ các món cũ thuộc tab này trong danh sách nhớ tạm thời để nạp bản nháp mới
                        chosenDishes = chosenDishes.filter(d => d.meal_type !== currentActiveTab);

                        // Đổ kết quả tối ưu của Python vào mảng quản lý giao diện
                        json.dishes.forEach(item => {
                            const originDish = allDishesPool.find(x => x.id == item.id);
                            if (originDish) {
                                chosenDishes.push({
                                    ...originDish,
                                    quantity: item.quantity,
                                    meal_type: currentActiveTab
                                });
                            }
                        });

                        renderSelectedDishesTable();
                        renderTabsWrapperOnly();
                        reCalculateNutrition();
                        alert("Tìm thấy thực đơn tối ưu thành công! Mời bạn kiểm tra bản nháp trên bảng.");
                    } else {
                        alert(json.message || "Không tìm thấy phương án thực đơn tối ưu.");
                        renderSelectedDishesTable();
                    }

                } catch (e) {
                    console.error(e);
                    alert("Lỗi kết nối bộ xử lý toán học Python.");
                    renderSelectedDishesTable();
                }
            }
        }
    </script>
@endsection