<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقديم مغلق - ورتل</title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/svg+xml">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-dark: #2d8a74;
            /* الأخضر الأساسي في كلمة ورتل */
            --primary-medium: #4fb299;
            /* الدرجة المتوسطة في خطوط الهاتف */
            --primary-light: #f0f9f7;
            /* خلفية باهتة مريحة للعين */

            --text-main: #2d3436;
            --text-muted: #636e72;
            --bg-body: #ffffff;
        }

        /* --- Global Reset --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            /* Prevent horizontal scroll */
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
           2. FIXED BACKGROUND
           ========================================= */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 50% 50%, #fff 0%, #f4fcf6 100%);
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.4;
            z-index: -1;
            filter: blur(2px);
            transition: transform 0.1s linear;
        }

        .p1 {
            top: 15%;
            left: 10%;
            width: 20px;
            height: 20px;
            background: var(--gold-main);
            animation: float 10s infinite;
        }

        .p2 {
            bottom: 20%;
            right: 15%;
            width: 30px;
            height: 30px;
            background: var(--primary-dark);
            animation: float 15s infinite reverse;
        }

        .p3 {
            top: 40%;
            right: 40%;
            width: 10px;
            height: 10px;
            background: var(--gold-main);
            animation: float 8s infinite;
        }

        .p4 {
            bottom: 10%;
            left: 20%;
            width: 50px;
            height: 50px;
            border: 4px solid var(--primary-dark);
            background: transparent;
            opacity: 0.1;
            animation: spin 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* =========================================
           3. NAVBAR
           ========================================= */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            /* Sticky to ensure it stays on top while scrolling */
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 100;
        }

        .navbar-brand img {
            height: 45px;
        }

        .back-link {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .back-link:hover {
            color: var(--gold-main);
            transform: translateX(5px);
        }

        /* =========================================
           4. MAIN CONTENT
           ========================================= */
        .main-wrapper {
            flex: 1 0 auto;
            /* Allow grow, don't shrink */
            display: flex;
            align-items: center;
            /* Center vertically on large screens */
            justify-content: center;
            /* Center horizontally */
            padding: 40px 20px;
            position: relative;
            min-height: 85vh;
            /* Ensure it takes most of the screen */
        }

        .status-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(25px);
            border-radius: 35px;
            padding: 50px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 30px 80px rgba(26, 77, 46, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
            animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
            margin: auto;
            /* Helps centering */
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(30px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--gold-light), #fff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 25px rgba(196, 154, 70, 0.15);
            font-size: 3rem;
            color: var(--gold-main);
            animation: iconFloat 4s ease-in-out infinite;
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .status-title {
            font-weight: 900;
            font-size: 2.2rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .status-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        /* Form Desktop */
        .notify-form {
            background: white;
            padding: 8px;
            border-radius: 16px;
            display: flex;
            gap: 10px;
            border: 1px solid #eee;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            transition: 0.3s;
        }

        .notify-form:focus-within {
            border-color: var(--gold-main);
            box-shadow: 0 8px 25px rgba(196, 154, 70, 0.15);
        }

        .notify-input {
            border: none;
            outline: none;
            padding: 10px 15px;
            flex-grow: 1;
            font-weight: 600;
            color: var(--primary-dark);
            font-family: inherit;
        }

        .btn-notify {
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 5px 0 #0f3d22;
            transition: 0.1s;
            white-space: nowrap;
        }

        .btn-notify:hover {
            transform: translateY(-2px);
        }

        .btn-notify:active {
            transform: translateY(4px);
            box-shadow: none !important;
        }

        .social-links {
            margin-top: 30px;
            opacity: 0;
            animation: fadeIn 1s ease forwards 0.5s;
        }

        .social-links a {
            color: #ccc;
            margin: 0 12px;
            font-size: 1.3rem;
            transition: 0.3s;
            display: inline-block;
        }

        .social-links a:hover {
            color: var(--primary-dark);
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Footer */
        footer {
            flex-shrink: 0;
            /* Don't shrink */
            text-align: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: 0.8rem;
            background: transparent;
        }

        /* =========================================
           CRITICAL RESPONSIVE FIXES
           ========================================= */
        @media (max-width: 768px) {

            /* 1. Navbar Adjustments */
            .navbar {
                padding: 10px 0;
            }

            .navbar-brand img {
                height: 35px;
            }

            /* Smaller logo */
            .back-link {
                font-size: 0.9rem;
            }

            /* 2. Layout & Spacing */
            .main-wrapper {
                align-items: flex-start;
                /* Prevent vertical centering cropping */
                padding-top: 40px;
                /* Spacing below navbar */
                padding-bottom: 40px;
                /* Spacing above footer */
                height: auto;
                /* Allow auto height */
            }

            /* 3. Card Adjustments */
            .status-card {
                padding: 30px 20px;
                margin: 0;
                /* Remove auto margin */
                border-radius: 25px;
            }

            /* 4. Typography Scaling */
            .status-title {
                font-size: 1.6rem;
                margin-bottom: 10px;
            }

            .status-desc {
                font-size: 0.95rem;
                margin-bottom: 25px;
            }

            .icon-wrapper {
                width: 80px;
                height: 80px;
                font-size: 2.2rem;
                margin-bottom: 20px;
            }

            /* 5. Form Stack (Crucial for Mobile) */
            .notify-form {
                flex-direction: column;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .notify-input {
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                margin-bottom: 12px;
                height: 55px;
                /* Easy touch target */
                text-align: center;
                width: 100%;
            }

            .btn-notify {
                width: 100%;
                height: 55px;
                font-size: 1.1rem;
            }

            /* 6. Hide Distractions */
            .particle {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">عذراً، التقديم مغلق</div>
        </div>
    </div>  -->

    <div class="animated-bg">
        <div class="particle p1" data-speed="2"></div>
        <div class="particle p2" data-speed="-1"></div>
        <div class="particle p3" data-speed="1"></div>
        <div class="particle p4" data-speed="0.5"></div>
    </div>

    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="#"><img width="45px" height="45px"
                    src="{{ asset('images/mainlogo.png') }}" alt="ورتل"></a>
            <a href="{{ route('welcome') }}" class="back-link">
                الرئيسية <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="status-card">

            <div class="icon-wrapper">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>

            <h1 class="status-title">اكتمل العدد حالياً</h1>

            <p class="status-desc">
                نشكر حماسك للانضمام إلينا.<br>
                باب التقديم مغلق مؤقتاً لاكتمال العدد المطلوب.<br>
                {{-- <strong>سجل بريدك وسنخبرك فور فتح الباب مجدداً.</strong> --}}
            </p>

            {{-- <form class="notify-form">
                <input type="email" class="notify-input" placeholder="اكتب بريدك الإلكتروني..." required>
                <button type="submit" class="btn-notify">نبهني عند الفتح <i class="fa-solid fa-bell ms-2"></i></button>
            </form> --}}

            <div class="social-links">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-x"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <footer>
        جميع الحقوق محفوظة © ورتل 2026
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

    <script>
        $(document).ready(function() {

            // Loader Logic
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
            }, 2000);

            // Parallax Effect (Desktop Only)
            if (window.innerWidth > 992) {
                document.addEventListener('mousemove', function(e) {
                    const x = (window.innerWidth - e.pageX) / 50;
                    const y = (window.innerHeight - e.pageY) / 50;

                    $('.particle').each(function() {
                        const speed = $(this).data('speed');
                        $(this).css('transform',
                            `translateX(${x * speed}px) translateY(${y * speed}px)`);
                    });
                });
            }

            // Form Interaction
            $('.notify-form').on('submit', function(e) {
                e.preventDefault();
                const btn = $('.btn-notify');
                btn.html('<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...');
                btn.css({
                    'opacity': '0.8',
                    'pointer-events': 'none'
                });

                setTimeout(() => {
                    $('.notify-form').html(
                        '<div class="text-success fw-bold w-100 py-2"><i class="fa-solid fa-check-circle me-2"></i> تم تسجيلك بنجاح!</div>'
                    );
                    $('.notify-form').css({
                        'background': '#e8f5e9',
                        'border-color': '#c8e6c9',
                        'justify-content': 'center'
                    });
                }, 1500);
            });
        });
    </script>
</body>

</html>
