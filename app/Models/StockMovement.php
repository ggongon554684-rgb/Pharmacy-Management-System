<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class StockMovement extends Model
{
    use HasFactory, SoftDeletes, Prunable;

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

    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
