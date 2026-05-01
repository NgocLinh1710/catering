@extends('layouts.app')

@section('title', 'Quản lý Món ăn')
@section('page_title', 'Danh mục Món ăn & Dinh dưỡng')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-2">
                <div class="relative w-64">
                    <input type="text" id="searchInput" placeholder="Tìm tên món ăn..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300">
                    <i class="fas fa-utensils absolute left-3 top-3 text-gray-400"></i>
                </div>
                <select id="filterCategory" onchange="loadDishes()"
                    class="border border-gray-300 rounded-lg px-4 focus:outline-none">
                    <option value="">Tất cả loại</option>
                    <option value="Món chính">Món chính</option>
                    <option value="Món xào">Món xào</option>
                    <option value="Canh">Canh</option>
                    <option value="Tráng miệng">Tráng miệng</option>
                </select>
            </div>

            <button onclick="openDishModal()"
                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow-md transition flex items-center">
                <i class="fas fa-plus mr-2"></i> Thêm Món Ăn
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                        <th class="p-4">Tên món</th>
                        <th class="p-4">Loại</th>
                        <th class="p-4 text-right">Giá (VNĐ)</th>
                        <th class="p-4 text-center">Calo (kcal)</th>
                        <th class="p-4 text-center">P - L - G (g)</th>
                        <th class="p-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody id="dish-table-body">
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="dishModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h3 id="modalTitle" class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Thêm Món Ăn Mới</h3>
            <form id="dishForm" class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Tên món ăn <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="dish_name" required class="w-full p-2 border border-gray-300 rounded mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Loại món <span
                            class="text-red-500">*</span></label>
                    <select id="dish_category" required class="w-full p-2 border border-gray-300 rounded mt-1">
                        <option value="Món chính">Món chính</option>
                        <option value="Món xào">Món xào</option>
                        <option value="Canh">Canh</option>
                        <option value="Tráng miệng">Tráng miệng</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Đơn giá (VNĐ) <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="dish_price" required min="0"
                        class="w-full p-2 border border-gray-300 rounded mt-1">
                </div>
                <div class="bg-blue-50 p-3 rounded-lg col-span-2 grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-blue-800 uppercase">Calo</label>
                        <input type="number" id="dish_calories" step="0.1" required
                            class="w-full p-2 border border-blue-200 rounded mt-1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-800 uppercase">Protein</label>
                        <input type="number" id="dish_protein" step="0.1"
                            class="w-full p-2 border border-blue-200 rounded mt-1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-800 uppercase">Lipid</label>
                        <input type="number" id="dish_lipid" step="0.1"
                            class="w-full p-2 border border-blue-200 rounded mt-1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-800 uppercase">Glucid</label>
                        <input type="number" id="dish_glucid" step="0.1"
                            class="w-full p-2 border border-blue-200 rounded mt-1">
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Hướng dẫn/Mô tả</label>
                    <textarea id="dish_instructions" rows="3"
                        class="w-full p-2 border border-gray-300 rounded mt-1"></textarea>
                </div>
                <div class="col-span-2 flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeDishModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Hủy</button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-500 text-white rounded-lg shadow hover:bg-green-600 transition">Lưu món
                        ăn</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let editingDishId = null;
        let searchTimeout = null; // Quản lý thời gian chờ người dùng gõ phím

        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function () {
                console.log("Đang tìm kiếm...");
                loadDishes();
            }, 500);
        });

        // Hàm load dữ liệu
        function loadDishes() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('filterCategory').value;

            const currentToken = localStorage.getItem('access_token');

            fetch(`/api/dishes?search=${search}&category=${category}`, {
                headers: {
                    'Authorization': 'Bearer ' + currentToken,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById('dish-table-body');
                    tbody.innerHTML = '';

                    if (res.data && res.data.length > 0) {
                        res.data.forEach(dish => {
                            tbody.innerHTML += `
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 font-semibold text-gray-800">${dish.name}</td>
                                    <td class="p-4"><span class="px-2 py-1 bg-gray-100 text-xs rounded-full">${dish.category}</span></td>
                                    <td class="p-4 text-right font-mono text-green-600">${new Intl.NumberFormat('vi-VN').format(dish.price)}</td>
                                    <td class="p-4 text-center text-orange-600 font-bold">${dish.calories}</td>
                                    <td class="p-4 text-center text-xs text-gray-500">
                                        P: ${dish.protein} | L: ${dish.lipid} | G: ${dish.glucid}
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick='editDish(${JSON.stringify(dish)})' class="text-blue-500 hover:text-blue-700 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteDish(${dish.id})" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-500">Không tìm thấy món ăn nào khớp với yêu cầu.</td></tr>';
                    }
                })
                .catch(err => console.error("Lỗi fetch:", err));
        }

        function openDishModal() {
            editingDishId = null;
            document.getElementById('dishForm').reset();
            document.getElementById('modalTitle').innerText = 'Thêm Món Ăn Mới';
            document.getElementById('dishModal').classList.remove('hidden');
            document.getElementById('dishModal').style.display = 'flex';
        }

        function closeDishModal() {
            document.getElementById('dishModal').classList.add('hidden');
            document.getElementById('dishModal').style.display = 'none';
        }

        document.getElementById('dishForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const currentToken = localStorage.getItem('access_token');
            const payload = {
                name: document.getElementById('dish_name').value,
                category: document.getElementById('dish_category').value,
                price: document.getElementById('dish_price').value,
                calories: document.getElementById('dish_calories').value,
                protein: document.getElementById('dish_protein').value || 0,
                lipid: document.getElementById('dish_lipid').value || 0,
                glucid: document.getElementById('dish_glucid').value || 0,
                instructions: document.getElementById('dish_instructions').value,
            };

            const url = editingDishId ? `/api/dishes/${editingDishId}` : '/api/dishes';
            const method = editingDishId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + currentToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(editingDishId ? "Cập nhật thành công!" : "Thêm món ăn thành công!");
                        closeDishModal();
                        loadDishes();
                    } else {
                        alert("Lỗi: " + (data.message || "Không thể lưu dữ liệu"));
                    }
                })
                .catch(err => alert("Có lỗi xảy ra khi kết nối đến máy chủ."));
        });

        function editDish(dish) {
            editingDishId = dish.id;
            document.getElementById('modalTitle').innerText = 'Chỉnh sửa Món ăn';
            document.getElementById('dish_name').value = dish.name;
            document.getElementById('dish_category').value = dish.category;
            document.getElementById('dish_price').value = dish.price;
            document.getElementById('dish_calories').value = dish.calories;
            document.getElementById('dish_protein').value = dish.protein;
            document.getElementById('dish_lipid').value = dish.lipid;
            document.getElementById('dish_glucid').value = dish.glucid;
            document.getElementById('dish_instructions').value = dish.instructions;
            document.getElementById('dishModal').style.display = 'flex';
        }

        function deleteDish(id) {
            if (confirm('Bạn có chắc chắn muốn xóa món ăn này?')) {
                const currentToken = localStorage.getItem('access_token');
                fetch(`/api/dishes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + currentToken,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message || "Đã xóa món ăn.");
                        loadDishes();
                    });
            }
        }

        window.onload = loadDishes;
    </script>
@endsection