<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $appends = ['age'];

    protected $fillable = [
        'user_id',
        'country_id',
        'phone',
        'address',
        'qualification',
        'professional_status',
        'gender',
        'profile_photo_path',

        'birth_date',
        'reading_level',
        'preferred_teacher_language',
        'reading_track',
        'memorized_amount',
        'plan_name',
        'reading_type',
        'teacher_response_speed',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function favorites()
    {
        return $this->belongsToMany(Teacher_application::class, 'favorites', 'student_id', 'teacher_id')
            ->withTimestamps();
    }

    public function getAgeAttribute()
    {
        if ($this->birth_date) {
            return \Carbon\Carbon::parse($this->birth_date)->age;
        }
        return null;
    }
}
