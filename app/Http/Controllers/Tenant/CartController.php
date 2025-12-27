<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1'
        ]);

        $cart = Cart::firstOrCreate(['user_id' => 1]); 

        $cartItem = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity] // Simplifikasi: replace qty
        );

        return response()->json(['message' => 'Masuk keranjang', 'item' => $cartItem]);
    }

    // GET /api/cart (Lihat isi keranjang)
    public function index()
    {
        $cart = Cart::with('items.product')->where('user_id', 1)->first();
        return response()->json($cart);
    }
}