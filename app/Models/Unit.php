<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'avg_meals_per_day',
        'meal_price',
        'status'
    ];

    public function employees()
    {
        return $this->belongsToMany(User::class, 'unit_user', 'unit_id', 'user_id');
    }

    public function targetAudiences()
    {
        return $this->hasMany(TargetAudience::class);
    }

    public function assignedEmployees()
    {
        return $this->belongsToMany(User::class, 'unit_user', 'unit_id', 'user_id');
    }
}