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
        'status',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
