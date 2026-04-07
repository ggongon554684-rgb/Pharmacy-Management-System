<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RxItem extends Model
{
    use HasFactory;

    protected $fillable = ['prescription_id', 'product_id', 'dosage', 'quantity'];

    public function prescription()

    {
        return $this->belongsTo(Prescription::class);
    }
    public function product()

    {
        return $this->belongsTo(Product::class);
    }
    
}
