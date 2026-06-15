@extends('layouts.app')

@section('title', 'Quản lý Hệ thống - Admin Catering')
@section('page_title', 'Tổng quan & Quản lý Hệ thống')

@section('content')
    <div class="space-y-8">

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex flex-wrap gap-3 mb-6 items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-building mr-2 text-blue-500"></i>Danh sách Doanh nghiệp đang sử dụng Hệ thống
                </h3>

                <div class="relative w-72">
                    <input type="text" id="adminSearchInput" placeholder="Tìm tên công ty, email, đại diện..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-300 focus:outline-none text-sm">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 border-b border-gray-200 text-sm uppercase">
                            <th class="p-4">Tên Doanh nghiệp</th>
                            <th class="p-4">Người đại diện</th>
                            <th class="p-4">Liên hệ</th>
                            <th class="p-4">Quy mô khách hàng</th>
                            <th class="p-4">Trạng thái</th>
                            <th class="p-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="companiesTable">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-gray-400 block"></i>
                                Đang tải danh sách doanh nghiệp...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-6 border-t pt-4 border-gray-100">
                <div class="text-sm text-gray-600">
                    Hiển thị tổng số: <span id="adminTotalItems" class="font-bold text-gray-800">0</span> doanh nghiệp
                </div>
                <div id="adminPagination" class="flex justify-center space-x-2">
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/pagination.js') }}"></script>

    <script>
        const apiHeaders = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Authorization': 'Bearer ' + localStorage.getItem('access_token')
        };

        const paginator = typeof PaginationManager === 'function'
            ? PaginationManager({ containerId: 'adminPagination', loadFunction: loadCompanies })
            : { currentPage: 1, searchKeyword: '', render: function () { } };

        function loadCompanies(page = 1, search = '') {
            paginator.currentPage = page;
            paginator.searchKeyword = search;

            const tableBody = document.getElementById('companiesTable');
            const totalItemsEl = document.getElementById('adminTotalItems');

            fetch(`/api/admin/companies?page=${page}&search=${encodeURIComponent(search)}`, {
                method: 'GET',
                headers: apiHeaders
            })
                .then(res => res.json())
                .then(res => {
                    tableBody.innerHTML = '';
                    totalItemsEl.innerText = res.total || 0;

                    if (!res.data || res.data.length === 0) {
                        tableBody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                        Không tìm thấy doanh nghiệp nào phù hợp.
                                    </td>
                                </tr>`;
                        return;
                    }

                    res.data.forEach(company => {
                        const isLocked = company.status === 'locked';
                        const rawContact = company.contact_person || '';
                        const isExpanded = rawContact.includes('(Đã mở rộng Quy mô)');
                        const cleanContact = rawContact.replace(' (Đã mở rộng Quy mô)', '');

                        tableBody.innerHTML += `
                                <tr class="border-b hover:bg-gray-50 text-sm">
                                    <td class="p-4 font-semibold text-gray-800">${company.company_name || 'N/A'}</td>
                                    <td class="p-4 text-gray-600">${cleanContact || 'N/A'}</td>
                                    <td class="p-4">
                                        <div><i class="fas fa-envelope text-gray-400 mr-1"></i>${company.email}</div>
                                        <div><i class="fas fa-phone text-gray-400 mr-1"></i>${company.phone || 'N/A'}</div>
                                    </td>
                                    <td class="p-4">
                                        ${isExpanded ? `
                                            <span class="px-2 py-1 rounded bg-purple-100 text-purple-700 text-xs font-bold shadow-sm">
                                                <i class="fas fa-crown mr-1"></i> Không giới hạn
                                            </span>
                                        ` : `
                                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs font-bold">
                                                <i class="fas fa-leaf mr-1 text-green-500"></i> Gói Free (Giới hạn ĐT)
                                            </span>
                                        `}
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold ${isLocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">
                                            ${isLocked ? 'Đang Khóa' : 'Hoạt động'}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center space-x-1 whitespace-nowrap">
                                        ${!isExpanded ? `
                                            <button onclick="upgradeScale(${company.id})" 
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded font-medium text-xs transition shadow" title="Mở rộng quy mô cung cấp suất ăn">
                                                <i class="fas fa-arrow-up mr-1"></i>Mở rộng
                                            </button>
                                        ` : ''}

                                        <button onclick="toggleLockCompany(${company.id}, ${isLocked ? 'true' : 'false'})" 
                                            class="px-3 py-1 rounded text-white font-medium text-xs transition shadow ${isLocked ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-amber-500 hover:bg-amber-600'}">
                                            <i class="fas ${isLocked ? 'fa-unlock' : 'fa-lock'} mr-1"></i>${isLocked ? 'Mở khóa' : 'Khóa'}
                                        </button>
                                        <button onclick="deleteCompany(${company.id})" 
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-medium text-xs transition shadow">
                                            <i class="fas fa-trash-alt mr-1"></i>Xóa
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
                    console.error("Lỗi dữ liệu Admin:", err);
                    tableBody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-red-500 font-medium">Không thể tải dữ liệu từ máy chủ. Vui lòng kiểm tra lại quyền Admin.</td></tr>';
                });
        }

        let typingTimer;
        document.getElementById('adminSearchInput').addEventListener('input', function () {
            clearTimeout(typingTimer);
            const queryValue = this.value;
            typingTimer = setTimeout(() => {
                loadCompanies(1, queryValue);
            }, 500);
        });

        // Hàm xử lý MỞ RỘNG QUY MÔ khách hàng
        function upgradeScale(id) {
            if (confirm('Bạn muốn MỞ RỘNG quy mô Khách hàng cho doanh nghiệp này?')) {
                fetch(`/api/admin/companies/${id}/upgrade-scale`, { method: 'POST', headers: apiHeaders })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Lỗi hệ thống!');
                        return data;
                    })
                    .then(res => {
                        alert(res.message || 'Nâng cấp quy mô thành công!');
                        loadCompanies(paginator.currentPage, paginator.searchKeyword);
                    })
                    .catch(err => alert("Lỗi: " + err.message));
            }
        }

        // Hàm xử lý KHÓA / MỞ KHÓA
        function toggleLockCompany(id, isLocked) {
            const actionText = isLocked ? 'MỞ KHÓA' : 'KHÓA';
            if (confirm(`Bạn chắc chắn muốn ${actionText} tài khoản doanh nghiệp này?`)) {
                fetch(`/api/admin/companies/${id}/toggle-lock`, { method: 'POST', headers: apiHeaders })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Lỗi hệ thống!');
                        return data;
                    })
                    .then(res => {
                        alert(res.message || 'Thao tác thành công!');
                        loadCompanies(paginator.currentPage, paginator.searchKeyword);
                    })
                    .catch(err => alert("Lỗi: " + err.message));
            }
        }

        // Hàm xử lý XÓA
        function deleteCompany(id) {
            if (confirm('CẢNH BÁO: Xóa doanh nghiệp sẽ xóa toàn bộ dữ liệu nhân sự, khách hàng liên quan! Bạn có chắc chắn muốn XÓA?')) {
                fetch(`/api/admin/companies/${id}`, { method: 'DELETE', headers: apiHeaders })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Lỗi hệ thống!');
                        return data;
                    })
                    .then(res => {
                        alert(res.message || 'Đã xóa doanh nghiệp thành công!');
                        loadCompanies(1, paginator.searchKeyword);
                    })
                    .catch(err => alert("Lỗi: " + err.message));
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadCompanies();
        });
    </script>
@endsection