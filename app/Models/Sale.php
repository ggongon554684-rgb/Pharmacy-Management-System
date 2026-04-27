<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
       'user_id', 'patient_id', 'prescription_id', 'total_amount', 'payment_method',
       'payment_tendered', 'payment_change_due', 'payment_reference',
       'insurance_provider', 'insurance_policy_number', 'insurance_authorization_code',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_tendered' => 'decimal:2',
        'payment_change_due' => 'decimal:2',
    ];
    
    public function lineItems()
    {
        return $this->hasMany(SaleLineItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
