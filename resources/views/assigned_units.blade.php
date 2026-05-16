@extends('layouts.app')

@section('title', 'Khách hàng phụ trách')
@section('page_title', 'Danh sách Khách hàng Phụ trách')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div id="unit-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-10 text-gray-400 italic">
                <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải danh sách đơn vị...
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
                    <div id="audience-list" class="space-y-2">
                    </div>
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
@endsection

@section('scripts')
    <script>
        const token = localStorage.getItem('access_token');
        let currentAudiences = [];

        async function loadAssignedUnits() {
            const res = await fetch('/api/my-thiet-lap-tieu-chuan', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const units = await res.json();

            const container = document.getElementById('unit-list');
            if (units.length === 0) {
                container.innerHTML = `<p class="col-span-full text-center py-10">Bạn chưa được phân công đơn vị nào.</p>`;
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
                                        <button onclick="openAudienceModal(${unit.id}, '${unit.name}')" class="flex items-center justify-center py-2 bg-gray-50 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                                            <i class="fas fa-cog mr-2 text-gray-400"></i> Định mức
                                        </button>
                                        <a href="/menu-planner?unit_id=${unit.id}" class="flex items-center justify-center py-2 bg-green-500 text-white rounded-lg text-sm font-bold hover:bg-green-600 shadow-md transition">
                                            <i class="fas fa-utensils mr-2"></i> Lập món
                                        </a>
                                    </div>
                                </div>
                            `).join('');
        }

        async function openAudienceModal(unitId, unitName) {
            document.getElementById('modal-unit-name').innerText = unitName;
            document.getElementById('current_unit_id').value = unitId;
            document.getElementById('audienceModal').classList.remove('hidden');
            loadAudiences(unitId);
        }

        async function loadAudiences(unitId) {
            const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const result = await res.json();
            currentAudiences = result.data;

            const list = document.getElementById('audience-list');
            list.innerHTML = currentAudiences.map(a => `
                                <button onclick="editAudience(${a.id})" class="w-full text-left p-3 rounded-lg border hover:border-green-400 hover:bg-green-50 transition group flex justify-between items-center">
                                    <span class="font-medium text-gray-700">${a.name}</span>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-green-500"></i>
                                </button>
                            `).join('');

            hideForm();
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
            document.getElementById('budget_per_serving').value = a.budget_per_serving;
            document.getElementById('target_calories').value = a.target_calories;
            document.getElementById('target_protein').value = a.target_protein;
            document.getElementById('target_fat').value = a.target_fat;
            document.getElementById('target_fiber').value = a.target_fiber;
            document.getElementById('allergy_tags').value = a.allergy_tags ? a.allergy_tags.join(', ') : '';

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
            const method = id ? 'PUT' : 'POST';
            const url = id ? `/api/target-audiences/${id}` : '/api/target-audiences';

            const payload = {
                unit_id: document.getElementById('current_unit_id').value,
                name: document.getElementById('target_name').value,
                budget_per_serving: document.getElementById('budget_per_serving').value,
                target_calories: document.getElementById('target_calories').value,
                target_protein: document.getElementById('target_protein').value,
                target_fat: document.getElementById('target_fat').value,
                target_fiber: document.getElementById('target_fiber').value,
                allergy_tags: document.getElementById('allergy_tags').value.split(',').map(t => t.trim()).filter(t => t !== "")
            };

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                alert("Lưu thành công!");
                loadAudiences(payload.unit_id);
            }
        };

        async function deleteAudience() {
            const id = document.getElementById('audience_id').value;
            if (!id || !confirm("Xóa nhóm này?")) return;

            const res = await fetch(`/api/target-audiences/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (res.ok) {
                loadAudiences(document.getElementById('current_unit_id').value);
            }
        }

        function closeAudienceModal() {
            document.getElementById('audienceModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', loadAssignedUnits);
    </script>
@endsection