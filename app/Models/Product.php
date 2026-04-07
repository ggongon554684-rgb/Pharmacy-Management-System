<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model

{
    use HasFactory;
    protected $fillable = [
        'name',
        'generic_name',
        'sku',
        'price',
        'reorder_level',
    ];

    public function inventoryBatches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
    public function rxItems()
    {
        return $this->hasMany(RxItem::class);
    }
}
