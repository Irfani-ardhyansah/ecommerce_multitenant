<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $guarded = ['id'];

    // Biar saat fetch cart, data produknya ikut terambil
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}