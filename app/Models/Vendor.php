<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Vendor extends Model
{
    protected $fillable = [
        'name',
        'event_id',
        'location_id',
        'notes',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
    
    public function stocks()
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
        return $this->belongsToMany(Item::class, 'vendor_item_stock')
            ->withPivot('quantity')
            ->withPivot('min_quantity')
            ->withPivot('vendor_id')
            ->withPivot('item_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vendor')
            ->withPivot('vendor_id')
            ->withPivot('user_id');
    }

}
    

