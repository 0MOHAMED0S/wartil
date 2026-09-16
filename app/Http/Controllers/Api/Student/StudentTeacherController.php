<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Teacher_application;
use App\Models\TeacherSlot;
use App\Models\UserPackage;
use App\Models\CallSession;
use App\Models\SlotBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class StudentTeacherController extends Controller
{
    private function getTeacherStats($teacherId)
    {
        $stats = [
            'students_count' => 0,
            'calls_count'    => 0,
            'slots_count'    => 0,
            'sessions_count' => 0,
        ];

        if (!$teacherId) return $stats;

        try {
            $callStudents = DB::table('call_sessions')
                ->where('teacher_id', $teacherId)
                ->where('status', 'ended')
                ->pluck('student_id')
                ->toArray();

            $stats['calls_count'] = count($callStudents);

            $slotStudents = DB::table('slot_bookings')
                ->join('teacher_slots', 'slot_bookings.teacher_slot_id', '=', 'teacher_slots.id')
                ->where('teacher_slots.teacher_id', $teacherId)
                ->where('slot_bookings.status', '!=', 'cancelled')
                ->pluck('slot_bookings.user_id')
                ->toArray();

            $stats['slots_count'] = count($slotStudents);
            $sessionStudents = [];
            try {
                $sessionStudents = DB::table('sessions')
                    ->where('teacher_id', $teacherId)
                    ->pluck('student_id')
                    ->toArray();

                $stats['sessions_count'] = count($sessionStudents);
            } catch (Exception $e) {
                $stats['sessions_count'] = 0;
            }

            $allUniqueStudents = array_unique(array_merge($callStudents, $slotStudents, $sessionStudents));
            $stats['students_count'] = count($allUniqueStudents);
        } catch (Exception $e) {
            Log::error("Stats Error for Teacher {$teacherId}: " . $e->getMessage());
        }

        return $stats;
    }
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $user = auth()->user();
            $student = $user ? $user->student : null;
            $query = Teacher_application::where('status', 'approved');
            if ($student) {
                $age = (int) $student->age_group;
                $studentGender = strtolower($student->gender);
                if ($age > 0 && $age <= 10) {
                } else {
                    if ($studentGender === 'female' || $studentGender === 'girl') {
                        $query->where('gender', 'female');
                    } elseif ($studentGender === 'male' || $studentGender === 'boy') {
                        $query->where('gender', 'male');
                    }
                }
            }
            $teachersPaginator = $query->with([
                'profile' => function ($query) {
                    $query->withAvg('ratings', 'rating')
                        ->withCount('ratings')
                        ->with('ratings.user');
                },
                'profile.user',
                'tracks'
            ])
                ->latest()
                ->paginate($perPage);
            $teachersPaginator->getCollection()->transform(function ($application) {
                $profile = $application->profile;
                $user = optional($profile)->user;
                $teacherId = $profile->id ?? null;

                $name = $user->name ?? $application->full_name;
                $photoPath = optional($profile)->profile_photo_path ?? $application->profile_photo_path;

                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                $stats = $this->getTeacherStats($teacherId);

                return [
                    'id'               => $teacherId,
                    'application_id'   => $application->id,
                    'name'             => $name,
                    'photo_url'        => $photoUrl,
                    'is_online'        => (bool) ($profile->is_online ?? false),

                    'rating'           => (float) number_format(optional($profile)->ratings_avg_rating ?? 5.0, 1, '.', ''),
                    'reviews_count'    => (int) (optional($profile)->ratings_count ?? 0),

                    'students_count'   => $stats['students_count'],
                    'calls_count'      => $stats['calls_count'],
                    'slots_count'      => $stats['slots_count'],
                    'sessions_count'   => $stats['sessions_count'],

                    'qualification'    => $application->qualification,
                    'country'          => $application->origin_country,
                    'languages'        => $application->languages,
                    'experience_years' => $application->experience_years,
                    'specialties'      => $application->tracks->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
                    'about'            => $application->ijazas_text,

                    'reviews_details'  => optional($profile)->ratings ? $profile->ratings->map(function ($rate) {
                        return [
                            'id'           => $rate->id,
                            'student_name' => optional($rate->user)->name ?? 'طالب مجهول',
                            'rating'       => (float) $rate->rating,
                            'comment'      => $rate->comment,
                            'date'         => $rate->created_at ? $rate->created_at->format('Y-m-d') : null,
                        ];
                    })->sortByDesc('id')->values() : [],

                    'user_data'        => $user,
                    'profile_data'     => $profile,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Teachers retrieved successfully.',
                'data'    => [
                    'teachers'   => $teachersPaginator->items(),
                    'pagination' => [
                        'total'        => $teachersPaginator->total(),
                        'per_page'     => (int) $teachersPaginator->perPage(),
                        'current_page' => $teachersPaginator->currentPage(),
                        'total_pages'  => $teachersPaginator->lastPage(),
                    ]
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Index Teachers Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error fetching teachers.'], 500);
        }
    }
    public function show($id)
    {
        try {
            $teacher = Teacher::with(['user', 'application.tracks', 'ratings.user'])
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->find($id);

            if (!$teacher) {
                return response()->json(['status' => false, 'message' => 'Teacher not found.'], 404);
            }

            $application = $teacher->application;
            $stats = $this->getTeacherStats($teacher->id);

            return response()->json([
                'status'  => true,
                'message' => 'Teacher profile retrieved successfully.',
                'data'    => [
                    'id'               => $teacher->id,
                    'application_id'   => $teacher->teacher_application_id,
                    'name'             => $teacher->user->name ?? $application->full_name,
                    'photo_url'        => $teacher->profile_photo_path ? asset('storage/' . $teacher->profile_photo_path) : null,
                    'is_online'        => (bool) $teacher->is_online,
                    'rating'           => (float) number_format($teacher->ratings_avg_rating ?? 5.0, 1, '.', ''),
                    'reviews_count'    => (int) ($teacher->ratings_count ?? 0),

                    'students_count'   => $stats['students_count'],
                    'calls_count'      => $stats['calls_count'],
                    'slots_count'      => $stats['slots_count'],
                    'sessions_count'   => $stats['sessions_count'],

                    'qualification'    => $application->qualification ?? null,
                    'country'          => $application->origin_country ?? null,
                    'languages'        => $application->languages ?? [],
                    'experience_years' => $application->experience_years ?? 0,

                    'gender'             => $application->gender ?? null,
                    'email'              => $application->email ?? null,
                    'phone'              => $application->phone ?? null,
                    'residence_location' => $application->residence_location ?? null,
                    'work_hours'         => $application->work_hours ?? null,
                    'online_experience'  => $application->online_experience ?? null,
                    'internet_quality'   => $application->internet_quality ?? null,
                    'tech_skills'        => $application->tech_skills ?? null,
                    'cv_pdf_url'         => $application->cv_pdf_path ? asset('storage/' . $application->cv_pdf_path) : null,
                    'application_status' => $application->status ?? null,
                    'ijazas_text' => $application->ijazas_text ?? null,

                    'specialties'      => collect($application->tracks ?? [])->map(fn($t) => [
                        'id'   => $t->id,
                        'name' => $t->name
                    ])->values(),
                    'about'            => $application->ijazas_text ?? null,

                    'reviews_details'  => $teacher->ratings->map(fn($rate) => [
                        'id'           => $rate->id,
                        'student_name' => optional($rate->user)->name ?? 'طالب مجهول',
                        'rating'       => (int) $rate->rating,
                        'comment'      => $rate->comment,
                        'date'         => $rate->created_at->format('Y-m-d'),
                    ])->sortByDesc('id')->values(),

                    'user_data'        => $teacher->user,
                    'profile_data'     => $teacher->makeHidden(['application', 'user', 'ratings'])
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Show Teacher Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error fetching profile.'], 500);
        }
    }
    public function getTeacherAvailableSlots(Request $request, $teacherId)
    {
        try {
            $perPage = $request->get('per_page', 20);

            $paginator = TeacherSlot::where('teacher_id', $teacherId)
                ->where('is_booked', false)
                ->where(function ($query) {
                    $query->where('date', '>', now()->toDateString())
                        ->orWhere(function ($q) {
                            $q->where('date', now()->toDateString())
                                ->where('start_time', '>', now()->format('H:i:s'));
                        });
                })
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->paginate($perPage);

            return response()->json([
                'status'  => true,
                'data'    => [
                    'calendar'   => $paginator->getCollection()->groupBy('date'),
                    'pagination' => [
                        'total'        => $paginator->total(),
                        'current_page' => $paginator->currentPage(),
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error fetching slots.'], 500);
        }
    }
    public function bookSlot(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:teacher_slots,id'
        ], [
            'slot_id.required' => 'يجب اختيار موعد للحجز.',
            'slot_id.exists'   => 'الموعد المختار غير موجود.'
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $slot = TeacherSlot::where('id', $request->slot_id)->lockForUpdate()->first();

            if ($slot->is_booked) {
                return response()->json(['status' => false, 'message' => 'عذراً، هذا الموعد تم حجزه بالفعل.'], 400);
            }

            $slotStart = Carbon::parse($slot->date . ' ' . $slot->start_time);
            $slotEnd   = Carbon::parse($slot->date . ' ' . $slot->end_time);

            if ($slotStart->isPast()) {
                return response()->json(['status' => false, 'message' => 'لا يمكن حجز مواعد قديمة.'], 400);
            }

            $duration = $slotStart->diffInMinutes($slotEnd);

            $ownerId = $user->accountOwner()->id;
            $userIds = array_unique([$user->id, $ownerId]);
            
            $activePackages = UserPackage::whereIn('user_id', $userIds)
                ->whereIn('status', ['active', 'Active'])
                ->where('remaining_minutes', '>', 0)
                ->where(fn($q) => $q->where('expires_at', '>', now())->orWhereNull('expires_at'))
                ->orderByRaw("CASE WHEN user_id = {$user->id} THEN 1 ELSE 2 END")
                ->orderByRaw('expires_at IS NULL ASC, expires_at ASC')
                ->lockForUpdate()
                ->get();

            if ($activePackages->sum('remaining_minutes') < $duration) {
                return response()->json(['status' => false, 'message' => 'رصيد دقائقك غير كافٍ لحجز هذا الموعد.'], 400);
            }

            $remainingToDeduct = $duration;
            foreach ($activePackages as $package) {
                if ($remainingToDeduct <= 0) break;

                $deduction = min($package->remaining_minutes, $remainingToDeduct);
                $package->remaining_minutes -= $deduction;
                $remainingToDeduct -= $deduction;

                if ($package->remaining_minutes <= 0) {
                    $package->status = 'exhausted';
                }
                $package->save();
            }

            $slot->update(['is_booked' => true]);
            $channelName = 'scheduled_' . $user->id . '_' . time();

            $booking = SlotBooking::create([
                'user_id'          => $user->id,
                'teacher_slot_id'  => $slot->id,
                'deducted_minutes' => $duration,
                'status'           => 'scheduled',
                'channel_name'     => $channelName,
                'started_at'       => $slotStart
            ]);

            DB::commit();

            try {
                $teacher = $slot->teacher;
                if ($teacher && $teacher->user) {
                    $notificationData = [
                        'booking_id'   => $booking->id,
                        'student_name' => $user->name,
                        'date'         => $slot->date,
                        'start_time'   => $slot->start_time,
                        'duration'     => $duration
                    ];

                    broadcast(new \App\Events\NewSlotBooked($teacher->id, $notificationData));

                    $timeFormatted = \Carbon\Carbon::parse($slot->start_time)->format('h:i A');
                    $teacher->user->notify(new \App\Notifications\DynamicNotification(
                        'حجز موعد جديد 📅',
                        "قام الطالب {$user->name} بحجز موعد يوم {$slot->date} الساعة {$timeFormatted}.",
                        'new_booking',
                        $notificationData
                    ));
                }
            } catch (Exception $e) {
                Log::error('Teacher Booking Notification Error: ' . $e->getMessage());
            }
            $callMock = [
                'id'           => $booking->id,
                'student_id'   => $user->id,
                'teacher_id'   => $slot->teacher_id,
                'channel_name' => $channelName,
                'status'       => 'initiated',
                'started_at'   => $slotStart,
            ];

            return response()->json([
                'status'  => true,
                'message' => "تم الحجز بنجاح. تم خصم $duration دقيقة من رصيدك.",
                'data'    => ['booking' => $booking, 'call' => $callMock]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Booking Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'فشلت عملية الحجز، يرجى المحاولة مرة أخرى.'], 500);
        }
    }
    public function cancelBookingByStudent(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:teacher_slots,id'
        ], [
            'slot_id.required' => 'يجب تحديد الموعد المراد إلغاؤه.',
            'slot_id.exists'   => 'الموعد المحدد غير موجود في سجلاتنا.'
        ]);

        $studentId = auth()->id();
        $studentName = auth()->user()->name;
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $booking = SlotBooking::where('teacher_slot_id', $request->slot_id)
                ->where('user_id', $studentId)
                ->where('status', 'scheduled')
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                return response()->json(['status' => false, 'message' => 'عذراً، هذا الحجز غير موجود أو تم إلغاؤه مسبقاً.'], 404);
            }

            $slot = TeacherSlot::where('id', $request->slot_id)->lockForUpdate()->first();
            $slotStartDateTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
            $diffInMinutes = $now->diffInMinutes($slotStartDateTime, false);

            if ($diffInMinutes < 90) {
                return response()->json([
                    'status'  => false,
                    'message' => 'عذراً، لا يمكن إلغاء الموعد قبل بدايته بأقل من ساعة ونصف حسب سياسة المنصة.'
                ], 400);
            }

            $refundMinutes = $booking->deducted_minutes;
            if ($refundMinutes > 0) {
                $studentUser = \App\Models\User::find($studentId);
                $ownerId = $studentUser ? $studentUser->accountOwner()->id : $studentId;
                $userIds = array_unique([$studentId, $ownerId]);

                $packageToRefund = UserPackage::whereIn('user_id', $userIds)
                    ->whereIn('status', ['active', 'Active'])
                    ->orderByRaw("CASE WHEN user_id = {$studentId} THEN 1 ELSE 2 END")
                    ->orderBy('id', 'desc')
                    ->first();

                if ($packageToRefund) {
                    $packageToRefund->increment('remaining_minutes', $refundMinutes);
                } else {
                    $lastPackage = UserPackage::whereIn('user_id', $userIds)
                        ->orderByRaw("CASE WHEN user_id = {$studentId} THEN 1 ELSE 2 END")
                        ->latest()
                        ->first();
                    if ($lastPackage) {
                        $lastPackage->update([
                            'remaining_minutes' => $lastPackage->remaining_minutes + $refundMinutes,
                            'status' => 'active'
                        ]);
                    }
                }
            }

            $booking->update(['status' => 'cancelled']);
            $slot->update(['is_booked' => false]);

            DB::commit();

            try {
                $teacher = $slot->teacher;
                if ($teacher && $teacher->user) {
                    $notificationData = [
                        'slot_id'      => $slot->id,
                        'student_name' => $studentName,
                        'date'         => $slot->date,
                        'start_time'   => $slot->start_time,
                    ];

                    broadcast(new \App\Events\SlotBookingCancelled($teacher->id, $notificationData));

                    $timeFormatted = Carbon::parse($slot->start_time)->format('h:i A');
                    $teacher->user->notify(new \App\Notifications\DynamicNotification(
                        'إلغاء حجز من قبل طالب ❌',
                        "قام الطالب {$studentName} بإلغاء موعده ليوم {$slot->date} الساعة {$timeFormatted}.",
                        'booking_cancelled',
                        $notificationData
                    ));
                }
            } catch (Exception $e) {
                Log::error('Teacher Cancel Booking Notification Error: ' . $e->getMessage());
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم إلغاء الموعد بنجاح، وتمت إعادة الدقائق إلى رصيدك.'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student Cancel Booking Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ تقني أثناء محاولة إلغاء الحجز، يرجى المحاولة لاحقاً.'], 500);
        }
    }
    public function featuredTeachers(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 5);
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $user = auth()->user();
            $student = $user ? $user->student : null;
            $teachersQuery = Teacher_application::where('status', 'approved')
                ->whereHas('profile')
                ->with([
                    'profile' => function ($query) {
                        $query->withAvg('ratings', 'rating')
                            ->withCount('ratings')
                            ->with('ratings.user');
                    },
                    'profile.user',
                    'tracks'
                ]);
            if ($student) {
                $age = (int) $student->age_group;
                $studentGender = strtolower($student->gender);
                if (!($age > 0 && $age <= 10)) {
                    if ($studentGender === 'female' || $studentGender === 'girl') {
                        $teachersQuery->where('gender', 'female');
                    } elseif ($studentGender === 'male' || $studentGender === 'boy') {
                        $teachersQuery->where('gender', 'male');
                    }
                }
            }
            $allTeachers = $teachersQuery->get()
                ->sortByDesc(function ($application) {
                    return optional($application->profile)->ratings_avg_rating ?? $application->experience_years ?? 0;
                })
                ->values();
            $total = $allTeachers->count();
            $paginatedItems = $allTeachers->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $formattedTeachers = $paginatedItems->map(function ($application) {
                $profile = $application->profile;
                $user = optional($profile)->user;
                $teacherId = optional($profile)->id;

                $name = optional($user)->name ?? $application->full_name;
                $photoPath = optional($profile)->profile_photo_path ?? $application->profile_photo_path;

                $photoUrl = $photoPath
                    ? asset('storage/' . $photoPath)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1a4d2e&color=fff&size=128';

                $stats = $this->getTeacherStats($teacherId);
                $rating = (float) number_format(optional($profile)->ratings_avg_rating ?? 5.0, 1, '.', '');

                return [
                    'id'               => $teacherId,
                    'application_id'   => $application->id,
                    'name'             => $name,
                    'photo_url'        => $photoUrl,
                    'is_online'        => (bool) optional($profile)->is_online,

                    'rating'           => $rating,
                    'reviews_count'    => (int) (optional($profile)->ratings_count ?? 0),

                    'students_count'   => $stats['students_count'],
                    'calls_count'      => $stats['calls_count'],
                    'slots_count'      => $stats['slots_count'],
                    'sessions_count'   => $stats['sessions_count'],

                    'qualification'    => $application->qualification,
                    'country'          => $application->origin_country,
                    'languages'        => $application->languages,
                    'experience_years' => $application->experience_years,
                    'specialties'      => $application->tracks->map(function ($track) {
                        return [
                            'id'   => $track->id,
                            'name' => $track->name,
                        ];
                    }),
                    'about'            => $application->ijazas_text,
                    'reviews_details'  => optional($profile)->ratings ? $profile->ratings->map(function ($rate) {
                        return [
                            'id'           => $rate->id,
                            'student_name' => optional($rate->user)->name ?? 'طالب مجهول',
                            'rating'       => (float) $rate->rating,
                            'comment'      => $rate->comment,
                            'date'         => $rate->created_at ? $rate->created_at->format('Y-m-d') : null,
                        ];
                    })->sortByDesc('id')->take(3)->values() : [],

                    'user_data'        => $user,
                    'profile_data'     => $profile ? array_merge($profile->toArray(), [
                        'average_rating' => $rating
                    ]) : null,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع قائمة المعلمين بنجاح.',
                'data'    => [
                    'teachers'   => $formattedTeachers,
                    'pagination' => [
                        'total'        => $total,
                        'per_page'     => (int) $perPage,
                        'current_page' => $currentPage,
                        'total_pages'  => (int) ceil($total / $perPage) ?: 1
                    ]
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Featured Teachers Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ تقني أثناء استرجاع بيانات المعلمين المتميزين، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
    public function getTeachersByTrack(Request $request, $trackId)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $user = auth()->user();
            $student = $user ? $user->student : null;
            $teachersQuery = Teacher_application::where('status', 'approved')
                ->whereHas('tracks', function ($query) use ($trackId) {
                    $query->where('tracks.id', $trackId);
                })
                ->whereHas('profile');
            if ($student) {
                $age = (int) $student->age_group;
                $studentGender = strtolower($student->gender);
                if (!($age > 0 && $age <= 10)) {
                    if ($studentGender === 'female' || $studentGender === 'girl') {
                        $teachersQuery->where('gender', 'female');
                    } elseif ($studentGender === 'male' || $studentGender === 'boy') {
                        $teachersQuery->where('gender', 'male');
                    }
                }
            }
            $allTeachers = $teachersQuery->with([
                'profile' => function ($query) {
                    $query->withAvg('ratings', 'rating')
                        ->withCount('ratings')
                        ->with('ratings.user');
                },
                'profile.user',
                'tracks'
            ])
                ->get()
                ->sortByDesc(function ($application) {
                    return optional($application->profile)->ratings_avg_rating ?? 0;
                })
                ->values();

            $total = $allTeachers->count();
            $paginatedItems = $allTeachers->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $formattedTeachers = $paginatedItems->map(function ($application) {
                $profile = $application->profile;
                $user = optional($profile)->user;
                $teacherId = optional($profile)->id;
                $stats = $this->getTeacherStats($teacherId);
                $rating = (float) number_format(optional($profile)->ratings_avg_rating ?? 5.0, 1, '.', '');

                return [
                    'id'               => $teacherId,
                    'name'             => $user->name ?? $application->full_name,
                    'photo_url'        => $profile->profile_photo_path ? asset('storage/' . $profile->profile_photo_path) : null,
                    'is_online'        => (bool) optional($profile)->is_online,
                    'rating'           => $rating,
                    'reviews_count'    => (int) (optional($profile)->ratings_count ?? 0),
                    'country'          => $application->origin_country,
                    'experience_years' => $application->experience_years,
                    'specialties'      => $application->tracks->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم جلب المعلمين في هذا المسار بنجاح.',
                'data'    => [
                    'teachers'   => $formattedTeachers,
                    'pagination' => [
                        'total'        => $total,
                        'per_page'     => (int) $perPage,
                        'current_page' => $currentPage,
                        'total_pages'  => (int) ceil($total / $perPage) ?: 1
                    ]
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Filter Teachers by Track Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تصفية المعلمين حسب المسار.'
            ], 500);
        }
    }
}
