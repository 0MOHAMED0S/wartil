<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق ورتل -   منصه تعليم القرآن </title>
    <link rel="icon" href="{{ asset('images/mainlogo.png') }}" type="image/png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <style>
        :root {
            /* Brand Identity */
            --primary-dark: #2d8a74;
            --primary-medium: #4fb299;
            --primary-light: #f0f9f7;
            --gold-main: #d4a753;
            --gold-light: #fdf5e6;

            /* UI Colors */
            --bg-body: #ffffff;
            --text-main: #1e4d42;
            --text-muted: #6a8d85;
            --card-cream: #fcf8f0;
            --btn-orange: #d4a753;
            --btn-orange-shadow: #b3893f;
        }

        /* --- Global Reset & Scroll Locking --- */
        html,
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden !important;
            width: 100%;
            -webkit-font-smoothing: antialiased;
        }

        body.loading-locked,
        body.nav-locked {
            overflow: hidden !important;
            height: 100vh;
        }

        /* =========================================
           1. CINEMATIC LOADER
           ========================================= */
        /* #loader-screen {
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

        .loader-container {
            position: relative;
            overflow: hidden;
            padding: 20px;
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
            animation: fillText 2.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            white-space: nowrap;
        }

        .loader-sub {
            margin-top: 10px;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: var(--gold-main);
            font-weight: 700;
            opacity: 0;
            transform: translateY(15px);
            animation: fadeUp 0.8s ease forwards 1.8s;
            text-align: center;
            letter-spacing: 1px;
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
        } */

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
            transition: all 0.4s ease;
            z-index: 1000;
        }

        .nav-link {
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0 10px;
            font-size: 1rem;
            position: relative;
        }

        @media (min-width: 992px) {
            .nav-link::before {
                content: '';
                position: absolute;
                bottom: 0;
                right: 0;
                width: 0;
                height: 3px;
                background: var(--gold-main);
                transition: 0.3s;
                border-radius: 2px;
            }

            .nav-link:hover::before,
            .nav-link.active::before {
                width: 100%;
            }
        }

        .nav-link-teacher {
            color: var(--gold-main) !important;
            border: 1px solid var(--gold-main);
            border-radius: 50px;
            padding: 6px 20px !important;
            margin-left: 10px;
            transition: 0.3s;
            font-weight: 700;
            display: inline-block;
            text-decoration: none;
        }

        .nav-link-teacher:hover {
            background: var(--gold-main);
            color: white !important;
        }

        .btn-nav-cta {
            background: var(--primary-dark);
            color: white;
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(26, 77, 46, 0.2);
            transition: 0.3s;
            border: none;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .btn-nav-cta:hover {
            transform: translateY(-2px);
            background: var(--gold-main);
            color: white;
        }

        .navbar-toggler {
            border: none;
            padding: 0;
            color: var(--primary-dark);
            font-size: 1.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* =========================================
           3. HERO SECTION
           ========================================= */
        .hero-section {
            padding-top: 140px;
            padding-bottom: 80px;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .hero-bg-anim {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 0% 0%, #f1fcf5 0%, transparent 60%), radial-gradient(circle at 100% 100%, #fffbf0 0%, transparent 60%);
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
            animation: floatBubble 10s infinite alternate;
        }

        .shape-1 {
            width: 350px;
            height: 350px;
            background: #e0f2f1;
            top: -50px;
            left: -50px;
        }

        .shape-2 {
            width: 250px;
            height: 250px;
            background: #fff8e1;
            bottom: 0;
            right: -20px;
            animation-delay: -5s;
        }

        @keyframes floatBubble {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(30px, 30px);
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1.2;
            color: var(--primary-dark);
            margin-bottom: 20px;
            background: linear-gradient(to right, var(--primary-dark) 0%, var(--gold-main) 50%, var(--primary-dark) 100%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shineText 5s linear infinite;
        }

        @keyframes shineText {
            to {
                background-position: 200% center;
            }
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 550px;
        }

        .hero-image-wrapper {
            position: relative;
            perspective: 1000px;
            margin-top: 10px;
        }

        .hero-phone {
            width: 100%;
            max-width: 360px;
            border-radius: 40px;
            box-shadow: 0 40px 80px rgba(26, 77, 46, 0.15);
            border: 8px solid white;
            animation: floatPhone 6s ease-in-out infinite;
            transform: rotateY(-10deg) rotateX(5deg);
        }

        @keyframes floatPhone {

            0%,
            100% {
                transform: translateY(0) rotateY(-10deg);
            }

            50% {
                transform: translateY(-15px) rotateY(-5deg);
            }
        }

        .store-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 14px;
            text-decoration: none;
            transition: 0.3s;
            border: 1px solid rgba(0, 0, 0, 0.1);
            min-width: 150px;
        }

        .store-btn-dark {
            background: #1f2937;
            color: white;
        }

        .store-btn-dark:hover {
            background: black;
            transform: translateY(-4px);
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .store-btn-light {
            background: white;
            color: #1f2937;
        }

        .store-btn-light:hover {
            background: #f9fafb;
            transform: translateY(-4px);
            color: #1f2937;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .store-btn-icon {
            font-size: 24px;
            margin-left: 10px;
        }

        .store-btn-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
            text-align: left;
        }

        .store-btn-small {
            font-size: 10px;
            opacity: 0.8;
        }

        .store-btn-big {
            font-size: 15px;
            font-weight: 700;
        }

        .teacher-separator {
            display: flex;
            align-items: center;
            margin: 30px 0 15px 0;
            color: var(--gold-main);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .teacher-separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(196, 154, 70, 0.3);
            margin-right: 15px;
        }


        /* =========================================
           4. NEW STATS SECTION (COUNTER)
           ========================================= */
        .stats-section {
            background: var(--primary-dark);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .stats-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .stat-item {
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--gold-main);
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 600;
        }


        /* =========================================
           OTHER SECTIONS
           ========================================= */
        .why-box {
            text-align: center;
            padding: 30px;
            height: 100%;
            transition: 0.3s;
        }

        .why-icon {
            width: 80px;
            height: 80px;
            background: var(--gold-light);
            color: var(--gold-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            transition: 0.3s;
        }

        .why-box:hover .why-icon {
            background: var(--gold-main);
            color: white;
            transform: rotateY(180deg);
        }

        .about-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border-right: 4px solid var(--gold-main);
            height: 100%;
        }

        .about-icon-small {
            color: var(--primary-dark);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .section-header {
            text-center: center;
            margin-bottom: 50px;
        }

        .section-tag {
            color: var(--gold-main);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .section-title {
            font-weight: 800;
            color: var(--primary-dark);
            font-size: 2.2rem;
        }

        .feature-box {
            background: white;
            padding: 35px 25px;
            border-radius: 24px;
            transition: 0.4s;
            border: 1px solid #f0f0f0;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
            border-color: var(--gold-main);
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            transition: 0.4s;
        }

        .feature-box:hover .icon-wrapper {
            background: var(--primary-dark);
            color: var(--gold-main);
            transform: rotate(5deg);
        }

        .pricing-section {
            background-color: var(--primary-light);
            padding: 100px 0;
            position: relative;
        }

        .pricing-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to bottom, #ffffff, transparent);
        }

        .pkg-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid rgba(45, 138, 116, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            z-index: 1;
        }

        .pkg-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary-medium);
            transition: 0.3s;
            z-index: 0;
            opacity: 0.5;
        }

        .pkg-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px rgba(45, 138, 116, 0.1);
            border-color: rgba(45, 138, 116, 0.3);
        }

        .pkg-card:hover::before {
            height: 8px;
            opacity: 1;
        }

        .pkg-card.featured {
            background: linear-gradient(145deg, #ffffff, #fdfaf5);
            border: 2px solid var(--gold-main);
            box-shadow: 0 20px 40px rgba(212, 167, 83, 0.15);
            transform: scale(1.02);
            z-index: 5;
        }

        .pkg-card.featured::before {
            background: var(--gold-main);
            height: 8px;
            opacity: 1;
        }

        .pkg-card.featured:hover {
            transform: scale(1.02) translateY(-10px);
            box-shadow: 0 30px 60px rgba(212, 167, 83, 0.25);
        }

        .badge-popular {
            position: absolute;
            top: 25px;
            left: -35px;
            background: linear-gradient(135deg, var(--gold-main), #b3893f);
            color: #ffffff;
            padding: 8px 45px;
            font-weight: 800;
            font-size: 0.75rem;
            letter-spacing: 1px;
            transform: rotate(-45deg);
            box-shadow: 0 5px 15px rgba(212, 167, 83, 0.4);
            z-index: 10;
        }

        .pkg-name {
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .pkg-gift {
            background: var(--primary-light);
            border-radius: 12px;
            padding: 10px 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 25px;
            font-size: 1rem;
            border: 1px dashed rgba(45, 138, 116, 0.3);
            width: 100%;
        }

        .featured .pkg-gift {
            background: var(--gold-light);
            color: #b3893f;
            border-color: rgba(212, 167, 83, 0.3);
        }

        .pkg-card ul li {
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        .pkg-card ul li:last-child {
            border-bottom: none;
        }

        .btn-3d {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            position: relative;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 20px;
            overflow: hidden;
            z-index: 2;
        }

        .btn-3d::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.2), transparent);
            z-index: -1;
        }

        .btn-3d.orange {
            background: linear-gradient(to bottom, #dec288, var(--gold-main));
            color: #ffffff;
            box-shadow: 0 6px 0 #b3893f, 0 15px 20px rgba(212, 167, 83, 0.4);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .btn-3d.green {
            background: linear-gradient(to bottom, var(--primary-medium), var(--primary-dark));
            color: #ffffff;
            box-shadow: 0 6px 0 #185242, 0 15px 20px rgba(45, 138, 116, 0.3);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .btn-3d.outline {
            background: linear-gradient(to bottom, #ffffff, #f9f9f9);
            color: var(--primary-dark);
            border: 2px solid var(--primary-medium);
            box-shadow: 0 6px 0 #e2e8f0, 0 10px 15px rgba(0, 0, 0, 0.05);
        }

        .btn-3d:active {
            transform: translateY(6px);
            box-shadow: 0 0 0 transparent !important;
        }

        .cursor-icon {
            position: absolute;
            bottom: -8px;
            left: 15px;
            width: 28px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
            animation: floatCursor 2s infinite ease-in-out alternate;
            pointer-events: none;
        }

        .teacher-slide {
            padding: 10px;
        }

        .teacher-card {
            background: white;
            border-radius: 24px;
            padding: 25px 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
            border: 1px solid #f5f5f5;
            transition: 0.3s;
        }

        .teacher-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold-main);
        }

        .teacher-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 12px;
            padding: 3px;
            background: linear-gradient(to bottom, var(--primary-dark), var(--gold-main));
        }

        .teacher-img img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }

        /* Owl Carousel Custom Dots */
        .owl-theme .owl-dots .owl-dot {
            outline: none;
        }

        .owl-theme .owl-dots .owl-dot span {
            width: 10px;
            height: 10px;
            margin: 5px 7px;
            background: #e0e0e0;
            display: block;
            transition: opacity 0.2s ease, width 0.3s ease;
            border-radius: 30px;
            opacity: 0.5;
        }

        .owl-theme .owl-dots .owl-dot.active span,
        .owl-theme .owl-dots .owl-dot:hover span {
            background: var(--primary-main);
            width: 25px;
            opacity: 1;
        }

        /* =========================================
           6. NEW SECTIONS (TESTIMONIALS & CONTACT)
           ========================================= */

        /* Testimonials Section */
        .testimonials-section {
            background-color: #f9f9f9;
            padding: 80px 0;
            position: relative;
        }

        .testimonial-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            position: relative;
            margin-top: 30px;
            transition: 0.3s;
            border: 1px solid #f0f0f0;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--gold-main);
        }

        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: -40px;
            right: 30px;
        }

        .quote-icon {
            font-size: 3rem;
            color: var(--primary-light);
            position: absolute;
            top: 20px;
            left: 30px;
        }

        .stars {
            color: #ffc107;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        /* Contact Section */
        .contact-section {
            padding: 100px 0;
            background: #ffffff;
        }

        .contact-info-box {
            padding: 30px;
            border-radius: 20px;
            background: var(--primary-light);
            height: 100%;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: #ffffff;
            color: var(--primary-dark);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-left: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .contact-form {
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
        }

        .form-control {
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            background-color: #fbfbfb;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: var(--primary-main);
            box-shadow: 0 0 0 4px rgba(45, 138, 116, 0.1);
        }

        .btn-submit {
            background: var(--primary-dark);
            color: #ffffff;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background: var(--gold-main);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(212, 167, 83, 0.3);
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

        /* Responsive Fixes */
        @media (max-width: 991px) {
            .navbar {
                padding: 8px 0;
            }

            .navbar-collapse {
                background: white;
                padding: 25px 20px;
                border-radius: 20px;
                margin-top: 15px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
                text-align: center;
            }

            .nav-link {
                padding: 12px;
                border-bottom: 1px solid #f5f5f5;
                font-size: 1.1rem;
            }

            .nav-link::before {
                display: none;
            }

            .nav-link-teacher {
                display: block;
                width: 100%;
                margin: 20px 0 10px 0;
                padding: 10px !important;
                background: #fff8e1;
                color: var(--gold-main) !important;
                border: 1px solid var(--gold-main);
            }

            .btn-nav-cta {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
            }

            .hero-section {
                padding-top: 100px;
                padding-bottom: 50px;
                text-align: center;
            }

            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 15px;
            }

            .hero-subtitle {
                font-size: 1rem;
                margin: 0 auto 25px auto;
                padding: 0 10px;
            }

            .hero-btns-group {
                justify-content: center !important;
                gap: 10px !important;
            }

            .store-btn {
                flex: 1 1 auto;
                min-width: 130px;
                max-width: 180px;
                padding: 8px 12px;
            }

            .store-btn-icon {
                font-size: 20px;
                margin-left: 6px;
            }

            .store-btn-big {
                font-size: 12px;
            }

            .teacher-separator {
                justify-content: center;
                font-size: 0.85rem;
                margin: 30px 0 15px;
            }

            .teacher-separator::after {
                display: none;
            }

            .hero-image-wrapper {
                margin-top: 40px;
            }

            .hero-phone {
                max-width: 260px;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
                animation: none !important;
                transform: none !important;
            }

            .hero-bg-anim .floating-shape {
                display: none;
            }

            .pkg-card.featured {
                transform: none;
                margin: 15px 0;
                z-index: 1;
            }

            .badge-popular {
                top: -15px;
                left: 50%;
                transform: translateX(-50%) rotate(0deg);
                padding: 5px 20px;
                border-radius: 20px;
                width: auto;
            }

            /* Stats on Mobile */
            .stats-section {
                padding: 50px 0;
            }

            .stat-item {
                margin-bottom: 30px;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* --- Enhanced Tracks Section --- */
        .track-card-pro {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .track-card-pro:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--gold-main);
        }

        .track-header {
            padding: 30px 25px 20px;
            text-align: center;
            background: linear-gradient(180deg, #fbfbfb 0%, #fff 100%);
            border-bottom: 1px dashed #eee;
        }

        .track-icon-lg {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            color: var(--primary-main);
            border-radius: 50%;
            font-size: 2rem;
            transition: 0.4s;
        }

        .track-card-pro:hover .track-icon-lg {
            background: var(--primary-main);
            color: #fff;
            transform: rotateY(180deg);
        }

        .track-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .track-features {
            list-style: none;
            padding: 0;
            margin: 15px 0 25px;
            flex-grow: 1;
            /* Pushes button down */
        }

        .track-features li {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
        }

        .track-features li i {
            color: var(--gold-main);
            margin-left: 10px;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        .btn-track-outline {
            width: 100%;
            padding: 12px;
            border-radius: 50px;
            border: 2px solid var(--primary-light);
            color: var(--primary-dark);
            font-weight: 700;
            background: transparent;
            transition: 0.3s;
        }

        .btn-track-outline:hover {
            border-color: var(--primary-main);
            background: var(--primary-main);
            color: #fff;
        }
    </style>

<style>
    /* =========================================
           FLOATING MOCKUP ADS CAROUSEL (EXACT MATCH)
           ========================================= */
        .promotions-section {
            background-color: #f3f4f6; /* خلفية رمادية فاتحة جداً لتبرز البطاقة البيضاء */
            padding: 70px 0 50px;
            position: relative;
        }

        .ad-banner-link {
            display: block;
            text-decoration: none;
            outline: none;
            padding: 20px; /* مسافة ضرورية جداً ليظهر الظل براحته دون أن يُقطع */
        }

        .ad-banner-wrapper {
            background-color: #ffffff; /* لون أبيض نقي للبطاقة */
            border-radius: 24px; /* زوايا دائرية واضحة كما في صورتك */
            overflow: hidden;
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 7;
            height: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02); /* الظل الناعم المتطابق مع صورتك */
            border: 1px solid rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .ad-banner-link:hover .ad-banner-wrapper {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .ad-banner-img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* هذا ما سيجعل الصورة تظهر كاملة وتندمج مع الخلفية البيضاء */
            background-color: #ffffff; /* دمج لون الصورة مع لون البطاقة */
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .ad-banner-link:hover .ad-banner-img {
            transform: scale(1.02); /* زووم خفيف جداً وأنيق */
        }

        /* أيقونة الرابط */
        .ad-overlay-icon {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: #ffffff;
            color: var(--primary-dark);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            opacity: 0;
            transform: scale(0.8) translateY(10px);
            transition: all 0.4s ease;
            z-index: 2;
        }

        .ad-banner-link:hover .ad-overlay-icon {
            opacity: 1;
            transform: scale(1) translateY(0);
            color: var(--gold-main);
        }

        /* 📱 مقاسات مخصصة للموبايل لتحافظ على نفس الشكل */
        @media (max-width: 768px) {
            .ad-banner-wrapper {
                aspect-ratio: 16 / 7;
                height: auto;
                border-radius: 16px;
            }
            .promotions-section {
                padding: 40px 0 20px;
            }
            .ad-banner-link {
                padding: 15px 10px; /* تقليل البادينج في الموبايل */
            }
        }
</style>
</head>

<body>

    <!-- <div id="loader-screen">
        <div class="loader-container">
            <h1 class="loader-brand" data-text="ورتل">ورتل</h1>
            <div class="loader-sub">ورتل القرآن ترتيلاً</div>
        </div>
    </div> -->

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img width="45px" height="45px"
                    src="{{ asset('images/mainlogo.png') }}" alt="ورتل"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stats">الإحصائيات</a></li>
                    <li class="nav-item"><a class="nav-link" href="#why-us">لماذا ورتل</a></li>
                    <li class="nav-item"><a class="nav-link" href="#packages">الباقات</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">تواصل معنا</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-center">
                    <a href="{{ route('teacher.index') }}" class="nav-link-teacher"><i
                            class="fa-solid fa-chalkboard-user ms-1"></i> انضم كمعلم</a>
                    <button class="btn-nav-cta">حمل التطبيق <i class="fa-solid fa-arrow-down-long ms-2"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section d-flex align-items-center">
        <div class="hero-bg-anim">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div data-aos="fade-up" data-aos-delay="0">
                        <div class="d-inline-block px-3 py-2 rounded-pill bg-white shadow-sm mb-3 border border-1"><span
                                class="fw-bold text-success small"><i class="fa-solid fa-star me-2 text-warning"></i>
                                التطبيق الإسلامي الأول</span></div>
                    </div>
                    <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">احفظ القرآن الكريم<br>بإتقان وسند
                        متصل</h1>
                    <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="400">ابدأ رحلتك مع كتاب الله الآن برفقة
                        نخبة من المعلمون والمعلمات المجازون بالسند المتصل</p>
                    <div class="d-flex hero-btns-group mb-2" data-aos="fade-up" data-aos-delay="600">
                        <a href="#" class="store-btn store-btn-dark"><i class="fab fa-apple store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">Download on the</span><span
                                    class="store-btn-big">App Store</span></div>
                        </a>
                        <a href="#" class="store-btn store-btn-light"><i
                                class="fab fa-google-play store-btn-icon text-success"></i>
                            <div class="store-btn-text"><span class="store-btn-small">GET IT ON</span><span
                                    class="store-btn-big">Google Play</span></div>
                        </a>
                    </div>
                    <div class="teacher-separator" data-aos="fade-in" data-aos-delay="800"><i
                            class="fa-solid fa-user-tie ms-2"></i> نسخة المعلمين</div>
                    <div class="d-flex hero-btns-group" data-aos="fade-up" data-aos-delay="900">
                        <a href="#" class="store-btn store-btn-dark"
                            style="background: var(--primary-dark); border: 1px solid transparent;"><i
                                class="fab fa-apple store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span
                                    class="store-btn-big">App Store</span></div>
                        </a>
                        <a href="#" class="store-btn store-btn-light"
                            style="border-color: var(--primary-dark); color: var(--primary-dark);"><i
                                class="fab fa-google-play store-btn-icon"></i>
                            <div class="store-btn-text"><span class="store-btn-small">تطبيق المعلم</span><span
                                    class="store-btn-big">Google Play</span></div>
                        </a>
                    </div>
                    <div class="mt-4" data-aos="fade-in" data-aos-delay="1000"><a
                            href="{{ route('teacher.index') }}"
                            class="text-decoration-none fw-bold small text-muted">هل ترغب في الانضمام لفريقنا؟ <span
                                style="color: var(--gold-main); text-decoration: underline;">قدم طلبك الآن <i
                                    class="fa-solid fa-arrow-left"></i></span></a></div>
                </div>
                <div class="col-lg-6 text-center" data-aos="zoom-in" data-aos-duration="1200" data-aos-delay="300">
                    <div class="hero-image-wrapper"><img src="{{ asset('images/msa.jpeg') }}" alt="App UI"
                            class="hero-phone"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="stats-section">
        <div class="stats-bg-pattern"></div>
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" data-target="50000">0</div>
                        <div class="stat-label">طالب نشط</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" data-target="1200">0</div>
                        <div class="stat-label">معلم مجاز</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number" data-target="500000">0</div>
                        <div class="stat-label">دقيقة تلاوة</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number" data-target="4.9">0</div>
                        <div class="stat-label">تقييم التطبيق</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="why-us" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">لماذا نحن؟</span>
                <h2 class="section-title">تجربة تعليمية فريدة</h2>
                <p class="text-muted">نجمع بين الأصالة والتقنية الحديثة لتحقيق أهدافك</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-clock"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">على مدار الساعة</h4>
                        <p class="text-muted">جلسات مباشرة بالصوت أو الفيديو متاحة لك في أي وقت، 24 ساعة يومياً.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-users-rays"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">مختلف الأعمار</h4>
                        <p class="text-muted">مهما كان عمرك أو مستواك، ستجد معلماً متخصصاً ومنهجاً يناسبك.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="why-box">
                        <div class="why-icon"><i class="fa-solid fa-calendar-check"></i></div>
                        <h4 class="fw-bold mb-3 text-dark">خطط مرنة</h4>
                        <p class="text-muted">اختر خطتك ومسارك التعليمي المفضل بلا قيود وبحسب وقت فراغك.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(isset($ads) && $ads->where('status', 'active')->count() > 0)
    <section id="promotions" class="promotions-section">
        <div class="container">
            <div class="section-header text-center mb-2" data-aos="fade-up">
                <span class="section-tag">حصرياً لك</span>
                <h2 class="section-title">أحدث العروض والفعاليات</h2>
            </div>

            <div class="owl-carousel ads-carousel owl-theme" data-aos="fade-up" data-aos-delay="100">
                @foreach($ads->where('status', 'active') as $ad)
                    <div class="item">
                        @if($ad->link)
                            <a href="{{ $ad->link }}" target="_blank" class="ad-banner-link">
                        @else
                            <div class="ad-banner-link" style="cursor: default;">
                        @endif

                            <div class="ad-banner-wrapper">
                                <img src="{{ asset('storage/' . $ad->image) }}" alt="إعلان ترويجي" class="ad-banner-img">

                                @if($ad->link)
                                    <div class="ad-overlay-icon">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </div>
                                @endif
                            </div>

                        @if($ad->link)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section id="about" class="py-5" style="background: var(--primary-light);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-left">
                    <span class="section-tag">من نحن</span>
                    <h2 class="section-title mb-4" style="text-align: right;">عن تطبيق ورتل</h2>
                    <p class="text-muted lead" style="line-height: 1.8;">
                        تطبيق "ورتل" هو منصة تفاعلية رائدة لتعليم القرآن الكريم. نجمع بين أحدث التقنيات وأفضل الكفاءات
                        التعليمية لتمكين المسلمين حول العالم من تلاوة وحفظ كتاب الله.
                    </p>
                    <p class="text-muted">يتيح لك التطبيق القراءة على مقرئين مجازين، وتكرار الآيات، واستخدام أدوات ذكية
                        للمراجعة وتتبع التقدم.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-bullseye about-icon-small"></i>
                                <h5 class="fw-bold text-success">هدفنا</h5>
                                <p class="text-muted m-0 small">تمكين أي شخص، في أي عمر ومكان، من تعلم القرآن بسهولة
                                    وإتقان مع أفضل المعلمين.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="about-card">
                                <i class="fa-solid fa-envelope-open-text about-icon-small"></i>
                                <h5 class="fw-bold text-success">رسالتنا</h5>
                                <p class="text-muted m-0 small">إيصال القرآن إلى قلوب المسلمين عبر تعليم متقن يربط
                                    الأجيال بكلام الله حفظاً وتدبراً.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        <style>

        /* --- Premium Pricing Design --- */
        .pricing-section {
            background-color: #f8fafc;
            padding: 100px 0;
        }

        .premium-pkg-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 40px 30px;
            position: relative;
            border: 1px solid #e2e8f0;
            transition: all 0.4s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .premium-pkg-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-dark);
        }

        /* Featured & VIP Styles */
        .premium-pkg-card.is-featured {
            border: 2px solid var(--primary-dark);
        }

        .premium-pkg-card.is-vip {
            background: linear-gradient(180deg, #ffffff 0%, #fffcf5 100%);
            border-color: #d4a753;
        }

        /* Badge */
        .pkg-badge {
            position: absolute;
            top: 20px;
            right: -35px;
            background: #ef4444;
            color: white;
            padding: 6px 40px;
            font-size: 0.8rem;
            font-weight: 800;
            transform: rotate(45deg);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Header Sections */
        .pkg-top {
            text-align: center;
            margin-bottom: 25px;
        }

        .pkg-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .mins-num {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--primary-dark);
            line-height: 1;
        }

        .mins-unit {
            display: block;
            color: #64748b;
            font-weight: 700;
            font-size: 1rem;
        }

        .pkg-bonus {
            display: inline-block;
            background: #f0fdf4;
            color: #16a34a;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: 10px;
        }

        /* Content & Features */
        .pkg-content {
            flex-grow: 1;
            padding: 20px 0;
        }

        .pkg-features-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pkg-features-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #475569;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }

        .pkg-features-list li i {
            color: #10b981;
            font-size: 1.1rem;
        }

        /* Bottom & Pricing */
        .price-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .price-old {
            display: block;
            text-decoration: line-through;
            color: #94a3b8;
            font-size: 1.1rem;
            margin-bottom: -5px;
        }

        .price-current {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 2px;
            color: #0f172a;
        }

        .price-current .currency {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 10px;
        }

        .price-current .amount {
            font-size: 3rem;
            font-weight: 900;
        }

        /* Button */
        .pkg-action-btn {
            width: 100%;
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 20px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: 0.3s;
            cursor: pointer;
        }

        .pkg-action-btn:hover {
            background: #1a4d2e;
            gap: 20px;
        }

        .is-vip .pkg-action-btn {
            background: #d4a753;
        }
    </style>
    </style>
    <section id="packages" class="pricing-section">
        <div class="container">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <span class="section-tag">باقات الدقائق</span>
                <h2 class="section-title">اختر خطتك التعليمية</h2>
            </div>

            <div class="owl-carousel packages-carousel owl-theme py-4">
                @foreach ($packages as $package)
                    @php
                        $hasDiscount = $package->discount > 0;
                        $isVip = str_contains(strtolower($package->name), 'vip');
                        $originalPrice = $package->egypt_price; // Default base price
                        $currencyCode = 'EGP';
                        if(auth()->check() && auth()->user()->isStudent()) {
                            $studentProfile = auth()->user()->studentProfile;
                            $country = $studentProfile?->country;
                            $userRegion = $country?->region ?? 'foreign';
                            
                            // Check visibility
                            $showProp = 'show_in_' . $userRegion;
                            if (!$package->$showProp) {
                                continue;
                            }

                            $pricingService = app(\App\Services\PricingEngineService::class);
                            $basePriceUsd = $pricingService->getPackagePriceUsd($package, $studentProfile ?? auth()->user()->student);
                            $rate = $country?->rate_to_usd ?? 1;
                            $originalPrice = $basePriceUsd * $rate;
                            $currencyCode = $country?->currency_code ?? 'EGP';
                        }
                        $finalPrice = $hasDiscount ? $originalPrice * (1 - $package->discount / 100) : $originalPrice;
                    @endphp

                    <div class="item px-3" data-aos="{{ $hasDiscount ? 'zoom-in' : 'fade-up' }}">
                        <div
                            class="premium-pkg-card {{ $hasDiscount ? 'is-featured' : '' }} {{ $isVip ? 'is-vip' : '' }}">

                            @if ($hasDiscount)
                                <div class="pkg-badge">خصم {{ round($package->discount) }}%</div>
                            @endif

                            <div class="pkg-top">
                                <h4 class="pkg-title">{{ $package->name }}</h4>
                                <div class="pkg-minutes">
                                    <span class="mins-num">{{ $package->base_minutes }}</span>
                                    <span class="mins-unit">دقيقة</span>
                                    @if ($package->bonus_minutes > 0)
                                        <div class="pkg-bonus">
                                            + {{ $package->bonus_minutes }} دقيقة هدية
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="pkg-content">
                                <ul class="pkg-features-list">
                                    <li>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>صلاحية {{ $package->validity_days }} يوم</span>
                                    </li>
                                    @if ($package->description)
                                        <li>
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>{{ $package->description }}</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <div class="pkg-bottom">
                                <div class="price-box">
                                    @if ($hasDiscount)
                                        <span class="price-old">{{ number_format($originalPrice, 2) }} {{ $currencyCode }}</span>
                                    @endif
                                    <div class="price-current">
                                        <span class="amount">{{ number_format($finalPrice, 2) }} <span class="font-size:14px">{{ $currencyCode }}</span></span>
                                    </div>
                                </div>

                                {{-- <button class="pkg-action-btn">
                                <span>اشترك الآن</span>
                                <i class="fa-solid fa-arrow-left"></i>
                            </button> --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="teachers" class="py-5">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div><span class="section-tag">الكادر التعليمي</span>
                    <h2 class="section-title m-0">نخبة من العلماء</h2>
                </div>
            </div>
            <div class="owl-carousel teachers-carousel owl-theme">
                @foreach ($teachers as $teacher)
                    <div class="item">
                        <div class="teacher-slide">
                            <div class="teacher-card">
                                <div class="teacher-img">
                                    <img src="{{ $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : asset('images/default-avatar.png') }}"
                                        alt="{{ $teacher->user->name ?? 'Sheikh' }}"
                                        onerror="this.src='{{ asset('images/a1.jpg.webp') }}'">
                                </div>
                                <h5 class="fw-bold mb-1">{{ $teacher->user->name ?? 'معلم' }}</h5>
                                <p class="text-muted small">{{ $teacher->tracks->first()->name ?? 'القرآن الكريم' }}
                                </p>
                                <div class="text-warning small"><i class="fa-solid fa-star"></i>
                                    {{ $teacher->average_rating ?? '5.0' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-tag">قصص نجاح</span>
                <h2 class="section-title">ماذا يقول طلابنا؟</h2>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Ahmed+Ali&background=d4a753&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">أحمد علي</h5>
                            <small class="text-muted d-block mb-3">ختم القرآن في سنة</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "تجربة رائعة بكل المقاييس. سهولة في التواصل مع المشايخ، ومرونة في المواعيد ساعدتني
                                كثيراً في الاستمرار بالحفظ رغم انشغالي بالعمل."
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Sara+Mohamed&background=2d8a74&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">سارة محمد</h5>
                            <small class="text-muted d-block mb-3">أم لطالبين</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "تطبيق ممتاز للأطفال! واجهة محببة ومعلمون متخصصون في التعامل التربوي. لاحظت تحسناً
                                كبيراً في مخارج الحروف لدى أبنائي."
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <img src="https://ui-avatars.com/api/?name=Omar+Khaled&background=4fb299&color=fff"
                            class="testimonial-avatar" alt="User">
                        <i class="fa-solid fa-quote-left quote-icon"></i>
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark mb-1">عمر خالد</h5>
                            <small class="text-muted d-block mb-3">مغترب</small>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <p class="text-muted small m-0">
                                "كنت أبحث عن شيخ متقن للقراءات وأنا في الخارج، ووجدته في ورتل. جودة الصوت والصورة ممتازة
                                وكأنني في حلقة حقيقية."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-left">
                    <span class="section-tag">تواصل معنا</span>
                    <h2 class="section-title mb-4">نحن هنا لمساعدتك</h2>
                    <p class="text-muted mb-5">فريق دعم "ورتل" متاح على مدار الساعة للإجابة على استفساراتكم ومقترحاتكم.
                        لا تتردد في مراسلتنا.</p>

                    <div class="contact-info-box">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">البريد الإلكتروني</h6>
                                {{-- Dynamic Email Link --}}
                                <a href="mailto:{{ $contact->email ?? 'info@wartel.app' }}"
                                    class="text-muted text-decoration-none">
                                    {{ $contact->email ?? 'info@wartel.app' }}
                                </a>
                            </div>
                        </div>

                        <div class="contact-item mb-0">
                            <div class="contact-icon">
                                <i class="fa-solid fa-phone-volume"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">خدمة العملاء</h6>

                                {{--
                Dynamic WhatsApp Link
                We use str_replace to remove '+' or spaces for the URL,
                but keep the original format for display.
            --}}
                                @php
                                    $phoneDisplay = $contact->phone ?? '+201110562097';
                                    // Clean phone for URL (remove non-numeric chars)
                                    $phoneUrl = preg_replace('/[^0-9]/', '', $phoneDisplay);
                                @endphp

                                <a href="https://wa.me/{{ $phoneUrl }}?text=مرحبًا%20فريق%20ورتل%2C%20لدي%20استفسار%20حول%20التطبيق"
                                    target="_blank" class="text-muted text-decoration-none" dir="ltr">
                                    +{{ $phoneDisplay }}
                                </a>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-7" data-aos="fade-right">
                    <div class="contact-form">
                        <h4 class="fw-bold mb-4 text-dark">أرسل لنا رسالة</h4>
                        <form id="contactForm">
                            @csrf {{-- Still needed for AJAX headers --}}

                            {{-- Success/Error Message Container --}}
                            <div id="formMessage" class="alert d-none mb-3"></div>

                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control"
                                        placeholder="الاسم الكامل" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" name="email" class="form-control"
                                        placeholder="البريد الإلكتروني" required>
                                </div>
                                <div class="col-12">
                                    <input type="text" name="subject" class="form-control"
                                        placeholder="موضوع الرسالة" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="message" class="form-control" rows="5" placeholder="اكتب رسالتك هنا..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn-submit" id="submitBtn">
                                        <span class="btn-text">إرسال الرسالة</span>
                                        <i class="fa-solid fa-paper-plane ms-2"></i>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script>
        // ADD NO SCROLL ON LOAD
        // document.body.classList.add('loading-locked');

        $(document).ready(function() {
            // Loader Exit Logic
            setTimeout(() => {
                document.getElementById('loader-screen').classList.add('slide-up-exit');
                document.body.classList.remove('loading-locked');
            }, 3000);

            // Init Animations
            AOS.init({
                once: true,
                offset: 50,
                duration: 800
            });

            // Init Teachers Carousel
            $(".teachers-carousel").owlCarousel({
                rtl: true,
                loop: false,
                margin: 0,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                smartSpeed: 800,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 2
                    },
                    1000: {
                        items: 3
                    },
                    1200: {
                        items: 4
                    }
                }
            });

            // Init Ads Carousel
            $(".ads-carousel").owlCarousel({
                rtl: true,
                loop: false,
                margin: 20,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 4000,
                autoplayHoverPause: true,
                smartSpeed: 800,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    992: {
                        items: 3
                    }
                }
            });

            // Init Tracks Carousel
            $(".tracks-carousel").owlCarousel({
                rtl: true,
                loop: false,
                margin: 0,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 3500,
                autoplayHoverPause: true,
                smartSpeed: 800,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    992: {
                        items: 3
                    },
                    1200: {
                        items: 4
                    }
                }
            });

            // Init Packages Carousel
            $(".packages-carousel").owlCarousel({
                rtl: true,
                loop: false,
                margin: 0,
                nav: false,
                dots: true,
                autoplay: true,
                autoplayTimeout: 4500,
                autoplayHoverPause: true,
                smartSpeed: 800,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    992: {
                        items: 3
                    }
                }
            });

            // --- COUNTER ANIMATION LOGIC ---
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200; // The lower the slower

            const animateCounter = (counter) => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(() => animateCounter(counter), 20); // updates every 20ms
                } else {
                    counter.innerText = target;
                    if (target > 1000) counter.innerText += '+';
                }
            };

            const observerOptions = {
                threshold: 0.5
            };
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        animateCounter(counter);
                        observer.unobserve(counter);
                    }
                });
            }, observerOptions);

            counters.forEach(counter => {
                observer.observe(counter);
            });

            // =========================================
            // NAVBAR CUSTOM LOGIC (FIX FOR RESPONSIVE)
            // =========================================
            const $navbarCollapse = $('.navbar-collapse');
            const $body = $('body');

            // 1. Lock/Unlock Scroll when navbar toggles
            $navbarCollapse.on('show.bs.collapse', function() {
                $body.addClass('nav-locked');
            });

            $navbarCollapse.on('hide.bs.collapse', function() {
                $body.removeClass('nav-locked');
            });

            // 2. Close navbar when clicking any link
            $('.navbar-nav .nav-link, .nav-link-teacher, .btn-nav-cta').on('click', function() {
                if ($('.navbar-toggler').is(':visible')) {
                    $navbarCollapse.collapse('hide');
                }
            });

            // 3. Close navbar when clicking outside
            $(document).on('click', function(event) {
                const clickOver = $(event.target);
                const _opened = $navbarCollapse.hasClass('show');
                if (_opened === true && !clickOver.closest('.navbar').length) {
                    $navbarCollapse.collapse('hide');
                }
            });
        });

        // Contact Form AJAX
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('submitBtn');
            const msgBox = document.getElementById('formMessage');
            const spinner = btn.querySelector('.spinner-border');

            btn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            msgBox.classList.add('d-none');

            const formData = new FormData(form);

            fetch("{{ route('contact.send') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    msgBox.classList.remove('d-none', 'alert-success', 'alert-danger');

                    if (data.status === 'success') {
                        msgBox.classList.add('alert-success');
                        msgBox.innerText = data.message;
                        form.reset();
                    } else if (data.errors) {
                        msgBox.classList.add('alert-danger');
                        msgBox.innerText = Object.values(data.errors)[0][0];
                    } else {
                        msgBox.classList.add('alert-danger');
                        msgBox.innerText = data.message || 'حدث خطأ غير متوقع.';
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    msgBox.classList.remove('d-none', 'alert-success');
                    msgBox.classList.add('alert-danger');
                    msgBox.innerText = 'فشل الاتصال بالخادم.';
                });
        });
    </script>
</body>

</html>
