<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected static function booted(): void
    {
        // Trigger replacement: set_po_number (BEFORE INSERT)
        static::creating(function (self $order): void {
            if (! empty($order->po_number)) {
                return;
            }

            $order->po_number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 7));
        });
    }

    protected $fillable = [
        'po_number',
        'created_by',
        'approved_by',
        'status',
        'expected_date',
        'notes',
        'delivery_cost',
        'insurance_cost',
        'other_cost',
        'total_cost',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'delivery_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
