<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>قائمة المعلمين</title>
    <style>
        body {
            font-family: 'cairo', sans-serif;
            font-size: 10px;
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
            padding: 4px 6px;
            text-align: right;
            vertical-align: top;
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
                <th>م</th>
                <th>الاسم والاتصال</th>
                <th>الإقامة والجنسية</th>
                <th>المؤهلات والخبرات</th>
                <th>المسارات واللغات</th>
                <th>معلومات إضافية</th>
                <th>تفاصيل الحساب</th>
                <th>الحالة</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $index => $teacher)
            @php
                $userName = optional(optional($teacher->profile)->user)->name ?? $teacher->full_name;
                $userEmail = optional(optional($teacher->profile)->user)->email ?? $teacher->email;
                $langsRaw = $teacher->languages;
                $langs = is_array($langsRaw) || is_object($langsRaw) ? (array) $langsRaw : (json_decode($langsRaw, true) ?? []);
                $languagesStr = count($langs) > 0 ? implode('، ', $langs) : 'العربية';
                $teacherCategory = optional(optional($teacher->profile)->category)->name ?? '-';
                $tracks = $teacher->tracks && $teacher->tracks->count() > 0 ? $teacher->tracks->pluck('name')->implode('، ') : '-';
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <b>{{ $userName }}</b><br>
                    <span style="color: #666; font-size: 9px;">{{ $userEmail }}</span><br>
                    <span dir="ltr" style="font-size: 9px;">{{ $teacher->phone }}</span>
                </td>
                <td>
                    <b>ج:</b> {{ $teacher->origin_country ?? '-' }}<br>
                    <b>إ:</b> {{ $teacher->residence_location ?? '-' }}
                </td>
                <td>
                    <b>المؤهل:</b> {{ $teacher->qualification ?? '-' }}<br>
                    <b>الخبرة:</b> {{ $teacher->experience_years ? $teacher->experience_years . ' سنوات' : '-' }}<br>
                    <b>الإجازات:</b> {{ $teacher->ijazas_text ? \Illuminate\Support\Str::limit($teacher->ijazas_text, 30) : '-' }}
                </td>
                <td>
                    <b>المسارات:</b> {{ $tracks }}<br>
                    <b>اللغات:</b> {{ $languagesStr }}
                </td>
                <td>
                    <b>عمل:</b> {{ $teacher->work_hours ?? 0 }} س/يوم<br>
                    <b>نت:</b> {{ $teacher->internet_quality ?? '-' }}<br>
                    <b>تقنية:</b> {{ $teacher->tech_skills ?? '-' }}
                </td>
                <td>
                    <b>الفئة:</b> {{ $teacherCategory }}<br>
                    <b>الدقائق:</b> {{ optional($teacher->profile)->minutes ?? 0 }}
                </td>
                <td>
                    @if($teacher->status == 'approved')
                        <span class="text-success">مقبول</span>
                    @elseif($teacher->status == 'pending')
                        <span class="text-warning">مراجعة</span>
                    @else
                        <span class="text-danger">مرفوض/معطل</span>
                    @endif
                </td>
                <td>{{ $teacher->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
