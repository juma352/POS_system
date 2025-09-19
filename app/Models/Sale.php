<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'discount',
        'payment_method',
        'payment_ref',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
