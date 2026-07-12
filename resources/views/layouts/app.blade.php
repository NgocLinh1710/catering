<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Catering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .menu-item {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <h1 class="text-xl font-bold" style="color: #86efac;">
                <i class="fas fa-utensils mr-2"></i>Catering
            </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="#" id="menu-overview"
                class="menu-item items-center px-4 py-3 {{ Request::is('cong-ty/tong-quan') || Request::is('admin/tong-quan') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-th-large w-6"></i> Tổng quan
            </a>

            <a href="/quan-ly-khach-hang" id="menu-units"
                class="menu-item items-center px-4 py-3 {{ Request::is('quan-ly-khach-hang') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-building w-6"></i>Khách hàng
            </a>

            <a href="/quan-ly-nhan-vien" id="menu-employees"
                class="menu-item items-center px-4 py-3 {{ Request::is('quan-ly-nhan-vien') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-users w-6"></i> Nhân sự
            </a>

            <a href="/quan-ly-nguyen-lieu" id="menu-ingredients"
                class="menu-item items-center px-4 py-3 {{ Request::is('quan-ly-nguyen-lieu') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-leaf w-6"></i> Nguyên liệu
            </a>

            <a href="/quan-ly-mon-an" id="menu-dishes"
                class="menu-item items-center px-4 py-3 {{ Request::is('quan-ly-mon-an') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-utensils w-6"></i> Kho Món ăn
            </a>

            <a href="/thiet-lap-tieu-chuan" id="menu-standard"
                class="menu-item items-center px-4 py-3 {{ Request::is('thiet-lap-tieu-chuan*') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-sliders-h w-6"></i> Thiết lập tiêu chuẩn
            </a>

            <a href="/lap-thuc-don" id="menu-planning"
                class="menu-item items-center px-4 py-3 {{ Request::is('lap-thuc-don*') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-calendar-check w-6"></i> Lập Thực đơn
            </a>

            <a href="/admin/duyet-cong-ty" id="menu-companies"
                class="menu-item items-center px-4 py-3 {{ Request::is('admin/duyet-cong-ty') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-shield-alt w-6"></i> Duyệt Doanh nghiệp
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <button onclick="logout()"
                class="w-full flex items-center px-4 py-2 text-red-400 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-sign-out-alt w-6"></i> Đăng xuất
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 shrink-0 z-10">
            <h2 class="text-xl font-semibold text-gray-800">@yield('page_title')</h2>

            <div class="flex items-center text-gray-600">
                <div class="mr-4 text-right">
                    <div>Xin chào, <b id="userNameDisplay">Đang tải...</b></div>
                    <span id="userRoleDisplay"
                        class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full font-bold uppercase">Role</span>
                </div>
                <img id="userAvatarDisplay" src="https://ui-avatars.com/api/?name=User&background=86efac&color=1f2937"
                    class="h-8 w-8 rounded-full border border-gray-200">
            </div>
        </header>

        <div id="passwordAlert"
            class="hidden mx-8 mt-4 p-4 rounded-lg border border-yellow-300 bg-yellow-50 text-yellow-800 flex items-center justify-between">
            <div>
                <i class="fas fa-triangle-exclamation mr-2"></i>
                Bạn đang sử dụng mật khẩu tạm thời. Vui lòng đổi mật khẩu trước thời hạn quy định.
            </div>
            <button onclick="openChangePasswordModal()"
                class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Đổi ngay
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

    <div id="changePasswordModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Đổi mật khẩu</h3>

            <input id="newPassword" type="password" autocomplete="new-password" placeholder="Mật khẩu mới"
                class="w-full border rounded p-2 mb-1">
            <p id="newPasswordError" class="text-sm text-red-500 mb-3"></p>

            <input id="newPasswordConfirmation" type="password" autocomplete="new-password"
                placeholder="Nhập lại mật khẩu mới" class="w-full border rounded p-2 mb-1">
            <p id="confirmPasswordError" class="text-sm text-red-500 mb-4"></p>

            <div class="flex justify-end gap-2">
                <button onclick="closeChangePasswordModal()" class="px-4 py-2 border rounded">Đóng</button>
                <button onclick="submitPasswordChange()" class="px-4 py-2 bg-green-500 text-white rounded">
                    Lưu
                </button>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('access_token');
        if (!token) window.location.href = '/login';

        function checkUserRole() {
            fetch('/api/user', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
            })
                .then(response => {
                    if (response.status === 401) logout();
                    return response.json();
                })
                .then(user => {
                    document.getElementById('userNameDisplay').innerText = user.name;
                    document.getElementById('userRoleDisplay').innerText = user.role;
                    document.getElementById('userAvatarDisplay').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=86efac&color=1f2937`;

                    const role = user.role.toLowerCase();
                    const overviewBtn = document.getElementById('menu-overview');

                    if (user.must_change_password) {
                        document.getElementById('passwordAlert').classList.remove('hidden');
                    }

                    // Ẩn tất cả menu trước khi check role để tránh bị chồng chéo
                    const allMenus = [
                        'menu-overview',
                        'menu-companies',
                        'menu-ingredients',
                        'menu-dishes',
                        'menu-employees',
                        'menu-planning',
                        'menu-units',
                        'menu-standard'
                    ];
                    allMenus.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = 'none';
                    });

                    // Phân quyền hiển thị & Gán link Tổng quan
                    if (role === 'admin') {
                        overviewBtn.href = "/admin/tong-quan";
                        showMenu(['menu-overview', 'menu-companies']);
                    }
                    else if (role === 'company' || role === 'company_admin') {
                        overviewBtn.href = "/cong-ty/tong-quan";
                        showMenu([
                            'menu-overview',
                            'menu-units',
                            'menu-employees',
                            'menu-ingredients'
                        ]);
                    }
                    else if (role === 'employee') {
                        showMenu(['menu-dishes', 'menu-standard', 'menu-planning']);
                    }

                    if (typeof loadData === 'function') loadData();
                    if (typeof loadDishes === 'function' && role === 'employee') loadDishes();
                })
                .catch(error => console.error("Lỗi xác thực:", error));
        }

        function showMenu(menuIds) {
            menuIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'flex'; // Ép kiểu flex để ăn theo class items-center của bạn
            });
        }

        function logout() {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        }

        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.remove('hidden');
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('hidden');
        }

        async function submitPasswordChange() {
            const password = document.getElementById('newPassword').value;
            const confirmation = document.getElementById('newPasswordConfirmation').value;

            // Chặn submit nếu form validation đang có lỗi text hiển thị
            if (document.getElementById('newPasswordError').textContent || document.getElementById('confirmPasswordError').textContent) {
                alert('Vui lòng kiểm tra lại điều kiện mật khẩu!');
                return;
            }

            try {
                const res = await fetch('/api/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        password,
                        password_confirmation: confirmation
                    })
                });

                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    document.getElementById('passwordAlert').classList.add('hidden');
                    closeChangePasswordModal();
                    alert('Đổi mật khẩu thành công!');
                } else {
                    alert(data.message || 'Đổi mật khẩu thất bại.');
                }
            } catch (err) {
                alert('Có lỗi hệ thống xảy ra.');
            }
        }

        // Lắng nghe sự kiện Validation (Đã xóa đoạn trùng lặp)
        const pwInput = document.getElementById('newPassword');
        const confirmInput = document.getElementById('newPasswordConfirmation');
        const pwError = document.getElementById('newPasswordError');
        const confirmError = document.getElementById('confirmPasswordError');

        let confirmTimer;

        pwInput.addEventListener('input', () => {
            const ok = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(pwInput.value);
            pwError.textContent = ok || pwInput.value === ''
                ? ''
                : 'Mật khẩu phải có ít nhất 8 ký tự, gồm cả chữ và số.';
        });

        confirmInput.addEventListener('input', () => {
            clearTimeout(confirmTimer);
            confirmTimer = setTimeout(() => {
                confirmError.textContent =
                    (confirmInput.value && confirmInput.value !== pwInput.value)
                        ? 'Mật khẩu xác nhận chưa khớp.'
                        : '';
            }, 400);
        });

        document.addEventListener('DOMContentLoaded', checkUserRole);
    </script>

    <script src="{{ asset('js/pagination.js') }}"></script>
    @yield('scripts')
</body>

</html>