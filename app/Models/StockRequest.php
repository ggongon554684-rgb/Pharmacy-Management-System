<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'approved_by',
        'product_id',
        'quantity',
        'requested_quantity',
        'approved_quantity',
        'status',
        'reason',
        'adjustment_reason',
        'approved_at',
        'fulfilled_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
