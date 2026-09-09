@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        :root {
            --primary-color: #2d8a74;
            --primary-dark: #1b4d3e;
            --surface-color: #ffffff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        /* --- نظام التنبيهات (Responsive Toasts) --- */
        .fixed-alert-container { position: fixed; top: 25px; right: 25px; z-index: 10000; width: 100%; max-width: 350px; pointer-events: none; }
        .custom-toast {
            pointer-events: auto; background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px); border-radius: 14px; margin-bottom: 15px;
            overflow: hidden; position: relative; border-left: 5px solid transparent;
            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); direction: rtl;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .custom-toast.success { border-left-color: var(--primary-color); }
        .custom-toast.error { border-left-color: #ef4444; }
        .toast-content { padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
        .toast-icon { font-size: 1.6rem; }
        .success .toast-icon { color: var(--primary-color); }
        .error .toast-icon { color: #ef4444; }
        .toast-body { flex-grow: 1; text-align: right; }
        .toast-title { display: block; font-weight: 800; font-size: 0.95rem; color: var(--text-main); }
        .toast-message { margin: 0; font-size: 0.85rem; color: var(--text-muted); }
        .toast-close { background: none; border: none; font-size: 1.3rem; color: #94a3b8; cursor: pointer; order: -1; }
        .toast-progress { height: 3px; width: 100%; background: #f1f5f9; position: absolute; bottom: 0; right: 0; }
        .toast-progress::before { content: ""; position: absolute; bottom: 0; right: 0; height: 100%; width: 100%; }
        .success .toast-progress::before { background: var(--primary-color); animation: progressRun 5s linear forwards; }
        .error .toast-progress::before { background: #ef4444; animation: progressRun 5s linear forwards; }

        @keyframes progressRun { from { width: 100%; } to { width: 0%; } }
        @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* --- كروت الإحصائيات العلوية --- */
        .stat-card {
            background: #fff; border-radius: 20px; padding: 20px 25px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color);
            transition: all 0.3s ease; height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
        .stat-icon { width: 55px; height: 55px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

        /* --- الفلاتر وأدوات البحث المتجاوبة --- */
        .filter-section { background: transparent; padding: 0; margin-bottom: 25px; }
        .filter-scroll-wrapper { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .filter-scroll-wrapper::-webkit-scrollbar { display: none; }

        .filter-badge {
            padding: 8px 22px; border-radius: 50px; background: #fff; border: 1px solid var(--border-color);
            font-size: 0.9rem; font-weight: 700; color: var(--text-muted); cursor: pointer; transition: 0.3s; white-space: nowrap;
        }
        .filter-badge.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: 0 4px 15px rgba(45, 138, 116, 0.25); }
        .filter-badge:hover:not(.active) { border-color: var(--primary-color); color: var(--primary-color); }

        .search-box { position: relative; flex-grow: 1; min-width: 280px; max-width: 400px; }
        .search-input { border-radius: 50px; border: 1px solid var(--border-color); padding: 12px 20px 12px 45px; width: 100%; transition: all 0.3s; background: #fff; }
        .search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(45, 138, 116, 0.1); outline: none; }
        .search-icon { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* --- كروت الباقات (SaaS Pricing Cards) --- */
        .pkg-card {
            background: #fff; border-radius: 28px; border: 1px solid var(--border-color);
            display: flex; flex-direction: column; height: 100%; position: relative;
            /* تم إزالة overflow:hidden لكي لا يتم قص القائمة المنسدلة (Dropdown) */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            z-index: 1;
        }
        .pkg-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(45, 138, 116, 0.1); border-color: var(--primary-color); z-index: 5; }
        .pkg-card.inactive { opacity: 0.75; filter: grayscale(40%); }
        .pkg-card.inactive:hover { opacity: 1; filter: grayscale(0%); }

        .pkg-header {
            padding: 30px 25px 20px; text-align: center; background: linear-gradient(to bottom, var(--bg-light), #fff);
            border-bottom: 1px dashed var(--border-color); position: relative;
            border-radius: 28px 28px 0 0; /* تعويض الـ overflow hidden */
        }
        .pkg-title { font-weight: 800; font-size: 1.3rem; color: var(--text-main); margin-bottom: 15px; }
        .pkg-price-box { display: flex; align-items: baseline; justify-content: center; gap: 5px; color: var(--text-main); }
        .pkg-currency { font-size: 1.2rem; font-weight: 700; color: var(--text-muted); }
        .pkg-price { font-size: 3rem; font-weight: 900; line-height: 1; letter-spacing: -1px; }
        .pkg-old-price { text-decoration: line-through; color: #94a3b8; font-size: 1rem; font-weight: 600; margin-left: 8px; }

        /* شريط الخصم الاحترافي */
        .discount-ribbon {
            position: absolute; top: 25px; right: -35px; background: #f59e0b; color: #fff;
            padding: 6px 40px; font-weight: 800; font-size: 0.8rem; transform: rotate(45deg);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); z-index: 10; letter-spacing: 1px;
        }

        .pkg-body { padding: 25px; flex-grow: 1; }
        .pkg-desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; text-align: center; }

        .pkg-features { list-style: none; padding: 0; margin: 0; }
        .pkg-features li { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
        .pkg-features li i { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
        .feat-primary i { background: rgba(45, 138, 116, 0.1); color: var(--primary-color); }
        .feat-bonus i { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .feat-valid i { background: rgba(99, 102, 241, 0.1); color: #6366f1; }

        .pkg-footer {
            padding: 15px 25px; background: var(--bg-light); border-top: 1px solid var(--border-color);
            display: flex; justify-content: space-between; align-items: center;
            border-radius: 0 0 28px 28px;
        }

        .subscriber-badge { background: #fff; border: 1px solid var(--border-color); padding: 6px 14px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); box-shadow: 0 2px 4px rgba(0,0,0,0.02); }

        /* --- القائمة المنسدلة الاحترافية (Dropdown) --- */
        .pkg-actions .dropdown-toggle::after { display: none; }
        .pkg-actions .btn-dots { width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--border-color); color: var(--text-muted); transition: 0.2s; }
        .pkg-actions .btn-dots:hover { background: var(--bg-light); color: var(--primary-color); }

        .dropdown-menu-custom {
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-radius: 16px;
            padding: 8px;
            min-width: 180px;
            z-index: 1050; /* إصلاح مشكلة الاختفاء */
        }
        .dropdown-item-custom {
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        .dropdown-item-custom:hover { background-color: var(--bg-light); color: var(--primary-color); }
        .dropdown-item-custom.text-danger:hover { background-color: #fef2f2; color: #ef4444; }

        /* زر الإضافة */
        .add-pkg-btn {
            border: 2px dashed #cbd5e1; background: transparent; color: #94a3b8;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; min-height: 100%; transition: all 0.3s ease; text-align: center;
        }
        .add-pkg-btn:hover { border-color: var(--primary-color); background: rgba(45, 138, 116, 0.02); color: var(--primary-color); transform: translateY(-5px); }
        .add-icon-circle { width: 70px; height: 70px; border-radius: 50%; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 15px; color: inherit; transition: 0.3s; }
        .add-pkg-btn:hover .add-icon-circle { background: var(--primary-color); color: #fff; box-shadow: 0 8px 20px rgba(45, 138, 116, 0.3); }

        /* --- تصميم المودال وتنسيق الحقول --- */
        .custom-input { border: 1px solid var(--border-color); border-radius: 14px !important; padding: 12px 18px; font-size: 0.95rem; background-color: var(--bg-light); transition: all 0.3s ease; }
        .custom-input:focus { background-color: #fff; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(45, 138, 116, 0.1); }
        .form-label { font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 8px; }
        .modal-header-custom { background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%); border-bottom: 1px solid var(--border-color); padding: 25px; border-radius: 24px 24px 0 0; }

        .form-switch .form-check-input { width: 3em; height: 1.5em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: var(--primary-color); border-color: var(--primary-color); }

        /* --- Responsive Fixes --- */
        @media (max-width: 768px) {
            .filter-section { flex-direction: column; align-items: stretch !important; gap: 15px; }
            .search-box { width: 100%; max-width: none; }
            .fixed-alert-container { right: 10px; left: 10px; top: 10px; width: auto; max-width: none; }
            .stat-card { padding: 15px; }
            .stat-card h3 { font-size: 1.5rem; }
        }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            <h5 class="m-0 fw-bold fs-5 text-dark">إدارة الباقات التعليمية</h5>
        </div>
    </div>
@endsection

@section('content')

{{-- نظام التنبيهات المطور --}}
<div class="fixed-alert-container">
    {{-- رسائل النجاح --}}
    @if(session('success'))
        <div class="custom-toast success shadow-lg" role="alert">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body">
                    <span class="toast-title">تم بنجاح</span>
                    <p class="toast-message">{{ session('success') }}</p>
                </div>
                <div class="toast-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif

    {{-- رسائل الخطأ العادية (Flash Session) --}}
    @if(session('error'))
        <div class="custom-toast error shadow-lg" role="alert">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body">
                    <span class="toast-title">خطأ في العملية</span>
                    <p class="toast-message">{{ session('error') }}</p>
                </div>
                <div class="toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif

    {{-- رسائل أخطاء التحقق من البيانات (Validation Errors) --}}
    @if($errors->any())
        <div class="custom-toast error shadow-lg" role="alert">
            <div class="toast-content">
                <button type="button" class="toast-close" onclick="this.closest('.custom-toast').remove()">&times;</button>
                <div class="toast-body">
                    <span class="toast-title">خطأ في الإدخال</span>
                    <ul class="toast-message" style="list-style: none; padding: 0; margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="toast-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
            <div class="toast-progress"></div>
        </div>
    @endif
</div>
<div class="container-fluid p-3 p-md-4">

    {{-- الإحصائيات العلوية (Responsive Grid) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="border-bottom: 4px solid #6f42c1;">
                <div class="stat-info"><h3 class="fw-bold m-0 text-dark">{{ $totalPackages }}</h3><p class="text-muted small fw-bold mb-0 mt-1">إجمالي الباقات</p></div>
                <div class="stat-icon" style="background-color: #f3e8ff; color: #6f42c1;"><i class="fa-solid fa-layer-group"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="border-bottom: 4px solid #10b981;">
                <div class="stat-info"><h3 class="fw-bold m-0 text-dark">{{ $activePackages }}</h3><p class="text-muted small fw-bold mb-0 mt-1">باقات نشطة</p></div>
                <div class="stat-icon" style="background-color: #ecfdf5; color: #10b981;"><i class="fa-solid fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="border-bottom: 4px solid #ef4444;">
                <div class="stat-info"><h3 class="fw-bold m-0 text-dark">{{ $inactivePackages }}</h3><p class="text-muted small fw-bold mb-0 mt-1">باقات متوقفة</p></div>
                <div class="stat-icon" style="background-color: #fef2f2; color: #ef4444;"><i class="fa-solid fa-ban"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card" style="border-bottom: 4px solid #3b82f6;">
                <div class="stat-info"><h3 class="fw-bold m-0 text-dark">{{ number_format($totalSubscribers) }}</h3><p class="text-muted small fw-bold mb-0 mt-1">إجمالي المشتركين</p></div>
                <div class="stat-icon" style="background-color: #eff6ff; color: #3b82f6;"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
    </div>

    {{-- شريط الفلترة والبحث --}}
    <div class="filter-section d-flex justify-content-between align-items-center flex-wrap">
        <div class="filter-scroll-wrapper">
            <button class="filter-badge active" onclick="filterPackages('all', this)">عرض الكل</button>
            <button class="filter-badge" onclick="filterPackages('active', this)">الباقات النشطة</button>
            <button class="filter-badge" onclick="filterPackages('inactive', this)">المعطلة</button>
            <button class="filter-badge" onclick="filterPackages('discount', this)">العروض والخصومات 🔥</button>
        </div>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="pkgSearch" class="search-input" placeholder="ابحث عن باقة بالاسم..." onkeyup="searchPackages()">
        </div>
    </div>

    {{-- شبكة الباقات --}}
    <div class="row g-4" id="packagesGrid">

        {{-- كرت إضافة باقة جديدة --}}
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="pkg-card add-pkg-btn" data-bs-toggle="modal" data-bs-target="#createPackageModal">
                <div class="add-icon-circle"><i class="fa-solid fa-plus"></i></div>
                <h5 class="fw-bold text-dark m-0">إضافة باقة جديدة</h5>
                <span class="small text-muted mt-2">انقر هنا لإنشاء خطة تسعير</span>
            </div>
        </div>

        @foreach($packages as $package)
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 pkg-item"
             data-status="{{ $package->status }}"
             data-name="{{ strtolower($package->name) }}"
             data-discount="{{ $package->discount > 0 ? 'true' : 'false' }}">

            <div class="pkg-card {{ $package->status == 'inactive' ? 'inactive' : '' }}">
                @if($package->discount > 0)
                    <div class="discount-ribbon" style="overflow: hidden">خصم {{ $package->discount }}%</div>
                @endif

                <div class="pkg-header pb-3">
                    <h5 class="pkg-title {{ $package->discount > 0 ? 'text-warning' : '' }} mb-4">{{ $package->name }}</h5>
                    
                    <div class="d-flex justify-content-between text-center px-2">
                        {{-- Egypt --}}
                        <div class="d-flex flex-column flex-fill align-items-center">
                            <span class="text-muted mb-1 fw-bold" style="font-size: 0.75rem;">مصر</span>
                            @if($package->discount > 0 && $package->discount < 100)
                                @php $finalEgypt = $package->egypt_price * (1 - ($package->discount / 100)); @endphp
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($finalEgypt, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">{{ rtrim(rtrim(number_format($package->egypt_price, 2, '.', ''), '0'), '.') }} ج.م</del>
                            @elseif($package->discount >= 100)
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">0</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">مجاناً</del>
                            @else
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($package->egypt_price, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <div class="mt-1" style="height: 0.7rem;"></div> {{-- Spacer to align cards without discount --}}
                            @endif
                            <form action="{{ route('packages.update', $package->id) }}" method="POST" class="mt-2">
                                @csrf @method('PUT')
                                <input type="hidden" name="toggle_show_in_egypt" value="1">
                                <div class="form-check form-switch m-0 p-0" title="إظهار/إخفاء في مصر" style="min-height: auto; width: 30px;">
                                    <input class="form-check-input m-0 cursor-pointer" type="checkbox" name="show_in_egypt" value="1" onchange="this.form.submit()" {{ $package->show_in_egypt ? 'checked' : '' }} style="width: 2rem; height: 1rem;">
                                </div>
                            </form>
                        </div>
                        
                        <div class="border-end mx-1"></div>

                        {{-- Arab --}}
                        <div class="d-flex flex-column flex-fill align-items-center">
                            <span class="text-muted mb-1 fw-bold" style="font-size: 0.75rem;">عرب</span>
                            @if($package->discount > 0 && $package->discount < 100)
                                @php $finalArab = $package->arab_price * (1 - ($package->discount / 100)); @endphp
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($finalArab, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">{{ rtrim(rtrim(number_format($package->arab_price, 2, '.', ''), '0'), '.') }} ج.م</del>
                            @elseif($package->discount >= 100)
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">0</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">مجاناً</del>
                            @else
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($package->arab_price, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <div class="mt-1" style="height: 0.7rem;"></div>
                            @endif
                            <form action="{{ route('packages.update', $package->id) }}" method="POST" class="mt-2">
                                @csrf @method('PUT')
                                <input type="hidden" name="toggle_show_in_arab" value="1">
                                <div class="form-check form-switch m-0 p-0" title="إظهار/إخفاء في الدول العربية" style="min-height: auto; width: 30px;">
                                    <input class="form-check-input m-0 cursor-pointer" type="checkbox" name="show_in_arab" value="1" onchange="this.form.submit()" {{ $package->show_in_arab ? 'checked' : '' }} style="width: 2rem; height: 1rem;">
                                </div>
                            </form>
                        </div>

                        <div class="border-end mx-1"></div>

                        {{-- Foreign --}}
                        <div class="d-flex flex-column flex-fill align-items-center">
                            <span class="text-muted mb-1 fw-bold" style="font-size: 0.75rem;">أجانب</span>
                            @if($package->discount > 0 && $package->discount < 100)
                                @php $finalForeign = $package->foreign_price * (1 - ($package->discount / 100)); @endphp
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($finalForeign, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">{{ rtrim(rtrim(number_format($package->foreign_price, 2, '.', ''), '0'), '.') }} ج.م</del>
                            @elseif($package->discount >= 100)
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">0</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <del class="text-muted mt-1" style="font-size: 0.7rem;" dir="ltr">مجاناً</del>
                            @else
                                <div class="d-flex align-items-baseline justify-content-center" dir="ltr">
                                    <span class="fw-bold text-dark" style="font-size: 1.25rem;">{{ rtrim(rtrim(number_format($package->foreign_price, 2, '.', ''), '0'), '.') }}</span>
                                    <span class="text-muted ms-1" style="font-size: 0.7rem;">ج.م</span>
                                </div>
                                <div class="mt-1" style="height: 0.7rem;"></div>
                            @endif
                            <form action="{{ route('packages.update', $package->id) }}" method="POST" class="mt-2">
                                @csrf @method('PUT')
                                <input type="hidden" name="toggle_show_in_foreign" value="1">
                                <div class="form-check form-switch m-0 p-0" title="إظهار/إخفاء في الدول الأجنبية" style="min-height: auto; width: 30px;">
                                    <input class="form-check-input m-0 cursor-pointer" type="checkbox" name="show_in_foreign" value="1" onchange="this.form.submit()" {{ $package->show_in_foreign ? 'checked' : '' }} style="width: 2rem; height: 1rem;">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="pkg-body">
                    <p class="pkg-desc">{{ Str::limit($package->description, 60, '...') ?: 'لا يوجد وصف مضاف لهذه الباقة حالياً.' }}</p>

                    <ul class="pkg-features">
                        <li class="feat-primary"><i class="fa-solid fa-phone"></i> {{ $package->base_minutes }} دقيقة رصيد أساسي</li>
                        @if($package->bonus_minutes > 0)
                            <li class="feat-bonus"><i class="fa-solid fa-gift"></i> +{{ $package->bonus_minutes }} دقيقة إضافية مجاناً</li>
                        @endif
                        <li class="feat-valid"><i class="fa-solid fa-calendar-check"></i> صالحة لمدة {{ $package->validity_days }} يوم</li>
                    </ul>
                </div>

                <div class="pkg-footer">
                    <div class="subscriber-badge">
                        <i class="fa-solid fa-user-check me-1 text-primary"></i> {{ $package->subscribers_count }} مشترك
                    </div>

                    <div class="d-flex align-items-center gap-2 pkg-actions">
                        <form action="{{ route('packages.update', $package->id) }}" method="POST" class="m-0">
                            @csrf @method('PUT')
                            <input type="hidden" name="name" value="{{ $package->name }}">
                            <input type="hidden" name="base_minutes" value="{{ $package->base_minutes }}">
                            <input type="hidden" name="validity_days" value="{{ $package->validity_days }}">
                            <input type="hidden" name="status" value="{{ $package->status == 'active' ? 'inactive' : 'active' }}">

                            <div class="form-check form-switch m-0 p-0" title="تغيير حالة الباقة">
                                <input class="form-check-input m-0" type="checkbox" onchange="this.form.submit()" {{ $package->status == 'active' ? 'checked' : '' }}>
                            </div>
                        </form>

                        <div class="dropdown">
                            <button class="btn-dots dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-custom dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editPackageModal{{ $package->id }}">
                                        <i class="fa-solid fa-pen-to-square me-2 text-primary" style="width: 15px;"></i>تعديل البيانات
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-custom text-danger" data-bs-toggle="modal" data-bs-target="#deletePackageModal{{ $package->id }}">
                                        <i class="fa-solid fa-trash-can me-2" style="width: 15px;"></i>حذف الباقة
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- موديول التعديل الاحترافي لكل باقة --}}
        <div class="modal fade" id="editPackageModal{{ $package->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <form action="{{ route('packages.update', $package->id) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="is_full_update" value="1">
                        <div class="modal-header-custom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;"><i class="fa-solid fa-pen-to-square fs-5"></i></div>
                                <div><h5 class="fw-bold m-0 text-dark">تعديل: {{ $package->name }}</h5><small class="text-muted">تحديث خصائص وتسعير الباقة</small></div>
                            </div>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4 p-md-5">
                            <div class="row g-4">
                                <div class="col-12 col-md-12">
                                    <label class="form-label"><i class="fa-solid fa-tag text-muted me-1"></i> اسم الباقة</label>
                                    <input type="text" name="name" class="form-control custom-input" value="{{ $package->name }}" >
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (مصر)</label>
                                    <input type="number" name="egypt_price" class="form-control custom-input text-success fw-bold" value="{{ $package->egypt_price }}" step="0.01" min="0" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (عرب)</label>
                                    <input type="number" name="arab_price" class="form-control custom-input text-success fw-bold" value="{{ $package->arab_price }}" step="0.01" min="0" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (أجانب)</label>
                                    <input type="number" name="foreign_price" class="form-control custom-input text-success fw-bold" value="{{ $package->foreign_price }}" step="0.01" min="0" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label"><i class="fa-solid fa-percent text-muted me-1"></i> الخصم (%)</label>
                                    <input type="number" name="discount" class="form-control custom-input text-warning fw-bold" value="{{ $package->discount }}" min="0" max="100">
                                </div>

                                <div class="col-12"><hr class="border-light my-2"></div>

                                <div class="col-12">
                                    <label class="form-label"><i class="fa-solid fa-earth-americas text-muted me-1"></i> ظهور الباقة حسب المنطقة</label>
                                    <div class="d-flex gap-4 mt-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_in_egypt" value="1" id="edit_show_in_egypt_{{ $package->id }}" {{ $package->show_in_egypt ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_show_in_egypt_{{ $package->id }}">مصر</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_in_arab" value="1" id="edit_show_in_arab_{{ $package->id }}" {{ $package->show_in_arab ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_show_in_arab_{{ $package->id }}">الدول العربية</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="show_in_foreign" value="1" id="edit_show_in_foreign_{{ $package->id }}" {{ $package->show_in_foreign ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="edit_show_in_foreign_{{ $package->id }}">الدول الأجنبية</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="border-light my-2"></div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label"><i class="fa-solid fa-phone text-muted me-1"></i> الدقائق الأساسية</label>
                                    <input type="number" name="base_minutes" class="form-control custom-input" value="{{ $package->base_minutes }}" >
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label"><i class="fa-solid fa-gift text-muted me-1"></i> دقائق الهدية</label>
                                    <input type="number" name="bonus_minutes" class="form-control custom-input" value="{{ $package->bonus_minutes }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label"><i class="fa-regular fa-calendar-check text-muted me-1"></i> الصلاحية (بالأيام)</label>
                                    <input type="number" name="validity_days" class="form-control custom-input" value="{{ $package->validity_days }}" >
                                </div>

                                <div class="col-12">
                                    <label class="form-label"><i class="fa-solid fa-align-left text-muted me-1"></i> وصف الباقة (اختياري)</label>
                                    <textarea name="description" class="form-control custom-input" rows="3" placeholder="اكتب وصفاً جذاباً يشرح مميزات الباقة...">{{ $package->description }}</textarea>
                                </div>
                                <input type="hidden" name="status" value="{{ $package->status }}">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0 bg-white">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">حفظ التعديلات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- موديول الحذف الاحترافي --}}
        <div class="modal fade" id="deletePackageModal{{ $package->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 p-4 pb-0">
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 text-danger opacity-75">
                            <i class="fa-solid fa-circle-exclamation fa-4x"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">تأكيد الحذف؟</h5>
                        <p class="text-muted small">هل أنت متأكد أنك تريد حذف باقة <strong>{{ $package->name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء وسيؤثر على المشتركين الحاليين.</p>

                        <div class="mt-4">
                            <form action="{{ route('packages.destroy', $package->id) }}" method="POST" class="m-0 w-100">
                                @csrf @method('DELETE')
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold shadow-sm py-2">نعم، احذف الباقة</button>
                                    <button type="button" class="btn btn-light rounded-pill w-100 fw-bold border py-2" data-bs-dismiss="modal">تراجع وإغلاق</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endforeach
    </div>
</div>

{{-- موديول إنشاء باقة جديدة الاحترافي --}}
<div class="modal fade" id="createPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('packages.store') }}" method="POST">
                @csrf
                <div class="modal-header-custom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;"><i class="fa-solid fa-plus fs-5"></i></div>
                        <div><h5 class="fw-bold m-0 text-dark">بناء باقة جديدة</h5><small class="text-muted">قم بإعداد خطة تسعير جديدة للمشتركين</small></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12 col-md-12">
                            <label class="form-label"><i class="fa-solid fa-tag text-muted me-1"></i> اسم الباقة <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control custom-input" placeholder="مثال: باقة التميز" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (مصر) <span class="text-danger">*</span></label>
                            <input type="number" name="egypt_price" class="form-control custom-input fw-bold" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (عرب) <span class="text-danger">*</span></label>
                            <input type="number" name="arab_price" class="form-control custom-input fw-bold" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="fa-solid fa-dollar-sign text-muted me-1"></i> السعر (أجانب) <span class="text-danger">*</span></label>
                            <input type="number" name="foreign_price" class="form-control custom-input fw-bold" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="fa-solid fa-percent text-muted me-1"></i> خصم تسويقي</label>
                            <input type="number" name="discount" class="form-control custom-input" value="0" min="0" max="100">
                        </div>

                        <div class="col-12"><hr class="border-light my-2"></div>

                        <div class="col-12">
                            <label class="form-label"><i class="fa-solid fa-earth-americas text-muted me-1"></i> ظهور الباقة حسب المنطقة</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_in_egypt" value="1" id="create_show_in_egypt" checked>
                                    <label class="form-check-label fw-bold" for="create_show_in_egypt">مصر</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_in_arab" value="1" id="create_show_in_arab" checked>
                                    <label class="form-check-label fw-bold" for="create_show_in_arab">الدول العربية</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_in_foreign" value="1" id="create_show_in_foreign" checked>
                                    <label class="form-check-label fw-bold" for="create_show_in_foreign">الدول الأجنبية</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12"><hr class="border-light my-2"></div>

                        <div class="col-6 col-md-4">
                            <label class="form-label"><i class="fa-solid fa-phone text-muted me-1"></i> الدقائق <span class="text-danger">*</span></label>
                            <input type="number" name="base_minutes" class="form-control custom-input" placeholder="100" >
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label"><i class="fa-solid fa-gift text-muted me-1"></i> بونص (هدية)</label>
                            <input type="number" name="bonus_minutes" class="form-control custom-input" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="fa-regular fa-calendar-check text-muted me-1"></i> الصلاحية <span class="text-danger">*</span></label>
                            <input type="number" name="validity_days" class="form-control custom-input" value="30" >
                        </div>

                        <div class="col-12">
                            <label class="form-label"><i class="fa-solid fa-align-left text-muted me-1"></i> وصف الباقة</label>
                            <textarea name="description" class="form-control custom-input" rows="3" placeholder="اشرح للمستخدمين لماذا يجب عليهم اختيار هذه الباقة..."></textarea>
                        </div>
                        <input type="hidden" name="status" value="active">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">تأكيد ونشر الباقة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- بحث ذكي ---
    function searchPackages() {
        let input = document.getElementById('pkgSearch').value.toLowerCase();
        let items = document.querySelectorAll('.pkg-item');
        items.forEach(item => {
            let name = item.getAttribute('data-name');
            item.style.display = name.includes(input) ? "block" : "none";
        });
    }

    // --- فلترة الأزرار ---
    function filterPackages(type, btn) {
        document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        let items = document.querySelectorAll('.pkg-item');
        items.forEach(item => {
            if (type === 'all') item.style.display = "block";
            else if (type === 'active') item.style.display = (item.getAttribute('data-status') === 'active') ? "block" : "none";
            else if (type === 'inactive') item.style.display = (item.getAttribute('data-status') === 'inactive') ? "block" : "none";
            else if (type === 'discount') item.style.display = (item.getAttribute('data-discount') === 'true') ? "block" : "none";
        });
    }

    // --- إخفاء التنبيهات تلقائياً ---
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(toast => {
                toast.style.animation = "slideOutRight 0.5s ease-in forwards";
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);
    });
</script>
@endsection
