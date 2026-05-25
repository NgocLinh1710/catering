@extends('layouts.app')

@section('title', 'Khách hàng phụ trách')
@section('page_title', 'Danh sách Khách hàng Phụ trách')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div id="unit-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-10 text-gray-400 italic">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải danh sách khách hàng...
            </div>
        </div>
    </div>

    <div id="audienceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Cấu hình nhóm đối tượng</h3>
                    <p class="text-sm text-gray-500" id="modal-unit-name"></p>
                </div>
                <button onclick="closeAudienceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 border-r pr-4">
                    <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">Nhóm hiện có</h4>
                    <div id="audience-list" class="space-y-2"></div>
                    <button onclick="addNewAudienceForm()"
                        class="mt-4 w-full py-2 border-2 border-dashed border-green-300 text-green-600 rounded-lg hover:bg-green-50 transition text-sm font-bold">
                        + Thêm nhóm mới
                    </button>
                </div>

                <div class="lg:col-span-2">
                    <form id="audienceForm" class="hidden space-y-4">
                        <input type="hidden" id="audience_id">
                        <input type="hidden" id="current_unit_id">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Tên nhóm (VD: Khối 3
                                    tuổi)</label>
                                <input type="text" id="target_name" required
                                    class="w-full border-b focus:border-green-500 outline-none py-1 text-lg font-semibold">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Ngân sách / suất
                                    (VNĐ)</label>
                                <input type="number" id="budget_per_serving" class="w-full border rounded p-2 mt-1">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Calories mục tiêu
                                    (Kcal)</label>
                                <input type="number" id="target_calories" class="w-full border rounded p-2 mt-1">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-400">Protein (g)</label>
                                <input type="number" id="target_protein" class="w-full border rounded p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400">Fat (g)</label>
                                <input type="number" id="target_fat" class="w-full border rounded p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400">Chất xơ (g)</label>
                                <input type="number" id="target_fiber" class="w-full border rounded p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dị ứng / Tôn giáo
                                (Tags)</label>
                            <input type="text" id="allergy_tags"
                                placeholder="Không ăn lạc, Không ăn bò... (cách nhau bằng dấu phẩy)"
                                class="w-full border rounded p-2 text-sm">
                        </div>

                        <div class="flex justify-end space-x-2 pt-4 border-t">
                            <button type="button" id="btnDeleteAudience" onclick="deleteAudience()"
                                class="px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg text-sm font-bold hidden">Xóa
                                nhóm</button>
                            <button type="submit"
                                class="px-6 py-2 bg-green-500 text-white rounded-lg font-bold hover:bg-green-600 shadow-md transition">Lưu
                                cấu hình</button>
                        </div>
                    </form>
                    <div id="no-selection-msg" class="text-center py-20 text-gray-400 italic">
                        Chọn một nhóm bên trái hoặc bấm "Thêm nhóm mới" để thiết lập định mức.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="menuSelectionModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform transition-all">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-utensils text-green-500 mr-2"></i>Chọn nhóm
                        lập thực đơn</h3>
                    <p class="text-xs text-gray-500 mt-1" id="menu-modal-unit-name"></p>
                </div>
                <button onclick="closeMenuSelectionModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div id="menu-modal-loading" class="text-center py-8 text-gray-400 italic">
                <i class="fas fa-circle-notch fa-spin mr-2 text-green-500"></i> Đang đọc nhóm đối tượng...
            </div>

            <div id="menu-audience-container" class="space-y-2.5 max-h-64 overflow-y-auto hidden">
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        if (typeof window.token === 'undefined' || !window.token) {
            window.token = localStorage.getItem('access_token') || sessionStorage.getItem('token') || '';
        }

        let currentAudiences = [];

        async function loadAssignedUnits() {
            try {
                const res = await fetch('/api/my-thiet-lap-tieu-chuan', {
                    headers: {
                        'Authorization': 'Bearer ' + window.token,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) throw new Error('Lỗi tải dữ liệu máy chủ');

                const units = await res.json();
                const container = document.getElementById('unit-list');

                if (!units || units.length === 0) {
                    container.innerHTML = `<p class="col-span-full text-center py-10 text-gray-500 bg-white rounded-xl p-5 border shadow-sm">Bạn chưa được phân công quản lý đơn vị/khách hàng nào.</p>`;
                    return;
                }

                container.innerHTML = units.map(unit => `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-green-100 text-green-600 p-3 rounded-lg">
                                    <i class="fas fa-school text-2xl"></i>
                                </div>
                                <span class="text-xs font-bold px-2 py-1 bg-blue-50 text-blue-600 rounded-full">Đang quản lý</span>
                            </div>
                            <h3 class="font-bold text-gray-800 text-lg mb-1">${unit.name}</h3>
                            <p class="text-sm text-gray-500 mb-4"><i class="fas fa-map-marker-alt mr-1"></i> ${unit.address || 'Chưa cập nhật'}</p>

                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <button onclick="openAudienceModal(${unit.id}, '${unit.name.replace(/'/g, "\\'")}')" class="flex items-center justify-center py-2 bg-gray-50 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                                    <i class="fas fa-cog mr-2 text-gray-400"></i> Định mức
                                </button>
                                <button onclick="openMenuSelectionModal(${unit.id}, '${unit.name.replace(/'/g, "\\'")}')" class="flex items-center justify-center py-2 bg-green-500 text-white rounded-lg text-sm font-bold hover:bg-green-600 shadow-md transition">
                                    <i class="fas fa-utensils mr-2"></i> Thực đơn
                                </button>
                            </div>
                        </div>
                    `).join('');
            } catch (error) {
                console.error(error);
                document.getElementById('unit-list').innerHTML = `<p class="col-span-full text-center py-10 text-red-500">Lỗi kết nối máy chủ hoặc phiên đăng nhập hết hạn.</p>`;
            }
        }

        async function openMenuSelectionModal(unitId, unitName) {
            document.getElementById('menu-modal-unit-name').innerText = unitName;
            document.getElementById('menuSelectionModal').classList.remove('hidden');

            const loadingEl = document.getElementById('menu-modal-loading');
            const containerEl = document.getElementById('menu-audience-container');

            loadingEl.classList.remove('hidden');
            containerEl.classList.add('hidden');

            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: { 'Authorization': 'Bearer ' + window.token }
                });
                const result = await res.json();
                const audiences = result.data || result || [];

                loadingEl.classList.add('hidden');
                containerEl.classList.remove('hidden');

                if (audiences.length === 0) {
                    containerEl.innerHTML = `
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-400 italic mb-3">Khách hàng này chưa lập nhóm tiêu chuẩn nào!</p>
                                <button onclick="closeMenuSelectionModal(); openAudienceModal(${unitId}, '${unitName.replace(/'/g, "\\'")}')" class="text-xs font-bold text-green-600 hover:underline">
                                    <i class="fas fa-plus mr-1"></i> Khởi tạo nhóm đối tượng ngay
                                </button>
                            </div>
                        `;
                } else {
                    containerEl.innerHTML = audiences.map(a => `
                            <button type="button" onclick="redirectToPlanner(${unitId}, ${a.id})" 
                                class="w-full flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50 hover:bg-green-50 hover:border-green-300 text-left transition group">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm group-hover:text-green-700 transition">${a.name}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Định mức: ${parseFloat(a.budget_per_serving || 0).toLocaleString()}đ · ${a.target_calories || 0} Kcal</p>
                                </div>
                                <span class="h-7 w-7 rounded-full bg-white flex items-center justify-center text-gray-400 group-hover:bg-green-500 group-hover:text-white transition shadow-sm">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </button>
                        `).join('');
                }
            } catch (error) {
                console.error(error);
                loadingEl.innerHTML = `<p class="text-xs text-red-500 py-4">Lỗi tải danh sách đối tượng.</p>`;
            }
        }

        function redirectToPlanner(unitId, audienceId) {
            window.location.href = `/lap-thuc-don?unit_id=${unitId}&audience_id=${audienceId}`;
        }

        function closeMenuSelectionModal() {
            document.getElementById('menuSelectionModal').classList.add('hidden');
        }

        async function openAudienceModal(unitId, unitName) {
            document.getElementById('modal-unit-name').innerText = unitName;
            document.getElementById('current_unit_id').value = unitId;
            document.getElementById('audienceModal').classList.remove('hidden');
            loadAudiences(unitId);
        }

        async function loadAudiences(unitId) {
            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: { 'Authorization': 'Bearer ' + window.token }
                });
                const result = await res.json();
                currentAudiences = result.data || result || [];

                const list = document.getElementById('audience-list');
                if (currentAudiences.length === 0) {
                    list.innerHTML = `<p class="text-xs text-gray-400 text-center py-4">Chưa có nhóm nào</p>`;
                } else {
                    list.innerHTML = currentAudiences.map(a => `
                            <button type="button" onclick="editAudience(${a.id})" class="w-full text-left p-3 rounded-lg border hover:border-green-400 hover:bg-green-50 transition group flex justify-between items-center">
                                <span class="font-medium text-gray-700 text-sm">${a.name}</span>
                                <i class="fas fa-chevron-right text-gray-300 group-hover:text-green-500"></i>
                            </button>
                        `).join('');
                }
                hideForm();
            } catch (error) {
                console.error("Lỗi lấy danh sách đối tượng:", error);
            }
        }

        function addNewAudienceForm() {
            document.getElementById('audienceForm').reset();
            document.getElementById('audience_id').value = '';
            document.getElementById('btnDeleteAudience').classList.add('hidden');
            showForm();
        }

        function editAudience(id) {
            const a = currentAudiences.find(item => item.id === id);
            if (!a) return;

            document.getElementById('audience_id').value = a.id;
            document.getElementById('target_name').value = a.name;
            document.getElementById('budget_per_serving').value = a.budget_per_serving || 0;
            document.getElementById('target_calories').value = a.target_calories || 0;
            document.getElementById('target_protein').value = a.target_protein || 0;
            document.getElementById('target_fat').value = a.target_fat || 0;
            document.getElementById('target_fiber').value = a.target_fiber || 0;

            if (a.allergy_tags) {
                document.getElementById('allergy_tags').value = Array.isArray(a.allergy_tags) ? a.allergy_tags.join(', ') : a.allergy_tags;
            } else {
                document.getElementById('allergy_tags').value = '';
            }

            document.getElementById('btnDeleteAudience').classList.remove('hidden');
            showForm();
        }

        function showForm() {
            document.getElementById('audienceForm').classList.remove('hidden');
            document.getElementById('no-selection-msg').classList.add('hidden');
        }

        function hideForm() {
            document.getElementById('audienceForm').classList.add('hidden');
            document.getElementById('no-selection-msg').classList.remove('hidden');
        }

        document.getElementById('audienceForm').onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('audience_id').value;
            const url = id ? `/api/target-audiences/${id}` : '/api/target-audiences';
            const unitId = document.getElementById('current_unit_id').value;

            const tagsInput = document.getElementById('allergy_tags').value;
            const allergyTagsString = tagsInput ? tagsInput.split(',').map(t => t.trim()).filter(t => t !== "").join(', ') : '';

            const payload = {
                unit_id: parseInt(unitId),
                name: document.getElementById('target_name').value.trim(),
                budget_per_serving: parseFloat(document.getElementById('budget_per_serving').value) || 0,
                target_calories: parseFloat(document.getElementById('target_calories').value) || 0,
                target_protein: parseFloat(document.getElementById('target_protein').value) || 0,
                target_fat: parseFloat(document.getElementById('target_fat').value) || 0,
                target_fiber: parseFloat(document.getElementById('target_fiber').value) || 0,
                allergy_tags: allergyTagsString
            };

            if (id) {
                payload._method = 'PUT';
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + window.token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    alert("Lưu cấu hình thành công!");
                    loadAudiences(unitId);
                } else {
                    const errData = await res.json();
                    if (errData.errors) {
                        const errorMessages = Object.values(errData.errors).flat().join('\n');
                        alert("Lỗi dữ liệu:\n" + errorMessages);
                    } else {
                        alert("Lỗi: " + (errData.message || "Không thể lưu dữ liệu"));
                    }
                }
            } catch (error) {
                alert("Đã xảy ra lỗi hệ thống kết nối.");
            }
        };

        async function deleteAudience() {
            const id = document.getElementById('audience_id').value;
            const unitId = document.getElementById('current_unit_id').value;
            if (!id || !confirm("Bạn có chắc chắn muốn xóa nhóm đối tượng này?")) return;

            try {
                const res = await fetch(`/api/target-audiences/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + window.token,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    alert("Xóa thành công!");
                    loadAudiences(unitId);
                }
            } catch (error) {
                alert("Không thể xóa nhóm này vào lúc này.");
            }
        }

        function closeAudienceModal() {
            document.getElementById('audienceModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', loadAssignedUnits);
    </script>
@endsection