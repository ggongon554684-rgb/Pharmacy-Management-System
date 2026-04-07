<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'birthdate','contact_info','allergies'];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
    

}
