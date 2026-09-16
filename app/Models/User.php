<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'privacy_agree',
        'parent_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* =======================
        Role Helpers (Clean)
    ======================== */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }
    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }
    public function teacherApplication()
    {
        return $this->hasOneThrough(Teacher_application::class, Teacher::class, 'user_id', 'id', 'id', 'teacher_application_id');
    }
    public function packages()
    {
        return $this->hasMany(UserPackage::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function dependents()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function accountOwner()
    {
        return $this->parent_id ? User::find($this->parent_id) : $this;
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }
    public function country()
    {
        return $this->hasOneThrough(
            Country::class,
            Student::class,
            'user_id',    // Foreign key on Student table
            'id',         // Foreign key on Country table
            'id',         // Local key on User table
            'country_id'  // Local key on Student table
        );
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'privacy_agree' => 'boolean',
        ];
    }
    public function callSessions()
    {
        return $this->hasMany(CallSession::class, 'student_id');
    }
    public function routeNotificationForOneSignal()
    {
        return ['include_external_user_ids' => [(string) $this->id]];
    }
}
