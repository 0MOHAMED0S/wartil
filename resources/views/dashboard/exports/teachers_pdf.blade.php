<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>قائمة المعلمين</title>
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
        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
        }
        .text-success { color: #15803d; }
        .text-warning { color: #a16207; }
        .text-danger { color: #b91c1c; }
    </style>
</head>
<body>

    <h2>سجل المعلمين - ورتل</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المعلم</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>الجنسية</th>
                <th>الإقامة</th>
                <th>الجنس</th>
                <th>الحالة</th>
                <th>تاريخ الانضمام</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $index => $teacher)
            @php
                $userName = optional(optional($teacher->profile)->user)->name ?? $teacher->name;
                $userEmail = optional(optional($teacher->profile)->user)->email ?? $teacher->email;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $userName }}</td>
                <td>{{ $userEmail }}</td>
                <td dir="ltr">{{ $teacher->phone }}</td>
                <td>{{ $teacher->origin_country ?? 'غير محدد' }}</td>
                <td>{{ $teacher->residence_location ?? 'غير محدد' }}</td>
                <td>
                    @if($teacher->gender == 'male') ذكر @elseif($teacher->gender == 'female') أنثى @else غير محدد @endif
                </td>
                <td>
                    @if($teacher->status == 'approved')
                        <span class="text-success">مقبول</span>
                    @elseif($teacher->status == 'pending')
                        <span class="text-warning">قيد المراجعة</span>
                    @else
                        <span class="text-danger">مرفوض</span>
                    @endif
                </td>
                <td>{{ $teacher->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
