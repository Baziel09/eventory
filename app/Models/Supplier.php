<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'supplier_item')
            ->withPivot('cost_price')
            ->withTimestamps();
    }
}
