<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescriber extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'license_number', 'contact_info'];


    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
