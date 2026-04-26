<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Catering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 md:p-10 shadow-xl rounded-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-500 mb-2"><i class="fas fa-utensils mr-2"></i>Catering</h1>
            <p class="text-gray-500">Đăng nhập vào bảng điều khiển của bạn</p>
        </div>

        <form id="loginForm" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" id="email" required placeholder="Nhập email của bạn"
                        class="w-full pl-10 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="password" required placeholder="Nhập mật khẩu"
                        class="w-full pl-10 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-gray-600">
                    <input type="checkbox" class="mr-2 rounded text-green-500 focus:ring-green-400"> Ghi nhớ tôi
                </label>
                <a href="#" class="text-green-600 hover:underline">Quên mật khẩu?</a>
            </div>

            <button type="submit"
                class="w-full bg-gray-900 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition shadow-md">
                Đăng Nhập
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            Công ty của bạn chưa có tài khoản?
            <a href="/dang-ky" class="text-green-600 font-medium hover:underline">Đăng ký ngay</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Gọi API đăng nhập
            fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email, password: password })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Lưu token
                        localStorage.setItem('access_token', data.access_token);
                        localStorage.setItem('user_role', data.user.role);

                        const role = data.user.role.toLowerCase();

                        // Phân quyền điều hướng
                        if (role === 'admin') {
                            window.location.href = '/tong-quan'; // Admin về trang tổng quan
                        }
                        else if (role === 'company' || role === 'company_admin' || role === 'employee') {
                            window.location.href = '/quan-ly-mon-an'; // Cty & NV vào trang món ăn
                        }
                        else {
                            window.location.href = '/';
                        }
                    } else {
                        alert(data.message || 'Đăng nhập thất bại. Kiểm tra lại Email/Mật khẩu!');
                    }
                })
                .catch(error => {
                    console.error('Lỗi kết nối:', error);
                    alert('Không thể kết nối đến máy chủ!');
                });
        });
    </script>
</body>

</html>