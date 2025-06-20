<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit_id',
        'category_id'
    ];

    protected static ?string $titleAttribute = 'name';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(VendorItemStock::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
    public function Unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_item')
            ->withPivot('cost_price')
            ->withTimestamps();
    }
    
    public function firstSupplier()
    {
        return $this->suppliers()->first();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_item_stock')
            ->withPivot('quantity')
            ->withPivot('vendor_id')
            ->withPivot('item_id');
    }
}
