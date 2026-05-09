@extends('layouts.app')

@section('title', 'Quản lý Nguyên liệu')
@section('page_title', 'Danh mục Nguyên liệu Gốc')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600 text-sm italic">
                <i class="fas fa-info-circle mr-1"></i> Lưu ý: Chỉ số dinh dưỡng tính trên <b>1kg</b> nguyên liệu.
            </p>
            <div class="flex justify-between items-center mb-6 gap-4">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" placeholder="Tìm tên nguyên liệu..."
                        class="pl-10 pr-4 py-2 w-full border rounded-lg focus:ring-2 focus:ring-green-400 outline-none transition">
                </div>

                <button onclick="openIngModal(null)"
                    class="px-4 py-2 bg-[#86efac] text-gray-900 font-bold rounded-lg hover:bg-green-400 transition flex items-center shadow-md shrink-0">
                    <i class="fas fa-plus mr-2"></i> Thêm Thực Phẩm
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 border-b text-sm uppercase">
                        <th class="p-4">Tên thực phẩm</th>
                        <th class="p-4 text-center">Calories (Kcal)</th>
                        <th class="p-4 text-center">Protein (g)</th>
                        <th class="p-4 text-center">Giá nhập/kg</th>
                        <th class="p-4 text-center">Lịch sử</th>
                        <th class="p-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="ing-table-body">
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 italic">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải dữ liệu thực phẩm...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex justify-center items-center mt-4 space-x-4">
            <div class="text-sm text-gray-600">
                Có tất cả <span id="totalIngredients">0</span> nguyên liệu
            </div>
            <div id="pagination" class="flex space-x-2"></div>
        </div>
    </div>

    <div id="ingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-xl font-bold text-gray-800" id="ingModalTitle">
                    <i class="fas fa-leaf text-green-500 mr-2"></i>Thêm Nguyên Liệu
                </h3>
                <button onclick="closeIngModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="ingForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">Tên thực phẩm</label>
                    <input type="text" id="ing_name" required placeholder="Nhập tên thực phẩm..."
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-[#86efac] outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700 font-mono text-red-500">Tags Cảnh báo (Dị
                        ứng/Tôn giáo)</label>
                    <input type="text" id="ing_tags" placeholder="VD: Hải sản, Đậu phộng, Không thịt lợn"
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-red-300 outline-none transition text-sm">
                    <p class="text-[10px] text-gray-400 mt-1 italic">* Các tag cách nhau bằng dấu phẩy</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Calories (Kcal)</label>
                        <input type="number" step="0.1" id="ing_calories" required placeholder="0.0"
                            class="w-full border p-2 rounded focus:ring-1 focus:ring-green-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Protein (g)</label>
                        <input type="number" step="0.1" id="ing_protein" required placeholder="0.0"
                            class="w-full border p-2 rounded focus:ring-1 focus:ring-green-400 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-gray-700">Lipid/Béo (g)</label>
                        <input type="number" step="0.1" id="ing_lipid" placeholder="0.0"
                            class="w-full border p-2 rounded text-sm focus:ring-1 focus:ring-green-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-gray-700">Glucid/Bột (g)</label>
                        <input type="number" step="0.1" id="ing_glucid" placeholder="0.0"
                            class="w-full border p-2 rounded text-sm focus:ring-1 focus:ring-green-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-gray-700">Chất xơ (g)</label>
                        <input type="number" step="0.1" id="ing_fiber" placeholder="0.0"
                            class="w-full border p-2 rounded text-sm focus:ring-1 focus:ring-green-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">Giá nhập (VNĐ/kg)</label>
                    <input type="number" id="ing_price_per_kg" required placeholder="Nhập giá..."
                        class="w-full border p-2 rounded focus:ring-1 focus:ring-green-400 outline-none">
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeIngModal()"
                        class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Hủy</button>
                    <button type="submit"
                        class="px-6 py-2 bg-[#86efac] text-gray-900 rounded-lg font-bold hover:bg-green-400 shadow-md transition">
                        Lưu Kho
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="priceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-lg font-bold text-gray-800">Cập nhật giá mới</h3>
                <button onclick="closePriceModal()" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times"></i></button>
            </div>
            <form id="priceForm" class="space-y-4">
                <input type="hidden" id="price_ing_id">
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">Nguyên liệu</label>
                    <input type="text" id="price_ing_name" disabled
                        class="w-full bg-gray-50 border p-2 rounded text-gray-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">Giá mới (VNĐ/kg)</label>
                    <input type="number" id="new_price" required
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">Ngày áp dụng</label>
                    <input type="date" id="applied_date" required
                        class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closePriceModal()" class="px-4 py-2 bg-gray-100 rounded-lg">Hủy</button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-500 text-white rounded-lg font-bold hover:bg-green-600 transition shadow-md">Lưu
                        giá mới</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let editId = null;

        const paginator = PaginationManager({
            containerId: 'pagination',
            loadFunction: loadData
        });

        async function loadData(page = 1, search = '') {
            paginator.currentPage = page;
            paginator.searchKeyword = search;

            try {
                const res = await fetch(`/api/ingredients?page=${page}&search=${encodeURIComponent(search)}`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                const result = await res.json();
                const ingredients = result.data;
                const tbody = document.getElementById('ing-table-body');
                document.getElementById('totalIngredients').innerText = result.total;

                if (ingredients.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-gray-500 font-medium">Không tìm thấy nguyên liệu.</td></tr>`;
                    return;
                }

                tbody.innerHTML = ingredients.map(i => {
                    // Xử lý hiển thị Tags
                    const tagHtml = i.tags && i.tags.length > 0
                        ? i.tags.map(t => `<span class="bg-red-50 text-red-500 text-[9px] px-1 rounded border border-red-100 mr-1">${t}</span>`).join('')
                        : '';

                    return `
                                        <tr class="border-b hover:bg-gray-50 transition group text-sm">
                                            <td class="p-4">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-gray-700"><i class="fas fa-carrot text-orange-400 mr-2"></i>${i.name}</span>
                                                    <div class="mt-1">${tagHtml}</div>
                                                    <span class="text-[10px] text-gray-400 font-normal italic mt-1">
                                                        Béo: ${parseFloat(i.lipid || 0)}g | Đường: ${parseFloat(i.glucid || 0)}g | Xơ: ${parseFloat(i.fiber || 0)}g
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-4 text-center font-mono text-orange-600 font-bold">${parseFloat(i.calories).toLocaleString()}</td>
                                            <td class="p-4 text-center text-blue-600 font-medium">${parseFloat(i.protein).toLocaleString()}g</td>
                                            <td class="p-4 text-center font-bold text-gray-800">${i.price_per_kg ? Math.round(i.price_per_kg).toLocaleString() : '0'}đ</td>
                                            <td class="p-4 text-center">
                                                <button onclick="openPriceModal(${i.id}, '${i.name}', ${i.price_per_kg})" 
                                                    class="text-green-500 hover:bg-green-50 p-2 rounded-full transition" title="Cập nhật giá biến động">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </td>
                                            <td class="p-4 text-center">
                                                <div class="flex justify-center space-x-2">
                                                    <button onclick="editIng('${encodeURIComponent(JSON.stringify(i))}')" class="text-blue-400 hover:text-blue-600 p-2">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteIng(${i.id})" class="text-red-400 hover:text-red-600 p-2">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>`;
                }).join('');

                paginator.render(result.last_page, result.current_page);
            } catch (error) {
                console.error("Lỗi khi tải nguyên liệu:", error);
            }
        }

        // Logic Modal Nguyên liệu
        function openIngModal(data = null) {
            const modal = document.getElementById('ingModal');
            const title = document.getElementById('ingModalTitle');
            const form = document.getElementById('ingForm');
            modal.classList.remove('hidden');

            if (data) {
                editId = data.id;
                title.innerHTML = '<i class="fas fa-edit text-blue-500 mr-2"></i>Sửa Nguyên Liệu';
                document.getElementById('ing_name').value = data.name;
                document.getElementById('ing_calories').value = data.calories;
                document.getElementById('ing_protein').value = data.protein;
                document.getElementById('ing_lipid').value = data.lipid || 0;
                document.getElementById('ing_glucid').value = data.glucid || 0;
                document.getElementById('ing_fiber').value = data.fiber || 0;
                document.getElementById('ing_price_per_kg').value = data.price_per_kg;
                document.getElementById('ing_tags').value = data.tags ? data.tags.join(', ') : '';
            } else {
                editId = null;
                title.innerHTML = '<i class="fas fa-leaf text-green-500 mr-2"></i>Thêm Nguyên Liệu';
                form.reset();
            }
        }

        function closeIngModal() {
            document.getElementById('ingModal').classList.add('hidden');
            editId = null;
        }

        document.getElementById('ingForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = {
                name: document.getElementById('ing_name').value,
                calories: parseFloat(document.getElementById('ing_calories').value) || 0,
                protein: parseFloat(document.getElementById('ing_protein').value) || 0,
                lipid: parseFloat(document.getElementById('ing_lipid').value) || 0,
                glucid: parseFloat(document.getElementById('ing_glucid').value) || 0,
                fiber: parseFloat(document.getElementById('ing_fiber').value) || 0,
                price_per_kg: parseFloat(document.getElementById('ing_price_per_kg').value) || 0,
                tags: document.getElementById('ing_tags').value.split(',').map(t => t.trim()).filter(t => t !== ""),
                unit: 'kg'
            };

            const method = editId ? 'PUT' : 'POST';
            const url = editId ? `/api/ingredients/${editId}` : '/api/ingredients';

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
                    alert('Cập nhật nguyên liệu thành công!');
                    closeIngModal();
                    loadData(paginator.currentPage, paginator.searchKeyword);
                } else {
                    const err = await res.json();
                    alert("Lỗi: " + (err.message || "Thất bại"));
                }
            } catch (err) { alert("Lỗi kết nối!"); }
        });

        // Logic Lịch sử giá
        window.openPriceModal = function (id, name, currentPrice) {
            document.getElementById('price_ing_id').value = id;
            document.getElementById('price_ing_name').value = name;
            document.getElementById('new_price').value = Math.round(currentPrice);
            document.getElementById('applied_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('priceModal').classList.remove('hidden');
        }

        window.closePriceModal = function () {
            document.getElementById('priceModal').classList.add('hidden');
        }

        document.getElementById('priceForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = {
                ingredient_id: document.getElementById('price_ing_id').value,
                price: parseFloat(document.getElementById('new_price').value),
                applied_date: document.getElementById('applied_date').value
            };
            const res = await fetch('/api/ingredients/update-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            if (res.ok) {
                alert("Đã cập nhật giá mới!");
                closePriceModal();
                loadData(paginator.currentPage, paginator.searchKeyword);
            } else { alert("Lỗi cập nhật giá!"); }
        });

        async function deleteIng(id) {
            if (!confirm('Xóa nguyên liệu này?')) return;
            const res = await fetch(`/api/ingredients/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (res.ok) loadData(paginator.currentPage, paginator.searchKeyword);
        }

        window.editIng = function (dataStr) {
            openIngModal(JSON.parse(decodeURIComponent(dataStr)));
        }

        // Search Debounce
        let typingTimer;
        document.getElementById('searchInput').addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => loadData(1, this.value), 500);
        });

        document.addEventListener('DOMContentLoaded', () => loadData());
    </script>
@endsection