@extends('layouts.app')

@section('title', 'Duyệt Công ty - Admin Catering')
@section('page_title', 'Danh sách Đăng ký Chờ duyệt')

@section('content')
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-3 mb-6 items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Yêu cầu mới cần xử lý</h3>

            <div class="relative w-72">
                <input type="text" id="approvalSearchInput" placeholder="Tìm tên doanh nghiệp, email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:outline-none text-sm">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
            </div>
        </div>

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
            <tbody id="pendingCompaniesTable">
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2 text-gray-400 block"></i>
                        Đang tải danh sách yêu cầu chờ duyệt...
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center mt-6 border-t pt-4 border-gray-100">
            <div class="text-sm text-gray-600">
                Tổng số yêu cầu: <span id="approvalTotalItems" class="font-bold text-gray-800">0</span>
            </div>
            <div id="approvalPagination" class="flex justify-center space-x-2">
            </div>
        </div>
    </div>

    <x-ai-chatbot />
@endsection

@section('scripts')
    <script src="{{ asset('js/pagination.js') }}"></script>

    <script>
        const apiToken = localStorage.getItem('access_token') || '';

        window.getUiDescription = () =>
            "Giao diện: 'Duyệt doanh nghiệp đăng ký'.\n" +
            "1. Các thành phần hiển thị: Danh sách các doanh nghiệp mới nộp đơn đăng ký sử dụng hệ thống.\n" +
            "2. Các nút chức năng trên từng yêu cầu đăng ký:\n" +
            "   - Nút 'Duyệt': Chấp nhận doanh nghiệp vào hệ thống. Hành động này sẽ kích hoạt hệ thống tự động sinh mật khẩu ngẫu nhiên cho doanh nghiệp đó.\n" +
            "   - Nút 'Từ chối': Từ chối yêu cầu đăng ký hiện tại. Doanh nghiệp bị từ chối vẫn giữ quyền đăng ký lại từ đầu nếu cần.\n" +
            "➡️ THỨ TỰ THỰC HIỆN: Admin kiểm tra thông tin đơn đăng ký -> Bấm 'Duyệt' để cấp tài khoản tự động hoặc bấm 'Từ chối' để bác bỏ đơn.";

        const paginator = typeof PaginationManager === 'function'
            ? PaginationManager({ containerId: 'approvalPagination', loadFunction: loadPendingCompanies })
            : { currentPage: 1, searchKeyword: '', render: function () { } };

        function loadPendingCompanies(page = 1, search = '') {
            paginator.currentPage = page;
            paginator.searchKeyword = search;

            const tableBody = document.getElementById('pendingCompaniesTable');
            const totalItemsEl = document.getElementById('approvalTotalItems');

            fetch(`/api/admin/pending-companies?page=${page}&search=${encodeURIComponent(search)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + apiToken
                }
            })
                .then(res => res.json())
                .then(res => {
                    tableBody.innerHTML = '';
                    totalItemsEl.innerText = res.total || 0;

                    if (!res.data || res.data.length === 0) {
                        tableBody.innerHTML = `
                                                    <tr>
                                                        <td colspan="5" class="p-8 text-center text-gray-500">
                                                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                                            Hiện không có yêu cầu đăng ký nào đang chờ duyệt.
                                                        </td>
                                                    </tr>`;
                        return;
                    }

                    res.data.forEach(company => {
                        // Định dạng lại ngày tháng tạo
                        let createdDate = 'N/A';
                        if (company.created_at) {
                            const dateObj = new Date(company.created_at);
                            const d = String(dateObj.getDate()).padStart(2, '0');
                            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                            const y = dateObj.getFullYear();
                            const h = String(dateObj.getHours()).padStart(2, '0');
                            const i = String(dateObj.getMinutes()).padStart(2, '0');
                            createdDate = `${d}/${m}/${y} ${h}:${i}`;
                        }

                        tableBody.innerHTML += `
                                                    <tr class="border-b hover:bg-gray-50">
                                                        <td class="p-4 text-sm text-gray-500">${createdDate}</td>
                                                        <td class="p-4 font-semibold text-gray-800">${company.company_name}</td>
                                                        <td class="p-4 text-gray-600">${company.contact_person}</td>
                                                        <td class="p-4 text-sm">
                                                            <div><i class="fas fa-envelope text-gray-400 mr-1"></i> ${company.email}</div>
                                                            <div><i class="fas fa-phone text-gray-400 mr-1"></i> ${company.phone}</div>
                                                        </td>
                                                        <td class="p-4 text-center space-x-2 whitespace-nowrap">
                                                            <button onclick="approveCompany(${company.id})"
                                                                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition shadow">
                                                                <i class="fas fa-check"></i> Duyệt
                                                            </button>
                                                            <button onclick="rejectCompany(${company.id})"
                                                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition shadow">
                                                                <i class="fas fa-times"></i> Từ chối
                                                            </button>
                                                        </td>
                                                    </tr>
                                                `;
                    });

                    if (typeof paginator.render === 'function') {
                        paginator.render(res.last_page, res.current_page);
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi tải danh sách chờ duyệt:', err);
                    tableBody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500 font-medium">Lỗi kết nối máy chủ không thể lấy dữ liệu.</td></tr>';
                });
        }

        let typingTimer;
        document.getElementById('approvalSearchInput').addEventListener('input', function () {
            clearTimeout(typingTimer);
            const queryValue = this.value;
            typingTimer = setTimeout(() => {
                loadPendingCompanies(1, queryValue);
            }, 500);
        });

        function approveCompany(id) {
            if (confirm('Bạn có chắc chắn muốn DUYỆT và cấp tài khoản cho công ty này?')) {
                fetch('/api/admin/approve-company/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + apiToken
                    }
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success' || res.success) {
                            alert(`DUYỆT THÀNH CÔNG!\n\nEmail đăng nhập: ${res.email}\nMật khẩu hệ thống cấp: ${res.password}\n\n(Lưu ý: Mật khẩu này sau này sẽ được hệ thống gửi thẳng vào Email của khách)`);
                            loadPendingCompanies(paginator.currentPage, paginator.searchKeyword);
                        } else {
                            alert('Lỗi: ' + res.message);
                        }
                    })
                    .catch(async (error) => {
                        console.error(error);
                        alert('Có lỗi hệ thống xảy ra khi duyệt doanh nghiệp.');
                    });
            }
        }

        // Hàm TỪ CHỐI yêu cầu đăng ký
        function rejectCompany(id) {
            if (confirm('Bạn có chắc chắn muốn TỪ CHỐI yêu cầu đăng ký này không?')) {
                fetch('/api/admin/reject-company/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + apiToken
                    }
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success') {
                            alert(res.message);
                            loadPendingCompanies(paginator.currentPage, paginator.searchKeyword);
                        } else {
                            alert('Lỗi: ' + res.message);
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi khi từ chối:', error);
                        alert('Có lỗi hệ thống xảy ra khi thực hiện từ chối.');
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadPendingCompanies();
        });
    </script>
@endsection