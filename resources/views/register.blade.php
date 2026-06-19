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

                <form id="registerForm" class="space-y-4" novalidate>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên Doanh nghiệp / Bếp ăn</label>
                        <input type="text" id="company_name" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                        <span id="error_company_name" class="text-red-500 italic text-xs mt-1 block hidden"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Người đại diện</label>
                        <input type="text" id="contact_person" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                        <span id="error_contact_person" class="text-red-500 italic text-xs mt-1 block hidden"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email nhận tài khoản</label>
                        <input type="email" id="email" required
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                        <span id="error_email" class="text-red-500 italic text-xs mt-1 block hidden"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                        <input type="text" id="phone" required maxlength="10"
                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
                        <span id="error_phone" class="text-red-500 italic text-xs mt-1 block hidden"></span>
                    </div>

                    <button type="submit" id="submitBtn" disabled
                        class="w-full bg-gray-400 text-white font-bold py-3 rounded cursor-not-allowed transition shadow-md mt-4">
                        Gửi Yêu Cầu Đăng Ký
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');
        const companyInput = document.getElementById('company_name');
        const contactInput = document.getElementById('contact_person');

        const validationStatus = {
            company_name: false,
            contact_person: false,
            email: false,
            phone: false
        };

        function showFieldError(inputId, message) {
            const errorSpan = document.getElementById(`error_${inputId}`);
            if (errorSpan) {
                if (message) {
                    errorSpan.textContent = message;
                    errorSpan.classList.remove('hidden');
                } else {
                    errorSpan.textContent = '';
                    errorSpan.classList.add('hidden');
                }
            }
        }

        function checkFormValidity() {
            const isValid = Object.values(validationStatus).every(status => status === true);
            if (isValid) {
                submitBtn.removeAttribute('disabled');
                submitBtn.className = "w-full bg-gray-900 text-white font-bold py-3 rounded hover:bg-gray-800 transition shadow-md mt-4 cursor-pointer";
            } else {
                submitBtn.setAttribute('disabled', 'true');
                submitBtn.className = "w-full bg-gray-400 text-white font-bold py-3 rounded cursor-not-allowed transition shadow-md mt-4";
            }
        }

        // Kiểm tra Số điện thoại
        phoneInput.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0 && value[0] !== '0') {
                value = '0' + value;
            }
            this.value = value.substring(0, 10);

            if (this.value.length === 0) {
                showFieldError('phone', 'Vui lòng nhập số điện thoại');
                validationStatus.phone = false;
            } else if (this.value.length < 10) {
                showFieldError('phone', 'Chưa đủ 10 số');
                validationStatus.phone = false;
            } else {
                showFieldError('phone', '');
                validationStatus.phone = true;
            }
            checkFormValidity();
        });

        // Kiểm tra ô trống dữ liệu chữ
        [companyInput, contactInput].forEach(input => {
            input.addEventListener('input', function () {
                if (!this.value.trim()) {
                    showFieldError(this.id, 'Trường này không được để trống');
                    validationStatus[this.id] = false;
                } else {
                    showFieldError(this.id, '');
                    validationStatus[this.id] = true;
                }
                checkFormValidity();
            });
        });

        // Kiểm tra định dạng Email
        emailInput.addEventListener('input', function () {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.value.trim()) {
                showFieldError('email', 'Vui lòng nhập Email');
                validationStatus.email = false;
            } else if (!emailRegex.test(this.value)) {
                showFieldError('email', 'Email không đúng định dạng');
                validationStatus.email = false;
            } else {
                showFieldError('email', '');
                validationStatus.email = true;
            }
            checkFormValidity();
        });

        // Kiểm tra dữ liệu trùng lặp thời gian thực qua API phụ (nếu có)
        async function checkUniqueInDatabase(type, value) {
            if (!value) return;
            if (type === 'phone' && value.length < 10) return;

            try {
                const response = await fetch(`/api/check-unique?type=${type}&value=${value}`);
                const result = await response.json();

                if (result.exists) {
                    showFieldError(type, `${type === 'email' ? 'Email' : 'Số điện thoại'} này đã được đăng ký!`);
                    validationStatus[type] = false;
                } else {
                    validationStatus[type] = true;
                }
            } catch (error) {
                console.error('Lỗi check trùng lặp:', error);
            }
            checkFormValidity();
        }

        emailInput.addEventListener('change', function () {
            if (validationStatus.email) checkUniqueInDatabase('email', this.value.trim());
        });

        phoneInput.addEventListener('change', function () {
            if (validationStatus.phone && this.value.length === 10) checkUniqueInDatabase('phone', this.value.trim());
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!Object.values(validationStatus).every(status => status === true)) {
                return;
            }

            const data = {
                company_name: companyInput.value.trim(),
                contact_person: contactInput.value.trim(),
                email: emailInput.value.trim(),
                phone: phoneInput.value.trim()
            };

            fetch('/api/register-company', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(async response => {
                    const res = await response.json();

                    if (response.ok && (res.status === 'success' || res.success === true)) {
                        form.classList.add('hidden');
                        document.getElementById('successMessage').classList.remove('hidden');
                    } else {
                        let errorText = "";

                        if (res.errors && typeof res.errors === 'object') {
                            let errorList = [];
                            for (let key in res.errors) {
                                if (res.errors.hasOwnProperty(key)) {
                                    let fieldMessages = res.errors[key];
                                    if (Array.isArray(fieldMessages)) {
                                        errorList.push(...fieldMessages);
                                    } else {
                                        errorList.push(fieldMessages);
                                    }
                                }
                            }
                            errorText = errorList.join(" ").toLowerCase();
                        } else {
                            errorText = (res.message || JSON.stringify(res)).toLowerCase();
                        }

                        let isEmailTaken = errorText.includes('email') && (errorText.includes('taken') || errorText.includes('exist') || errorText.includes('đã'));
                        let isPhoneTaken = errorText.includes('phone') && (errorText.includes('taken') || errorText.includes('exist') || errorText.includes('đã'));

                        if (isEmailTaken && isPhoneTaken) {
                            alert('Lỗi: Cả Email và Số điện thoại này đều đã được đăng ký hệ thống!');
                        } else if (isEmailTaken) {
                            alert('Lỗi: Email này đã được đăng ký!');
                        } else if (isPhoneTaken) {
                            alert('Lỗi: Số điện thoại này đã được đăng ký!');
                        } else {
                            alert('Lỗi đăng ký: ' + (res.message || 'Thông tin nhập vào không hợp lệ hoặc đã tồn tại!'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Lỗi mạng/hệ thống:', error);
                    alert('Lỗi kết nối: Không thể gửi yêu cầu đăng ký lúc này!');
                });
        });
    </script>
</body>

</html>