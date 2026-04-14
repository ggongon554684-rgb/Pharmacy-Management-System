<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pre_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
