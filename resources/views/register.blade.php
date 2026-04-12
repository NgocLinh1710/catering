<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Dịch vụ - Catering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">

    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-green-500"><i class="fas fa-utensils mr-2"></i>Catering</h1>
            <a href="/login" class="text-gray-600 hover:text-green-500 font-medium transition">Đăng nhập</a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="bg-white p-8 flex shadow-xl rounded-2xl w-full max-w-4xl overflow-hidden">

            <div class="w-1/2 pr-8 border-r hidden md:block">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Số hóa quy trình quản lý thực đơn</h2>
                <p class="text-gray-600 mb-6">Đăng ký ngay để trải nghiệm nền tảng quản lý kho món ăn và tự động hóa
                    thực đơn thông minh nhất.</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Tối ưu chi phí
                        thực đơn</li>
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Quản lý kho
                        thực phẩm chặt chẽ</li>
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Tính toán Calo
                        & Cảnh báo dị ứng tự động</li>
                </ul>
            </div>

            <div class="w-full md:w-1/2 md:pl-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6 text-center">Gửi yêu cầu mở tài khoản</h3>

                <div id="successMessage"
                    class="hidden bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm text-center">
                    <i class="fas fa-check-circle mr-1"></i> Gửi yêu cầu thành công! Quản trị viên sẽ liên hệ và cấp tài
                    khoản cho bạn qua Email sớm nhất.
                </div>

                <form id="registerForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên Doanh nghiệp / Bếp ăn</label>
                        <input type="text" id="company_name" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Người đại diện</label>
                        <input type="text" id="contact_person" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email nhận tài khoản</label>
                        <input type="email" id="email" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                        <input type="text" id="phone" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    <button type="submit"
                        class="w-full bg-gray-900 text-white font-bold py-3 rounded hover:bg-gray-800 transition shadow-md mt-4">
                        Gửi Yêu Cầu Đăng Ký
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const data = {
                company_name: document.getElementById('company_name').value,
                contact_person: document.getElementById('contact_person').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value
            };

            // Gọi API gửi yêu cầu đăng ký (chưa viết API)
            fetch('/api/register-company', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        document.getElementById('registerForm').classList.add('hidden');
                        document.getElementById('successMessage').classList.remove('hidden');
                    } else {
                        alert('Lỗi: ' + (res.message || 'Email này có thể đã được đăng ký!'));
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        });
    </script>
</body>

</html>