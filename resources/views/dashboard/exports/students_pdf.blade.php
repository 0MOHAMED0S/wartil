<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>قائمة الطلاب</title>
    <style>
        body {
            font-family: 'cairo', sans-serif;
            font-size: 12px;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #2d8a74;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #dddddd;
        }
        th, td {
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>سجل الطلاب - ورتل</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المعرف (ID)</th>
                <th>اسم الطالب</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>النوع</th>
                <th>البلد</th>
                <th>الرصيد المتبقي (دقائق)</th>
                <th>تاريخ التسجيل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            @php
                $gender = ($student->student->gender ?? '') == 'male' ? 'ذكر' : 'أنثى';
                $remainingMinutes = collect($student->packages)->where('status', 'active')->sum('remaining_minutes') ?? 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->id }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td dir="ltr">{{ $student->student->phone ?? '' }}</td>
                <td>{{ $gender }}</td>
                <td>{{ optional($student->student->country)->name ?? '-' }}</td>
                <td>{{ $remainingMinutes }}</td>
                <td>{{ $student->created_at ? $student->created_at->format('Y-m-d') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
