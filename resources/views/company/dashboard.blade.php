@extends('layouts.app')

@section('title', 'Tổng quan Công ty')
@section('page_title', 'Bảng điều khiển Công ty')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <div class="text-gray-400 text-xs font-black uppercase tracking-wider">Tổng số khách hàng</div>
                <div class="text-3xl font-black text-gray-800 mt-1" id="count-clients">0</div>
                <div class="text-green-500 text-xs mt-2 font-bold"><i class="fas fa-university mr-1"></i> Đang hợp tác</div>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-green-50 text-green-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-building"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <div class="text-gray-400 text-xs font-black uppercase tracking-wider">Tổng số nhân sự</div>
                <div class="text-3xl font-black text-gray-800 mt-1" id="count-employees">0</div>
                <div class="text-blue-500 text-xs mt-2 font-bold"><i class="fas fa-user-shield mr-1"></i> Đang hoạt động
                </div>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <div class="text-gray-400 text-xs font-black uppercase tracking-wider">Danh mục nguyên liệu</div>
                <div class="text-3xl font-black text-gray-800 mt-1" id="count-ings">0</div>
                <div class="text-orange-500 text-xs mt-2 font-bold"><i class="fas fa-box mr-1"></i> Thực phẩm trong kho
                </div>
            </div>
            <div
                class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-apple-whole"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between"
            id="chart-card-wrapper">
            <div>
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-1">
                    <i class="fas fa-chart-pie text-green-500 mr-1"></i> Cơ cấu nhóm suất ăn
                </h3>
                <p class="text-xs text-gray-400 mb-4">Tỷ lệ phân phối suất ăn hệ thống</p>
            </div>

            <div class="w-full flex items-center justify-center min-h-[250px]" id="chart-container">
                <canvas id="menuStructureChart" class="max-w-[220px] max-h-[220px]"></canvas>
                <div id="chart-empty-state" class="hidden text-center p-6">
                    <div
                        class="w-16 h-16 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-400">Chưa có số liệu thực đơn nào được lập.</p>
                </div>
            </div>

            <div class="text-center text-[10px] text-gray-400 font-bold mt-2">Dữ liệu cập nhật thời gian thực</div>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                <div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-1">
                        <i class="fas fa-list-check text-green-500 mr-1"></i> Danh sách Khách hàng
                    </h3>
                    <p class="text-xs text-gray-400">Các đơn vị, trường học đang phục vụ</p>
                </div>
                <button id="btnExportExcel"
                    class="px-4 py-2 bg-emerald-500 text-white font-bold rounded-lg hover:bg-emerald-600 transition text-xs shadow-sm">
                    <i class="fas fa-file-excel mr-1"></i>
                    Xuất báo cáo Excel
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-inner">
                <table class="w-full text-left border-collapse">
                    <thead
                        class="bg-gray-50 text-gray-500 text-xs font-black uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="p-4">Tên đơn vị</th>
                            <th class="p-4">Địa chỉ</th>
                            <th class="p-4 text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="client-table-body" class="text-xs font-medium text-gray-700 divide-y divide-gray-50">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Export -->
    <div id="exportModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

        <div class="bg-white rounded-xl shadow-xl w-[550px] p-6">

            <h2 class="text-lg font-bold mb-5">
                Xuất báo cáo Excel
            </h2>

            <div class="space-y-2">

                <label class="flex items-center gap-2">
                    <input type="checkbox" value="clients" checked>
                    Danh sách khách hàng
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" value="ingredients" checked>
                    Danh sách nguyên liệu
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" value="menus" checked>
                    Báo cáo ngân sách thực đơn
                </label>

            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">

                <div>
                    <label class="text-sm font-semibold">
                        Từ ngày
                    </label>

                    <input type="date" id="fromDate" class="w-full border rounded-lg p-2 mt-1">
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Đến ngày
                    </label>

                    <input type="date" id="toDate" class="w-full border rounded-lg p-2 mt-1">
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-6">

                <button id="closeExportModal" class="px-4 py-2 border rounded-lg">
                    Hủy
                </button>

                <button id="confirmExport" class="px-5 py-2 bg-green-600 text-white rounded-lg">
                    Xuất Excel
                </button>

            </div>

        </div>

    </div>

    <x-ai-chatbot />
@endsection

