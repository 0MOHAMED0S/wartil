<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherCategory extends Model
{
    protected $fillable = ['name', 'egypt_rate', 'arab_rate', 'foreign_rate'];

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'category_id');
    }
}
