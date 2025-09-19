<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $lowStockProducts = Product::where('quantity', '<=', 'min_stock_level')->get();
        $outOfStockProducts = Product::where('quantity', 0)->get();
        
        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'stats' => [
                'total_products' => $products->count(),
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockProducts->count(),
                'total_value' => $products->sum(function ($product) {
                    return $product->price * $product->quantity;
                })
            ]
        ]);
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $newQuantity = $product->quantity + $request->adjustment;
        
        if ($newQuantity < 0) {
            return back()->withErrors(['adjustment' => 'Stock cannot be negative']);
        }

        $product->update(['quantity' => $newQuantity]);

        // Log the adjustment (you might want to create a stock_adjustments table)
        
        return back()->with('success', 'Stock adjusted successfully');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:products,id',
            'updates.*.quantity' => 'required|integer|min:0',
            'updates.*.min_stock_level' => 'required|integer|min:0',
        ]);

        foreach ($request->updates as $update) {
            Product::where('id', $update['id'])->update([
                'quantity' => $update['quantity'],
                'min_stock_level' => $update['min_stock_level']
            ]);
        }

        return back()->with('success', 'Inventory updated successfully');
    }
}