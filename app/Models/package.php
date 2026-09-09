<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'egypt_price',
        'arab_price',
        'foreign_price',
        'show_in_egypt',
        'show_in_arab',
        'show_in_foreign',
        'discount',
        'base_minutes',
        'bonus_minutes',
        'validity_days',
        'description',
        'status',
    ];
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }
}
