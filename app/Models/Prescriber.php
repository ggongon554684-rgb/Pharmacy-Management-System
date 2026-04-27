<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Prescriber extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'license_number', 'contact_info'];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
