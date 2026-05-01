<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Catering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <h1 class="text-xl font-bold" style="color: #86efac;">
                <i class="fas fa-utensils mr-2"></i>Catering
            </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="/tong-quan" id="menu-overview"
                class="hidden items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-chart-pie w-6"></i> Tổng quan
            </a>

            <a href="/admin/duyet-cong-ty" id="menu-companies"
                class="hidden items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-building w-6"></i> Duyệt Công ty
            </a>

            <a href="/quan-ly-mon-an" id="menu-dishes"
                class="hidden items-center px-4 py-3 {{ Request::is('quan-ly-mon-an') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-hamburger w-6"></i> Kho Món ăn
            </a>

            <a href="/quan-ly-nhan-vien" id="menu-employees"
                class="hidden items-center px-4 py-3 {{ Request::is('quan-ly-nhan-vien') ? 'bg-[#86efac] text-gray-900 shadow-md' : 'text-gray-300 hover:bg-gray-800' }} rounded-lg transition">
                <i class="fas fa-users w-6"></i> Nhân sự
            </a>

            <a href="#" id="menu-ingredients"
                class="hidden items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-seedling w-6"></i> Nguyên liệu
            </a>

            <a href="#" id="menu-planning"
                class="hidden items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-calendar-alt w-6"></i> Lập Thực đơn
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
                    class="h-8 w-8 rounded-full">
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

    <script>
        const token = localStorage.getItem('access_token');
        if (!token) window.location.href = '/login';

        function checkUserRole() {
            fetch('/api/user', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
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

                    // Logic Ẩn/Hiện Menu
                    if (role === 'admin') {
                        document.getElementById('menu-overview').style.display = 'flex';
                        document.getElementById('menu-companies').style.display = 'flex';
                    }
                    else if (role === 'company' || role === 'company_admin') {
                        document.getElementById('menu-ingredients').style.display = 'flex';
                        document.getElementById('menu-dishes').style.display = 'flex';
                        document.getElementById('menu-employees').style.display = 'flex';
                    }
                    else if (role === 'employee') {
                        document.getElementById('menu-dishes').style.display = 'flex';
                        document.getElementById('menu-planning').style.display = 'flex';
                    }

                    if (typeof loadDishes === 'function') {
                        loadDishes();
                    }
                })
                .catch(error => console.error("Lỗi:", error));
        }

        function logout() {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkUserRole();
        }
        );
    </script>

    @yield('scripts')
</body>

</html>