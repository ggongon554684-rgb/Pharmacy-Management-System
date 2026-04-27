<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Prescription extends Model
{
    use HasFactory, SoftDeletes, Prunable;
    protected $fillable = [
        'patient_id', 'prescriber_id', 'issued_date', 'status'
    ];

    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescriber()
    {
        return $this->belongsTo(Prescriber::class);
    }
    public function prescriptionItems()
    {
        return $this->hasMany(RxItem::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function dispensedByProduct(): array
    {
        if (! $this->id) {
            return [];
        }

        return SaleLineItem::query()
            ->selectRaw('inventory_batches.product_id, SUM(sale_line_items.quantity) as qty')
            ->join('sales', 'sales.id', '=', 'sale_line_items.sale_id')
            ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
            ->where('sales.prescription_id', $this->id)
            ->groupBy('inventory_batches.product_id')
            ->pluck('qty', 'inventory_batches.product_id')
            ->map(fn ($qty) => (int) $qty)
            ->all();
    }

    public function remainingByProduct(): array
    {
        $dispensed = $this->dispensedByProduct();
        $remaining = [];

        foreach ($this->prescriptionItems as $item) {
            $productId = (int) $item->product_id;
            $remaining[$productId] = max(0, (int) $item->quantity - (int) ($dispensed[$productId] ?? 0));
        }

        return $remaining;
    }

    protected $casts = [
        'status' => 'string',
        'issued_date' => 'date',
    ];
}