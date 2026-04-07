<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'batch_number', 'quantity', 'cost_price', 'expiry_date'];

    // Relationship: A batch belongs to one specific product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function saleLineItems()
    {
        return $this->hasMany(SaleLineItem::class);
    }

    protected $casts = [
        'expiry_date' => 'date',
    ];
}               