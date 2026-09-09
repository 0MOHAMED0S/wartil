@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* --- التصميم العام --- */
        .student-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
            background-color: #f0f2f5;
            color: #2d8a74;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }

        .table-card {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: #fafafa;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            border: 1px solid transparent;
            background: transparent;
        }

        .action-btn:hover {
            background-color: #f0f2f5;
            border-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        /* --- Smart Search Styles --- */
        .search-box {
            position: relative;
            min-width: 350px;
            flex-grow: 1;
        }

        .search-icon-wrapper {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            background: none;
            border: none;
            outline: none;
            padding: 0;
            transition: 0.3s;
        }

        .search-icon-wrapper:hover {
            color: #2d8a74;
            cursor: pointer;
        }

        .search-box input {
            padding-right: 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding-top: 10px;
            padding-bottom: 10px;
            transition: 0.3s;
            width: 100%;
        }

        .search-box input:focus {
            border-color: #2d8a74;
            box-shadow: 0 0 0 3px rgba(45, 138, 116, 0.1);
            outline: none;
        }

        .search-hint {
            font-size: 0.75rem;
            position: absolute;
            bottom: -20px;
            right: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        /* --- ننسيق حقول الإدخال --- */
        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2d8a74;
            box-shadow: 0 0 0 3px rgba(45, 138, 116, 0.1);
        }

        .custom-input {
            border: 1px solid #e2e8f0;
            border-radius: 10px !important;
            padding: 10px 15px;
            font-size: 0.95rem;
            background-color: #f8fafc;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            background-color: #fff;
        }

        /* --- التنبيهات الثابتة (Responsive) --- */
        .fixed-alert-container {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 10000;
            width: 100%;
            max-width: 350px;
            pointer-events: none;
        }

        .custom-toast {
            pointer-events: auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            position: relative;
            border-left: 5px solid transparent;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            direction: rtl;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .custom-toast.success {
            border-left-color: #2d8a74;
        }

        .custom-toast.error {
            border-left-color: #e11d48;
        }

        .toast-content {
            padding: 15px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon {
            font-size: 1.6rem;
        }

        .success .toast-icon {
            color: #2d8a74;
        }

        .error .toast-icon {
            color: #e11d48;
        }

        .toast-body {
            flex-grow: 1;
            text-align: right;
        }

        .toast-title {
            display: block;
            font-weight: 800;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .toast-message {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #94a3b8;
            cursor: pointer;
            order: -1;
        }

        .toast-progress {
            height: 3px;
            width: 100%;
            background: #f1f5f9;
            position: absolute;
            bottom: 0;
            right: 0;
        }

        .toast-progress::before {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            height: 100%;
            width: 100%;
        }

        .success .toast-progress::before {
            background: #2d8a74;
            animation: progressRun 5s linear forwards;
        }

        .error .toast-progress::before {
            background: #e11d48;
            animation: progressRun 5s linear forwards;
        }

        @keyframes progressRun {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* --- تصميم الفلاتر الاحترافية المتجاوبة --- */
        .filter-scroll-wrapper {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 5px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .filter-scroll-wrapper::-webkit-scrollbar {
            display: none;
        }

        .filter-badge {
            padding: 6px 16px;
            border-radius: 50px;
            background: #fff;
            border: 1px solid #e2e8f0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-badge.active {
            background: #2d8a74;
            color: #fff;
            border-color: #2d8a74;
            box-shadow: 0 4px 10px rgba(45, 138, 116, 0.2);
        }

        .filter-badge:hover:not(.active) {
            border-color: #2d8a74;
            color: #2d8a74;
        }

        /* --- Creative Gift Modal Styles --- */
        .gift-card-header {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .gift-icon-anim {
            font-size: 3.5rem;
            color: #fff;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .gift-selection-box {
            border: 2px dashed #e2e8f0;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .gift-selection-box:focus-within {
            border-color: #ff9a9e;
            background-color: #fffafb;
        }

        /* --- تصميم المودال الاحترافي (Profile Modal) --- */
        .profile-header-bg {
            background: linear-gradient(135deg, #2d8a74 0%, #1e5e4f 100%);
            height: 100px;
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .profile-avatar-wrapper {
            position: relative;
            margin-top: -50px;
            text-align: center;
            margin-bottom: 15px;
        }

        .profile-avatar-wrapper img,
        .profile-avatar-wrapper .student-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            object-fit: cover;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #2d8a74;
        }

        .info-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 10px;
            background: #f8fafc;
            transition: 0.2s;
        }

        .info-list-item:hover {
            background: #f1f5f9;
        }

        .info-list-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: white;
            color: #2d8a74;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .info-list-content h6 {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }

        .info-list-content p {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 600;
            margin: 0;
            word-break: break-word;
        }

        .package-scroll {
            max-height: 250px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .package-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .package-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* --- التحسينات الخاصة بالشاشات المتجاوبة --- */
        @media (max-width: 768px) {
            .search-box {
                min-width: 100%;
            }

            .modal-dialog {
                margin: 10px;
            }

            .filter-container-wrapper {
                flex-direction: column;
                align-items: stretch !important;
            }

            .filter-container-wrapper select,
            .filter-container-wrapper input[type="date"] {
                width: 100% !important;
                margin-bottom: 10px;
            }

            .fixed-alert-container {
                right: 10px;
                left: 10px;
                top: 10px;
                width: auto;
                max-width: none;
            }
        }

        .bg-soft-primary {
            background-color: rgba(45, 138, 116, 0.1);
        }

        .transition-hover {
            transition: all 0.3s ease;
        }

        .transition-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
            border-color: #2d8a74 !important;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-new {
            background: linear-gradient(45deg, #ff5f6d, #ffc371);
            color: white;
            font-size: 0.6rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-right: 5px;
            animation: pulse 1.5s infinite;
        }

        .stats-summary-card {
            background: linear-gradient(135deg, #2d8a74 0%, #1e5e4f 100%);
            color: white;
            border-radius: 24px;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .stats-summary-card::after {
            content: "\f501";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 150px;
            opacity: 0.1;
        }

        .stats-filter-box {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5">إدارة الطلاب</h5>
        </div>
        <div>
            <a href="{{ route('admin.students.export', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fa-solid fa-file-csv me-1"></i> تصدير (CSV)
            </a>
        </div>
    </div>
@endsection

@section('content')

    <div class="fixed-alert-container">
        @if (session('success'))
            <div class="custom-toast success shadow-lg" role="alert">
                <div class="toast-content">
                    <button type="button" class="toast-close"
                        onclick="this.closest('.custom-toast').remove()">&times;</button>
                    <div class="toast-body"><span class="toast-title">تم بنجاح</span>
                        <p class="toast-message">{{ session('success') }}</p>
                    </div>
                    <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
                </div>
                <div class="toast-progress"></div>
            </div>
        @endif
        @if (session('error'))
            <div class="custom-toast error shadow-lg" role="alert">
                <div class="toast-content">
                    <button type="button" class="toast-close"
                        onclick="this.closest('.custom-toast').remove()">&times;</button>
                    <div class="toast-body"><span class="toast-title">خطأ في العملية</span>
                        <p class="toast-message">{{ session('error') }}</p>
                    </div>
                    <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                </div>
                <div class="toast-progress"></div>
            </div>
        @endif
    </div>

    <div class="container-fluid p-3 p-md-4">
        {{-- كروت الإحصائيات العلوية (Responsive Grid) --}}
        <div class="row g-3 mb-4 text-center text-md-start">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3 border h-100 transition-hover">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary p-2 rounded-circle"><i
                            class="fa-solid fa-user-graduate fs-5"></i></div>
                    <div>
                        <h4 class="m-0 fw-bold fs-5">{{ number_format($totalStudents) }}</h4><small
                            class="text-muted fw-bold">إجمالي الطلاب</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3 border h-100 transition-hover">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning p-2 rounded-circle"><i
                            class="fa-solid fa-hourglass-half fs-5"></i></div>
                    <div>
                        <h4 class="m-0 fw-bold fs-5">{{ number_format($totalMinutesRegistered) }}</h4><small
                            class="text-muted fw-bold">دقائق الرصيد الكلية</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3 border h-100 transition-hover">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger p-2 rounded-circle"><i
                            class="fa-solid fa-gift fs-5"></i></div>
                    <div>
                        <h4 class="m-0 fw-bold fs-5">{{ number_format($totalGifts) }}</h4><small
                            class="text-muted fw-bold">إجمالي الهدايا</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3 border h-100 transition-hover">
                    <div class="stat-icon bg-success bg-opacity-10 text-success p-2 rounded-circle"><i
                            class="fa-solid fa-box-open fs-5"></i></div>
                    <div>
                        <h4 class="m-0 fw-bold fs-5">{{ number_format($activePackagesCount) }}</h4><small
                            class="text-muted fw-bold">باقات نشطة حالياً</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🟢 نموذج البحث والفلترة (Request) --}}
        <form action="{{ url()->current() }}" method="GET" id="searchFilterForm">

            {{-- حقل مخفي لحفظ حالة الفلتر من الأزرار العلوية --}}
            <input type="hidden" name="filter" id="filterInput" value="{{ request('filter', 'all') }}">

            {{-- الفلاتر (Responsive Scroll) --}}
            <div class="filter-scroll-wrapper mb-4" id="filterButtons">
                <button type="button" class="filter-badge {{ request('filter', 'all') == 'all' ? 'active' : '' }}"
                    onclick="submitFilter('all')">الكل</button>
                <button type="button" class="filter-badge {{ request('filter') == 'gift' ? 'active' : '' }}"
                    onclick="submitFilter('gift')">باقات هدايا 🎁</button>
                @foreach ($allPackages as $pkg)
                    <button type="button"
                        class="filter-badge {{ request('filter') == 'pkg-' . $pkg->name ? 'active' : '' }}"
                        onclick="submitFilter('pkg-{{ $pkg->name }}')">{{ $pkg->name }}</button>
                @endforeach
            </div>

            {{-- أدوات البحث (Responsive Wrapper) --}}
            <div class="filter-container filter-container-wrapper">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold m-0 text-dark">قائمة الطلاب</h5>
                    <span class="badge bg-soft-primary text-primary rounded-pill px-3"
                        id="resultsCount">{{ method_exists($students, 'total') ? $students->total() : $students->count() }}
                        نتيجة لـ بحثك</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap w-100 w-md-auto">
                    <select name="country" id="countryFilter"
                        class="form-select form-select-sm rounded-pill border-0 shadow-sm px-3 py-2" style="width: 150px;"
                        onchange="document.getElementById('searchFilterForm').submit()">
                        <option value="all" {{ request('country', 'all') == 'all' ? 'selected' : '' }}>كل الدول</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->name }}" {{ request('country') == $c->name ? 'selected' : '' }}>
                                {{ $c->name }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="date" id="joinDateFilter"
                        class="form-control form-control-sm rounded-pill border-0 shadow-sm px-3 py-2" style="width: 160px;"
                        value="{{ request('date') }}" onchange="document.getElementById('searchFilterForm').submit()">

                    <div class="search-box flex-grow-1">
                        <button type="submit" class="search-icon-wrapper" id="searchIcon" title="بحث">
                            <i class="fa-solid fa-search"></i>
                        </button>
                        <input type="text" name="search" id="searchInput" class="form-control rounded-pill bg-white"
                            placeholder="ابحث بالاسم، الهاتف، الإيميل واضغط Enter..." value="{{ request('search') }}">
                        <div id="searchHint" class="search-hint" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </form>

        {{-- الجدول (Responsive Wrapper) --}}
        <div class="card table-card border-0 shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="studentsTable" style="min-width: 800px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="p-3 text-muted small fw-bold border-0 text-start">الطالب</th>
                                <th class="p-3 text-muted small fw-bold border-0 text-start">الاتصال</th>
                                <th class="p-3 text-muted small fw-bold border-0 text-start">الباقة</th>
                                <th class="p-3 text-muted small fw-bold border-0 text-center">الرصيد الكلي</th>
                                <th class="p-3 text-muted small fw-bold border-0 text-end">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            @forelse($students as $student)
                                @php
                                    $extraPackages =
                                        count($student->all_packages) > 1
                                            ? ' +' . (count($student->all_packages) - 1)
                                            : '';
                                    $hasGift = collect($student->all_packages)->contains('is_gift', true);
                                    $isNewToday = \Carbon\Carbon::parse($student->created_at)->isToday();
                                @endphp
                                <tr class="student-row">
                                    <td class="p-3 border-bottom-0 text-start">
                                        <div class="d-flex align-items-center">
                                            @if ($student->profile_image)
                                                <img src="{{ $student->profile_image }}"
                                                    class="student-avatar rounded-circle me-3 border shadow-sm">
                                            @else
                                                <div class="student-avatar rounded-circle me-3">
                                                    {{ $student->avatar_initials }}</div>
                                            @endif
                                            <div class="mx-2 text-start">
                                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center"
                                                    style="font-size: 0.9rem;">
                                                    {{ $student->name }} @if ($isNewToday)
                                                        <span class="badge-new">جديد</span>
                                                    @endif
                                                </h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">ID:
                                                    #{{ 1000 + $student->id }} | {{ $student->country_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 border-bottom-0 text-start">
                                        <div class="d-flex flex-column text-start"><small class="mb-1 text-muted"><i
                                                    class="fa-solid fa-envelope me-1"></i>
                                                {{ $student->email }}</small><small class="text-muted"><i
                                                    class="fa-solid fa-phone me-1"></i> {{ $student->phone }}</small>
                                        </div>
                                    </td>
                                    <td class="p-3 border-bottom-0 text-start"><span
                                            class="badge {{ $student->package_name == 'لا يوجد باقة' ? 'bg-secondary' : 'bg-success' }} bg-opacity-10 {{ $student->package_name == 'لا يوجد باقة' ? 'text-secondary' : 'text-success' }} px-3 py-2 rounded-pill fw-bold"
                                            style="font-size: 0.75rem;"><i class="fa-solid fa-box-open me-1"></i>
                                            {{ $student->package_name }}{{ $extraPackages }}</span></td>
                                    <td class="p-3 border-bottom-0 text-center"><span
                                            class="fw-bold {{ $student->total_minutes > 0 ? 'text-dark' : 'text-danger' }}">{{ number_format($student->total_minutes) }}</span><small
                                            class="text-muted d-block" style="font-size: 10px">دقيقة</small></td>
                                    <td class="p-3 border-bottom-0 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="action-btn text-muted"
                                                onclick="showStudentDetails({{ json_encode($student) }})"
                                                title="عرض التفاصيل"><i class="fa-solid fa-eye"></i></button>
                                            <button type="button" class="action-btn text-primary"
                                                onclick="showEditModal({{ json_encode($student) }})" title="تعديل"><i
                                                    class="fa-solid fa-pen"></i></button>
                                            <button type="button" class="action-btn text-warning"
                                                onclick="showGiftModal({{ $student->id }}, '{{ $student->name }}')"
                                                title="إهداء باقة"><i class="fa-solid fa-gift"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-magnifying-glass fs-2 mb-3 d-block opacity-25"></i>
                                        <h6 class="fw-bold">لا يوجد نتائج تطابق بحثك</h6>
                                        <p class="small">الرجاء تغيير كلمات البحث أو إزالة الفلاتر والمحاولة مرة أخرى.</p>
                                        <a href="{{ route('admin.students') }}"
                                            class="btn btn-outline-primary btn-sm rounded-pill mt-2">عرض كل الطلاب</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    @if (method_exists($students, 'firstItem') && method_exists($students, 'lastItem'))
                        <small class="text-muted">عرض {{ $students->firstItem() }} - {{ $students->lastItem() }} من
                            {{ $students->total() }} طالب</small>
                    @endif
                    <div class="laravel-pagination">
                        @if (method_exists($students, 'links'))
                            {{ $students->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- قسم إحصائيات تسجيل الطلاب --}}
        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-5">
                <div class="stats-summary-card h-100 shadow-lg border-0">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h6 class="text-white text-opacity-75 fw-bold mb-1">تحليل التسجيلات</h6>
                            <h2 class="text-white fw-bold mb-0">إحصائيات الطلاب</h2>
                        </div>
                        <div class="bg-white bg-opacity-20 p-2 rounded-3 text-white"><i
                                class="fa-solid fa-chart-line fs-4"></i></div>
                    </div>
                    <div class="display-stats py-3">
                        <h1 class="fw-bold text-white mb-2" id="filteredStudentsDisplay">
                            {{ number_format($totalStudents) }} <small class="fs-4">طالب</small></h1>
                        <span class="badge bg-white bg-opacity-25 rounded-pill text-white border-0 py-2 px-3"
                            id="statsDateLabel">الفترة: كافة الأوقات</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-7">
                <div class="stats-filter-box h-100">
                    <h6 class="fw-bold mb-4 text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-primary"></i> تتبع نشاط التسجيل
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">من تاريخ</label>
                            <input type="date" id="statsFromDate" class="form-control custom-input"
                                onchange="calculatePeriodStats()">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">إلى تاريخ</label>
                            <input type="date" id="statsToDate" class="form-control custom-input"
                                onchange="calculatePeriodStats()">
                        </div>
                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold d-block mb-3">اختصارات زمنية</label>
                            <div class="filter-scroll-wrapper">
                                <button
                                    class="btn btn-light rounded-pill px-4 fw-bold small text-muted border quick-stat-btn"
                                    onclick="quickStatsFilter('today', this)">اليوم</button>
                                <button
                                    class="btn btn-light rounded-pill px-4 fw-bold small text-muted border quick-stat-btn"
                                    onclick="quickStatsFilter('month', this)">هذا الشهر</button>
                                <button
                                    class="btn btn-light rounded-pill px-4 fw-bold small text-muted border quick-stat-btn"
                                    onclick="quickStatsFilter('lastMonth', this)">الشهر الماضي</button>
                                <button
                                    class="btn btn-light rounded-pill px-4 fw-bold small text-muted border quick-stat-btn active-all"
                                    onclick="quickStatsFilter('all', this)">كل الأوقات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- 1. مودال عرض التفاصيل (البروفايل الاحترافي) --}}
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                <div class="profile-header-bg">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-12 col-md-5 bg-white border-end position-relative">
                            <div class="profile-avatar-wrapper">
                                <img id="modalImage" src="" style="display: none;" alt="Student Avatar">
                                <div id="modalInitialsLarge" class="student-avatar-large" style="display: none;"></div>
                            </div>
                            <div class="text-center px-4 pb-4">
                                <h4 id="modalName" class="fw-bold text-dark mb-1"></h4>
                                <span id="modalCountry"
                                    class="badge bg-light text-dark border px-3 py-1 rounded-pill mb-4"><i
                                        class="fa-solid fa-location-dot text-danger me-1"></i> </span>

                                <div class="info-list-item">
                                    <div class="info-list-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="info-list-content text-start">
                                        <h6>البريد الإلكتروني</h6>
                                        <p id="modalEmail"></p>
                                    </div>
                                </div>
                                <div class="info-list-item">
                                    <div class="info-list-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div class="info-list-content text-start">
                                        <h6>رقم الهاتف</h6>
                                        <p id="modalPhone" dir="ltr"></p>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="info-list-item px-2 py-2">
                                            <div class="info-list-content text-start w-100">
                                                <h6 style="font-size: 0.65rem;">الجنس</h6>
                                                <p id="modalGender" style="font-size: 0.85rem;"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-list-item px-2 py-2">
                                            <div class="info-list-content text-start w-100">
                                                <h6 style="font-size: 0.65rem;">الوظيفة</h6>
                                                <p id="modalJob" style="font-size: 0.85rem;"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-list-item mt-2">
                                    <div class="info-list-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                                    <div class="info-list-content text-start">
                                        <h6>المؤهل</h6>
                                        <p id="modalQualification"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 p-4 p-md-5 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold m-0 text-dark"><i
                                        class="fa-solid fa-box-open text-primary me-2"></i>سجل الاشتراكات</h6>
                                <span class="badge bg-white text-primary border px-3 py-2 rounded-pill shadow-sm"
                                    id="packageCount"></span>
                            </div>
                            <div class="package-scroll custom-scrollbar pe-2">
                                <table class="table table-borderless align-middle mb-0">
                                    <tbody id="modalPackagesTable">
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 pt-3 border-top text-center text-muted small">
                                تاريخ الانضمام للمنصة: <span id="modalJoinDate" class="fw-bold text-dark"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. مودال التعديل الاحترافي --}}
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form id="updateForm" method="POST" action="">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 bg-light p-4 pb-3 rounded-top-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px;">
                                <i class="fa-solid fa-user-pen fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold m-0 text-dark">تعديل بيانات الطالب</h5>
                                <small class="text-muted">تحديث المعلومات الأساسية للملف الشخصي</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label"><i class="fa-regular fa-user text-muted me-1"></i>الاسم
                                    بالكامل</label>
                                <input type="text" name="name" id="edit_name" class="form-control custom-input"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="fa-solid fa-phone text-muted me-1"></i>رقم
                                    الهاتف</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control custom-input"
                                    dir="ltr">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="fa-solid fa-graduation-cap text-muted me-1"></i>المؤهل
                                    العلمي</label>
                                <input type="text" name="qualification" id="edit_qualification"
                                    class="form-control custom-input">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label"><i class="fa-solid fa-briefcase text-muted me-1"></i>الحالة
                                    المهنية</label>
                                <input type="text" name="professional_status" id="edit_job"
                                    class="form-control custom-input">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label"><i
                                        class="fa-solid fa-venus-mars text-muted me-1"></i>الجنس</label>
                                <select name="gender" id="edit_gender" class="form-select custom-input">
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button"
                            class="btn btn-light rounded-pill px-4 fw-bold text-muted flex-grow-1 flex-md-grow-0"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit"
                            class="btn btn-primary rounded-pill px-5 fw-bold shadow flex-grow-1 flex-md-grow-0">حفظ
                            التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. مودال إهداء باقة --}}
    <div class="modal fade" id="giftPackageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form id="giftForm" method="POST" action="">
                    @csrf
                    <div class="gift-card-header">
                        <i class="fa-solid fa-gift gift-icon-anim"></i>
                        <h4 class="text-white fw-bold mt-3 mb-1">إهداء باقة خاصة!</h4>
                        <p class="text-white text-opacity-75 small mb-0">إرسال باقة تعليمية مجانية للطالب</p>
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="text-muted mb-1">أنت الآن تقوم بإهداء باقة لـ</p>
                        <h5 id="giftStudentName" class="fw-bold text-dark mb-4"></h5>
                        <div class="gift-selection-box p-3 text-start bg-light">
                            <label class="form-label fw-bold small text-primary mb-2"><i
                                    class="fa-solid fa-star me-1"></i> اختر الباقة المميزة</label>
                            <select name="package_id" class="form-select custom-input fw-bold bg-white" required>
                                <option value="" selected disabled>تصفح الباقات المتوفرة...</option>
                                @isset($allPackages)
                                    @foreach ($allPackages as $pkg)
                                        <option value="{{ $pkg->id }}">🎁 {{ $pkg->name }} —
                                            ({{ $pkg->base_minutes + $pkg->bonus_minutes }} دقيقة)</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 justify-content-center">
                        <button type="submit" class="btn btn-lg rounded-pill px-5 fw-bold shadow-sm w-100 mx-3"
                            style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: white; border: none;">إرسال
                            الهدية الآن</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // --- دوال إحصائيات التسجيل (لم يتم تغييرها) ---
        const ORIGINAL_STUDENT_DATES = @json($allStudentDates ?? []);

        function isDateInRange(targetDateStr, fromDateStr, toDateStr) {
            if (!targetDateStr) return false;
            const target = new Date(targetDateStr);
            target.setHours(0, 0, 0, 0);
            const from = new Date(fromDateStr);
            from.setHours(0, 0, 0, 0);
            const to = new Date(toDateStr);
            to.setHours(0, 0, 0, 0);
            return target.getTime() >= from.getTime() && target.getTime() <= to.getTime();
        }

        function calculatePeriodStats() {
            const fromInput = document.getElementById('statsFromDate').value;
            const toInput = document.getElementById('statsToDate').value;
            if (!fromInput || !toInput) return;

            let count = 0;
            ORIGINAL_STUDENT_DATES.forEach(dateStr => {
                if (isDateInRange(dateStr, fromInput, toInput)) count++;
            });

            document.getElementById('filteredStudentsDisplay').innerHTML =
                `${count.toLocaleString()} <small class="fs-4">طالب</small>`;
            document.getElementById('statsDateLabel').innerText = `الفترة: من ${fromInput} إلى ${toInput}`;
        }

        function quickStatsFilter(period, btn) {
            document.querySelectorAll('.quick-stat-btn').forEach(b => {
                b.classList.remove('bg-primary', 'text-white', 'active-all');
                b.classList.add('btn-light', 'text-muted');
            });
            btn.classList.remove('btn-light', 'text-muted');
            btn.classList.add('bg-primary', 'text-white', 'active-all');

            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const todayStr = new Date(now - offset).toISOString().split('T')[0];

            if (period === 'all') {
                document.getElementById('filteredStudentsDisplay').innerHTML =
                    '{{ number_format($totalStudents) }} <small class="fs-4">طالب</small>';
                document.getElementById('statsDateLabel').innerText = 'الفترة: كافة الأوقات';
                document.getElementById('statsFromDate').value = '';
                document.getElementById('statsToDate').value = '';
                return;
            }

            let fromStr = todayStr,
                toStr = todayStr;

            if (period === 'month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                fromStr = new Date(firstDay.getTime() - offset).toISOString().split('T')[0];
            } else if (period === 'lastMonth') {
                const firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const lastDayLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                fromStr = new Date(firstDayLastMonth.getTime() - offset).toISOString().split('T')[0];
                toStr = new Date(lastDayLastMonth.getTime() - offset).toISOString().split('T')[0];
            }

            document.getElementById('statsFromDate').value = fromStr;
            document.getElementById('statsToDate').value = toStr;
            calculatePeriodStats();
        }

        // --- تحديث المودالات ---
        function showStudentDetails(student) {
            const modalImg = document.getElementById('modalImage');
            const modalInit = document.getElementById('modalInitialsLarge');

            if (student.profile_image) {
                modalImg.src = student.profile_image;
                modalImg.style.display = 'inline-flex';
                modalInit.style.display = 'none';
            } else {
                modalImg.style.display = 'none';
                modalInit.style.display = 'inline-flex';
                modalInit.innerText = student.avatar_initials;
            }

            document.getElementById('modalName').innerText = student.name;
            document.getElementById('modalEmail').innerText = student.email;
            document.getElementById('modalPhone').innerText = student.phone || 'غير متوفر';
            document.getElementById('modalCountry').innerHTML =
                `<i class="fa-solid fa-location-dot text-danger me-1"></i> ${student.country_name}`;
            document.getElementById('modalGender').innerText = student.gender || 'غير محدد';
            document.getElementById('modalJob').innerText = student.job || 'غير محدد';
            document.getElementById('modalQualification').innerText = student.qualification || 'غير محدد';
            document.getElementById('modalJoinDate').innerText = student.created_at_human || 'غير محدد';

            const tableBody = document.getElementById('modalPackagesTable');
            const countBadge = document.getElementById('packageCount');
            tableBody.innerHTML = '';

            if (student.all_packages && student.all_packages.length > 0) {
                countBadge.innerText = `${student.all_packages.length} باقات`;
                student.all_packages.forEach(pkg => {
                    const giftTag = pkg.is_gift ?
                        `<span class="badge bg-warning bg-opacity-10 text-warning ms-2 rounded-pill" style="font-size: 0.6rem;">هدية 🎁</span>` :
                        '';
                    tableBody.innerHTML += `
                    <tr class="bg-white rounded-3 shadow-sm mb-2 d-block w-100">
                        <td class="p-3 border-0 w-100 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center">${pkg.name} ${giftTag}</h6>
                                <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> ${pkg.date}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 rounded-pill px-3 py-2">${pkg.remain} دقيقة</span>
                            </div>
                        </td>
                    </tr>`;
                });
            } else {
                countBadge.innerText = '0 باقات';
                tableBody.innerHTML =
                    '<tr><td colspan="3" class="text-center py-5 text-muted"><i class="fa-solid fa-box-open fs-2 mb-2 opacity-50"></i><br>لا توجد باقات حالية</td></tr>';
            }
            new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
        }

        function showEditModal(student) {
            document.getElementById('updateForm').action = `/admin/students/${student.id}`;
            document.getElementById('edit_name').value = student.name;
            document.getElementById('edit_phone').value = student.phone;
            document.getElementById('edit_qualification').value = student.qualification;
            document.getElementById('edit_job').value = student.job;
            document.getElementById('edit_gender').value = (student.gender === 'ذكر') ? 'male' : 'female';
            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        }

        function showGiftModal(id, name) {
            document.getElementById('giftForm').action = `/admin/students/gift/${id}`;
            document.getElementById('giftStudentName').innerText = name;
            new bootstrap.Modal(document.getElementById('giftPackageModal')).show();
        }

        // ========================================================
        // دالة إرسال الفلترة (Submit Filter)
        // ========================================================
        function submitFilter(filterValue) {
            document.getElementById('filterInput').value = filterValue;
            document.getElementById('searchFilterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', () => {
            // إخفاء الـ Toasts بعد 5 ثواني
            setTimeout(() => {
                document.querySelectorAll('.custom-toast').forEach(toast => {
                    toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                    setTimeout(() => toast.remove(), 500);
                });
            }, 5000);

            // التلميح الذكي أثناء الكتابة
            document.getElementById('searchInput').addEventListener('keyup', function(e) {
                let search = this.value.trim();
                let hintBox = document.getElementById('searchHint');

                if (search.length > 0) {
                    hintBox.style.display = 'block';
                    if (search.includes('@')) {
                        hintBox.innerHTML =
                            '<i class="fa-solid fa-envelope text-primary ms-1"></i> اضغط Enter للبحث بالبريد...';
                        hintBox.className = 'search-hint text-primary';
                    } else if (/^\d+$/.test(search)) {
                        hintBox.innerHTML =
                            '<i class="fa-solid fa-phone text-success ms-1"></i> اضغط Enter للبحث بالرقم...';
                        hintBox.className = 'search-hint text-success';
                    } else {
                        hintBox.innerHTML =
                            '<i class="fa-solid fa-user text-info ms-1"></i> اضغط Enter للبحث بالاسم...';
                        hintBox.className = 'search-hint text-info';
                    }
                } else {
                    hintBox.style.display = 'none';
                }
            });
        });
    </script>
@endsection
