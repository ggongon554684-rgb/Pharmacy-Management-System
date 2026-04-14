<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_front_shop',
    ];

    protected $casts = [
        'is_front_shop' => 'boolean',
    ];

    public function inventoryBatches()
    {
        return $this->hasMany(InventoryBatch::class, 'location_id');
    }
}
