<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotBooking extends Model
{
    // ممتاز جداً، يغنيك عن كتابة كل الأعمدة
    protected $guarded = [];

    // 🟢 إضافة الـ Casts ضرورية جداً للتعامل مع التواريخ بسلاسة
    protected $casts = [
        'started_at'        => 'datetime',
        'ended_at'          => 'datetime',
        'student_joined_at' => 'datetime',
        'teacher_joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(TeacherSlot::class, 'teacher_slot_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
    public function reports()
{
    return $this->morphMany(TeacherReport::class, 'reportable');
}
}
