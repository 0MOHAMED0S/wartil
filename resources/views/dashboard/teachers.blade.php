@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/teachers.css') }}">
    <style>
        /* --- Stats Cards --- */
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); border: 1px solid transparent; transition: 0.3s; display: flex; align-items: center; justify-content: space-between; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .stat-purple { border-left: 4px solid #6f42c1; } .stat-purple .stat-icon-box { background: #f3e8ff; color: #6f42c1; }
        .stat-orange { border-left: 4px solid #fd7e14; } .stat-orange .stat-icon-box { background: #fff4e6; color: #fd7e14; }
        .stat-green { border-left: 4px solid #198754; } .stat-green .stat-icon-box { background: #d1e7dd; color: #198754; }
        .stat-red { border-left: 4px solid #dc3545; } .stat-red .stat-icon-box { background: #f8d7da; color: #dc3545; }

        /* --- Filter Buttons --- */
        .filter-btn { border: 1px solid #eee; background: white; color: #666; padding: 8px 16px; border-radius: 30px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; margin-left: 5px; white-space: nowrap; }
        .filter-btn:hover, .filter-btn.active { background: var(--primary-dark); color: white; border-color: var(--primary-dark); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        /* Mobile Scrollable Filters */
        #filterButtons {
            overflow-x: auto;
            padding-bottom: 5px;
            -webkit-overflow-scrolling: touch;
        }
        #filterButtons::-webkit-scrollbar { height: 4px; }
        #filterButtons::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

        /* --- Details & Modal --- */
        .info-section-title { font-size: 0.9rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 15px; border-bottom: 2px solid var(--gold-main); padding-bottom: 8px; display: inline-block; }
        .detail-item { margin-bottom: 12px; }
        .detail-label { font-size: 0.75rem; color: #999; display: block; margin-bottom: 3px; }
        .detail-val { font-weight: 600; color: #333; font-size: 0.95rem; word-break: break-word; }
        .tag-badge { background: #f8f9fa; border: 1px solid #eee; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; color: #555; display: inline-block; margin-left: 5px; margin-bottom: 5px; }
        .track-badge { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; display: inline-block; margin-left: 5px; margin-bottom: 5px; }

        /* Profile Image */
        .profile-img-container { position: relative; width: 120px; height: 120px; margin: 0 auto 15px; }
        .profile-img-main { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid var(--gold-main); padding: 3px; background: white; }
        .upload-btn-wrapper { position: absolute; bottom: 5px; right: 5px; }
        .btn-upload-icon { width: 32px; height: 32px; border-radius: 50%; background: var(--primary-dark); color: white; display: flex; align-items: center; justify-content: center; border: 2px solid white; cursor: pointer; transition: 0.3s; }
        .btn-upload-icon:hover { background: var(--gold-main); transform: scale(1.1); }

        /* Inputs & Read Only Boxes */
        .admin-input-box { background: #f8f9fa; padding: 15px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 15px; }
        .admin-info-box { background: #f0fdf4; padding: 15px; border-radius: 12px; border: 1px solid #bbf7d0; margin-bottom: 15px; text-align: center; }
        .form-label { font-size: 0.8rem; font-weight: 700; color: #666; margin-bottom: 5px; }

        /* Responsive Modal Sidebar */
        .modal-sidebar-col { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        @media (min-width: 992px) {
            .modal-sidebar-col { border-bottom: none; border-left: 1px solid #eee; padding-bottom: 0; margin-bottom: 0; height: 100%; overflow-y: auto; }
        }

        /* Header Switch */
        .registration-control { background: #fff; padding: 10px 20px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; border: 1px solid #eee; }
        .registration-control .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; margin: 0; }
        .registration-control .form-check-input:checked { background-color: #198754; border-color: #198754; }
        .status-text { font-weight: 700; font-size: 0.85rem; transition: 0.3s; }
        .text-open { color: #198754; } .text-closed { color: #dc3545; }

        /* Professional Table Styles */
        .table-responsive { display: block; width: 100%; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .professional-table { width: 100%; min-width: 1200px; border-collapse: separate; border-spacing: 0; background: #fff; }
        .professional-table thead th { position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 2px solid #e2e8f0; }
        .professional-table th { font-weight: 700; color: #1e293b; font-size: 0.85rem; padding: 16px 12px; white-space: nowrap; text-align: center; border-left: 1px solid #f1f5f9; }
        .professional-table th:last-child { border-left: none; }
        .professional-table th i { color: #64748b; margin-right: 6px; font-size: 0.9rem; }
        .professional-table td { padding: 12px 12px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; color: #334155; text-align: center; border-left: 1px solid #f1f5f9; }
        .professional-table td:last-child { border-left: none; }
        .professional-table tr:hover td { background-color: #f8fafc; }
        
        .teacher-cell { display: flex; align-items: center; justify-content: flex-start; gap: 12px; }
        .teacher-cell img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .teacher-name { font-weight: 700; color: #1e293b; font-size: 0.9rem; text-align: right; }
        
        .dotted-list { list-style: none; padding: 0; margin: 0; text-align: right; }
        .dotted-list li { position: relative; padding-right: 12px; margin-bottom: 4px; font-size: 0.8rem; font-weight: 600; color: #475569; }
        .dotted-list li::before { content: '•'; position: absolute; right: 0; top: 0; color: #0d9488; font-size: 1.2rem; line-height: 1; }
        
        /* Category Dropdown styling */
        .cat-1 { color: #8b5cf6 !important; border-color: #8b5cf6 !important; background-color: #f3e8ff !important; } /* Purple */
        .cat-2 { color: #3b82f6 !important; border-color: #3b82f6 !important; background-color: #dbeafe !important; } /* Blue */
        .cat-3 { color: #10b981 !important; border-color: #10b981 !important; background-color: #d1fae5 !important; } /* Green */
        .cat-4 { color: #f59e0b !important; border-color: #f59e0b !important; background-color: #fef3c7 !important; } /* Orange */
        select.form-select.cat-1:focus, select.form-select.cat-2:focus, select.form-select.cat-3:focus, select.form-select.cat-4:focus { box-shadow: none; }
        
        /* Actions Styling */
        .action-column { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .action-btns-vertical { display: flex; flex-direction: column; gap: 6px; }
        .btn-mockup { display: flex; align-items: center; justify-content: space-between; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; color: #fff; border: none; cursor: pointer; transition: 0.2s; width: 80px; }
        .btn-mockup-approve { background-color: #10b981; }
        .btn-mockup-approve:hover { background-color: #059669; }
        .btn-mockup-reject { background-color: #ef4444; }
        .btn-mockup-reject:hover { background-color: #dc2626; }
        .btn-mockup i { font-size: 0.85rem; margin-right: 6px; }
        
        .badge-soft { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; white-space: nowrap; display: inline-flex; align-items: center; gap: 5px; }
        .badge-soft-primary { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-soft-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-soft-warning { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .badge-soft-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-soft-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        
        .action-btns { display: flex; gap: 8px; align-items: center; }
        .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; border: none; transition: 0.2s; text-decoration: none; cursor: pointer; }
        .btn-action-view { background: #f1f5f9; color: #475569; padding: 6px 8px; }
        .btn-action-view:hover { background: #e2e8f0; color: #0f172a; }
        .btn-action-approve { background: #10b981; color: #fff; box-shadow: 0 2px 5px rgba(16, 185, 129, 0.2); }
        .btn-action-approve:hover { background: #059669; transform: translateY(-1px); }
        .btn-action-reject { background: #ef4444; color: #fff; box-shadow: 0 2px 5px rgba(239, 68, 68, 0.2); }
        .btn-action-reject:hover { background: #dc2626; transform: translateY(-1px); }

        @media (max-width: 768px) {
            .registration-control { margin-top: 15px; width: 100%; justify-content: space-between; }
            #searchInput { width: 100%; margin-top: 10px; }
        }

        /* Pagination Styles */
        .pagination-wrapper { margin-top: 20px; display: flex; justify-content: center; }

        /* --- Smart Search Styles --- */
        .search-container { position: relative; }
        .search-icon-wrapper { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); z-index: 10; color: #94a3b8; background: none; border: none; outline: none; padding: 0; transition: 0.3s; }
        .search-icon-wrapper:hover { color: var(--primary-dark); cursor: pointer; }
        .search-input { padding-right: 40px !important; border-radius: 20px; transition: 0.3s; border: 1px solid #e2e8f0; }
        .search-input:focus { box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); border-color: var(--primary-dark); }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100 gap-2">
        <div>
            <h5 class="m-0 fw-bold fs-5">إدارة طلبات التسجيل</h5>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid p-3 p-md-4">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation fs-4 me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Stats Grid --}}
        <div class="row g-3 mb-4" id="stats-wrapper">
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-purple p-3 p-md-4">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">إجمالي المعلمين</h6>
                        <h3 class="fw-bold m-0 text-dark">{{ $teachers->total() ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-orange p-3 p-md-4">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">قيد المراجعة (الكل)</h6>
                        <h3 class="fw-bold m-0 text-dark">{{ $pendingCount ?? \App\Models\Teacher_application::where('status', 'pending')->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-clock"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-green p-3 p-md-4">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">المعلمين المقبولين (الكل)</h6>
                        <h3 class="fw-bold m-0 text-dark">{{ $approvedCount ?? \App\Models\Teacher_application::where('status', 'approved')->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card stat-red p-3 p-md-4">
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">مرفوض/غير مفعل (الكل)</h6>
                        <h3 class="fw-bold m-0 text-dark">{{ $rejectedCount ?? \App\Models\Teacher_application::whereIn('status', ['rejected', 'not_active'])->count() }}</h3>
                    </div>
                    <div class="stat-icon-box"><i class="fa-solid fa-ban"></i></div>
                </div>
            </div>
        </div>

        {{-- Filter Form --}}
        <form action="{{ url()->current() }}" method="GET" id="searchFilterForm" class="d-flex flex-column flex-xl-row justify-content-between align-items-stretch align-items-xl-center mb-4 gap-3 bg-white p-2 p-md-3 rounded-4 shadow-sm border">
            <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">
            <div class="d-flex flex-wrap flex-grow-1 gap-2" id="filterButtons">
                <button type="button" class="filter-btn {{ request('status', 'all') == 'all' ? 'active' : '' }}" onclick="submitFilter('all')">الكل</button>
                <button type="button" class="filter-btn {{ request('status') == 'pending' ? 'active' : '' }}" onclick="submitFilter('pending')">قيد المراجعة</button>
                <button type="button" class="filter-btn {{ request('status') == 'approved' ? 'active' : '' }}" onclick="submitFilter('approved')">مقبول</button>
                <button type="button" class="filter-btn {{ request('status') == 'not_active' ? 'active' : '' }}" onclick="submitFilter('not_active')">غير مفعل</button>
                <button type="button" class="filter-btn {{ request('status') == 'rejected' ? 'active' : '' }}" onclick="submitFilter('rejected')">مرفوض</button>
            </div>
            <div class="search-container" style="min-width: 300px; width: 100%;">
                <button type="submit" class="search-icon-wrapper"><i class="fa-solid fa-magnifying-glass"></i></button>
                <input type="text" name="search" class="form-control search-input w-100" placeholder="بحث..." value="{{ request('search') }}">
            </div>
        </form>

        {{-- Control Row: Registration Status & Export --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 mb-3 mt-3">
            {{-- Switch --}}
            <div class="registration-control d-flex align-items-center justify-content-center justify-content-sm-start bg-white border rounded-pill px-3 py-2 shadow-sm gap-2" style="font-size: 0.9rem; width: fit-content;">
                <span class="text-muted fw-bold">حالة التسجيل:</span>
                <form action="{{ route('settings.toggleRegistration') }}" method="POST" class="m-0 d-flex align-items-center">
                    @csrf
                    <div class="form-check form-switch m-0 d-flex align-items-center gap-1">
                        @php
                            $setting = \App\Models\Setting::first();
                            $isOpen = optional($setting)->teacher_application_status === 'open';
                        @endphp
                        <input class="form-check-input" type="checkbox" name="teacher_application_status" id="registrationToggle" onchange="this.form.submit()" {{ $isOpen ? 'checked' : '' }}>
                        <label class="status-text mb-0 {{ $isOpen ? 'text-success' : 'text-danger' }} fw-bold" for="registrationToggle" style="cursor: pointer; padding-right: 5px;">
                            {{ $isOpen ? 'مفتوح' : 'مغلق' }}
                        </label>
                    </div>
                </form>
            </div>

            {{-- أزرار التصدير --}}
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('admin.teachers.export', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold flex-grow-1 flex-sm-grow-0 text-center shadow-sm d-flex align-items-center justify-content-center gap-1">
                    <i class="fa-solid fa-file-csv"></i> تصدير (CSV)
                </a>
                <a href="{{ route('admin.teachers.export.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm fw-bold flex-grow-1 flex-sm-grow-0 text-center shadow-sm d-flex align-items-center justify-content-center gap-1">
                    <i class="fa-solid fa-file-pdf"></i> تصدير (PDF)
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="card table-card border-0 shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px;">
                    <table class="table table-hover align-middle mb-0" id="teachersTable" style="background: #fff; width: 100%; min-width: 1100px;">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 16px; font-weight: 700; color: #1e293b; text-align: right; border-bottom: none;">بيانات المعلم الأساسية</th>
                                <th style="padding: 16px; font-weight: 700; color: #1e293b; border-bottom: none;">الموقع واللغات</th>
                                <th style="padding: 16px; font-weight: 700; color: #1e293b; border-bottom: none;">التخصص والمسارات</th>
                                <th style="padding: 16px; font-weight: 700; color: #1e293b; border-bottom: none;">تاريخ وتوفر المعلم</th>
                                <th style="padding: 16px; font-weight: 700; color: #1e293b; text-align: center; border-bottom: none;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teachers as $teacher)
                                @php
                                    $userName = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;
                                    $userEmail = optional(optional($teacher->profile)->user)->email ?? $teacher->email;
                                    $imagePath = $teacher->status == 'pending' ? $teacher->profile_photo_path : (optional($teacher->profile)->profile_photo_path ?? $teacher->profile_photo_path);
                                    $langsRaw = $teacher->languages;
                                    $langs = is_array($langsRaw) || is_object($langsRaw) ? (array) $langsRaw : (json_decode($langsRaw, true) ?? []);
                                @endphp
                                
                                <tr style="background: #fff;">
                                    {{-- 1. Teacher Info (Stacked) --}}
                                    <td style="padding: 16px; text-align: right; border-bottom: none;">
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            <img src="{{ $imagePath ? asset('storage/' . $imagePath) : asset('images/default-avatar.png') }}" 
                                                 alt="Profile" style="width: 55px; height: 55px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
                                            <div class="d-flex flex-column gap-1">
                                                <span style="font-weight: 700; color: #0f172a; font-size: 1rem;">
                                                    {{ $userName }}
                                                    @if($teacher->gender == 'male') <i class="fa-solid fa-mars text-primary ms-1" title="ذكر"></i> @elseif($teacher->gender == 'female') <i class="fa-solid fa-venus text-danger ms-1" title="أنثى"></i> @endif
                                                </span>
                                                <span style="color: #64748b; font-size: 0.85rem;"><i class="fa-solid fa-envelope me-1"></i>{{ $userEmail }}</span>
                                                <div class="d-flex gap-3 align-items-center mt-1">
                                                    <span style="color: #0d9488; font-size: 0.85rem; font-weight: 600;"><i class="fa-solid fa-phone me-1"></i><span dir="ltr">{{ $teacher->phone }}</span></span>
                                                    @if($teacher->cv_pdf_path)
                                                        <a href="{{ asset('storage/' . $teacher->cv_pdf_path) }}" target="_blank" class="badge bg-soft-danger text-danger text-decoration-none" title="عرض السيرة الذاتية" style="font-size: 0.75rem;">
                                                            <i class="fa-solid fa-file-pdf me-1"></i> السيرة الذاتية
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 2. Location & Languages (Stacked) --}}
                                    <td style="padding: 16px; border-bottom: none;">
                                        <div class="d-flex flex-column gap-2">
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-earth-americas text-muted" style="width:18px;"></i> <strong>الجنسية:</strong> {{ $teacher->origin_country ?? 'غير محدد' }}
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-location-dot text-muted" style="width:18px;"></i> <strong>الإقامة:</strong> {{ $teacher->residence_location ?? 'غير محدد' }}
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-language text-muted" style="width:18px;"></i> <strong>اللغات:</strong> 
                                                {{ count($langs) > 0 ? implode('، ', $langs) : 'العربية' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Category & Tracks (Stacked) --}}
                                    <td style="padding: 16px; border-bottom: none;">
                                        <div class="d-flex flex-column gap-2">
                                            @if($teacher->status == 'approved' && $teacher->profile)
                                                <div style="font-size: 0.85rem; color: #334155;">
                                                    <i class="fa-solid fa-star text-warning" style="width:18px;"></i>
                                                    <span style="font-weight: 600;">الفئة:</span>
                                                    <span class="badge bg-light text-dark border">{{ optional($teacher->profile->category)->name ?? 'بدون فئة' }}</span>
                                                </div>
                                            @else
                                                <div style="font-size: 0.85rem; color: #334155;">
                                                    <i class="fa-solid fa-briefcase text-muted" style="width:18px;"></i>
                                                    <span style="font-weight: 600;">القسم:</span>
                                                    @if($teacher->teacher_category == 'both')
                                                        قرآن وعربية
                                                    @elseif($teacher->teacher_category == 'quran')
                                                        قرآن كريم
                                                    @else
                                                        لغة عربية
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-folder-open text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">المسارات:</span>
                                                @if($teacher->tracks && $teacher->tracks->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                    @foreach($teacher->tracks as $track)
                                                        <span class="badge bg-soft-primary text-primary" style="font-size: 0.7rem;">{{ $track->name }}</span>
                                                    @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted small">لا يوجد</span>
                                                @endif
                                            </div>
                                            
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-graduation-cap text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">المؤهل:</span> 
                                                <span class="text-muted">{{ Str::limit($teacher->qualification ?? 'غير محدد', 20) }}</span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-briefcase text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">الخبرة:</span> 
                                                <span class="text-muted">{{ $teacher->experience_years ? $teacher->experience_years . ' سنوات' : 'غير محدد' }}</span>
                                            </div>
                                            @if($teacher->ijazas_text)
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-book-quran text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">الإجازات:</span> 
                                                <span class="text-muted">{{ Str::limit($teacher->ijazas_text, 25) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 4. Date & Availability (Stacked) --}}
                                    <td style="padding: 16px; border-bottom: none;">
                                        <div class="d-flex flex-column gap-2">
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-regular fa-calendar-plus text-muted" style="width:18px;"></i> 
                                                <span style="font-weight: 600;">تاريخ الانضمام:</span> <br>
                                                <span class="ms-4 text-muted">{{ $teacher->created_at->format('Y-m-d') }}</span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-regular fa-clock text-muted" style="width:18px;"></i> 
                                                <span style="font-weight: 600;">متاح يومياً:</span> <br>
                                                <span class="ms-4 text-primary fw-bold">{{ $teacher->work_hours ?? 0 }} ساعة</span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-chalkboard-user text-muted" style="width:18px;"></i> 
                                                <span style="font-weight: 600;">إجمالي الساعات المُدرّسة:</span> <br>
                                                @php
                                                    $totalMinutes = optional($teacher->profile)->minutes ?? 0;
                                                    $totalHours = floor($totalMinutes / 60);
                                                @endphp
                                                <span class="ms-4 text-success fw-bold">{{ $totalHours }} ساعة</span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-wifi text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">الإنترنت:</span> 
                                                <span class="badge bg-light text-dark border">{{ $teacher->internet_quality ?? 'غير محدد' }}</span>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #334155;">
                                                <i class="fa-solid fa-laptop-code text-muted" style="width:18px;"></i>
                                                <span style="font-weight: 600;">التقنية:</span> 
                                                <span class="badge bg-light text-dark border">{{ $teacher->tech_skills ?? 'غير محدد' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 5. Actions / Status --}}
                                    <td style="padding: 16px; text-align: center; vertical-align: middle; border-bottom: none;">
                                        <div class="d-flex flex-column gap-2 justify-content-center align-items-center">
                                            @if($teacher->status == 'approved')
                                                <span class="badge bg-success px-3 py-2 rounded-pill w-100" style="font-size: 0.8rem;"><i class="fa-solid fa-check me-1"></i> مقبول</span>
                                            @elseif($teacher->status == 'rejected')
                                                <span class="badge bg-danger px-3 py-2 rounded-pill w-100" style="font-size: 0.8rem;"><i class="fa-solid fa-ban me-1"></i> مرفوض</span>
                                            @elseif($teacher->status == 'pending')
                                                <span class="badge bg-warning px-3 py-2 rounded-pill w-100 text-dark" style="font-size: 0.8rem;"><i class="fa-solid fa-clock me-1"></i> قيد المراجعة</span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill w-100" style="font-size: 0.8rem;">غير مفعل</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                
                                {{-- Row 2: Expanded Edit/Approve Form --}}
                                <tr style="border-bottom: 3px solid #e2e8f0; background: #fafafa;">
                                    <td colspan="5" style="padding: 12px 16px; border-top: 1px dashed #e2e8f0;">
                                        <div class="d-flex flex-column flex-xl-row align-items-center gap-2 w-100">
                                            <div class="d-flex align-items-center gap-2 me-xl-4 mb-2 mb-xl-0">
                                                <i class="fa-solid fa-gears text-muted"></i>
                                                <span class="fw-bold text-muted small" style="white-space: nowrap;">إدارة الحساب:</span>
                                            </div>
                                            
                                            @if ($teacher->status == 'pending')
                                                <form action="{{ route('teacher.approve', $teacher->id) }}" method="POST" id="approveForm{{ $teacher->id }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 mb-0" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 220px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                                        <input type="email" name="email" class="form-control border-start-0" value="{{ $teacher->email }}" required placeholder="البريد الإلكتروني">
                                                    </div>
                                                    
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 180px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-layer-group"></i></span>
                                                        <select name="category_id" class="form-select border-start-0 fw-bold" required>
                                                            <option value="">-- اختر الفئة --</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 180px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                                        <input type="password" name="password" class="form-control border-start-0" placeholder="كلمة المرور (للتفعيل)" required minlength="8">
                                                    </div>
                                                </form>
                                                
                                                <div class="d-flex align-items-center gap-2 ms-xl-auto mt-2 mt-xl-0">
                                                    <button type="submit" form="approveForm{{ $teacher->id }}" class="btn btn-sm btn-success fw-bold px-3 shadow-sm rounded-3">
                                                        <i class="fa-solid fa-check me-1"></i> قبول وتفعيل
                                                    </button>
                                                    <form action="{{ route('teacher.reject', $teacher->id) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 shadow-sm rounded-3" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                                                            <i class="fa-solid fa-xmark me-1"></i> رفض
                                                        </button>
                                                    </form>
                                                </div>
                                            
                                            @elseif($teacher->status == 'approved' || $teacher->status == 'not_active')
                                                <form action="{{ route('teacher.updateDetails', $teacher->id) }}" method="POST" id="updateForm{{ $teacher->id }}" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 mb-0" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 180px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                                        <input type="text" name="name" class="form-control border-start-0" value="{{ $userName }}" required placeholder="الاسم">
                                                    </div>

                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 200px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                                        <input type="email" name="email" class="form-control border-start-0" value="{{ $userEmail }}" required placeholder="البريد الإلكتروني">
                                                    </div>
                                                    
                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 150px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-signal"></i></span>
                                                        <select name="status" class="form-select border-start-0 fw-bold {{ $teacher->status == 'approved' ? 'text-success' : 'text-secondary' }}">
                                                            <option value="approved" {{ $teacher->status == 'approved' ? 'selected' : '' }}>نشط (مقبول)</option>
                                                            <option value="not_active" {{ $teacher->status == 'not_active' ? 'selected' : '' }}>غير مفعل</option>
                                                        </select>
                                                    </div>

                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 150px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-layer-group"></i></span>
                                                        <select name="category_id" class="form-select border-start-0 fw-bold" required>
                                                            <option value="" disabled>-- اختر الفئة --</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ (optional($teacher->profile)->category_id == $category->id) ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="input-group input-group-sm shadow-sm" style="max-width: 150px; flex: 1 1 auto;">
                                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                                        <input type="password" name="password" class="form-control border-start-0" placeholder="تغيير المرور..." minlength="8">
                                                    </div>
                                                </form>
                                                <div class="ms-xl-auto mt-2 mt-xl-0">
                                                    <button type="submit" form="updateForm{{ $teacher->id }}" class="btn btn-sm btn-primary fw-bold px-4 shadow-sm rounded-3">
                                                        <i class="fa-solid fa-save me-1"></i> حفظ
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-3">
                                            <i class="fa-solid fa-folder-open fs-1 text-muted opacity-25"></i>
                                        </div>
                                        <h6 class="fw-bold">لم يتم العثور على بيانات!</h6>
                                        <p class="small">لا توجد نتائج تطابق بحثك في قاعدة البيانات.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- إضافة روابط الترقيم (Pagination Links) أسفل الجدول --}}
                @if(method_exists($teachers, 'links'))
                    <div class="pagination-wrapper p-3 border-top m-0">
                        {{ $teachers->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile(input, imgIdClass) {
            const modal = input.closest('.modal');
            const preview = modal.querySelector('.' + imgIdClass);
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener("load", function() {
                preview.src = reader.result;
            }, false);
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // ===============================================
        // إرسال البحث والفلترة عن طريق Standard Request (بدون AJAX)
        // ===============================================

        // دالة الفلترة وإرسال الفورم (تعمل عند النقر على الأزرار)
        function submitFilter(status) {
            document.getElementById('statusInput').value = status;
            document.getElementById('searchFilterForm').submit();
        }

        // إظهار التلميح الذكي أثناء الكتابة في حقل البحث
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            let search = this.value.trim();
            let hintBox = document.getElementById('searchHint');

            if (search.length > 0) {
                hintBox.style.display = 'block';
                if (search.includes('@')) {
                    hintBox.innerHTML = '<i class="fa-solid fa-envelope text-primary ms-1"></i> اضغط Enter للبحث بالبريد...';
                    hintBox.className = 'search-hint text-primary';
                } else if (/^\d+$/.test(search)) {
                    hintBox.innerHTML = '<i class="fa-solid fa-phone text-success ms-1"></i> اضغط Enter للبحث بالرقم...';
                    hintBox.className = 'search-hint text-success';
                } else {
                    hintBox.innerHTML = '<i class="fa-solid fa-user text-info ms-1"></i> اضغط Enter للبحث بالاسم...';
                    hintBox.className = 'search-hint text-info';
                }
            } else {
                hintBox.style.display = 'none';
            }
        });
    </script>
@endsection
