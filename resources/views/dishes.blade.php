<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Món ăn - Catering</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-900 text-white flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <h1 class="text-xl font-bold" style="color: #86efac;">
                <i class="fas fa-utensils mr-2"></i>Catering
            </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="#" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-chart-pie w-6"></i> Tổng quan
            </a>

            <a href="#" class="flex items-center px-4 py-3 text-gray-900 rounded-lg shadow-md"
                style="background-color: #86efac;">
                <i class="fas fa-hamburger w-6"></i> Kho Món ăn
            </a>

            <a href="#" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-seedling w-6"></i> Nguyên liệu
            </a>

            <a href="#" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-calendar-alt w-6"></i> Lập Thực đơn
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <button onclick="logout()"
                class="w-full flex items-center px-4 py-2 text-red-400 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-sign-out-alt w-6"></i> Đăng xuất
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 z-10">
            <h2 class="text-xl font-semibold text-gray-800">Quản lý Kho Món Ăn</h2>
            <div class="flex items-center text-gray-600">
                <span class="mr-4">Xin chào, <b id="userNameDisplay">Admin</b></span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=86efac&color=1f2937"
                    class="h-8 w-8 rounded-full">
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">

                <div class="flex justify-between items-center mb-6">

                    <div class="relative w-64">
                        <input type="text" placeholder="Tìm kiếm món ăn..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none"
                            onfocus="this.style.boxShadow='0 0 0 2px #86efac'" onblur="this.style.boxShadow='none'">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>

                    <button onclick="openModal()"
                        class="px-4 py-2 rounded-lg shadow-md transition flex items-center text-gray-800"
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
                            <td colspan="4" class="p-4 text-center text-gray-500">
                                Đang tải dữ liệu...
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </main>

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
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                        Hủy
                    </button>

                    <button type="submit" class="px-4 py-2 rounded-lg shadow text-gray-800 transition"
                        style="background-color: #86efac;" onmouseover="this.style.backgroundColor='#4ade80'"
                        onmouseout="this.style.backgroundColor='#86efac'">
                        Lưu món
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Kiểm tra xem có Token chưa, chưa thì về Login
        const token = localStorage.getItem('access_token');
        if (!token) {
            window.location.href = '/login';
        }

        function logout() {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        }

        // Hàm Load danh sách món ăn 
        function loadDishes() {
            fetch('/api/dishes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (response.status === 401) { logout(); }
                return response.json();
            })
            .then(data => {
                const tableBody = document.getElementById('dish-table-body');
                tableBody.innerHTML = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(dish => {
                        let tags = dish.dish_tags ? dish.dish_tags.join(', ') : '<span class="text-gray-400 text-sm">Không có</span>';
                        let row = `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 text-gray-500">#${dish.id}</td>
                            <td class="p-4 font-semibold text-gray-800">${dish.name}</td>
                            <td class="p-4 text-green-600 font-bold">${dish.total_calories} kcal</td>
                            <td class="p-4 text-sm text-red-500">${tags}</td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Chưa có món ăn nào.</td></tr>';
                }
            })
            .catch(error => {
                console.error("Lỗi tải dữ liệu:", error);
                document.getElementById('dish-table-body').innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-500">Lỗi tải dữ liệu!</td></tr>';
            });
        }

        // Chạy khi mở trang
        document.addEventListener('DOMContentLoaded', loadDishes);

        // Logic ẩn/hiện Modal
        const modal = document.getElementById('addDishModal');
        function openModal() { modal.classList.remove('hidden'); }
        function closeModal() {
            modal.classList.add('hidden');
            document.getElementById('addDishForm').reset();
        }

        // Gửi Form thêm món ăn
        document.getElementById('addDishForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const requestData = {
                name: document.getElementById('dishName').value,
                total_calories: document.getElementById('dishCalories').value,
                instructions: document.getElementById('dishInstructions').value,
                ingredients: { 1: { quantity: 0.2 } } // Tạm thời fix cứng
            };

            fetch('/api/dishes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                    loadDishes(); // Load lại bảng ngay lập tức
                } else {
                    alert('Lỗi: ' + (data.message || 'Kiểm tra lại dữ liệu'));
                }
            })
            .catch(error => console.error("Lỗi thêm món:", error));
        });
    </script>
</body>
</html>