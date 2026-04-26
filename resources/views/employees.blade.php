@extends('layouts.app')

@section('title', 'Quản lý Nhân sự')
@section('page_title', 'Quản lý Nhân Viên')

@section('content')
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-64">
                <input type="text" placeholder="Tìm nhân viên..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none"
                    onfocus="this.style.boxShadow='0 0 0 2px #86efac'" onblur="this.style.boxShadow='none'">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <button onclick="openModal()" class="px-4 py-2 rounded-lg shadow-md transition flex items-center text-gray-800"
                style="background-color: #86efac;" onmouseover="this.style.backgroundColor='#4ade80'"
                onmouseout="this.style.backgroundColor='#86efac'">
                <i class="fas fa-user-plus mr-2"></i> Thêm Nhân Viên
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                    <th class="p-4">STT</th>
                    <th class="p-4">Họ và Tên</th>
                    <th class="p-4">Email Đăng nhập</th>
                    <th class="p-4 text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody id="employee-table-body">
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="addEmployeeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 w-96 shadow-xl rounded-xl bg-white">
            <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Thêm Nhân Viên</h3>
            <form id="addEmployeeForm">
                <input type="text" id="emp_name" placeholder="Họ và Tên" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">
                <input type="email" id="emp_email" placeholder="Email đăng nhập" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">
                <input type="password" id="emp_password" placeholder="Mật khẩu (Tối thiểu 6 ký tự)" required minlength="6"
                    class="w-full p-2 mb-4 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Hủy</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg shadow text-gray-800 transition bg-[#86efac] hover:bg-[#4ade80]">Lưu Tài
                        Khoản</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function loadEmployees() {
            fetch('/api/employees', {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
            })
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('employee-table-body');
                    tableBody.innerHTML = '';
                    if (data.data && data.data.length > 0) {
                        data.data.forEach((emp, index) => {
                            let stt = index + 1;

                            tableBody.innerHTML += `
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-4 text-gray-700 font-medium">${stt}</td> 
                                    <td class="p-4 font-semibold text-gray-800">${emp.name}</td>
                                    <td class="p-4 text-gray-600">${emp.email}</td>
                                    <td class="p-4 text-center"><span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs font-bold uppercase">Hoạt động</span></td>
                                </tr>`;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-500">Chưa có nhân viên nào.</td></tr>';
                    }
                });
        }

        const modal = document.getElementById('addEmployeeModal');
        function openModal() { modal.classList.remove('hidden'); }
        function closeModal() { modal.classList.add('hidden'); document.getElementById('addEmployeeForm').reset(); }

        document.getElementById('addEmployeeForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const payload = {
                name: document.getElementById('emp_name').value,
                email: document.getElementById('emp_email').value,
                password: document.getElementById('emp_password').value
            };

            fetch('/api/employees', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') { closeModal(); loadEmployees(); }
                    else { alert('Lỗi: ' + (data.message || 'Email này có thể đã tồn tại!')); }
                });
        });

        document.addEventListener('DOMContentLoaded', loadEmployees);
    </script>
@endsection