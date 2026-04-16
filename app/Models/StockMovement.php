<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'inventory_batch_id',
        'moved_by',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
