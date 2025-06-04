<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'location', 
        'start_date', 
        'end_date'
    ];

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
