<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
}
