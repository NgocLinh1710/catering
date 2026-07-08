@extends('layouts.app')

@section('title', 'Quản lý Nhân sự')
@section('page_title', 'Quản lý Nhân Viên')

@section('content')
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-64">
                <input type="text" id="searchInput" placeholder="Tìm tên hoặc email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
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
                    <th class="p-4">Họ và Tên</th>
                    <th class="p-4">Email Đăng nhập</th>
                    <th class="p-4 text-center">Trạng thái</th>
                    <th class="p-4 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody id="employee-table-body">
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">Đang tải dữ liệu...</td>
                </tr>
            </tbody>
        </table>
        <div class="flex justify-center items-center mt-4 space-x-4">
            <div class="text-sm text-gray-600">
                Có tất cả <span id="totalItems">0</span> nhân viên
            </div>
            <div id="pagination" class="flex justify-center space-x-2">
            </div>
        </div>
    </div>

    <div id="addEmployeeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="p-6 w-96 shadow-xl rounded-xl bg-white">
            <h3 id="modalTitle" class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Thêm Nhân Viên</h3>
            <form id="addEmployeeForm"> <input type="text" id="emp_name" placeholder="Họ và Tên" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">
                <input type="email" id="emp_email" placeholder="Email đăng nhập" required
                    class="w-full p-2 mb-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">

                <input type="password" id="emp_password" placeholder="Mật khẩu" minlength="6"
                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-300">

                <span id="passwordError" class="text-red-500 italic text-xs mt-1 block hidden"></span>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Hủy</button>
                    <button type="submit" id="btnSave"
                        class="px-4 py-2 rounded-lg shadow text-gray-800 transition bg-[#86efac] hover:bg-[#4ade80]">
                        Lưu Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-ai-chatbot />
@endsection

