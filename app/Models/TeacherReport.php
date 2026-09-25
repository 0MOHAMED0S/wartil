<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherReport extends Model
{
    protected $fillable = ['teacher_id', 'student_id', 'report'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
