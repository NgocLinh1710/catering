<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetAudience extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'allergy_tags',
        'religion_tags',
        'target_calories',
        'target_protein',
        'target_fat',
        'target_fiber',
        'budget_per_serving',
        'required_foods'
    ];

    protected $casts = [
        'allergy_tags' => 'array',
        'religion_tags' => 'array',
        'required_foods' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}