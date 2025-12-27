<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Ambil cart milik user beserta item dan detail produknya
        $cart = Cart::with(['items.product'])->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        return response()->json($cart);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request) {
            $user = auth()->user();
            
            // 1. Kunci Produk biar gak dipotong user lain di detik yang sama
            $product = Product::where('id', $request->product_id)->lockForUpdate()->first();

            // 2. Cek Stok Cukup?
            if ($product->stock < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok tidak cukup. Sisa stok: {$product->stock}"
                ]);
            }

            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Cek apakah item sudah ada di cart?
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($cartItem) {
                // Logic: Kalau sudah ada, tambah quantity-nya
                $cartItem->quantity += $request->quantity;
                $cartItem->save();
            } else {
                // Logic: Kalau belum ada, buat baru
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity
                ]);
            }

            // 3. POTONG STOK PRODUK (Reservation)
            $product->decrement('stock', $request->quantity);

            return response()->json([
                'message' => 'Produk masuk keranjang & stok diamankan.',
                'data' => $cartItem
            ]);
        });
    }

    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request, $itemId) {
            $user = auth()->user();
            
            // Ambil item, pastikan punya user yang login
            $cartItem = CartItem::whereHas('cart', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('id', $itemId)->firstOrFail();

            $product = Product::where('id', $cartItem->product_id)->lockForUpdate()->first();

            $oldQty = $cartItem->quantity;
            $newQty = $request->quantity;
            $diff = $newQty - $oldQty;

            if ($diff > 0) {
                // User mau NAMBAH qty (misal dari 2 jadi 5, butuh 3 lagi)
                // Cek apakah stok produk cukup untuk nambahin 3?
                if ($product->stock < $diff) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok tambahan tidak cukup. Sisa stok tersedia: {$product->stock}"
                    ]);
                }
                // Potong stok produk
                $product->decrement('stock', $diff);

            } elseif ($diff < 0) {
                // User mau KURANGI qty (misal dari 5 jadi 2, balikin 3)
                // Kembalikan stok ke produk (pakai abs karena diff minus)
                $product->increment('stock', abs($diff));
            }

            // Update item cart
            $cartItem->update(['quantity' => $newQty]);

            return response()->json([
                'message' => 'Keranjang diperbarui.',
                'item' => $cartItem
            ]);
        });
    }

    public function destroy($itemId)
    {
        return DB::transaction(function () use ($itemId) {
            $user = auth()->user();

            $cartItem = CartItem::whereHas('cart', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('id', $itemId)->firstOrFail();

            $product = Product::where('id', $cartItem->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->increment('stock', $cartItem->quantity);
            }

            $cartItem->delete();

            return response()->json([
                'message' => 'Item dihapus dari keranjang. Stok dikembalikan ke etalase.'
            ]);
        });
    }

    public function checkout()
    {
        return DB::transaction(function () {
            $user = auth()->user();

            $carts = CartItem::whereHas('cart', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

            foreach($carts as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            CartItem::whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->delete();

            return response()->json([
                'message' => 'Item berhasil ter checkout.'
            ]);
        });
    }
}