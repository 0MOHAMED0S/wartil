<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انضم للكادر التعليمي - ورتل</title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/svg+xml">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            /* Brand Identity - مستوحاة مباشرة من الشعار */
            --primary-dark: #2d8a74;      /* الأخضر الأساسي في كلمة ورتل */
            --primary-medium: #4fb299;    /* الدرجة المتوسطة في خطوط الهاتف */
            --primary-light: #f0f9f7;     /* خلفية باهتة مريحة للعين */

            --gold-main: #d4a753;         /* لون صفحات المصحف في الشعار */
            --gold-light: #fdf5e6;        /* لون كريمي فاتح للخلفيات */

            /* UI Colors */
            --bg-body: #ffffff;
            --text-main: #1e4d42;         /* أخضر داكن جدًا للنصوص بدلاً من الأسود */
            --text-muted: #6a8d85;

            /* Specific Components */
            --card-cream: #fcf8f0;        /* للبطاقات المميزة */
            --btn-orange: #d4a753;        /* استخدام الذهبي للأزرار بدلاً من البرتقالي لتوحيد الهوية */
            --btn-orange-shadow: #b3893f;
        }

        /* --- Global Reset & Lock --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #fcfcfc;
            color: var(--text-main);
            overflow-x: hidden !important;
            width: 100%;
        }

        body.loading-locked {
            overflow: hidden !important;
            height: 100vh;
        }

        /* =========================================
           1. LOADER
           ========================================= */
        #loader-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .loader-brand {
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 900;
            line-height: 1;
            position: relative;
            color: #f3f3f3;
            margin: 0;
            letter-spacing: -2px;
        }

        .loader-brand::before {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            color: var(--primary-dark);
            border-right: 4px solid var(--gold-main);
            overflow: hidden;
            animation: fillText 2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }

        .loader-sub {
            margin-top: 10px;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: var(--gold-main);
            font-weight: 700;
            opacity: 0;
            transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.5s;
            text-align: center;
        }

        @keyframes fillText {
            0% {
                width: 0;
            }

            100% {
                width: 100%;
            }
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up-exit {
            transform: translateY(-100%);
        }

        /* =========================================
           2. NAVBAR
           ========================================= */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .navbar-brand img {
            height: 50px;
        }

        .back-link {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 700;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover {
            color: var(--gold-main);
            transform: translateX(5px);
        }

        /* =========================================
           3. PAGE HEADER
           ========================================= */
        .page-header {
            padding: 120px 0 60px;
            text-align: center;
            background: linear-gradient(180deg, #fff 0%, #fcfcfc 100%);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* =========================================
           4. SIDEBAR FEATURES (UPDATED)
           ========================================= */
        .side-feature-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 15px;
            border: 1px solid #f0f0f0;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .side-feature-card:hover {
            transform: translateX(-5px);
            border-color: var(--gold-main);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        }

        .side-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            color: var(--primary-dark);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .side-content h6 {
            margin: 0 0 5px;
            font-weight: 800;
            color: var(--text-main);
            font-size: 1rem;
        }

        .side-content p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .conditions-box {
            background: var(--gold-light);
            border: 2px dashed var(--gold-main);
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
        }

        .condition-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .condition-list li {
            position: relative;
            padding-right: 25px;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary-dark);
        }

        .condition-list li:last-child {
            margin-bottom: 0;
        }

        .condition-list li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 0;
            top: 3px;
            color: var(--gold-main);
            font-size: 0.8rem;
        }

        /* Sticky Sidebar on Desktop */
        .sticky-sidebar {
            position: sticky;
            top: 100px;
            z-index: 10;
        }

        /* =========================================
           5. FORM STYLES
           ========================================= */
        .reg-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.02);
            margin-bottom: 80px;
        }

        .section-label {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .section-label::before {
            content: '';
            width: 5px;
            height: 25px;
            background: var(--gold-main);
            border-radius: 5px;
        }

        .form-floating>.form-control,
        .form-floating>.form-select {
            border-radius: 15px;
            border: 1px solid #eee;
            background-color: #fdfdfd;
            height: 60px;
        }

        .form-floating>.form-control:focus,
        .form-floating>.form-select:focus {
            border-color: var(--gold-main);
            box-shadow: 0 0 0 4px rgba(196, 154, 70, 0.1);
        }

        .form-floating>label {
            padding-right: 20px;
            color: #999;
        }

        .custom-check-card {
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s;
            display: block;
            height: 100%;
        }

        .custom-check-card:hover {
            background: #f9f9f9;
        }

        .form-check-input:checked+.custom-check-label .custom-check-card {
            border-color: var(--gold-main);
            background: var(--gold-light);
            color: var(--primary-dark);
            font-weight: bold;
        }

        .file-upload-box {
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            background: #fafafa;
        }

        .file-upload-box:hover,
        .file-upload-box.active {
            border-color: var(--primary-dark);
            background: #f0fdf4;
        }

        .file-icon {
            font-size: 2rem;
            color: #aaa;
            margin-bottom: 10px;
        }

        .file-text {
            font-weight: 600;
            color: var(--text-main);
            display: block;
        }

        .file-sub {
            font-size: 0.8rem;
            color: #999;
        }

        .btn-submit {
            width: 100%;
            border: none;
            padding: 18px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.2rem;
            background: var(--primary-dark);
            color: white;
            box-shadow: 0 6px 0 #0f3d22;
            transition: 0.1s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: #143d24;
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(6px);
            box-shadow: none;
        }

        /* =========================================
           5. PROFESSIONAL MEGA FOOTER
           ========================================= */
        footer {
            background-color: #0f2922;
            /* Darker than primary-dark */
            color: #ecf0f1;
            padding: 80px 0 30px;
            font-size: 0.95rem;
            text-align: right;
        }

        .footer-logo {
            height: 70px;
            margin-bottom: 25px;
        }

        .footer-desc {
            color: #bdc3c7;
            margin-bottom: 30px;
            line-height: 1.8;
            max-width: 90%;
        }

        .footer-title {
            color: var(--gold-main);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.1rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 3px;
            background: var(--gold-main);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            text-decoration: none;
            color: #d1d5db;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--gold-main);
            transform: translateX(-5px);
        }

        .footer-links a i {
            font-size: 12px;
            margin-left: 8px;
            color: var(--gold-main);
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin-left: 10px;
            transition: 0.3s;
            font-size: 1.1rem;
            text-decoration: none;
        }

        .social-icons a:hover {
            background: var(--gold-main);
            transform: translateY(-3px);
        }

        .newsletter-form {
            position: relative;
            margin-top: 20px;
        }

        .newsletter-form input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 50px;
            border-radius: 50px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .newsletter-form input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }

        .newsletter-form button {
            position: absolute;
            top: 5px;
            left: 5px;
            height: 38px;
            width: 38px;
            border-radius: 50%;
            border: none;
            background: var(--gold-main);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.3s;
        }

        .newsletter-form button:hover {
            background: white;
            color: var(--gold-main);
        }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .copyright {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* --- New Photo Upload Styles --- */
        .profile-photo-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 30px;
        }

        .photo-preview-container {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--gold-main);
            background: #f8f8f8;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .photo-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .photo-preview-container i {
            font-size: 3rem;
            color: #ddd;
        }

        .upload-photo-btn {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: var(--primary-dark);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 2px solid white;
            transition: 0.2s;
        }

        .upload-photo-btn:hover {
            background: var(--gold-main);
        }

        @media (max-width: 992px) {
            .page-header {
                padding-top: 100px;
            }

            .reg-card {
                padding: 25px 15px;
                border-radius: 20px;
            }

            .sticky-sidebar {
                position: relative;
                top: 0;
                margin-bottom: 40px;
            }
        }
    </style>
