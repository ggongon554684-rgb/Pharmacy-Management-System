<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'payment_method',
        'scan_token',
        'status',
        'scanned_at',
        'fulfilled_at',
        'sale_id',
        'fulfilled_by',
    ];

    protected $casts = [
        'scanned_at'   => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PreOrderItem::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }
}