@section('scripts')
    <script>
        let editingId = null;

        const paginator = PaginationManager({
            containerId: 'pagination',
            loadFunction: loadEmployees
        });

        window.getUiDescription = () =>
            "Giao diện: 'Quản lý Nhân sự' của Doanh nghiệp.\n" +
            "1. Bộ lọc: Ô 'Tìm kiếm' để tìm nhanh nhân viên theo Tên hoặc theo Email đăng nhập.\n" +
            "2. Nút 'Thêm Nhân Viên': Bấm vào mở Form gồm: Họ và tên, Email đăng nhập, và Mật khẩu (⚠️ Điều kiện: Mật khẩu bắt buộc phải từ 6 ký tự trở lên, chứa cả chữ và số).\n" +
            "3. Các nút thao tác trên từng nhân viên:\n" +
            "   - Nút hình cây bút (Chỉnh sửa): Sửa thông tin nhân viên (gồm các ô nhập tương tự form thêm mới).\n" +
            "   - Nút hình cái khóa (Khóa/Mở tài khoản): Khi bấm Khóa, nhân viên đó lập tức không thể đăng nhập vào hệ thống.\n" +
            "   - Nút hình thùng rác (Xóa): Xóa nhân viên. HỆ QUẢ: Xóa toàn bộ các món ăn do nhân viên đó tạo ra. Tuy nhiên, số liệu Chi tiêu và Thiết lập tiêu chuẩn của khách hàng mà nhân viên đó từng phụ trách VẪN ĐƯỢC GIỮ LẠI.\n" +
            "➡️ THỨ TỰ THỰC HIỆN: Thêm nhân viên mới (nhập đúng định dạng mật khẩu) -> Giao việc bên trang Khách hàng -> Quản lý trạng thái bằng nút Khóa hoặc Xóa nếu nhân sự nghỉ việc.";

        function loadEmployees(page = 1, search = '') {
            paginator.currentPage = page;
            paginator.searchKeyword = search;

            fetch(`/api/employees?page=${page}&search=${search}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
            })
                .then(res => res.json())
                .then(res => {
                    const tableBody = document.getElementById('employee-table-body');
                    tableBody.innerHTML = '';

                    res.data.forEach((emp) => {
                        // Kiểm tra trạng thái để hiển thị màu sắc
                        const statusBadge = emp.status === 'inactive'
                            ? '<span class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-bold uppercase">Bị Khóa</span>'
                            : '<span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-xs font-bold uppercase">Hoạt động</span>';

                        const lockIcon = emp.status === 'inactive' ? 'fa-unlock text-green-500' : 'fa-lock text-orange-500';
                        const lockTitle = emp.status === 'inactive' ? 'Mở khóa' : 'Khóa tài khoản';

                        tableBody.innerHTML += `
                                                                                                                                            <tr class="border-b hover:bg-gray-50 ${emp.status === 'inactive' ? 'bg-gray-50' : ''}">
                                                                                                                                                <td class="p-4 font-semibold ${emp.status === 'inactive' ? 'text-gray-400' : 'text-gray-800'}">${emp.name}</td>
                                                                                                                                                <td class="p-4 text-gray-600">${emp.email}</td>
                                                                                                                                                <td class="p-4 text-center">${statusBadge}</td>
                                                                                                                                                <td class="p-4 text-center">
                                                                                                                                                    <button onclick="openModal(${emp.id}, '${emp.name}', '${emp.email}')" class="text-blue-500 hover:text-blue-700 mr-3" title="Chỉnh sửa">
                                                                                                                                                        <i class="fas fa-edit"></i>
                                                                                                                                                    </button>
                                                                                                                                                    <button onclick="toggleEmployeeStatus(${emp.id}, '${emp.status}')" class="hover:opacity-70 transition mr-3" title="${lockTitle}">
                                                                                                                                                        <i class="fas ${lockIcon}"></i>
                                                                                                                                                    </button>
                                                                                                                                                    <button onclick="deletePermanent(${emp.id})" class="text-red-500 hover:text-red-700" title="Xóa vĩnh viễn">
                                                                                                                                                        <i class="fas fa-trash"></i>
                                                                                                                                                    </button>
                                                                                                                                                </td>
                                                                                                                                            </tr>`;
                    });

                    document.getElementById('totalItems').innerText = res.total;
                    paginator.render(res.last_page, res.current_page);
                });
        }

        // Xử lý sự kiện gõ phím để tìm kiếm 
        let typingTimer;
        document.getElementById('searchInput').addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                loadEmployees(1, this.value);
            }, 500);
        });

        const modal = document.getElementById('addEmployeeModal');

        function openModal(id = null, name = '', email = '') {
            editingId = id;
            modal.classList.remove('hidden');
            const title = document.getElementById('modalTitle');
            const passwordInput = document.getElementById('emp_password');

            if (editingId) {
                title.innerText = 'Chỉnh Sửa Nhân Viên';
                document.getElementById('emp_name').value = name;
                document.getElementById('emp_email').value = email;
                passwordInput.placeholder = "Mật khẩu mới (Để trống nếu giữ nguyên)";
                passwordInput.required = false;
            } else {
                title.innerText = 'Thêm Nhân Viên Mới';
                document.getElementById('addEmployeeForm').reset();
                passwordInput.placeholder = "Mật khẩu (Tối thiểu 6 ký tự)";
                passwordInput.required = true;
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
            editingId = null;
        }

        // Xử lý gửi Form 
        document.getElementById('addEmployeeForm').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validatePassword()) {
                return;
            }

            const payload = {
                name: document.getElementById('emp_name').value,
                email: document.getElementById('emp_email').value
            };

            const password = document.getElementById('emp_password').value;
            if (password) payload.password = password;

            const url = editingId ? `/api/employees/${editingId}` : '/api/employees';
            const method = editingId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.data) {
                        alert(editingId ? 'Cập nhật thành công!' : 'Thêm mới thành công!');
                        closeModal();
                        loadEmployees();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra!'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi kết nối hệ thống!');
                });
        });

        function deleteEmployee(id) {
            if (confirm('Bạn có chắc chắn muốn xóa nhân viên này?')) {
                fetch(`/api/employees/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token }
                })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        loadEmployees();
                    });
            }
        }

        // Hàm Khóa/Mở khóa
        function toggleEmployeeStatus(id, currentStatus) {
            const action = currentStatus === 'inactive' ? 'mở khóa' : 'khóa';
            if (confirm(`Bạn có chắc chắn muốn ${action} nhân viên này?`)) {
                fetch(`/api/employees/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        loadEmployees();
                    });
            }
        }

        // Hàm xóa vĩnh viễn
        function deletePermanent(id) {
            if (confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN nhân viên này? Dữ liệu không thể khôi phục.')) {
                fetch(`/api/employees/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            loadEmployees();
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Lỗi:', err);
                        alert('Không thể kết nối đến máy chủ!');
                    });
            }
        }

        const passwordInput = document.getElementById('emp_password');
        const passwordError = document.getElementById('passwordError');

        function validatePassword() {

            // Khi đang sửa nhân viên và để trống mật khẩu
            if (editingId && passwordInput.value.trim() === '') {
                passwordError.classList.add('hidden');
                return true;
            }

            const password = passwordInput.value;

            if (password.length < 6) {
                passwordError.textContent = 'Mật khẩu phải có ít nhất 6 ký tự';
                passwordError.classList.remove('hidden');
                return false;
            }

            if (!/[A-Za-z]/.test(password)) {
                passwordError.textContent = 'Mật khẩu phải chứa ít nhất 1 chữ cái';
                passwordError.classList.remove('hidden');
                return false;
            }

            if (!/[0-9]/.test(password)) {
                passwordError.textContent = 'Mật khẩu phải chứa ít nhất 1 chữ số';
                passwordError.classList.remove('hidden');
                return false;
            }

            passwordError.classList.add('hidden');
            return true;
        }

        passwordInput.addEventListener('input', validatePassword);

        document.addEventListener('DOMContentLoaded', loadEmployees);
    </script>
@endsection