<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - ورتل</title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/png">

    {{-- Bootstrap & Fonts --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    {{-- Custom Styles --}}
    {{-- <link rel="stylesheet" href="{{ asset('dashboard/css/loader.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('dashboard/css/sidebar.css') }}"> --}}

    <style>
        :root {
            /* --- Brand Colors --- */
            --primary-dark: #2d8a74;
            --primary-light: #e8f5e9;
            --gold-main: #c49a46;
            --gold-light: #fff8e1;
            --bg-body: #f3f4f6;
            --text-main: #2d3436;

            /* --- Layout Dimensions --- */
            --sidebar-width: 280px;
            --navbar-height: 70px;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }



        /* =========================================
           1. LAYOUT STRUCTURE
           ========================================= */
        #wrapper {
            display: flex;
            width: 100%;
            overflow-x: hidden;
        }

        /* Sidebar Container */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            background: #ffffff;
            z-index: 1000;
            transition: all 0.3s ease-in-out;
            border-left: 1px solid #f0f0f0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.02);
        }

        /* Main Content Wrapper */
        #content-wrapper {
            width: 100%;
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease-in-out;
        }

        /* =========================================
           2. SIDEBAR STYLING (FIXED COLORS)
           ========================================= */
        .sidebar-header {
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-bottom: 1px solid #f8f9fa;
        }

        .sidebar-brand {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-right: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 15px;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #636e72;
            /* Neutral text color */
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .sidebar-link i {
            width: 25px;
            font-size: 1.1rem;
            margin-left: 10px;
            text-align: center;
            color: #b2bec3;
            /* Neutral Icon color */
            transition: 0.3s;
        }

        /* Hover State */
        .sidebar-link:hover {
            background-color: #f0fdf4;
            /* Very light green */
            color: var(--primary-dark);
            transform: translateX(-3px);
        }

        .sidebar-link:hover i {
            color: var(--gold-main);
        }

        /* Active State */
        .sidebar-link.active {
            background: var(--primary-dark);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(45, 138, 116, 0.3);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        /* Logout Link Specifics */
        .sidebar-link.text-danger:hover {
            background-color: #fff5f5;
            color: #dc3545 !important;
        }

        .sidebar-link.text-danger:hover i {
            color: #dc3545;
        }

        /* Scrollbar for sidebar */
        #sidebar::-webkit-scrollbar {
            width: 5px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #eee;
            border-radius: 10px;
        }

        /* =========================================
           3. TOP NAVBAR
           ========================================= */
        .top-navbar {
            height: var(--navbar-height);
            background: white;
            padding: 0 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .menu-toggle-btn {
            background: transparent;
            border: none;
            font-size: 1.4rem;
            color: var(--primary-dark);
            cursor: pointer;
            display: none;
            padding: 5px;
        }

        /* =========================================
           4. DROPDOWNS & NOTIFICATIONS
           ========================================= */
        .notification-menu {
            width: 280px;
            padding: 0;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .notif-header {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--primary-dark);
            background: #fcfcfc;
            border-radius: 12px 12px 0 0;
        }

        .notif-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f9f9f9;
            display: flex;
            align-items: start;
            gap: 12px;
            transition: 0.2s;
        }

        .notif-item:hover {
            background: #f8f9fa;
        }

        .notif-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e8f5e9;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .notif-content p {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .notif-content span {
            font-size: 0.7rem;
            color: #999;
        }

        /* =========================================
           5. RESPONSIVE LOGIC
           ========================================= */
        @media (max-width: 992px) {
            #sidebar {
                right: calc(var(--sidebar-width) * -1);
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            }

            #content-wrapper {
                margin-right: 0;
                width: 100%;
            }

            .menu-toggle-btn {
                display: block;
                margin-left: 15px;
            }

            body.sidebar-active #sidebar {
                right: 0;
            }

            body.sidebar-active {
                overflow: hidden;
            }

            /* Overlay */
            #overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 950;
                display: none;
                opacity: 0;
                transition: opacity 0.3s;
                backdrop-filter: blur(2px);
            }

            body.sidebar-active #overlay {
                display: block;
                opacity: 1;
            }

            .top-navbar {
                padding: 0 15px;
            }
        }
    </style>

    @yield('styles')
