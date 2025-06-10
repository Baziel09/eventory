<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'event_id',
        'description',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
    public function locations()
{
    return $this->hasMany(Location::class);
}


}