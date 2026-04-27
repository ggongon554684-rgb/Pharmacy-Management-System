<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Patient extends Model
{
    use HasFactory, SoftDeletes, Prunable;
    protected $fillable = [
        'name', 'birthdate','contact_info','allergies'];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
    

}
