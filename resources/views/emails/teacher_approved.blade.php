<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم قبول طلبك - ورتل</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        body {
            margin: 0; padding: 0; background-color: #f3f4f6;
            font-family: 'Cairo', sans-serif; color: #2d3436;
        }
        .email-wrapper { width: 100%; background-color: #f3f4f6; padding: 40px 0; }
        .email-container {
            max-width: 600px; margin: 0 auto; background-color: #ffffff;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;
        }
        .email-header {
            background-color: #ffffff; padding: 30px 0; text-align: center;
            border-bottom: 4px solid #1a4d2e;
        }
        .logo {
            font-size: 28px; font-weight: 800; color: #1a4d2e;
            text-decoration: none; letter-spacing: -1px;
        }
        .email-body { padding: 40px 30px; text-align: center; }
        h1 { color: #1a4d2e; font-size: 22px; margin: 0 0 16px; font-weight: 700; }
        p { color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px; }

        /* Credential Box */
        .info-box {
            background-color: #f8f9fa; border: 1px solid #eee;
            border-radius: 12px; padding: 20px; margin: 20px 0;
            text-align: right;
        }
        .info-item { margin-bottom: 12px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 8px; }
        .info-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .label { font-weight: 700; color: #1a4d2e; display: block; font-size: 14px; margin-bottom: 4px; }
        .value { font-size: 16px; color: #333; font-family: 'Courier New', monospace; direction: ltr; display: inline-block; }
        .salary-val { color: #198754; font-weight: 700; font-family: 'Cairo', sans-serif; }

        .btn {
            display: inline-block; background-color: #1a4d2e; color: #ffffff;
            text-decoration: none; padding: 12px 30px; border-radius: 50px;
            font-weight: 700; margin-top: 20px; transition: 0.3s;
        }
        .btn:hover { background-color: #143d24; }

        .email-footer {
            background-color: #fafafa; padding: 20px; text-align: center;
            font-size: 13px; color: #9ca3af; border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <a href="#" class="logo">ورتـــل</a>
            </div>
            <div class="email-body">
                <h1>أهلاً بك يا {{ $user->name }}! 🎉</h1>
                <p>يسعدنا إبلاغك بأنه قد تم قبول طلب انضمامك إلى فريق معلمي منصة ورتل.<br>لقد تم إنشاء حسابك بنجاح، وفيما يلي بيانات الدخول الخاصة بك وتفاصيل الفئة التي تم تعيينك بها.</p>

                <div class="info-box">
                    <div class="info-item">
                        <span class="label">📧 البريد الإلكتروني (اسم المستخدم)</span>
                        <span class="value">{{ $user->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">🔑 كلمة المرور</span>
                        <span class="value">{{ $password }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">⭐ الفئة التعليمية</span>
                        <span class="value salary-val">{{ $categoryName }}</span>
                    </div>
                </div>

                <p>يمكنك الآن تسجيل الدخول والبدء في رحلتك التعليمية معنا.</p>
            </div>
            <div class="email-footer">
                &copy; {{ date('Y') }} منصة ورتل. جميع الحقوق محفوظة.
            </div>
        </div>
    </div>
</body>
</html>
