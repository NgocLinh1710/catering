@extends('layouts.app')

@section('title', 'Tổng quan Công ty')
@section('page_title', 'Bảng điều khiển Công ty')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="text-gray-400 text-sm font-bold uppercase">Tổng số khách hàng</div>
            <div class="text-3xl font-black text-gray-800" id="count-clients">0</div>
            <div class="text-green-500 text-xs mt-2"><i class="fas fa-university mr-1"></i> Trường học & Bệnh viện</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="text-gray-400 text-sm font-bold uppercase">Nhân sự</div>
            <div class="text-3xl font-black text-gray-800" id="count-employees">0</div>
            <div class="text-blue-500 text-xs mt-2"><i class="fas fa-user-shield mr-1"></i> Đang hoạt động</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="text-gray-400 text-sm font-bold uppercase">Nguyên liệu trong kho</div>
            <div class="text-3xl font-black text-gray-800" id="count-ings">0</div>
            <div class="text-orange-500 text-xs mt-2"><i class="fas fa-box mr-1"></i> Danh mục thực phẩm</div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-700">Danh sách Khách hàng (Trường học/Đơn vị)</h3>
            <button onclick="alert('Tính năng đang phát triển')"
                class="px-4 py-2 bg-gray-900 text-white rounded-lg font-bold hover:bg-gray-800 transition text-sm">
                + Thêm Khách Hàng Mới
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="p-4">Tên đơn vị</th>
                        <th class="p-4">Địa chỉ</th>
                        <th class="p-4 text-center">Suất ăn trung bình/ngày</th>
                        <th class="p-4 text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="client-table-body">
                    <tr class="border-b">
                        <td class="p-4 font-bold">Trường Tiểu học Chu Văn An</td>
                        <td class="p-4 text-gray-600">Quận Tây Hồ, Hà Nội</td>
                        <td class="p-4 text-center">1,200</td>
                        <td class="p-4 text-center">
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Đang phục
                                vụ</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.loadData = async function () {
            // Sau này sẽ gọi API để lấy số liệu thực tế 
            document.getElementById('count-clients').innerText = "5";
            document.getElementById('count-employees').innerText = "12";
            document.getElementById('count-ings').innerText = "85";
        }
    </script>
@endsection