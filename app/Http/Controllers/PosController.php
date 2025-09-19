<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('quantity', '>', 0)->get();
        
        return Inertia::render('POS/Index', [
            'products' => $products
        ]);
    }

    public function processTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,mpesa,card',
            'amount_paid' => 'required|numeric|min:0',
            'customer_phone' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $discount = $request->discount ?? 0;
            $total = $subtotal - $discount;
            
            // Validate payment amount
            if ($request->amount_paid < $total) {
                return response()->json([
                    'error' => 'Insufficient payment amount'
                ], 400);
            }
            
            // Create sale
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'discount' => $discount,
                'payment_method' => $request->payment_method,
                'payment_ref' => $request->payment_ref ?? null,
            ]);
            
            // Create sale items and update inventory
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                // Check stock availability
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => 0,
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
                
                // Update product quantity
                $product->decrement('quantity', $item['quantity']);
            }
            
            DB::commit();
            
            $change = $request->amount_paid - $total;
            
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'total' => $total,
                'amount_paid' => $request->amount_paid,
                'change' => $change,
                'receipt_data' => $this->generateReceiptData($sale)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    private function generateReceiptData($sale)
    {
        $sale->load('items.product');
        
        return [
            'sale_id' => $sale->id,
            'date' => $sale->created_at->format('Y-m-d H:i:s'),
            'items' => $sale->items->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal
                ];
            }),
            'subtotal' => $sale->items->sum('subtotal'),
            'discount' => $sale->discount,
            'total' => $sale->total,
            'payment_method' => $sale->payment_method
        ];
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        
        $products = Product::where('quantity', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('barcode', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get();
            
        return response()->json($products);
    }
}