@extends('layouts.app')

@section('title', 'Khách hàng phụ trách')
@section('page_title', 'Danh sách Khách hàng Phụ trách')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div id="unit-list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-16 text-gray-400 italic">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Đang tải danh sách khách hàng...
            </div>
        </div>
    </div>

    {{-- MODAL CẤU HÌNH NHÓM --}}
    <div id="audienceModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[92vh] overflow-hidden">

            {{-- HEADER --}}
            <div class="flex justify-between items-center px-7 py-5 border-b bg-gray-50">
                <div>
                    <h3 class="text-2xl font-black text-gray-800">
                        Cấu hình nhóm đối tượng
                    </h3>

                    <p id="modal-unit-name" class="text-sm text-gray-500 mt-1"></p>
                </div>

                <button onclick="closeAudienceModal()"
                    class="h-11 w-11 rounded-full hover:bg-gray-200 transition text-gray-500">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">

                {{-- SIDEBAR --}}
                <div class="lg:col-span-4 border-r bg-gray-50/70 p-5">

                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-black text-gray-700 uppercase text-sm tracking-wide">
                            Nhóm hiện có
                        </h4>

                        <span id="count-audience"
                            class="text-xs font-bold bg-green-100 text-green-700 px-2 py-1 rounded-full">
                            0 nhóm
                        </span>
                    </div>

                    <div id="audience-list" class="space-y-3"></div>

                    <button onclick="addNewAudienceForm()"
                        class="mt-5 w-full py-3 border-2 border-dashed border-green-300 text-green-600 rounded-2xl hover:bg-green-50 transition font-bold">
                        <i class="fas fa-plus mr-2"></i>
                        Thêm nhóm mới
                    </button>
                </div>

                {{-- FORM --}}
                <div class="lg:col-span-8 p-6 overflow-y-auto max-h-[78vh]">

                    <div id="no-selection-msg" class="h-full flex flex-col items-center justify-center py-24 text-center">
                        <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center mb-5">
                            <i class="fas fa-users text-4xl text-gray-300"></i>
                        </div>

                        <h3 class="text-xl font-bold text-gray-700 mb-2">
                            Chưa chọn nhóm đối tượng
                        </h3>

                        <p class="text-gray-400 text-sm">
                            Hãy chọn nhóm bên trái hoặc tạo nhóm mới để cấu hình định mức suất ăn.
                        </p>
                    </div>

                    <form id="audienceForm" class="hidden space-y-7">

                        <input type="hidden" id="audience_id">
                        <input type="hidden" id="current_unit_id">

                        {{-- THÔNG TIN CHUNG --}}
                        <div class="space-y-5">

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-500 mb-2">
                                    Tên nhóm đối tượng
                                </label>

                                <input type="text" id="target_name" required placeholder="Ví dụ: Khối lớp 7 - nam"
                                    class="w-full border-0 border-b-2 border-gray-200 focus:border-green-500 outline-none px-0 py-3 text-3xl font-black text-gray-800">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-500 mb-2">
                                        Ngân sách / suất (VNĐ)
                                    </label>

                                    <input type="number" id="budget_per_serving" required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-lg font-bold focus:ring-2 focus:ring-green-400 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-500 mb-2">
                                        Calories mục tiêu (Kcal)
                                    </label>

                                    <input type="number" id="target_calories" required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-lg font-bold focus:ring-2 focus:ring-green-400 outline-none">
                                </div>
                            </div>

                            {{-- DINH DƯỠNG --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-400 mb-2">
                                        Protein (g)
                                    </label>

                                    <input type="number" id="target_protein" required
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 bg-white focus:ring-2 focus:ring-green-300 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-400 mb-2">
                                        Fat / Lipid (g)
                                    </label>

                                    <input type="number" id="target_fat" required
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 bg-white focus:ring-2 focus:ring-green-300 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase text-gray-400 mb-2">
                                        Chất xơ (g)
                                    </label>

                                    <input type="number" id="target_fiber" required
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 bg-white focus:ring-2 focus:ring-green-300 outline-none">
                                </div>
                            </div>

                            {{-- NOTE THÔNG BÁO NGHIỆP VỤ --}}
                            <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="h-12 w-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                        <i class="fas fa-info-circle text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-blue-800 mb-2">
                                            Cấu hình tiêu chuẩn định mức
                                        </h3>
                                        <p class="text-sm text-blue-700 leading-relaxed">
                                            Phần này chỉ lưu định mức dinh dưỡng mục tiêu và ngân sách chuẩn cho nhóm đối
                                            tượng.
                                            <br><br>
                                            <b>Lưu ý:</b> Số lượng suất ăn, các suất ăn đặc biệt (ăn chay, dị ứng hải sản,
                                            dị ứng đậu phộng, kiêng thịt heo theo tôn giáo,...) sẽ được điều chỉnh linh hoạt
                                            theo số liệu báo suất thực tế tại giao diện <b>Lập thực đơn theo ngày</b>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="flex justify-between items-center pt-5 border-t">

                            <button type="button" id="btnDeleteAudience" onclick="deleteAudience()"
                                class="hidden px-5 py-3 rounded-xl text-red-500 hover:bg-red-50 font-black transition">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Xóa nhóm
                            </button>

                            <button type="submit"
                                class="ml-auto px-7 py-3 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-black shadow-lg transition">
                                <i class="fas fa-save mr-2"></i>
                                Lưu cấu hình
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

    {{-- MODAL CHỌN NHÓM LẬP THỰC ĐƠN --}}
    <div id="menuSelectionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            <div class="flex justify-between items-center border-b px-6 py-5 bg-gray-50">
                <div>
                    <h3 class="text-xl font-black text-gray-800">
                        <i class="fas fa-utensils text-green-500 mr-2"></i>
                        Chọn nhóm lập thực đơn
                    </h3>
                    <p id="menu-modal-unit-name" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <button onclick="closeMenuSelectionModal()"
                    class="h-10 w-10 rounded-full hover:bg-gray-200 transition text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-5">
                <div id="menu-modal-loading" class="text-center py-10 text-gray-400 italic">
                    <i class="fas fa-circle-notch fa-spin mr-2 text-green-500"></i>
                    Đang đọc dữ liệu nhóm đối tượng...
                </div>

                <div id="menu-audience-container" class="hidden space-y-3 max-h-[400px] overflow-y-auto">
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        if (typeof window.token === 'undefined' || !window.token) {
            window.token =
                localStorage.getItem('access_token') ||
                sessionStorage.getItem('token') ||
                '';
        }

        let currentAudiences = [];

        document.addEventListener('DOMContentLoaded', () => {
            loadAssignedUnits();
        });

        async function loadAssignedUnits() {
            try {
                const res = await fetch('/api/my-thiet-lap-tieu-chuan', {
                    headers: {
                        'Authorization': 'Bearer ' + window.token,
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    throw new Error('Lỗi tải dữ liệu');
                }

                const units = await res.json();
                const container = document.getElementById('unit-list');

                if (!units || units.length === 0) {
                    container.innerHTML = `
                            <div class="col-span-full bg-white rounded-3xl border p-10 text-center text-gray-400">
                                Chưa có đơn vị nào được phân công.
                            </div>
                        `;
                    return;
                }

                container.innerHTML = units.map(unit => `
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-lg transition p-6">
                            <div class="flex items-start justify-between mb-5">
                                <div class="h-16 w-16 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center">
                                    <i class="fas fa-school text-3xl"></i>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-black">
                                    Đang quản lý
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-gray-800 mb-2">
                                ${unit.name}
                            </h3>

                            <p class="text-sm text-gray-500 mb-6">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                ${unit.address || 'Chưa cập nhật địa chỉ'}
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    onclick="openAudienceModal(${unit.id}, '${unit.name.replace(/'/g, "\\'")}')"
                                    class="py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 transition font-bold text-gray-700">
                                    <i class="fas fa-cog mr-2"></i>
                                    Định mức
                                </button>

                                <button
                                    onclick="openMenuSelectionModal(${unit.id}, '${unit.name.replace(/'/g, "\\'")}')"
                                    class="py-3 rounded-2xl bg-green-500 hover:bg-green-600 transition font-black text-white shadow">
                                    <i class="fas fa-utensils mr-2"></i>
                                    Thực đơn
                                </button>
                            </div>
                        </div>
                    `).join('');

            } catch (e) {
                console.error(e);
                document.getElementById('unit-list').innerHTML = `
                        <div class="col-span-full bg-white rounded-3xl border p-10 text-center text-red-500">
                            Không thể tải dữ liệu từ máy chủ.
                        </div>
                    `;
            }
        }

        async function openAudienceModal(unitId, unitName) {
            document.getElementById('modal-unit-name').innerText = unitName;
            document.getElementById('current_unit_id').value = unitId;
            document.getElementById('audienceModal').classList.remove('hidden');
            await loadAudiences(unitId);
        }

        async function loadAudiences(unitId) {
            try {
                const res = await fetch(`/api/units/${unitId}/target-audiences`, {
                    headers: {
                        'Authorization': 'Bearer ' + window.token
                    }
                });

                const result = await res.json();
                currentAudiences = result.data || result || [];

                const list = document.getElementById('audience-list');
                document.getElementById('count-audience').innerText = `${currentAudiences.length} nhóm`;

                if (currentAudiences.length === 0) {
                    list.innerHTML = `
                            <div class="text-center text-gray-400 italic py-8">
                                Chưa có nhóm nào
                            </div>
                        `;
                } else {
                    list.innerHTML = currentAudiences.map(a => `
                            <button
                                type="button"
                                onclick="editAudience(${a.id})"
                                class="w-full text-left p-4 rounded-2xl border border-gray-200 hover:border-green-400 hover:bg-green-50 transition group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-black text-gray-800 text-sm">
                                            ${a.name}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            ${parseFloat(a.budget_per_serving || 0).toLocaleString()}đ ·
                                            ${a.target_calories || 0} Kcal
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-green-500"></i>
                                </div>
                            </button>
                        `).join('');
                }

                hideForm();
            } catch (e) {
                console.error(e);
            }
        }

        function showForm() {
            document.getElementById('audienceForm').classList.remove('hidden');
            document.getElementById('no-selection-msg').classList.add('hidden');
        }

        function hideForm() {
            document.getElementById('audienceForm').classList.add('hidden');
            document.getElementById('no-selection-msg').classList.remove('hidden');
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

            showForm();
            document.getElementById('audience_id').value = a.id;
            document.getElementById('target_name').value = a.name || '';
            document.getElementById('budget_per_serving').value = a.budget_per_serving || 0;
            document.getElementById('target_calories').value = a.target_calories || 0;
            document.getElementById('target_protein').value = a.target_protein || 0;
            document.getElementById('target_fat').value = a.target_fat || 0;
            document.getElementById('target_fiber').value = a.target_fiber || 0;

            document.getElementById('btnDeleteAudience').classList.remove('hidden');
        }

        document.getElementById('audienceForm').onsubmit = async (e) => {
            e.preventDefault();

            const id = document.getElementById('audience_id').value;
            const unitId = document.getElementById('current_unit_id').value;
            const url = id ? `/api/target-audiences/${id}` : '/api/target-audiences';

            const payload = {
                unit_id: parseInt(unitId),
                name: document.getElementById('target_name').value.trim(),
                budget_per_serving: parseFloat(document.getElementById('budget_per_serving').value) || 0,
                target_calories: parseFloat(document.getElementById('target_calories').value) || 0,
                target_protein: parseFloat(document.getElementById('target_protein').value) || 0,
                target_fat: parseFloat(document.getElementById('target_fat').value) || 0,
                target_fiber: parseFloat(document.getElementById('target_fiber').value) || 0,
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

                const data = await res.json();

                if (res.ok) {
                    alert('Lưu cấu hình thành công!');
                    loadAudiences(unitId);
                } else {
                    if (data.errors) {
                        alert(Object.values(data.errors).flat().join('\n'));
                    } else {
                        alert(data.message || 'Không thể lưu dữ liệu');
                    }
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi kết nối hệ thống');
            }
        };

        async function deleteAudience() {
            const id = document.getElementById('audience_id').value;
            const unitId = document.getElementById('current_unit_id').value;

            if (!id) return;
            if (!confirm('Bạn có chắc chắn muốn xóa nhóm này?')) return;

            try {
                const res = await fetch(`/api/target-audiences/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + window.token,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    alert('Đã xóa nhóm!');
                    loadAudiences(unitId);
                } else {
                    alert('Không thể xóa nhóm');
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi kết nối hệ thống');
            }
        }

        function closeAudienceModal() {
            document.getElementById('audienceModal').classList.add('hidden');
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
                    headers: {
                        'Authorization': 'Bearer ' + window.token
                    }
                });

                const result = await res.json();
                const audiences = result.data || result || [];

                loadingEl.classList.add('hidden');
                containerEl.classList.remove('hidden');

                if (audiences.length === 0) {
                    containerEl.innerHTML = `
                            <div class="text-center py-10 text-gray-400">
                                Chưa có nhóm đối tượng nào.
                            </div>
                        `;
                } else {
                    containerEl.innerHTML = audiences.map(a => `
                            <button
                                onclick="redirectToPlanner(${unitId}, ${a.id})"
                                class="w-full text-left p-4 rounded-2xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-black text-gray-800 text-sm">
                                            ${a.name}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            ${parseFloat(a.budget_per_serving || 0).toLocaleString()}đ ·
                                            ${a.target_calories || 0} Kcal
                                        </div>
                                    </div>
                                    <div class="h-9 w-9 rounded-full bg-white border flex items-center justify-center text-gray-400 group-hover:bg-green-500 group-hover:text-white transition">
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </div>
                                </div>
                            </button>
                        `).join('');
                }
            } catch (e) {
                console.error(e);
                loadingEl.innerHTML = `
                        <div class="text-center text-red-500 text-sm py-10">
                            Không thể tải dữ liệu nhóm đối tượng
                        </div>
                    `;
            }
        }

        function closeMenuSelectionModal() {
            document.getElementById('menuSelectionModal').classList.add('hidden');
        }

        function redirectToPlanner(unitId, audienceId) {
            window.location.href = `/lap-thuc-don?unit_id=${unitId}&audience_id=${audienceId}`;
        }
    </script>
@endsection