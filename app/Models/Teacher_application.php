<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher_application extends Model
{
    protected $fillable = [
        'full_name',
        'gender',
        'email',
        'phone',
        'origin_country',
        'residence_location',
        'qualification',
        'languages',
        'experience_years',
        'work_hours',
        'online_experience',
        'internet_quality',
        'tech_skills',
        'ijazas_text',
        'cv_pdf_path',
        'status',
        'profile_photo_path'
    ];

    protected $casts = [
        'languages'   => 'array',
    ];
        public function tracks()
    {
        return $this->belongsToMany(Track::class, 'teacher_application_tracks');
    }
    public function profile()
    {
        return $this->hasOne(Teacher::class, 'teacher_application_id');
    }
    // 1. Hide 'full_name' and append 'name' so JSON returns "name" instead of "full_name"
    protected $hidden = ['full_name'];
    protected $appends = ['name'];

    // 2. Map 'name' to 'full_name'
    public function getNameAttribute()
    {
        return $this->attributes['full_name'] ?? null;
    }

    // 3. Return full URL for profile_photo_path (and cv_pdf_path if needed)
    public function getProfilePhotoPathAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }

    public function getCvPdfPathAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return asset('storage/' . ltrim($value, '/'));
    }
    
    public function toArray()
    {
        $profile = $this->profile;
        if ($profile) {
            $profile->loadMissing(['user', 'ratings.user']);
            $profile->loadCount(['ratings']);
            $profile->loadAvg('ratings', 'rating');
        }

        $user = $profile ? $profile->user : null;

        // Build full photo_url
        $rawPhoto = ($profile && $profile->profile_photo_path)
            ? $profile->profile_photo_path
            : $this->getRawOriginal('profile_photo_path');

        $photoUrl = null;
        if ($rawPhoto) {
            $photoUrl = filter_var($rawPhoto, FILTER_VALIDATE_URL)
                ? $rawPhoto
                : asset('storage/' . ltrim($rawPhoto, '/'));
        }

        // Counts from DB based on teacher profile id
        $teacherId = $profile ? $profile->id : null;

        $studentsCount = $teacherId
            ? DB::table('call_sessions')
                ->where('teacher_id', $teacherId)
                ->whereNotNull('student_id')
                ->distinct('student_id')
                ->count('student_id')
            : 0;

        $callsCount = $teacherId
            ? DB::table('call_sessions')->where('teacher_id', $teacherId)->count()
            : 0;

        $slotsCount = ($teacherId && \Illuminate\Support\Facades\Schema::hasTable('slots'))
            ? DB::table('slots')->where('teacher_id', $teacherId)->count()
            : (($teacherId && \Illuminate\Support\Facades\Schema::hasTable('teacher_slots'))
                ? DB::table('teacher_slots')->where('teacher_id', $teacherId)->count()
                : 0);

        $sessionsCount = ($teacherId && \Illuminate\Support\Facades\Schema::hasTable('sessions'))
            ? DB::table('sessions')->where('teacher_id', $teacherId)->count()
            : 0;

        $avgRating = $profile ? round((float) ($profile->ratings_avg_rating ?? $profile->average_rating ?? 0), 1) : 0;
        $reviewsCount = $profile ? (int) ($profile->ratings_count ?? $profile->ratings->count()) : 0;

        if ($profile) {
            $profile->average_rating = $avgRating;
        }

        $tracks = $this->relationLoaded('tracks') ? $this->tracks : $this->tracks()->get();

        return [
            'id'               => $profile ? $profile->id : $this->id,
            'application_id'   => $this->id,
            'name'             => $this->full_name ?? ($user ? $user->name : null),
            'photo_url'        => $photoUrl,
            'is_online'        => $profile ? (bool) $profile->is_online : false,
            'rating'           => $avgRating,
            'reviews_count'    => $reviewsCount,
            'students_count'   => $studentsCount,
            'calls_count'      => $callsCount,
            'slots_count'      => $slotsCount,
            'sessions_count'   => $sessionsCount,
            'qualification'    => $this->qualification,
            'country'          => $this->origin_country,
            'languages'        => $this->languages ?? [],
            'experience_years' => (int) $this->experience_years,
            'specialties'      => $tracks->map(function ($track) {
                return [
                    'id'   => $track->id,
                    'name' => $track->name,
                ];
            })->values()->all(),
            'about'            => $this->ijazas_text,
            'reviews_details'  => ($profile && $profile->ratings)
                ? $profile->ratings->sortByDesc('id')->map(function ($rating) {
                    return [
                        'id'           => $rating->id,
                        'student_name' => optional($rating->user)->name,
                        'rating'       => (int) $rating->rating,
                        'comment'      => $rating->comment,
                        'date'         => optional($rating->created_at)->format('Y-m-d'),
                    ];
                })->values()->all()
                : [],
            'user_data'        => $user ? $user->toArray() : null,
            'profile_data'     => $profile ? $profile->toArray() : null,
        ];
    }
}
