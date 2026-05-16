@extends('layouts.app')

@section('title', 'Quản lý Món ăn')
@section('page_title', 'Kho Món Ăn Chế Biến')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <div class="relative w-1/3">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" id="searchInput" placeholder="Tìm tên món ăn..."
                class="w-full pl-10 pr-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-green-400 transition">
        </div>

        <button onclick="openDishModal()"
            class="bg-[#86efac] text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-green-400 transition shadow-md">
            <i class="fas fa-plus mr-2"></i>Tạo Món Ăn Mới
        </button>
    </div>

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-700 uppercase"><i class="fas fa-utensils mr-2"></i>Danh sách món ăn</h3>
        </div>

        <div id="dish-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-3 text-center py-10 text-gray-400 italic">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải thực đơn...
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <p id="dish-count" class="text-sm text-gray-500 font-medium"></p>

            <div id="pagination" class="flex space-x-2"></div>
        </div>
    </div>

    <div id="dishModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold text-gray-800">Thiết lập công thức món ăn</h3>
                <button onclick="closeDishModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="dishForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Tên món ăn</label>
                    <input type="text" id="dish_name" required placeholder="Ví dụ: Thịt kho trứng"
                        class="w-full border p-2 rounded-lg outline-none focus:ring-2 focus:ring-green-400 transition">
                </div>

                <div class="border p-4 rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-bold text-green-700">Nguyên liệu thành phần</label>
                        <span class="text-[10px] text-gray-500 italic">* Tính trên đơn vị kg</span>
                    </div>

                    <div id="ingredient-selectors" class="space-y-3">
                    </div>

                    <button type="button" onclick="addIngredientRow()"
                        class="mt-4 flex items-center text-blue-600 text-sm font-bold hover:text-blue-800 transition">
                        <i class="fas fa-plus-circle mr-1"></i> Thêm nguyên liệu vào món
                    </button>
                </div>

                <div class="bg-gray-900 text-white p-4 rounded-xl shadow-inner grid grid-cols-2 gap-4">
                    <div class="border-r border-gray-700 pr-2">
                        <p class="text-[10px] text-gray-400 uppercase font-semibold">Tổng năng lượng</p>
                        <p class="text-xl font-bold text-green-400"><span id="total-calories">0</span> <small
                                class="text-xs">Kcal</small></p>
                    </div>
                    <div class="pl-2">
                        <p class="text-[10px] text-gray-400 uppercase font-semibold">Giá vốn ước tính</p>
                        <p class="text-xl font-bold text-[#86efac]"><span id="total-cost">0</span> <small
                                class="text-xs">đ</small></p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeDishModal()"
                        class="px-5 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition font-medium">Hủy</button>
                    <button type="submit"
                        class="px-8 py-2 bg-[#86efac] text-gray-900 rounded-lg font-bold hover:bg-green-400 shadow-md transition">
                        Lưu Món Ăn
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.token = localStorage.getItem('access_token');
        let allIngredients = [];

        if (!token) {
            console.error("Không tìm thấy mã xác thực (Token). Vui lòng đăng nhập lại.");
        }

        window.loadData = async function () {
            try {
                const resIng = await fetch('/api/ingredients', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await resIng.json();
                allIngredients = result.data || [];

                loadDishes();
            } catch (err) {
                console.error("Lỗi tải dữ liệu:", err);
            }
        }

        let typingTimer;

        // Tìm kiếm
        document.getElementById('searchInput').addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            const searchTerm = this.value;
            typingTimer = setTimeout(() => {
                loadDishes(searchTerm);
            }, 500);
        });

        async function loadDishes(search = '', page = 1) {
            const listContainer = document.getElementById('dish-list');
            try {
                const res = await fetch(
                    `/api/dishes?page=${page}&search=${encodeURIComponent(search)}`,
                    {
                        headers: { 'Authorization': 'Bearer ' + token }
                    }
                );
                const response = await res.json();
                const dishes = response.data;

                if (dishes.length === 0) {
                    listContainer.innerHTML = `<div class="col-span-3 text-center py-10 text-gray-400 italic">Chưa có món ăn nào được tạo.</div>`;
                    return;
                }

                listContainer.innerHTML = dishes.map(dish => {
                    // Render tags dị ứng từ Model Dish gửi về
                    const tagHtml = dish.allergy_tags && dish.allergy_tags.length > 0
                        ? dish.allergy_tags.map(tag => `<span class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full font-bold mr-1 border border-red-200">${tag}</span>`).join('')
                        : '<span class="text-[10px] text-gray-400 italic">Không có cảnh báo</span>';

                    return `
                                                                                                                <div class="bg-white border rounded-xl overflow-hidden hover:shadow-lg transition group">
                                                                                                                    <div class="p-4">
                                                                                                                        <div class="flex justify-between items-start mb-2">
                                                                                                                            <h4 class="font-bold text-gray-800 group-hover:text-green-600 transition">${dish.name}</h4>
                                                                                                                            <div class="flex space-x-2">
                                                                                                                                <button onclick="editDish(${dish.id})" class="text-gray-300 hover:text-blue-500 transition">
                                                                                                                                    <i class="fas fa-edit text-sm"></i>
                                                                                                                                </button>
                                                                                                                                <button onclick="deleteDish(${dish.id})" class="text-gray-300 hover:text-red-500 transition">
                                                                                                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                                                                                                </button>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                        <div class="flex flex-wrap gap-1 mb-3">${tagHtml}</div>
                                                                                                                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-gray-50 p-2 rounded-lg">
                                                                                                                                <div class="flex flex-col">
                                                                                                                                    <span class="text-gray-400">Năng lượng</span>
                                                                                                                                    <span class="font-bold text-orange-600">${Math.round(dish.total_calories)} Kcal</span>
                                                                                                                                </div>
                                                                                                                                <div class="flex flex-col">
                                                                                                                                    <span class="text-gray-400">Giá vốn/suất</span>
                                                                                                                                    <span class="font-bold text-blue-600">${Math.round(dish.estimated_cost).toLocaleString()}đ</span>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                    `;
                }).join('');
                renderPagination(response, search);
            } catch (err) {
                listContainer.innerHTML = `<p class="text-red-500 text-center col-span-3">Lỗi khi tải danh sách món!</p>`;
            }
        }

        function renderPagination(response, search = '') {
            const pagination = document.getElementById('pagination');
            const dishCount = document.getElementById('dish-count');

            // Hiển thị tổng số món
            dishCount.innerHTML = `Có <b>${response.total}</b> món ăn`;

            let html = '';

            // <<
            html += `
                <button
                    onclick="loadDishes('${search}', 1)"
                    ${response.current_page === 1 ? 'disabled' : ''}
                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">
                    &laquo;
                </button>
                `;

            // <
            html += `
                <button
                    onclick="loadDishes('${search}', ${response.current_page - 1})"
                    ${response.current_page === 1 ? 'disabled' : ''}
                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">
                    &lt;
                </button>
                `;

            // số trang
            for (let i = 1; i <= response.last_page; i++) {
                html += `
                    <button
                        onclick="loadDishes('${search}', ${i})"
                        class="px-3 py-1 rounded border ${i === response.current_page
                        ? 'bg-green-500 text-white'
                        : 'bg-white hover:bg-gray-100'
                    }">
                        ${i}
                    </button>
                    `;
            }

            // >
            html += `
                <button
                    onclick="loadDishes('${search}', ${response.current_page + 1})"
                    ${response.current_page === response.last_page ? 'disabled' : ''}
                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">
                    &gt;
                </button>
                `;

            // >>
            html += `
                <button
                    onclick="loadDishes('${search}', ${response.last_page})"
                    ${response.current_page === response.last_page ? 'disabled' : ''}
                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 disabled:opacity-50">
                    &raquo;
                </button>
                `;

            pagination.innerHTML = html;
        }

        function addIngredientRow() {
            const container = document.getElementById('ingredient-selectors');
            const row = document.createElement('div');
            row.className = "flex space-x-2 items-center ing-row bg-white p-2 rounded-lg border border-gray-100 shadow-sm";
            row.innerHTML = `
                                                                                                                            <select class="flex-1 border-none bg-transparent p-1 rounded text-sm ing-select focus:ring-0" onchange="calculateNutrients()">
                                                                                                                                <option value="">-- Chọn thực phẩm --</option>
                                                                                                                                ${allIngredients.map(i => `<option value="${i.id}" data-calo="${i.calories}" data-price="${i.price_per_kg}">${i.name}</option>`).join('')}
                                                                                                                            </select>

                                                                                                                            <div class="flex items-center bg-gray-100 px-2 rounded-md">
                                                                                                                                <input type="number" step="any" placeholder="0" class="w-16 bg-transparent border-none p-1 text-sm ing-weight focus:ring-0 text-right" oninput="calculateNutrients()">
                                                                                                                                <select class="text-[10px] bg-transparent border-none font-bold ml-1 focus:ring-0 ing-unit" onchange="calculateNutrients()">
                                                                                                                                    <option value="kg">kg</option>
                                                                                                                                    <option value="g">gam</option>
                                                                                                                                </select>
                                                                                                                            </div>

                                                                                                                            <button type="button" onclick="this.parentElement.remove(); calculateNutrients()" class="text-gray-300 hover:text-red-500 px-2 transition">
                                                                                                                                <i class="fas fa-minus-circle"></i>
                                                                                                                            </button>
                                                                                                                        `;
            container.appendChild(row);
        }

        // Tính toán thời gian thực (Real-time)
        function calculateNutrients() {
            let totalCalo = 0;
            let totalCost = 0;

            document.querySelectorAll('.ing-row').forEach(row => {
                const select = row.querySelector('.ing-select');
                const weightInput = parseFloat(row.querySelector('.ing-weight').value) || 0;
                const unit = row.querySelector('.ing-unit').value;
                const option = select.options[select.selectedIndex];

                if (option.value) {
                    // Chuyển đổi về kg để tính toán với giá tiền (vì giá là theo kg)
                    let weightInKg = (unit === 'g') ? weightInput / 1000 : weightInput;

                    totalCalo += (parseFloat(option.dataset.calo) * weightInKg);
                    totalCost += (parseFloat(option.dataset.price) * weightInKg);
                }
            });

            document.getElementById('total-calories').innerText = Math.round(totalCalo);
            document.getElementById('total-cost').innerText = Math.round(totalCost).toLocaleString();
        }

        function openDishModal() {
            document.getElementById('dishModal').classList.remove('hidden');
            document.getElementById('dishForm').reset();
            document.getElementById('ingredient-selectors').innerHTML = "";
            document.getElementById('total-calories').innerText = "0";
            document.getElementById('total-cost').innerText = "0";
            addIngredientRow();
        }

        function closeDishModal() {
            document.getElementById('dishModal').classList.add('hidden');
        }

        document.getElementById('dishForm').onsubmit = async (e) => {
            e.preventDefault();

            const ingredients = [];
            document.querySelectorAll('.ing-row').forEach(row => {
                const id = row.querySelector('.ing-select').value;
                const weightInput = parseFloat(row.querySelector('.ing-weight').value);
                const unit = row.querySelector('.ing-unit').value;

                if (id && weightInput) {
                    const weightInGram = (unit === 'kg') ? weightInput * 1000 : weightInput;
                    ingredients.push({ id, weight: weightInGram });
                }
            });

            if (ingredients.length === 0) return alert("Vui lòng thêm ít nhất 1 nguyên liệu!");

            const isEdit = typeof editingDishId !== 'undefined' && editingDishId !== null;
            const url = isEdit ? `/api/dishes/${editingDishId}` : '/api/dishes';
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                name: document.getElementById('dish_name').value,
                ingredients: ingredients
            };

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    alert(isEdit ? "Cập nhật món ăn thành công!" : "Lưu món ăn thành công!");
                    editingDishId = null;
                    closeDishModal();
                    loadDishes();
                } else {
                    const error = await res.json();
                    alert("Lỗi: " + (error.message || "Không thể thực hiện thao tác"));
                }
            } catch (err) {
                alert("Lỗi kết nối server!");
                console.error(err);
            }
        };

        async function deleteDish(id) {
            if (!confirm("Bạn có chắc chắn muốn xóa công thức món này?")) return;
            try {
                const res = await fetch(`/api/dishes/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                if (res.ok) loadDishes();
            } catch (err) { alert("Lỗi khi xóa!"); }
        }

        let editingDishId = null;

        async function editDish(id) {
            editingDishId = id;
            try {
                const res = await fetch(`/api/dishes/${id}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const dish = await res.json();

                document.getElementById('dishModal').classList.remove('hidden');
                document.getElementById('dish_name').value = dish.name;
                document.getElementById('ingredient-selectors').innerHTML = "";

                dish.ingredients.forEach(ing => {
                    addIngredientRowWithData(ing.id, ing.pivot.weight);
                });

                calculateNutrients();
            } catch (err) { alert("Không thể tải thông tin món ăn!"); }
        }

        function addIngredientRowWithData(id, weightInGram) {
            addIngredientRow();
            const rows = document.querySelectorAll('.ing-row');
            const lastRow = rows[rows.length - 1];

            lastRow.querySelector('.ing-select').value = id;
            lastRow.querySelector('.ing-weight').value = weightInGram;
            lastRow.querySelector('.ing-unit').value = 'g';
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof token !== 'undefined') loadData();
        });
    </script>
@endsection