@section('scripts')
    <script>
        let menuChart = null;
        document.addEventListener("DOMContentLoaded", function () {
            const today = new Date().toISOString().split("T")[0];

            document.getElementById("toDate").value = today;

            const firstDay = new Date();
            firstDay.setDate(1);

            document.getElementById("fromDate").value =
                firstDay.toISOString().split("T")[0];
            loadDashboardData();

            const exportModal = document.getElementById("exportModal");

            document
                .getElementById("btnExportExcel")
                .addEventListener("click", () => {

                    exportModal.classList.remove("hidden");
                    exportModal.classList.add("flex");

                });

            document
                .getElementById("closeExportModal")
                .addEventListener("click", () => {

                    exportModal.classList.add("hidden");
                    exportModal.classList.remove("flex");

                });

            document
                .getElementById("confirmExport")
                .addEventListener("click", async () => {

                    const exportButton = document.getElementById("confirmExport");
                    const token = localStorage.getItem("access_token");

                    if (!token || token === "undefined" || token === "null") {
                        alert("Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.");
                        return;
                    }

                    let reports = [];

                    document
                        .querySelectorAll("#exportModal input[type=checkbox]:checked")
                        .forEach(c => reports.push(c.value));

                    if (reports.length === 0) {
                        alert("Vui lòng chọn ít nhất một loại báo cáo.");
                        return;
                    }

                    const from = document.getElementById("fromDate").value;
                    const to = document.getElementById("toDate").value;

                    if (!from || !to) {
                        alert("Vui lòng chọn khoảng thời gian.");
                        return;
                    }

                    if (from > to) {
                        alert("Ngày bắt đầu không được lớn hơn ngày kết thúc.");
                        return;
                    }

                    try {

                        exportButton.disabled = true;
                        exportButton.innerText = "Đang xuất...";

                        const response = await fetch("/api/company/export-report", {
                            method: "POST",

                            headers: {
                                Authorization: "Bearer " + token,
                                "Content-Type": "application/json",
                                "Accept": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json"
                            },

                            body: JSON.stringify({
                                reports: reports,
                                from: from,
                                to: to
                            })
                        });

                        if (!response.ok) {

                            const contentType = response.headers.get("Content-Type");

                            if (contentType && contentType.includes("application/json")) {
                                const errorData = await response.json();
                                alert(errorData.message || "Không thể xuất báo cáo.");
                            } else {
                                const text = await response.text();
                                alert(text || "Không thể xuất báo cáo.");
                            }

                            return;
                        }

                        const contentType = response.headers.get("Content-Type");

                        if (contentType && contentType.includes("application/json")) {
                            const errorData = await response.json();
                            alert(errorData.message || "Không thể xuất báo cáo.");
                            return;
                        }

                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement("a");
                        a.href = url;

                        let filename = "BaoCao.xlsx";

                        const disposition = response.headers.get("Content-Disposition");

                        if (disposition) {

                            const utf8 = disposition.match(/filename\*=UTF-8''([^;]+)/i);

                            if (utf8) {
                                filename = decodeURIComponent(utf8[1]);
                            } else {

                                const normal = disposition.match(/filename="?([^"]+)"?/i);

                                if (normal) {
                                    filename = normal[1];
                                }
                            }
                        }

                        a.download = filename;

                        document.body.appendChild(a);
                        a.click();
                        a.remove();

                        window.URL.revokeObjectURL(url);

                        exportModal.classList.add("hidden");
                        exportModal.classList.remove("flex");

                    } catch (error) {
                        exportModal.classList.add("hidden");
                        exportModal.classList.remove("flex");
                        console.error("Lỗi xuất Excel:", error);
                        alert("Không thể xuất báo cáo Excel. Vui lòng thử lại.");
                    } finally {

                        exportButton.disabled = false;
                        exportButton.innerText = "Xuất Excel";

                    }

                });
            exportModal.addEventListener("click", function (e) {
                if (e.target === exportModal) {
                    exportModal.classList.add("hidden");
                    exportModal.classList.remove("flex");
                }
            });
        });

        window.getUiDescription = () =>
            "Giao diện: 'Tổng quan Doanh nghiệp' (Dashboard số liệu).\n" +
            "1. Khung số liệu thống kê: Hiển thị 3 chỉ số dạng số gồm: Số khách hàng đang hợp tác, Số nhân viên đang hoạt động, và Số nguyên liệu đã được thêm vào kho.\n" +
            "2. Biểu đồ hiển thị: Biểu đồ tròn thể hiện tổng số lượng từng suất ăn (Suất thường, Suất chay, Suất dị ứng) cộng dồn của TẤT CẢ các khách hàng.\n" +
            "3. Danh sách khách hàng: Bảng hiển thị thông tin các khách hàng kèm nút 'Xuất báo cáo Excel'. Khi nhấn sẽ mở biểu mẫu lựa chọn dữ liệu cần xuất và khoảng thời gian thống kê.\n" +
            "➡️ THỨ TỰ THỰC HIỆN: Theo dõi số liệu tổng quan, sau đó có thể xuất báo cáo Excel theo nhu cầu.";

        async function loadDashboardData() {
            try {
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                };

                const token = localStorage.getItem('access_token');
                if (token && token !== 'undefined' && token !== 'null') {
                    headers['Authorization'] = 'Bearer ' + token;
                }

                const res = await fetch('/api/company/dashboard-stats', {
                    method: 'GET',
                    headers: headers
                });

                if (!res.ok) throw new Error("Yêu cầu không thành công");
                const data = await res.json();

                if (data.status === 'error') {
                    alert(data.message || "Lỗi tải dữ liệu!");
                    return;
                }

                const counts = data.counts ?? {};

                document.getElementById("count-clients").innerText = counts.clients ?? 0;
                document.getElementById("count-employees").innerText = counts.employees ?? 0;
                document.getElementById("count-ings").innerText = counts.ingredients ?? 0;

                const tableBody = document.getElementById('client-table-body');
                tableBody.innerHTML = '';

                if (!data.clients || data.clients.length === 0) {
                    tableBody.innerHTML = `
                                                                                                                                                                                                <tr>
                                                                                                                                                                                                    <td colspan="3" class="p-8 text-center text-gray-400 font-bold">
                                                                                                                                                                                                        <i class="fas fa-folder-open text-xl mb-2 block text-gray-200"></i>
                                                                                                                                                                                                        Chưa có dữ liệu đơn vị khách hàng nào trong hệ thống.
                                                                                                                                                                                                    </td>
                                                                                                                                                                                                </tr>`;
                } else {
                    data.clients.forEach(client => {
                        const isAction = client.status == 1 || client.status == 'active';
                        const statusBadge = isAction
                            ? `<span class="bg-green-50 text-green-600 px-2.5 py-1 rounded-lg text-[10px] font-black border border-green-100"><i class="fas fa-circle mr-1 text-[8px]"></i>Đang hợp tác</span>`
                            : `<span class="bg-gray-50 text-gray-400 px-2.5 py-1 rounded-lg text-[10px] font-black border border-gray-100">Tạm ngưng hợp tác</span>`;

                        tableBody.innerHTML += `
                                                                                                                                                                                                    <tr class="hover:bg-gray-50/50 transition">
                                                                                                                                                                                                        <td class="p-4 font-bold text-gray-800">${client.name}</td>
                                                                                                                                                                                                        <td class="p-4 text-gray-500">${client.address || 'Chưa cập nhật'}</td>
                                                                                                                                                                                                        <td class="p-4 text-center">${statusBadge}</td>
                                                                                                                                                                                                    </tr>
                                                                                                                                                                                                `;
                    });
                }

                // Khởi tạo biểu đồ cơ cấu suất ăn (Chỉ vẽ khi có số liệu, ngược lại hiện 0/Trống)
                const values = data.chart?.values ?? [];
                const labels = data.chart?.labels ?? [];

                const totalServings = values.reduce((sum, val) => sum + val, 0);

                const chartCanvas = document.getElementById('menuStructureChart');
                const emptyState = document.getElementById('chart-empty-state');

                if (totalServings === 0) {

                    chartCanvas.classList.add('hidden');
                    emptyState.classList.remove('hidden');

                } else {

                    chartCanvas.classList.remove('hidden');
                    emptyState.classList.add('hidden');

                    if (menuChart) {
                        menuChart.destroy();
                    }

                    const ctx = chartCanvas.getContext('2d');

                    menuChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b'],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 10,
                                            weight: 'bold'
                                        },
                                        boxWidth: 10,
                                        padding: 12
                                    }
                                }
                            },
                            cutout: '70%'
                        }
                    });

                }

            } catch (err) {
                console.error(err);
                alert("Lỗi hệ thống: Không thể tải số liệu tổng quan của tài khoản công ty.");
            }
        }
    </script>
@endsection