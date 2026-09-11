<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'teacher_application_status',
        'free_minutes_enabled',
        'free_minutes_amount',
        'free_minutes_validity_days',
    ];
}