</head>

<body class="loader-locked">

    {{-- 1. LOADER --}}
    {{-- @include('dashboard.layouts.combonents.loader') --}}

    {{-- 2. OVERLAY FOR MOBILE --}}
    <div id="overlay"></div>

    <div id="wrapper">

        {{-- 3. SIDEBAR --}}
        <nav id="sidebar">
            <div class="sidebar-header">
                <img width="50" height="50" src="{{ asset('images/mainlogo.png') }}" alt="Logo">
                <span class="sidebar-brand">ورتل - أدمن</span>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge"></i> الرئيسية
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.students') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.students') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> الطلاب
                    </a>
                </li>
                <li>
                    <a href="{{ route('teachers.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'teachers.') ? 'active' : '' }}">
                        <i class="fa-solid fa-chalkboard-user"></i> المعلمون
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher-categories.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'teacher-categories.') ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group"></i> فئات المعلمين
                    </a>
                </li>
                <li>
                    <a href="{{ route('tracks.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'tracks.') ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group"></i> المسارات
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.recitations.create') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.recitations.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-video"></i> المقرأه
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.bookings.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.bookings.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-check"></i> الحجوزات
                    </a>
                </li>
                <li>
                    <a href="{{ route('calls.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'calls.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-phone"></i> المكالمات
                    </a>
                </li>
                <li>
                    <a href="{{ route('packages.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'packages.') ? 'active' : '' }}">
                        <i class="fa-solid fa-sack-dollar"></i> الباقات
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.subscriptions') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.subscriptions') ? 'active' : '' }}">
                        <i class="fa-solid fa-id-card"></i> الاشتراكات
                    </a>
                </li>
                <li>
                    <a href="{{ route('coupons.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'coupons.') ? 'active' : '' }}">
                        <i class="fa-solid fa-tags"></i> الكوبونات
                    </a>
                </li>
                <li>
                    <a href="{{ route('ads.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'ads.') ? 'active' : '' }}">
                        <i class="fa-solid fa-ad"></i> الإعلانات
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.withdrawals.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.withdrawals') ? 'active' : '' }}">
                        <i class="fa-solid fa-money-bill-transfer"></i> السحب
                    </a>
                </li>
                <li>
                    <a href="{{ route('countries.index') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'countries.') ? 'active' : '' }}">
                        <i class="fa-solid fa-globe"></i> الدول
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile') }}"
                        class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'profile') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-gear"></i> الملف الشخصي
                    </a>
                </li>
                {{-- Spacer --}}
                <li style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
                    <a href="#" class="sidebar-link text-danger" data-bs-toggle="modal"
                        data-bs-target="#logoutModal">
                        <i class="fa-solid fa-right-from-bracket"></i> تسجيل خروج
                    </a>
                </li>
            </ul>
        </nav>

        {{-- 4. CONTENT AREA --}}
        <div id="content-wrapper">

            {{-- Top Navbar --}}
            <nav class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="menu-toggle-btn ms-2" aria-label="Toggle Menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="page-title-container">
                        @yield('title')
                    </div>
                </div>

                {{-- Profile & Notifications --}}
                <div class="d-flex align-items-center gap-3">

                    {{-- Notifications --}}
@php
    // جلب عدد الإشعارات غير المقروءة وأحدث 5 إشعارات
    $unreadCount = auth()->user()->unreadNotifications->count();
    $recentNotifications = auth()->user()->unreadNotifications()->take(5)->get();
@endphp

