<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Sales/Create');
    }
    public function index()
{
    $sales = Sale::with('items.product')
        ->latest()
        ->paginate(10);

    return inertia('Sales/Index', [
        'sales' => $sales
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,transfer',
        ]);

        $sale = Sale::create([
            'total' => $validated['quantity'] * $validated['price'],
            'payment_method' => $validated['payment_method'],
            'user_id' => auth()->id(), // Assuming authenticated user creates the sale
        ]);

        $sale->items()->create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
        ]);

        return redirect(route('sales.index'))->with('success', 'Sale created successfully.');
    }

}