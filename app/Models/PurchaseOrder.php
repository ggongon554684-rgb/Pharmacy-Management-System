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
    ];

    protected $casts = [
        'expected_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
