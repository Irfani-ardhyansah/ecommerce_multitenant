<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    
    // Relasi juga harus update namespace
    public function cartItems() {
        return $this->hasMany(CartItem::class);
    }
}