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
    
    
}
