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
                    <th class="p-3 border-b">Tổng Calo</th>
                    <th class="p-3 border-b">Tags Dị ứng</th>
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
        document.addEventListener('DOMContentLoaded', function () {
            fetch('/api/dishes')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('dish-table-body');
                    tableBody.innerHTML = '';

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(dish => {
                            let tags = dish.dish_tags ? dish.dish_tags.join(', ') : 'Không có';

                            let row = `
                                <tr style="border-bottom: 1px solid #e5e7eb;" 
                                    onmouseover="this.style.backgroundColor='#f9fafb'" 
                                    onmouseout="this.style.backgroundColor='transparent'">
                                    <td class="p-3">${dish.id}</td>
                                    <td class="p-3 font-semibold" style="color: #1f2937;">${dish.name}</td>
                                    <td class="p-3 font-bold" style="color: #16a34a;">${dish.total_calories} kcal</td>
                                    <td class="p-3 text-sm" style="color: #ef4444;">${tags}</td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    } else {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="4" class="p-3 text-center">
                                    Chưa có món ăn nào trong hệ thống.
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi gọi API:', error);
                    document.getElementById('dish-table-body').innerHTML = `
                        <tr>
                            <td colspan="4" class="p-3 text-center" style="color: #ef4444;">
                                Lỗi kết nối API!
                            </td>
                        </tr>
                    `;
                });
        });
    </script>

</body>

</html>