<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBatch extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['product_id', 'location_id', 'batch_number', 'quantity', 'cost_price', 'expiry_date'];

    // Relationship: A batch belongs to one specific product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function saleLineItems()
    {
        return $this->hasMany(SaleLineItem::class);
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function scopeReleasable(Builder $query): Builder
    {
        return $query
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    public function scopeForLocationCode(Builder $query, string $code): Builder
    {
        return $query->whereIn('location_id', 
            \App\Models\InventoryLocation::where('code', $code)->select('id')
        );
    }
}               