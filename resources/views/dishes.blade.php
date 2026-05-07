@extends('layouts.app')

@section('title', 'Quản lý Món ăn')
@section('page_title', 'Kho Món Ăn Chế Biến')@section('content')<div
        class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-700 uppercase"><i class="fas fa-utensils mr-2"></i>Danh sách món ăn</h3>
            <button onclick="openDishModal()"
                class="bg-[#86efac] text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-green-400 transition shadow-md">
                <i class="fas fa-plus mr-2"></i>Tạo Món Ăn Mới
            </button>
        </div>

        <div id="dish-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        </div>
    </div>

    <div id="dishModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold">Thiết lập công thức món ăn</h3>
                <button onclick="closeDishModal()" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times"></i></button>
            </div>

            <form id="dishForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Tên món ăn</label>
                    <input type="text" id="dish_name" required placeholder="Ví dụ: Thịt kho trứng"
                        class="w-full border p-2 rounded outline-none">
                </div>

                <div class="border p-4 rounded-lg bg-gray-50">
                    <label class="block text-sm font-bold mb-3 text-green-700">Chọn nguyên liệu (Công ty cung cấp)</label>
                    <div id="ingredient-selectors" class="space-y-3">
                    </div>
                    <button type="button" onclick="addIngredientRow()"
                        class="mt-3 text-blue-600 text-sm font-bold hover:underline">
                        + Thêm nguyên liệu vào món
                    </button>
                </div>

                <div class="bg-gray-900 text-white p-4 rounded-lg flex justify-between">
                    <div>Tổng Calories: <span id="total-calories" class="font-bold text-[#86efac]">0</span> Kcal</div>
                    <div>Ước tính giá vốn: <span id="total-cost" class="font-bold text-[#86efac]">0</span>đ</div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeDishModal()" class="px-4 py-2 bg-gray-100 rounded-lg">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-[#86efac] text-gray-900 rounded-lg font-bold">Lưu món
                        ăn</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let allIngredients = []; // Chứa danh sách từ Công ty

        window.loadData = async function () {
            // 1. Load nguyên liệu để chuẩn bị cho Modal
            const resIng = await fetch('/api/ingredients', { headers: { 'Authorization': 'Bearer ' + token } });
            allIngredients = await resIng.json();

            // 2. Load danh sách món ăn đã tạo
            loadDishes();
        }

        function addIngredientRow() {
            const container = document.getElementById('ingredient-selectors');
            const row = document.createElement('div');
            row.className = "flex space-x-2 items-center ing-row";
            row.innerHTML = `
                        <select class="flex-1 border p-2 rounded text-sm ing-select" onchange="calculateNutrients()">
                            <option value="">-- Chọn thực phẩm --</option>
                            ${allIngredients.map(i => `<option value="${i.id}" data-calo="${i.calories}" data-price="${i.price_per_kg}">${i.name}</option>`).join('')}
                        </select>
                        <input type="number" step="0.01" placeholder="kg" class="w-20 border p-2 rounded text-sm ing-weight" oninput="calculateNutrients()">
                        <button type="button" onclick="this.parentElement.remove(); calculateNutrients()" class="text-red-500 p-2"><i class="fas fa-trash"></i></button>
                    `;
            container.appendChild(row);
        }

        function calculateNutrients() {
            let totalCalo = 0;
            let totalCost = 0;
            document.querySelectorAll('.ing-row').forEach(row => {
                const select = row.querySelector('.ing-select');
                const weight = row.querySelector('.ing-weight').value || 0;
                const option = select.options[select.selectedIndex];

                if (option.value) {
                    totalCalo += (option.dataset.calo * weight);
                    totalCost += (option.dataset.price * weight);
                }
            });
            document.getElementById('total-calories').innerText = Math.round(totalCalo);
            document.getElementById('total-cost').innerText = Math.round(totalCost).toLocaleString();
        }

        function openDishModal() {
            document.getElementById('dishModal').classList.remove('hidden');
            if (document.getElementById('ingredient-selectors').innerHTML === "") addIngredientRow();
        }

        function closeDishModal() { document.getElementById('dishModal').classList.add('hidden'); }

        // Xử lý lưu món ăn 
        document.getElementById('dishForm').onsubmit = async (e) => {
            e.preventDefault();

            // Thu thập dữ liệu nguyên liệu từ các dòng đã chọn
            const ingredients = [];
            document.querySelectorAll('.ing-row').forEach(row => {
                const id = row.querySelector('.ing-select').value;
                const weight = row.querySelector('.ing-weight').value;
                if (id && weight) ingredients.push({ id, weight });
            });

            const payload = {
                name: document.getElementById('dish_name').value,
                ingredients: ingredients,
                total_calories: document.getElementById('total-calories').innerText,
                total_cost: document.getElementById('total-cost').innerText.replace(/,/g, '')
            };

            const res = await fetch('/api/dishes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                alert("Lưu công thức thành công!");
                closeDishModal();
                loadDishes(); // Gọi hàm load lại danh sách món ăn
            }
        };
    </script>
@endsection