<div class="dropdown">
    <div class="position-relative p-1" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
        <i class="fa-regular fa-bell fs-5 text-secondary"></i>

        {{-- إظهار العداد فقط إذا كان هناك إشعارات غير مقروءة --}}
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>

    <ul class="dropdown-menu dropdown-menu-end notification-menu shadow-lg" style="width: 320px; max-height: 450px; overflow-y: auto;">
        <li class="notif-header px-3 py-2 fw-bold border-bottom d-flex justify-content-between align-items-center">
            <span>الإشعارات</span>
            @if($unreadCount > 0)
                <span class="badge bg-primary rounded-pill">{{ $unreadCount }} جديد</span>
            @endif
        </li>

        {{-- عرض أحدث الإشعارات ديناميكياً --}}
        @forelse($recentNotifications as $notification)
            @php
                $data = $notification->data;
                $icon = 'fa-bell';

                // تغيير الأيقونة حسب نوع الإشعار
                if(isset($data['type'])) {
                    if($data['type'] == 'new_student') $icon = 'fa-user-graduate';
                    if($data['type'] == 'new_order') $icon = 'fa-sack-dollar';
                    if($data['type'] == 'new_teacher_application') $icon = 'fa-chalkboard-user';
                    if($data['type'] == 'new_withdrawal') $icon = 'fa-money-bill-transfer';
                }
            @endphp
            <li>
                {{-- توجيه الإشعار لصفحة الإشعارات --}}
                <a class="dropdown-item notif-item d-flex gap-3 align-items-start py-3 border-bottom text-wrap" href="{{ route('admin.notifications.index') }}">
                    <div class="notif-icon text-primary mt-1"><i class="fa-solid {{ $icon }} fs-5"></i></div>
                    <div class="notif-content flex-grow-1">
                        <p class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">{{ $data['title'] ?? 'إشعار جديد' }}</p>
                        <span class="text-muted d-block" style="font-size: 0.75rem;">
                            <i class="fa-regular fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                </a>
            </li>
        @empty
            <li>
                <div class="dropdown-item text-center text-muted py-4 small">
                    <i class="fa-regular fa-bell-slash fs-4 mb-2 d-block opacity-50"></i>
                    لا توجد إشعارات جديدة
                </div>
            </li>
        @endforelse

        {{-- زر عرض الكل يوجه للصفحة التي برمجناها --}}
        <li class="position-sticky bottom-0 bg-white">
            <a class="dropdown-item text-center p-3 small text-primary fw-bold" href="{{ route('admin.notifications.index') }}" style="background-color: #f8fafc;">
                عرض كل الإشعارات <i class="fa-solid fa-arrow-left ms-1"></i>
            </a>
        </li>
    </ul>
</div>

                    {{-- Admin Info --}}
                    <div class="d-flex align-items-center">
                        <div class="admin-details text-end d-none d-md-block ms-2">
                            <span
                                class="d-block fw-bold small text-dark">{{ Auth::user()->name ?? 'المدير العام' }}</span>
                            <span class="d-block small text-muted"
                                style="font-size: 0.7rem;">{{ Auth::user()->email ?? 'admin@wartel.com' }}</span>
                        </div>
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=2d8a74&color=fff"
                            class="rounded-circle border" width="40" height="40" alt="Admin">
                    </div>
                </div>
            </nav>

            {{-- Main Content --}}
            <main class="content-body">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- LOGOUT MODAL --}}
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3 text-danger fs-1"><i class="fa-solid fa-right-from-bracket"></i></div>
                    <h5 class="fw-bold mb-2">تسجيل الخروج</h5>
                    <p class="text-muted small">هل أنت متأكد من رغبتك في تسجيل الخروج؟</p>
                    <div class="mt-4 d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-sm px-3"
                            data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm px-3">تأكيد الخروج</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @yield('modals')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // Loader
        // window.addEventListener('load', () => {
        //     const loader = document.getElementById('loader-screen');
        //     if(loader) {
        //         setTimeout(() => {
        //             loader.classList.add('slide-up-exit');
        //             document.body.classList.remove('loader-locked');
        //         }, 1000);
        //     }
        // });

        // Sidebar Logic
        const toggleBtn = document.querySelector('.menu-toggle-btn');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                body.classList.add('sidebar-active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                body.classList.remove('sidebar-active');
            });
        }

        // Auto-close on mobile link click
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) body.classList.remove('sidebar-active');
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) body.classList.remove('sidebar-active');
        });
    </script>

    @yield('scripts')
</body>
</html>
