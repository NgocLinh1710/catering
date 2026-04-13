<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt Công ty - Admin Catering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-900 text-white flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <h1 class="text-xl font-bold" style="color: #86efac;">
                <i class="fas fa-utensils mr-2"></i>Catering
            </h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="/tong-quan"
                class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-chart-pie w-6"></i> Tổng quan
            </a>
            <a href="/admin/duyet-cong-ty" class="flex items-center px-4 py-3 text-gray-900 rounded-lg shadow-md"
                style="background-color: #86efac;">
                <i class="fas fa-building w-6"></i> Duyệt Công ty
            </a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <button onclick="logout()"
                class="w-full flex items-center px-4 py-2 text-red-400 hover:bg-gray-800 rounded-lg transition">
                <i class="fas fa-sign-out-alt w-6"></i> Đăng xuất
            </button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 z-10">
            <h2 class="text-xl font-semibold text-gray-800">Danh sách Đăng ký Chờ duyệt</h2>
            <div class="flex items-center text-gray-600">
                <div class="mr-4 text-right">
                    <div>Xin chào, <b>Admin</b></div>
                    <span
                        class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full font-bold uppercase">ADMIN</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=86efac&color=1f2937"
                    class="h-8 w-8 rounded-full">
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-6">Yêu cầu mới cần xử lý</h3>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                            <th class="p-4">Ngày gửi</th>
                            <th class="p-4">Tên Doanh nghiệp</th>
                            <th class="p-4">Người đại diện</th>
                            <th class="p-4">Thông tin liên hệ</th>
                            <th class="p-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($pendingCompanies) > 0)
                            @foreach($pendingCompanies as $company)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-4 text-sm text-gray-500">{{ $company->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="p-4 font-semibold text-gray-800">{{ $company->company_name }}</td>
                                    <td class="p-4 text-gray-600">{{ $company->contact_person }}</td>
                                    <td class="p-4 text-sm">
                                        <div><i class="fas fa-envelope text-gray-400 mr-1"></i> {{ $company->email }}</div>
                                        <div><i class="fas fa-phone text-gray-400 mr-1"></i> {{ $company->phone }}</div>
                                    </td>
                                    <td class="p-4 text-center space-x-2">
                                        <button onclick="approveCompany({{ $company->id }})"
                                            class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition shadow">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                        <button onclick="rejectCompany({{ $company->id }})"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition shadow">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                    Hiện không có yêu cầu đăng ký nào đang chờ duyệt.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Kiểm tra Token đăng nhập
        const token = localStorage.getItem('access_token');
        if (!token) window.location.href = '/login';

        function logout() {
            localStorage.removeItem('access_token');
            window.location.href = '/login';
        }

        // Tạm thời hiển thị Alert, viết API xử lý tạo Tài khoản sau
        function approveCompany(id) {
            if (confirm('Bạn có chắc chắn muốn DUYỆT và cấp tài khoản cho công ty này?')) {

                // Gọi API duyệt
                fetch('/api/admin/approve-company/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success') {
                            alert(`DUYỆT THÀNH CÔNG!\n\nEmail đăng nhập: ${res.email}\nMật khẩu hệ thống cấp: ${res.password}\n\n(Lưu ý: Mật khẩu này sau này sẽ được hệ thống gửi thẳng vào Email của khách)`);

                            location.reload();
                        } else {
                            alert('Lỗi: ' + res.message);
                        }
                    })
                    .catch(error => console.error('Lỗi:', error));
            }
        }

        function rejectCompany(id) {
            if (confirm('Bạn có muốn TỪ CHỐI yêu cầu đăng ký này?')) {
                alert('Tính năng đang phát triển: Sẽ gọi API đổi trạng thái thành Reject cho ID: ' + id);
            }
        }
    </script>
</body>

</html>