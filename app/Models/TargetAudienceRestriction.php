<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetAudienceRestriction extends Model
{
    protected $fillable = [
        'target_audience_id',
        'name',
        'tag',
        'note',
        'default_quantity'
    ];

    public function audience()
    {
        return $this->belongsTo(TargetAudience::class);
    }
}