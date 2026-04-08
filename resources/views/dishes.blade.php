<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Món ăn - Catering</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body style="background-color: #f3f4f6;" class="p-8">

    <div class="max-w-4xl mx-auto p-6 rounded-lg shadow-md" style="background-color: #ffffff;">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold" style="color: #1f2937;">Kho Món Ăn</h1>
            <button class="text-white px-4 py-2 rounded" style="background-color: #86efac;"
                onmouseover="this.style.backgroundColor='#4ade80'" onmouseout="this.style.backgroundColor='#86efac'">
                + Thêm món mới
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr style="background-color: #e5e7eb; color: #374151;">
                    <th class="p-3 border-b">ID</th>
                    <th class="p-3 border-b">Tên món ăn</th>
                    <th class="p-3 border-b">Tổng calo</th>
                    <th class="p-3 border-b">Tags dị ứng</th>
                </tr>
            </thead>
            <tbody id="dish-table-body">
                <tr>
                    <td colspan="4" class="p-3 text-center" style="color: #6b7280;">
                        Đang tải dữ liệu...
                    </td>
                </tr>
            </tbody>
        </table>
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
                            let tags = dish.dish_tags ? dish.dish_tags.join(', ') : 'Không có';
                            let row = `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">${dish.id}</td>
                            <td class="p-3 font-semibold text-gray-800">${dish.name}</td>
                            <td class="p-3 text-green-600 font-bold">${dish.total_calories} kcal</td>
                            <td class="p-3 text-sm text-red-500">${tags}</td>
                        </tr>`;
                            tableBody.innerHTML += row;
                        });
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', loadDishes);

        const modal = document.getElementById('addDishModal');
        function openModal() { modal.classList.remove('hidden'); }
        function closeModal() {
            modal.classList.add('hidden');
            document.getElementById('addDishForm').reset();
        }

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
                        alert('Đã thêm món ăn thành công!');
                        closeModal();
                        loadDishes();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Kiểm tra lại dữ liệu'));
                    }
                });
        });
    </script>

</body>

</html>