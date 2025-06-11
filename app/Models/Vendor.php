<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Vendor extends Model
{
    protected $fillable = [
        'name',
        'event_id',
        'location_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(VendorItemStock::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
    
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot(['quantity', 'total'])->with('unit');
    }
}
    

