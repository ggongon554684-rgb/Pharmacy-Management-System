<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    public function frontShopBatches()
    {
        return $this->hasMany(InventoryBatch::class)->whereHas('location', function ($query) {
            $query->where('code', 'front');
        });
    }

    public function backInventoryBatches()
    {
        return $this->hasMany(InventoryBatch::class)->whereHas('location', function ($query) {
            $query->where('code', 'back');
        });
    }

    public function rxItems()
    {
        return $this->hasMany(RxItem::class);
    }
}
