@extends('layouts.app')

@section('title', 'Quản lý Khách hàng')
@section('page_title', 'Danh sách Khách hàng')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-64">
                <input type="text" id="searchInput" placeholder="Tìm tên khách hàng..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <button onclick="openUnitModal()"
                class="px-4 py-2 rounded-lg shadow-md transition flex items-center text-gray-800"
                style="background-color: #86efac;" onmouseover="this.style.backgroundColor='#4ade80'"
                onmouseout="this.style.backgroundColor='#86efac'">
                <i class="fas fa-plus-circle mr-2"></i> Thêm Khách Hàng Mới
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                    <th class="p-4">Tên Khách hàng</th>
                    <th class="p-4">Địa Chỉ</th>
                    <th class="p-4 text-center">Định Mức (VNĐ)</th>
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
            <h3 id="unitModalTitle" class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Thêm Đơn Vị & Giao Việc</h3>
            <form id="unitForm">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <input type="text" id="unit_name" placeholder="Tên đơn vị (Trường học...)" required
                        class="col-span-2 p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-300">
                    <input type="text" id="unit_address" placeholder="Địa chỉ"
                        class="col-span-2 p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-300">
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-2">Giao cho nhân viên quản lý:</label>
                <div class="max-h-40 overflow-y-auto border rounded p-2 mb-4" id="employeeSelectGrid">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUnitModal()" class="px-4 py-2 bg-gray-100 rounded">Hủy</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg shadow text-gray-800 transition bg-[#86efac] hover:bg-[#4ade80]">Lưu &
                        Giao
                        Việc</button>
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
        let currentUnitId = null;
        let editingUnitId = null;
        const paginator = PaginationManager({
            containerId: 'pagination',
            loadFunction: loadUnits
        });

        function loadUnits(page = 1, search = '') {

            paginator.currentPage = page;
            paginator.searchKeyword = search;

            fetch(`/api/units?page=${page}&search=${search}`, {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(res => {

                    const tbody = document.getElementById('unit-table-body');
                    tbody.innerHTML = '';

                    res.data.forEach(unit => {

                        const staffNames = unit.employees.length > 0
                            ? unit.employees.map(e => `
                                <span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs font-bold mr-1 inline-block mb-1 uppercase">
                                    ${e.name}
                                </span>
                            `).join('')
                            : '<span class="text-gray-400 italic text-xs font-light">Chưa có ai</span>';

                        tbody.innerHTML += `
                            <tr class="border-b hover:bg-gray-50">

                                <td class="p-4 font-semibold text-gray-800">
                                    ${unit.name}
                                </td>

                                <td class="p-4 text-gray-600">
                                    ${unit.address || '<span class="italic text-gray-300">Chưa cập nhật</span>'}
                                </td>

                                <td class="p-4 text-center font-bold text-orange-600">
                                    ${Number(unit.meal_price || 0).toLocaleString()}đ
                                </td>

                                <td class="p-4">
                                    ${staffNames}
                                </td>

                                <td class="p-4 text-center">

                                    <button 
                                        onclick="openAssignModal(${unit.id}, '${unit.name}')"
                                        class="text-orange-500 hover:opacity-70 transition mr-3"
                                        title="Phân công nhân sự">
                                        <i class="fas fa-user-tag"></i>
                                    </button>

                                    <button 
                                        onclick="openUnitModal(${unit.id}, '${unit.name}', '${unit.address}')"
                                        class="text-blue-500 hover:text-blue-700 mr-3"
                                        title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button 
                                        onclick="deleteUnit(${unit.id})"
                                        class="text-red-500 hover:text-red-700"
                                        title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </td>

                            </tr>
                        `;
                    });

                    document.getElementById('totalItems').innerText = res.total;

                    paginator.render(res.last_page, res.current_page);
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
            fetch('/api/employees', { headers: { 'Authorization': 'Bearer ' + token } })
                .then(res => res.json())
                .then(res => {
                    const grid = document.getElementById('employeeSelectGrid');
                    grid.innerHTML = res.data.map(emp => {
                        const isInactive = emp.status === 'inactive';

                        return `
                                                                <label class="flex items-center p-1 hover:bg-gray-50 cursor-pointer text-sm ${isInactive ? 'opacity-50 cursor-not-allowed' : ''}">
                                                                    <input type="checkbox" name="modal_emp_ids" value="${emp.id}" 
                                                                        ${assignedIds.includes(emp.id) ? 'checked' : ''} 
                                                                        ${isInactive ? 'disabled' : ''} 
                                                                        class="mr-2">
                                                                    <span class="${isInactive ? 'text-gray-400 italic' : ''}">
                                                                        ${emp.name} ${isInactive ? '(Tài khoản đã khóa)' : ''}
                                                                    </span>
                                                                </label>
                                                            `;
                    }).join('');
                });
        }

        // Xử lý Thêm/Sửa Khách hàng
        function openUnitModal(id = null, name = '', address = '', assignedIds = []) {
            editingUnitId = id;
            document.getElementById('unitModal').classList.remove('hidden');
            document.getElementById('unit_name').value = name;
            document.getElementById('unit_address').value = address;

            // Load nhân viên và tích chọn nếu đang ở chế độ Sửa
            loadEmployeesToModal(assignedIds);
        }

        function closeUnitModal() { document.getElementById('unitModal').classList.add('hidden'); }

        document.getElementById('unitForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const selectedEmpIds = Array.from(document.querySelectorAll('input[name="modal_emp_ids"]:checked')).map(cb => cb.value);

            const payload = {
                name: document.getElementById('unit_name').value,
                address: document.getElementById('unit_address').value,
                employee_ids: selectedEmpIds
            };

            const method = editingUnitId ? 'PUT' : 'POST';
            const url = editingUnitId ? `/api/units/${editingUnitId}` : '/api/units';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify(payload)
            }).then(() => {
                closeUnitModal();
                loadUnits();
            });
        });

        // Xử lý Phân công
        function openAssignModal(unitId, unitName) {
            currentUnitId = unitId;
            document.getElementById('assignUnitName').innerText = "Khách hàng: " + unitName;
            document.getElementById('assignModal').classList.remove('hidden');

            Promise.all([
                fetch('/api/employees', { headers: { 'Authorization': 'Bearer ' + token } }).then(res => res.json()),
                fetch('/api/units', { headers: { 'Authorization': 'Bearer ' + token } }).then(res => res.json())
            ]).then(([empRes, unitRes]) => {
                const employees = empRes.data;
                const currentUnit = unitRes.find(u => u.id === unitId);
                const assignedIds = currentUnit.employees.map(e => e.id);

                const checklist = document.getElementById('employeeChecklist');
                checklist.innerHTML = employees.map(emp => {
                    const isAssigned = assignedIds.includes(emp.id);
                    const isInactive = emp.status === 'inactive';

                    return `
                                                <label class="flex items-center p-2 hover:bg-gray-50 cursor-pointer border-b last:border-0 text-sm">
                                                    <input type="checkbox" name="emp_ids" value="${emp.id}" 
                                                        ${isAssigned ? 'checked' : ''} 
                                                        ${isInactive && !isAssigned ? 'disabled' : ''} 
                                                        onchange="handleInactiveCheckbox(this, ${isInactive})"
                                                        class="mr-3 h-4 w-4">
                                                    <span class="${isInactive ? 'text-red-500 italic' : 'text-gray-700'}">
                                                        ${emp.name} ${isInactive ? '(Tài khoản đã khóa)' : ''}
                                                    </span>
                                                </label>
                                            `;
                }).join('');
            });
        }

        function closeAssignModal() { document.getElementById('assignModal').classList.add('hidden'); }

        function saveAssignment() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="emp_ids"]:checked')).map(cb => cb.value);

            fetch(`/api/units/${currentUnitId}/assign-employees`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ employee_ids: selectedIds })
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    closeAssignModal();
                    loadUnits();
                });
        }

        function deleteUnit(id) {
            if (confirm('Xóa đơn vị này?')) {
                fetch(`/api/units/${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } })
                    .then(() => loadUnits());
            }
        }

        document.addEventListener('DOMContentLoaded', loadUnits);
    </script>
@endsection