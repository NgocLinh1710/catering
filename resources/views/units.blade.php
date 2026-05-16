@extends('layouts.app')

@section('title', 'Quản lý Khách hàng')
@section('page_title', 'Danh sách Khách hàng')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-3 mb-6 items-center">
            <div class="relative w-64">
                <input type="text" id="searchInput" placeholder="Tìm tên khách hàng..."
                    class="w-full pl-10 pr-4 py-2 border rounded-lg">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <select id="filterYear" class="border rounded-lg px-3 py-2" onchange="loadUnits()">
                @for($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>

            <select id="filterMonth" class="border rounded-lg px-3 py-2" onchange="loadUnits()">
                <option value="">Cả năm</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">Tháng {{ $m }}</option>
                @endfor
            </select>

            <button onclick="openUnitModal()" class="ml-auto px-4 py-2 bg-green-300 rounded-lg hover:bg-green-400">
                <i class="fas fa-plus-circle"></i> Thêm Khách Hàng Mới
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b text-sm uppercase">
                    <th class="p-4">Tên Khách hàng</th>
                    <th class="p-4">Địa Chỉ</th>
                    <th class="p-4 text-center">Tổng chi tiêu (VNĐ)</th>
                    <th class="p-4">Nhân sự quản lý</th>
                    <th class="p-4 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody id="unit-table-body">
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">Đang tải dữ liệu khách hàng...</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-center items-center mt-4 space-x-4">
            <div class="text-sm text-gray-600">
                Có tất cả <span id="totalItems">0</span> khách hàng
            </div>

            <div id="pagination" class="flex justify-center space-x-2">
            </div>
        </div>
    </div>

    <div id="unitModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 w-[500px] shadow-xl rounded-xl bg-white">
            <h3 id="unitModalTitle" class="text-xl font-bold mb-4 border-b pb-2">Tiêu đề</h3>
            <form id="unitForm">
                <div class="space-y-4 mb-4">
                    <div>
                        <label class="text-sm font-medium">Tên đơn vị/Khách hàng</label>
                        <input type="text" id="unit_name" required
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-green-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Địa chỉ</label>
                        <input type="text" id="unit_address"
                            class="w-full p-2 border rounded focus:ring-2 focus:ring-green-300">
                    </div>
                </div>

                <label class="block text-sm font-medium mb-2">Giao cho nhân viên quản lý:</label>
                <div class="max-h-40 overflow-y-auto border rounded p-2 mb-4" id="employeeSelectGrid"></div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUnitModal()" class="px-4 py-2 bg-gray-100 rounded">Hủy</button>
                    <button type="submit" id="unitSubmitBtn"
                        class="px-4 py-2 bg-green-300 rounded hover:bg-green-400">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <div id="assignModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 w-[500px] shadow-xl rounded-xl bg-white">
            <h3 class="text-xl font-bold mb-2 text-gray-800">Phân công quản lý</h3>
            <p id="assignUnitName" class="text-sm text-blue-600 mb-4 font-medium"></p>

            <div class="max-h-60 overflow-y-auto mb-4 border rounded p-2" id="employeeChecklist">
                <p class="text-gray-400 text-center">Đang tải danh sách nhân viên...</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 bg-gray-100 rounded">Hủy</button>
                <button type="button" onclick="saveAssignment()"
                    class="px-4 py-2 rounded-lg shadow text-gray-800 transition bg-[#86efac] hover:bg-[#4ade80]">
                    Xác nhận phân công
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var apiToken = window.token || localStorage.getItem('access_token') || sessionStorage.getItem('token') || '';

        let currentUnitId = null;
        let editingUnitId = null;
        const paginator = typeof PaginationManager === 'function'
            ? PaginationManager({ containerId: 'pagination', loadFunction: loadUnits })
            : { currentPage: 1, searchKeyword: '', render: function () { } };

        function loadUnits(page = 1, search = '') {
            paginator.currentPage = page;
            paginator.searchKeyword = search;

            const year = document.getElementById('filterYear').value;
            const month = document.getElementById('filterMonth').value;
            const searchVal = search || document.getElementById('searchInput').value;

            fetch(`/api/units?page=${page}&search=${searchVal}&year=${year}&month=${month}`, {
                headers: { 'Authorization': 'Bearer ' + apiToken }
            })
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById('unit-table-body');
                    tbody.innerHTML = '';

                    document.getElementById('totalItems').innerText = res.total || 0;

                    if (!res.data || res.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">Không tìm thấy khách hàng nào.</td></tr>';
                    } else {
                        res.data.forEach(unit => {
                            const tempConsumption = 5000000;
                            const isInactive = unit.status === 'inactive';

                            const employeesList = unit.employees || [];
                            const assignedEmployeeIds = employeesList.map(e => e.id);

                            tbody.innerHTML += `
                                        <tr class="border-b hover:bg-gray-50 ${isInactive ? 'bg-gray-100' : ''}">
                                            <td class="p-4 font-semibold ${isInactive ? 'text-gray-400' : 'text-gray-800'}">
                                                ${unit.name} ${isInactive ? '<span class="text-xs font-normal text-red-500">(Ngừng hợp tác)</span>' : ''}
                                            </td>
                                            <td class="p-4 ${isInactive ? 'text-gray-400' : 'text-gray-600'}">${unit.address || 'N/A'}</td>
                                            <td class="p-4 text-center font-bold ${isInactive ? 'text-gray-400' : 'text-blue-600'}">
                                                ${Number(tempConsumption).toLocaleString()}đ
                                            </td>
                                            <td class="p-4">
                                                ${employeesList.map(e => `
                                                    <span class="${isInactive ? 'bg-gray-200 text-gray-500' : 'bg-green-100 text-green-700'} px-2 py-1 rounded text-xs mr-1">
                                                        ${e.name}
                                                    </span>`).join('')}
                                            </td>
                                            <td class="p-4 text-center">
                                                <button onclick="openUnitModal(${unit.id}, '${unit.name}', '${unit.address || ''}', ${JSON.stringify(assignedEmployeeIds)})" 
                                                        class="text-blue-500 hover:text-blue-700 mr-3" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button onclick="toggleUnitStatus(${unit.id}, '${unit.status}')" 
                                                        class="${isInactive ? 'text-green-500' : 'text-orange-500'} hover:opacity-70 transition" 
                                                        title="${isInactive ? 'Kích hoạt lại' : 'Ngừng hợp tác'}">
                                                    <i class="fas ${isInactive ? 'fa-unlock' : 'fa-ban'}"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                        });
                    }

                    if (typeof paginator.render === 'function') {
                        paginator.render(res.last_page, res.current_page);
                    }
                })
                .catch(err => {
                    console.error("Lỗi tải dữ liệu:", err);
                    document.getElementById('unit-table-body').innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500">Lỗi kết nối máy chủ.</td></tr>';
                });
        }

        let typingTimer;
        document.getElementById('searchInput').addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                loadUnits(1, this.value);
            }, 500);
        });

        function loadEmployeesToModal(assignedIds = []) {
            fetch('/api/employees', { headers: { 'Authorization': 'Bearer ' + apiToken } })
                .then(res => res.json())
                .then(res => {
                    const grid = document.getElementById('employeeSelectGrid');
                    if (!res.data) { grid.innerHTML = ''; return; }

                    grid.innerHTML = res.data.map(emp => {
                        const isAssigned = assignedIds.includes(emp.id);
                        const isInactive = emp.status === 'inactive';
                        const canSelect = !isInactive || isAssigned;

                        return `
                                    <label class="flex items-center p-1 hover:bg-gray-50 cursor-pointer text-sm ${isInactive ? 'text-red-400' : ''}">
                                        <input type="checkbox" name="modal_emp_ids" value="${emp.id}" 
                                            ${isAssigned ? 'checked' : ''} 
                                            ${!canSelect ? 'disabled' : ''} 
                                            class="mr-2 h-4 w-4">
                                        <span>
                                            ${emp.name} ${isInactive ? '<b class="text-xs">(Tài khoản bị khóa)</b>' : ''}
                                        </span>
                                    </label>
                                `;
                    }).join('');
                });
        }

        function openUnitModal(id = null, name = '', address = '', assignedIds = []) {
            editingUnitId = id;
            document.getElementById('unitModal').classList.remove('hidden');

            document.getElementById('unit_name').value = name;
            document.getElementById('unit_address').value = address;

            const titleEl = document.getElementById('unitModalTitle');
            if (id) {
                titleEl.innerText = "Sửa Khách Hàng";
            } else {
                titleEl.innerText = "Thêm Khách Hàng Mới";
            }

            loadEmployeesToModal(assignedIds);
        }

        function closeUnitModal() { document.getElementById('unitModal').classList.add('hidden'); }

        document.getElementById('unitForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const selectedEmpIds = Array.from(document.querySelectorAll('input[name="modal_emp_ids"]:checked')).map(cb => cb.value);

            const payload = {
                name: document.getElementById('unit_name').value,
                address: document.getElementById('unit_address').value,
                employee_ids: selectedEmpIds,
                meal_price: 0,
                status: 'active'
            };

            const method = editingUnitId ? 'PUT' : 'POST';
            const url = editingUnitId ? `/api/units/${editingUnitId}` : '/api/units';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + apiToken },
                body: JSON.stringify(payload)
            }).then(res => {
                if (res.ok) {
                    closeUnitModal();
                    loadUnits();
                } else {
                    alert('Có lỗi xảy ra, vui lòng kiểm tra lại.');
                }
            });
        });

        function toggleUnitStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const confirmMsg = newStatus === 'inactive'
                ? 'Bạn có chắc chắn muốn ngừng hợp tác với khách hàng này?'
                : 'Kích hoạt lại khách hàng này?';

            if (confirm(confirmMsg)) {
                fetch(`/api/units/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + apiToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                    .then(res => res.json())
                    .then(data => {
                        loadUnits();
                    })
                    .catch(err => alert('Có lỗi xảy ra khi cập nhật trạng thái.'));
            }
        }

        document.addEventListener('DOMContentLoaded', loadUnits);
    </script>
@endsection