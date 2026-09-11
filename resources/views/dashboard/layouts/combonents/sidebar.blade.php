<nav id="sidebar">
    <div class="sidebar-header">
        <img width="50" height="70" src="{{ asset('images/mainlogo.png') }}" alt="Logo">
        <span class="sidebar-brand">ورتل - أدمن</span>
    </div>

    <ul class="sidebar-menu">
<li>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
        <i class="fa-solid fa-gauge"></i> الرئيسية
    </a>
</li>
<li>
    <a href="{{ route('tracks.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'tracks.') ? 'active' : '' }}">
        <i class="fa-solid fa-layer-group"></i> المسارات
    </a>
</li>
<li>
    <a href="{{ route('teachers.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'teachers.') ? 'active' : '' }}">
        <i class="fa-solid fa-chalkboard-user"></i> المعلمون
    </a>
</li>
<li>
    <a href="{{ route('packages.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'packages.') ? 'active' : '' }}">
        <i class="fa-solid fa-sack-dollar"></i> الباقات
    </a>
</li>
<li>
    <a href="{{ route('coupons.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'coupons.') ? 'active' : '' }}">
        <i class="fa-solid fa-tags"></i> الكوبونات
    </a>
</li>
<li>
    <a href="{{ route('ads.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'ads.') ? 'active' : '' }}">
        <i class="fa-solid fa-ad"></i> الإعلانات
    </a>
</li>
<li>
    <a href="{{ route('admin.students') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.students') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> الطلاب
    </a>
</li>
<li>
    <a href="{{ route('admin.recitations.create') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.sessions') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> المقرأه
    </a>
</li>
<li>
    <a href="{{ route('admin.subscriptions') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'admin.subscriptions') ? 'active' : '' }}">
        <i class="fa-solid fa-file-invoice-dollar"></i> الاشتراكات
    </a>
</li>
<li>
    <a href="{{ route('settings.freeMinutes') }}" class="sidebar-link {{ Route::currentRouteName() == 'settings.freeMinutes' ? 'active' : '' }}">
        <i class="fa-solid fa-gift"></i> إعدادات الهدايا
    </a>
</li>
<li>
    <a href="{{ route('profiles.index') }}" class="sidebar-link {{ Str::startsWith(Route::currentRouteName(), 'profiles.') ? 'active' : '' }}">
        <i class="fa-solid fa-tags"></i> الملف الشخصي
    </a>
</li>
        <li style="margin-top: 50px;">
            <a href="#" class="sidebar-link text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fa-solid fa-right-from-bracket"></i> تسجيل خروج
            </a>
        </li>
    </ul>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">

                <div class="mb-3 text-danger fs-1">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>

                <h5 class="fw-bold mb-2">تسجيل الخروج</h5>
                <p class="text-muted small">
                    هل أنت متأكد من رغبتك في تسجيل الخروج من لوحة التحكم؟
                </p>

                <div class="mt-4 d-flex justify-content-center gap-2">
                    <button
                        type="button"
                        class="btn btn-light btn-sm px-3"
                        data-bs-dismiss="modal">
                        إلغاء
                    </button>

                    {{-- Logout Form --}}
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            تأكيد الخروج
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

