<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorItemStock extends Model
{
    protected $table = 'vendor_item_stock';

    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'item_id',
        'quantity',
        'last_updated'
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
}
