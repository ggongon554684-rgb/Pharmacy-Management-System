<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

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

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
