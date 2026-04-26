@extends('layouts.app')

@section('title', 'Quản lý Món ăn')
@section('page_title', 'Quản lý Kho Món Ăn')

@section('content')
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-64">
                <input type="text" placeholder="Tìm kiếm món ăn..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none"
                    onfocus="this.style.boxShadow='0 0 0 2px #86efac'" onblur="this.style.boxShadow='none'">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <button onclick="openModal()" class="px-4 py-2 rounded-lg shadow-md transition flex items-center text-gray-800"
                style="background-color: #86efac;" onmouseover="this.style.backgroundColor='#4ade80'"
                onmouseout="this.style.backgroundColor='#86efac'">
                <i class="fas fa-plus mr-2"></i> Thêm món mới
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                    <th class="p-4">ID</th>
                    <th class="p-4">Tên món ăn</th>
                    <th class="p-4">Tổng Calo</th>
                    <th class="p-4">Tags Dị ứng</th>
                </tr>
            </thead>
            <tbody id="dish-table-body">
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="addDishModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 w-96 shadow-xl rounded-xl bg-white">
            <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Thêm món ăn mới</h3>
            <form id="addDishForm">
                <input type="text" id="dishName" placeholder="Tên món" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">
                <input type="number" id="dishCalories" placeholder="Calo" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">
                <textarea id="dishInstructions" placeholder="Cách làm"
                    class="w-full p-2 mb-4 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300"></textarea>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Hủy</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg shadow text-gray-800 transition bg-[#86efac] hover:bg-[#4ade80]">Lưu
                        món</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function loadDishes() {
            fetch('/api/dishes', {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
            })
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('dish-table-body');
                    tableBody.innerHTML = '';
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(dish => {
                            let tags = dish.dish_tags ? dish.dish_tags.join(', ') : '<span class="text-gray-400 text-sm">Không có</span>';
                            tableBody.innerHTML += `
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-4 text-gray-500">#${dish.id}</td>
                                        <td class="p-4 font-semibold text-gray-800">${dish.name}</td>
                                        <td class="p-4 text-green-600 font-bold">${dish.total_calories} kcal</td>
                                        <td class="p-4 text-sm text-red-500">${tags}</td>
                                    </tr>`;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Chưa có món ăn nào.</td></tr>';
                    }
                });
        }

        const modal = document.getElementById('addDishModal');
        function openModal() { modal.classList.remove('hidden'); }
        function closeModal() { modal.classList.add('hidden'); document.getElementById('addDishForm').reset(); }

        document.getElementById('addDishForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const requestData = {
                name: document.getElementById('dishName').value,
                total_calories: document.getElementById('dishCalories').value,
                instructions: document.getElementById('dishInstructions').value,
                ingredients: { 1: { quantity: 0.2 } }
            };

            fetch('/api/dishes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify(requestData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') { closeModal(); loadDishes(); }
                    else { alert('Lỗi: ' + (data.message || 'Kiểm tra lại dữ liệu')); }
                });
        });

        document.addEventListener('DOMContentLoaded', loadDishes);
    </script>
@endsection