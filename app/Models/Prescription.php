<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id', 'prescriber_id', 'issued_date', 'status'
    ];


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

    protected $casts = [
        'status' => 'string',
        'issued_date' => 'date',
    ];
}