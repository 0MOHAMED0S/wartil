<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم الإرسال بنجاح - ورتل</title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/svg+xml">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #2d8a74;
            /* الأخضر الأساسي في كلمة ورتل */
            --primary-medium: #4fb299;
            /* الدرجة المتوسطة في خطوط الهاتف */

            --gold-main: #c49a46;
            --gold-light: #fff8e1;
            --bg-body: #f8f9fa;
            --text-main: #2d3436;
        }

        /* --- Global Reset --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            /* Prevent horizontal scroll only */
            overflow-y: auto;
            /* Allow vertical scroll */
        }

        /* Navbar */
        .navbar {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .navbar-brand img {
            height: 50px;
        }

        /* Main Container */
        .main-content {
            flex: 1;
            /* Pushes footer down */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /* Better for scrolling than center */
            padding: 20px;
            margin-bottom: 40px;
        }

        /* Success Card */
        .success-card {
            background: white;
            padding: 60px 40px;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            max-width: 650px;
            width: 100%;
            text-align: center;
            position: relative;
            border-top: 5px solid var(--primary-dark);
            animation: cardEntrance 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Checkmark Animation */
        .check-container {
            width: 100px;
            height: 100px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            position: relative;
        }

        .check-icon {
            font-size: 3rem;
            color: var(--primary-dark);
            opacity: 0;
            transform: scale(0.5);
            animation: popCheck 0.5s cubic-bezier(0.5, 0, 0.5, 1.5) 0.3s forwards;
        }

        @keyframes popCheck {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Typography */
        .success-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .success-desc {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .highlight {
            color: var(--gold-main);
            font-weight: 700;
        }

        /* Timeline / Steps */
        .timeline {
            text-align: right;
            padding: 0 20px;
            margin-bottom: 40px;
            border-right: 2px solid #eee;
            margin-right: 20px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 25px;
            padding-right: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            right: -27px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #ccc;
        }

        .timeline-item.active::before {
            border-color: var(--primary-dark);
            background: var(--primary-dark);
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .t-title {
            font-weight: 700;
            font-size: 1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .t-desc {
            font-size: 0.9rem;
            color: #888;
        }

        /* Buttons */
        .btn-home {
            background: var(--primary-dark);
            color: white;
            padding: 14px 40px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
            width: 100%;
        }

        .btn-home:hover {
            background: #143d24;
            color: white;
            transform: translateY(-2px);
        }

        .contact-link {
            display: block;
            margin-top: 20px;
            color: #888;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .contact-link:hover {
            color: var(--primary-dark);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 25px;
            color: #aaa;
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .success-card {
                padding: 40px 25px;
            }

            .success-title {
                font-size: 1.6rem;
            }

            .check-container {
                width: 80px;
                height: 80px;
            }

            .check-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>

     <!-- <nav class="navbar">
        <div class="container justify-content-center">
            <a class="navbar-brand" href="#"><img width="45px" height="45px" src="{{ asset('images/mainlogo.png') }}" alt="ورتل"></a>
        </div>
    </nav>  -->

    <div class="main-content">
        <div class="success-card">

            <div class="check-container">
                <i class="fa-solid fa-check check-icon"></i>
            </div>

            <h1 class="success-title">تم استلام طلبك بنجاح!</h1>
            <p class="success-desc">
                شكراً لانضمامك لعائلة <span class="highlight">ورتل</span>.<br>
            </p>

            <div class="timeline">
                <div class="timeline-item active">
                    <div class="t-title text-success">تم استلام البيانات</div>
                    <div class="t-desc">وصلتنا بياناتك ومرفقاتك بنجاح.</div>
                </div>
                <div class="timeline-item">
                    <div class="t-title">المراجعة والتدقيق</div>
                    <div class="t-desc">ستقوم اللجنة الأكاديمية بمراجعة مؤهلاتك وتلاوتك خلال 48 ساعة.</div>
                </div>
                <div class="timeline-item">
                    <div class="t-title">تحديد المقابلة</div>
                    <div class="t-desc">في حال القبول المبدئي، سنتواصل معك عبر الواتساب.</div>
                </div>
            </div>

            <a href="{{ route('welcome') }}" class="btn-home">العودة للرئيسية</a>

            <!-- <a href="#" class="contact-link">
                <i class="fa-regular fa-envelope me-1"></i> هل واجهت مشكلة؟ تواصل معنا
            </a> -->
        </div>
    </div>

    <footer>
        جميع الحقوق محفوظة © ورتل 2026
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        // Professional Confetti Burst (Once on load)
        window.addEventListener('load', () => {
            var count = 200;
            var defaults = {
                origin: {
                    y: 0.7
                }
            };

            function fire(particleRatio, opts) {
                confetti(Object.assign({}, defaults, opts, {
                    particleCount: Math.floor(count * particleRatio)
                }));
            }

            // Burst Effect
            fire(0.25, {
                spread: 26,
                startVelocity: 55,
                colors: ['#1a4d2e', '#c49a46']
            });
            fire(0.2, {
                spread: 60,
                colors: ['#e8f5e9', '#fff8e1']
            });
            fire(0.35, {
                spread: 100,
                decay: 0.91,
                scalar: 0.8,
                colors: ['#1a4d2e', '#c49a46']
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 25,
                decay: 0.92,
                scalar: 1.2,
                colors: ['#2d3436']
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 45
            });
        });
    </script>
</body>

</html>
