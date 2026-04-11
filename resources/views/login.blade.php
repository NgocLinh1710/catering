<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Catering SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center h-screen" style="background-color: #f3f4f6;">

    <div class="p-8 rounded-lg shadow-md w-96" style="background-color: #ffffff;">
        <h2 class="text-2xl font-bold text-center mb-6" style="color: #1f2937;">
            Đăng nhập Hệ thống
        </h2>

        <form id="loginForm">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1" style="color: #374151;">
                    Email
                </label>
                <input type="email" id="email" value="admin@gmail.com" class="w-full p-2 rounded focus:outline-none"
                    style="border: 1px solid #d1d5db;" onfocus="this.style.boxShadow='0 0 0 2px #3b82f6'"
                    onblur="this.style.boxShadow='none'" required>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1" style="color: #374151;">
                    Mật khẩu
                </label>
                <input type="password" id="password" value="123456" class="w-full p-2 rounded focus:outline-none"
                    style="border: 1px solid #d1d5db;" onfocus="this.style.boxShadow='0 0 0 2px #3b82f6'"
                    onblur="this.style.boxShadow='none'" required>
            </div>

            <button type="submit" class="w-full text-gray-800 font-bold py-2 px-4 rounded transition"
                style="background-color: #86efac;" onmouseover="this.style.backgroundColor='#4ade80'"
                onmouseout="this.style.backgroundColor='#86efac'">
                🍗 Vào Bếp 🍔
            </button>
        </form>

        <p id="errorMessage" class="text-sm text-center mt-4 hidden" style="color: #ef4444;"></p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

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
                        localStorage.setItem('access_token', data.access_token);

                        const role = data.user.role.toLowerCase();

                        if (role === 'admin') {
                            window.location.href = '/tong-quan';
                        }
                        else if (role === 'company' || role === 'employee') {
                            window.location.href = '/quan-ly-mon-an';
                        }
                        else {
                            window.location.href = '/';
                        }

                    } else {
                        // Sai pass / email
                        const errorP = document.getElementById('errorMessage');
                        errorP.innerText = data.message;
                        errorP.classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        });
    </script>
</body>

</html>