</head>

<body>

    <!-- <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">انضم للكادر التعليمي</div>
        </div>
    </div> -->

    <nav class="navbar fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img width="45px" height="50px" src="{{ asset('images/mainlogo.png') }}"
                    alt="ورتل"></a>
            <a href="{{ route('welcome') }}" class="back-link">
                العودة للرئيسية <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title" data-aos="fade-up">كن شريكاً في تبليغ رسالة القرآن الكريم</h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
                انضم إلى نخبة من أفضل المعلمين والمقرئين حول العالم، وساهم في تعليم كتاب الله باستخدام أحدث التقنيات
                الرقمية.
            </p>
        </div>
    </section>

    <div class="container">
        <div class="row g-lg-5">

            <div class="col-lg-4 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
                <div class="sticky-sidebar">
                    <h4 class="fw-bold mb-4" style="color: var(--primary-dark)">لماذا تنضم لكادر وُرتّل؟</h4>

                    <div class="side-feature-card">
                        <div class="side-icon"><i class="fa-solid fa-clock"></i></div>
                        <div class="side-content">
                            <h6>مرونة في العمل</h6>
                            <p>اختر الأوقات التي تناسبك وقم بإدارة جدولك بسهولة.</p>
                        </div>
                    </div>

                    <div class="side-feature-card">
                        <div class="side-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                        <div class="side-content">
                            <h6>عائد مادي مجزي</h6>
                            <p>نقدر جهدك وخبرتك ونوفر لك نظام مكافآت تنافسي.</p>
                        </div>
                    </div>

                    <div class="side-feature-card">
                        <div class="side-icon"><i class="fa-solid fa-laptop-code"></i></div>
                        <div class="side-content">
                            <h6>بيئة أكاديمية متطورة</h6>
                            <p>استخدم أدوات تقنية متطورة تسهل عليك المتابعة.</p>
                        </div>
                    </div>

                    <div class="side-feature-card">
                        <div class="side-icon"><i class="fa-solid fa-earth-americas"></i></div>
                        <div class="side-content">
                            <h6>أثر مستدام</h6>
                            <p>كن جزءاً من رحلة حفظ وتلاوة ملايين المسلمين.</p>
                        </div>
                    </div>

                    <div class="conditions-box">
                        <h5 class="fw-bold mb-3" style="color: var(--primary-dark)">شروط الانضمام</h5>
                        <ul class="condition-list">
                            <li>إجازة معتمدة (للمسارات المتقدمة)</li>
                            <li>إتقان أحكام التجويد نظرياً وعملياً</li>
                            <li>القدرة على التعامل مع التطبيقات الإلكترونية</li>
                            <li>جودة اتصال إنترنت مستقرة</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <strong><i class="fa-solid fa-triangle-exclamation me-2"></i> يرجى تصحيح الأخطاء
                            التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form class="reg-card" method="POST" action="{{ route('teacher.apply') }}"
                    enctype="multipart/form-data" data-aos="fade-up" data-aos-delay="200">
                    @csrf

                    {{-- ================== الصورة الشخصية ================== --}}
                    <div class="profile-photo-wrapper">
                        <div class="photo-preview-container" id="photoPreview">
                            <i class="fa-solid fa-user"></i>
                            <img src="" alt="Profile Preview" id="profileImage">
                        </div>
                        <label for="profile_photo" class="upload-photo-btn">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" name="profile_photo" id="profile_photo" hidden accept="image/*">
                    </div>

                    {{-- ================== البيانات الشخصية ================== --}}
                    <h4 class="section-label mt-0">البيانات الشخصية</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="full_name"
                                    class="form-control @error('full_name') is-invalid @enderror" placeholder="الاسم"
                                    value="{{ old('full_name') }}" required>
                                <label>الاسم الثلاثي</label>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror"
                                    required>
                                    <option disabled selected value="">اختر الجنس</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى
                                    </option>
                                </select>
                                <label>الجنس</label>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="name@example.com" value="{{ old('email') }}" required>
                                <label>البريد الإلكتروني</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror" placeholder="رقم الهاتف"
                                    value="{{ old('phone') }}" required style="direction:ltr;text-align:right;">
                                <label>رقم الهاتف (واتساب)</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="origin_country"
                                    class="form-select country-api @error('origin_country') is-invalid @enderror"
                                    id="originCountry">
                                    <option disabled selected>اختر الدولة</option>
                                </select>
                                <input type="hidden" id="old_origin_country" value="{{ old('origin_country') }}">
                                <label>بلد الأصل</label>
                                @error('origin_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="residence_location"
                                    class="form-control @error('residence_location') is-invalid @enderror"
                                    placeholder="مكان الإقامة" value="{{ old('residence_location') }}" required>
                                <label>مكان الإقامة الحالي (الدولة - المدينة)</label>
                                @error('residence_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ================== الخلفية العلمية ================== --}}
                    <h4 class="section-label">الخلفية العلمية واللغوية</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" name="qualification"
                                    class="form-control @error('qualification') is-invalid @enderror"
                                    placeholder="المؤهل" value="{{ old('qualification') }}" required>
                                <label>المؤهل العلمي</label>
                                @error('qualification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h4 class="section-label">اختر المسارات التي ترغب بتدريسها</h4>

                        <div class="row g-3 mb-4">
                            @foreach ($tracks as $track)
                                <div class="col-md-4 col-6">
                                    <input type="checkbox" class="form-check-input d-none" name="tracks[]"
                                        id="track_{{ $track->id }}" value="{{ $track->id }}">

                                    <label class="custom-check-label w-100" for="track_{{ $track->id }}">
                                        <div class="custom-check-card text-center">
                                            <strong>{{ $track->name }}</strong>
                                        </div>
                                    </label>
                                </div>
                            @endforeach

                            @error('tracks')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted mb-2">اللغات التي تجيدها</label>
                            <div class="row g-2">
                                @php
                                    $languages = [
                                        'arabic' => 'العربية',
                                        'english' => 'الإنجليزية',
                                        'french' => 'الفرنسية',
                                        'urdu' => 'الأردية',
                                        'indonesian' => 'الإندونيسية',
                                        'turkish' => 'التركية',
                                        'spanish' => 'الإسبانية',
                                        'german' => 'الألمانية',
                                    ];
                                @endphp
                                @foreach ($languages as $key => $label)
                                    <div class="col-md-3 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input @error('languages') is-invalid @enderror"
                                                type="checkbox" name="languages[]" value="{{ $key }}"
                                                id="lang_{{ $key }}"
                                                {{ in_array($key, old('languages', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="lang_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('languages')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ================== الخبرة ================== --}}
                    <h4 class="section-label">الخبرة والقدرات التقنية</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" name="experience_years"
                                    class="form-control @error('experience_years') is-invalid @enderror"
                                    min="0" value="{{ old('experience_years') }}">
                                <label>عدد سنوات الخبرة التعليمية</label>
                                @error('experience_years')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" name="work_hours"
                                    class="form-control @error('work_hours') is-invalid @enderror" min="1"
                                    value="{{ old('work_hours') }}">
                                <label>ساعات العمل المتوقعة يومياً</label>
                                @error('work_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="online_experience"
                                    class="form-select @error('online_experience') is-invalid @enderror">
                                    <option disabled selected>اختر المستوى</option>
                                    <option value="beginner"
                                        {{ old('online_experience') == 'beginner' ? 'selected' : '' }}>مبتدئ (أول مرة)
                                    </option>
                                    <option value="intermediate"
                                        {{ old('online_experience') == 'intermediate' ? 'selected' : '' }}>متوسط (سنة
                                        إلى سنتين)</option>
                                    <option value="expert"
                                        {{ old('online_experience') == 'expert' ? 'selected' : '' }}>خبير (أكثر من
                                        سنتين)</option>
                                </select>
                                <label>الخبرة في التعليم عن بعد</label>
                                @error('online_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="internet_quality"
                                    class="form-select @error('internet_quality') is-invalid @enderror">
                                    <option disabled selected>اختر الجودة</option>
                                    <option value="weak" {{ old('internet_quality') == 'weak' ? 'selected' : '' }}>
                                        ضعيف</option>
                                    <option value="acceptable"
                                        {{ old('internet_quality') == 'acceptable' ? 'selected' : '' }}>مقبول (تقطيع
                                        نادر)</option>
                                    <option value="good" {{ old('internet_quality') == 'good' ? 'selected' : '' }}>
                                        جيد (مستقر غالباً)</option>
                                    <option value="excellent"
                                        {{ old('internet_quality') == 'excellent' ? 'selected' : '' }}>ممتاز (سرعة
                                        عالية وثبات)</option>
                                </select>
                                <label>جودة الإنترنت</label>
                                @error('internet_quality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <select name="tech_skills"
                                    class="form-select @error('tech_skills') is-invalid @enderror">
                                    <option disabled selected>اختر المستوى</option>
                                    <option value="beginner" {{ old('tech_skills') == 'beginner' ? 'selected' : '' }}>
                                        مبتدئ (أحتاج مساعدة)</option>
                                    <option value="intermediate"
                                        {{ old('tech_skills') == 'intermediate' ? 'selected' : '' }}>متوسط (أتعامل مع
                                        الزووم بسهولة)</option>
                                    <option value="advanced" {{ old('tech_skills') == 'advanced' ? 'selected' : '' }}>
                                        متقدم (أجيد التقنيات باحتراف)</option>
                                </select>
                                <label>المهارات التقنية</label>
                                @error('tech_skills')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ================== المرفقات ================== --}}
                    <h4 class="section-label">المرفقات والشهادات</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="ijazas_text" class="form-control @error('ijazas_text') is-invalid @enderror" style="height:80px">{{ old('ijazas_text') }}</textarea>
                                <label>الإجازات الحاصل عليها</label>
                                @error('ijazas_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="file-upload-box w-100 @error('cv_pdf') border-danger @enderror"
                                for="pdfUpload">
                                <input type="file" id="pdfUpload" name="cv_pdf" hidden accept=".pdf">
                                <i class="fa-solid fa-file-circle-check file-icon text-primary"></i>
                                <span class="file-text">السيرة الذاتية وشهادات الإجازة</span>
                                <span class="file-sub">ملف PDF واحد (حد أقصى 10MB)</span>
                            </label>
                            @error('cv_pdf')
                                <div class="text-danger small mt-1 text-center">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <button type="submit" class="btn-submit">إرسال الطلب</button>
                        <p class="text-muted small mt-2">
                            سيتم مراجعة بياناتك والتواصل معك قريباً
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

<footer>
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4 col-md-6">
                    <img src="{{ asset('images/mainlogo.png') }}" alt="Logo" class="footer-logo">
                    <p class="footer-desc">ورتل.. رفيقك في رحلة تعلم القرآن الكريم. نسعى لربط المسلمين بكتاب الله عبر
                        تقنيات حديثة وكوادر تعليمية مؤهلة.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/share/1HZASt1L9h/?mibextid=wwXIfr" target="_blank"><i
                                class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/wartil20?igsh=MWxidnk0cjl4YXpwNw==" target="_blank"><i
                                class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">خريطة الموقع</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-chevron-left text-xs"></i> الرئيسية</a></li>
                        <li><a href="#about"><i class="fa-solid fa-chevron-left text-xs"></i> عن التطبيق</a></li>
                        <li><a href="#packages"><i class="fa-solid fa-chevron-left text-xs"></i> الأسعار</a></li>
                        <li><a href="{{ route('teacher.index') }}"><i class="fa-solid fa-chevron-left text-xs"></i>
                                انضم كمعلم</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">المساعدة</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> سياسة الخصوصية</a></li>
                        {{-- <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> الشروط والأحكام</a></li>
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> الإلغاء والاسترجاع</a></li>
                        <li><a href="{{ route('policies') }}"><i class="fa-solid fa-chevron-left text-xs"></i> اتصل بنا</a></li> --}}
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">اشترك في النشرة</h5>
                    <p class="text-white-50 small mb-3">احصل على أحدث المقالات والنصائح القرآنية.</p>
                    <form class="position-relative mb-4">
                        <input type="email"
                            class="form-control bg-dark border-secondary text-white rounded-pill ps-4"
                            placeholder="البريد الإلكتروني">
                        <button class="btn position-absolute top-0 start-0 h-100 text-warning pe-3"><i
                                class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

            <div class="copyright">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-end">
                        &copy; {{ date('Y') }} جميع الحقوق محفوظة لـ <strong>ورتل</strong>.
                    </div>
                    <div class="col-md-6 text-center text-md-start mt-2 mt-md-0">
                        تم التطوير بكل ❤️ لخدمة القرآن الكريم
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Lock Scroll on Load
        // document.body.classList.add('loading-locked');

        $(document).ready(function() {
            // Loader Exit
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.classList.remove('loading-locked');
            }, 2000);

            // Init AOS
            AOS.init({
                once: true,
                offset: 50,
                duration: 800
            });

            // --- Profile Photo Preview Logic ---
            const photoInput = document.getElementById('profile_photo');
            const profileImage = document.getElementById('profileImage');
            const photoIcon = document.querySelector('#photoPreview i');

            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileImage.src = e.target.result;
                        profileImage.style.display = 'block';
                        photoIcon.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                }
            });

            // --- COUNTRY API LOGIC (Origin Only) ---
            const countryList = [
                {"name": "مصر", "code": "EG"},
                {"name": "السعودية", "code": "SA"},
                {"name": "الإمارات", "code": "AE"},
                {"name": "الكويت", "code": "KW"},
                {"name": "قطر", "code": "QA"},
                {"name": "البحرين", "code": "BH"},
                {"name": "عمان", "code": "OM"},
                {"name": "الأردن", "code": "JO"},
                {"name": "المغرب", "code": "MA"},
                {"name": "الجزائر", "code": "DZ"},
                {"name": "تونس", "code": "TN"},
                {"name": "فلسطين", "code": "PS"},
                {"name": "العراق", "code": "IQ"},
                {"name": "لبنان", "code": "LB"},
                {"name": "ليبيا", "code": "LY"},
                {"name": "السودان", "code": "SD"},
                {"name": "اليمن", "code": "YE"},
                {"name": "سوريا", "code": "SY"},
                {"name": "تركيا", "code": "TR"},
                {"name": "ماليزيا", "code": "MY"},
                {"name": "إندونيسيا", "code": "ID"},
                {"name": "الولايات المتحدة", "code": "US"},
                {"name": "المملكة المتحدة", "code": "GB"},
                {"name": "ألمانيا", "code": "DE"},
                {"name": "فرنسا", "code": "FR"}
            ];

            const originSelect = document.getElementById('originCountry');
            const oldCountry = document.getElementById('old_origin_country').value;

            if (originSelect) {
                originSelect.innerHTML = '<option selected disabled>اختر بلد الأصل</option>';
                countryList.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.code;
                    option.text = `${country.name}`;
                    if (country.code == oldCountry) {
                        option.selected = true;
                    }
                    originSelect.appendChild(option);
                });
            }

            // --- FILE UPLOAD LOGIC ---
            const fileInput = document.getElementById('pdfUpload');
            const fileBox = document.querySelector('.file-upload-box');
            const fileText = document.querySelector('.file-text');
            const fileIcon = document.querySelector('.file-icon');
            const fileSub = document.querySelector('.file-sub');

            const originalText = fileText.innerText;
            const originalSub = fileSub.innerText;

            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files.length > 0) {
                    const fileName = this.files[0].name;
                    fileText.innerText = fileName;
                    fileText.classList.add('text-success');
                    fileSub.innerText = "تم اختيار الملف بنجاح";
                    fileBox.classList.add('active');
                    fileBox.style.borderColor = 'var(--primary-dark)';
                    fileBox.style.backgroundColor = '#f0fdf4';
                    fileIcon.className = 'fa-solid fa-circle-check file-icon text-success';
                } else {
                    fileText.innerText = originalText;
                    fileText.classList.remove('text-success');
                    fileSub.innerText = originalSub;
                    fileBox.classList.remove('active');
                    fileBox.style.borderColor = '#ddd';
                    fileBox.style.backgroundColor = '#fafafa';
                    fileIcon.className = 'fa-solid fa-file-circle-check file-icon text-primary';
                }
            });
        });
    </script>
</body>
</html>
