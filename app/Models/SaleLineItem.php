<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleLineItem extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'inventory_batch_id', 'quantity', 'unit_price', 'subtotal'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
