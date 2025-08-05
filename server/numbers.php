<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم مدیریت شماره‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-busy {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-off {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-unknown {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .search-box {
            position: relative;
            margin-bottom: 2rem;
        }
        .search-box i {
            position: absolute;
            top: 12px;
            right: 15px;
            color: #6c757d;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- هدر زیبا -->
    <header class="header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-telephone-outbound-fill"></i> سیستم مدیریت شماره‌ها
            </h1>
            <p class="lead mb-0">لیست کامل شماره‌ها و وضعیت آن‌ها</p>
        </div>
    </header>

    <div class="container">
        <!-- جستجو و فیلتر -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="search-box">
                    <input type="text" id="searchInput" class="form-control form-control-lg ps-5" placeholder="جستجوی شماره...">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <div class="col-md-6">
                <select id="statusFilter" class="form-select form-select-lg">
                    <option value="all" selected>همه وضعیت‌ها</option>
                    <option value="ACTIVE">فعال</option>
                    <option value="BUSY">مشغول</option>
                    <option value="OFF">خاموش</option>
                </select>
            </div>
        </div>

        <!-- آمار کلی -->
        <div class="row mb-4" id="statsContainer">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">تعداد کل</h5>
                        <p class="display-6 text-primary" id="totalCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">فعال</h5>
                        <p class="display-6 text-success" id="activeCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">مشغول</h5>
                        <p class="display-6 text-warning" id="busyCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">خاموش</h5>
                        <p class="display-6 text-danger" id="offCount">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول شماره‌ها -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> لیست شماره‌ها
                </h5>
                <button id="refreshBtn" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> بروزرسانی
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>شماره</th>
                                <th>وضعیت</th>
                                <th>تاریخ آخرین بررسی</th>
                            </tr>
                        </thead>
                        <tbody id="numbersTableBody">
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="loading"></div>
                                    در حال دریافت اطلاعات...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- صفحه‌بندی -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4" id="pagination">
                        <!-- صفحات به صورت دینامیک ایجاد می‌شوند -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- فوتر -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">© <span id="currentYear"></span> سیستم مدیریت شماره‌ها. تمام حقوق محفوظ است.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // متغیرهای سراسری
        let currentPage = 1;
        const itemsPerPage = 10;
        let allNumbers = [];
        let filteredNumbers = [];

        // تاریخچه جستجو
        let searchTerm = '';
        let statusFilter = 'all';

        // هنگامی که DOM کاملاً بارگذاری شد
        document.addEventListener('DOMContentLoaded', function() {
            // تنظیم سال جاری در فوتر
            document.getElementById('currentYear').textContent = new Date().getFullYear();
            
            // بارگذاری اولیه داده‌ها
            loadNumbers();
            
            // رویدادهای جستجو و فیلتر
            document.getElementById('searchInput').addEventListener('input', function() {
                searchTerm = this.value.toLowerCase();
                filterNumbers();
            });
            
            document.getElementById('statusFilter').addEventListener('change', function() {
                statusFilter = this.value;
                filterNumbers();
            });
            
            // رویداد دکمه بروزرسانی
            document.getElementById('refreshBtn').addEventListener('click', function() {
                loadNumbers();
            });
        });

        // تابع برای بارگیری داده‌ها از سرور
        async function loadNumbers() {
            try {
                // نمایش حالت لودینگ
                document.getElementById('numbersTableBody').innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="loading"></div>
                            در حال دریافت اطلاعات...
                        </td>
                    </tr>
                `;
                
                // غیرفعال کردن دکمه بروزرسانی
                document.getElementById('refreshBtn').disabled = true;
                
                // درخواست به سرور برای دریافت داده‌ها
                const response = await fetch('get_numbers.php');
                if (!response.ok) {
                    throw new Error('خطا در دریافت اطلاعات از سرور');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    allNumbers = data.numbers;
                    updateStats(allNumbers);
                    filterNumbers();
                } else {
                    throw new Error(data.message || 'خطا در پردازش اطلاعات');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('numbersTableBody').innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            ${error.message}
                        </td>
                    </tr>
                `;
            } finally {
                // فعال کردن دکمه بروزرسانی
                document.getElementById('refreshBtn').disabled = false;
            }
        }

        // تابع برای فیلتر کردن شماره‌ها بر اساس جستجو و وضعیت
        function filterNumbers() {
            filteredNumbers = allNumbers.filter(number => {
                const matchesSearch = number.number.toLowerCase().includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || number.status === statusFilter;
                return matchesSearch && matchesStatus;
            });
            
            updateStats(filteredNumbers);
            renderTable();
            renderPagination();
        }

        // تابع برای به‌روزرسانی آمار
        function updateStats(numbers) {
            document.getElementById('totalCount').textContent = numbers.length;
            document.getElementById('activeCount').textContent = numbers.filter(n => n.status === 'ACTIVE').length;
            document.getElementById('busyCount').textContent = numbers.filter(n => n.status === 'BUSY').length;
            document.getElementById('offCount').textContent = numbers.filter(n => n.status === 'OFF').length;
        }

        // تابع برای رندر جدول
        function renderTable() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedNumbers = filteredNumbers.slice(startIndex, endIndex);
            
            const tbody = document.getElementById('numbersTableBody');
            
            if (paginatedNumbers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="bi bi-info-circle-fill"></i>
                            موردی یافت نشد
                        </td>
                    </tr>
                `;
                return;
            }
            
            let html = '';
            paginatedNumbers.forEach((number, index) => {
                const rowNumber = startIndex + index + 1;
                const statusClass = getStatusClass(number.status);
                const persianDate = convertToPersianDate(number.date_time);
                const statusText = getStatusText(number.status);
                
                html += `
                    <tr>
                        <td>${rowNumber}</td>
                        <td>${number.number}</td>
                        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        <td>${persianDate}</td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        // تابع برای رندر صفحه‌بندی
        function renderPagination() {
            const totalPages = Math.ceil(filteredNumbers.length / itemsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // دکمه قبلی
            html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">قبلی</a>
                </li>
            `;
            
            // صفحات
            for (let i = 1; i <= totalPages; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            
            // دکمه بعدی
            html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">بعدی</a>
                </li>
            `;
            
            pagination.innerHTML = html;
            
            // اضافه کردن رویداد به لینک‌های صفحه‌بندی
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    if (page !== currentPage) {
                        currentPage = page;
                        renderTable();
                    }
                });
            });
        }

        // تابع برای تبدیل وضعیت به کلاس CSS
        function getStatusClass(status) {
            switch (status) {
                case 'ACTIVE': return 'status-active';
                case 'BUSY': return 'status-busy';
                case 'OFF': return 'status-off';
                default: return 'status-unknown';
            }
        }

        // تابع برای تبدیل وضعیت به متن فارسی
        function getStatusText(status) {
            switch (status) {
                case 'ACTIVE': return 'فعال';
                case 'BUSY': return 'مشغول';
                case 'OFF': return 'خاموش';
                default: return status; // نمایش مقدار اصلی اگر وضعیت شناخته شده نبود
            }
        }

        // تابع برای تبدیل تاریخ میلادی به شمسی (ساده‌شده)
        function convertToPersianDate(gregorianDate) {
            if (!gregorianDate || gregorianDate === '0000-00-00 00:00:00') return '---';
            
            const date = new Date(gregorianDate);
            if (isNaN(date.getTime())) return '---';
            
            const options = { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Tehran'
            };
            
            return new Intl.DateTimeFormat('fa-IR', options).format(date);
        }
    </script>
</body>
</html>