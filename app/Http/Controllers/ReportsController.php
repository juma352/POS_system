<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        // Daily sales for the period
        $dailySales = Sale::selectRaw('DATE(created_at) as date, COUNT(*) as transactions, SUM(total) as revenue')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment method breakdown
        $paymentMethods = Sale::selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('payment_method')
            ->get();

        // Top selling products
        $topProducts = SaleItem::select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.subtotal) as revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Summary statistics
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count();
        $totalRevenue = Sale::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->sum('total');
        $averageTransaction = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        return Inertia::render('Reports/Sales', [
            'dailySales' => $dailySales,
            'paymentMethods' => $paymentMethods,
            'topProducts' => $topProducts,
            'summary' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'average_transaction' => $averageTransaction,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    public function inventoryReport()
    {
        $products = Product::select('name', 'quantity', 'min_stock_level', 'price')
            ->get()
            ->map(function ($product) {
                $product->stock_value = $product->quantity * $product->price;
                $product->status = $product->quantity == 0 ? 'Out of Stock' : 
                                 ($product->quantity <= $product->min_stock_level ? 'Low Stock' : 'In Stock');
                return $product;
            });

        $totalValue = $products->sum('stock_value');
        $lowStockCount = $products->where('status', 'Low Stock')->count();
        $outOfStockCount = $products->where('status', 'Out of Stock')->count();

        return Inertia::render('Reports/Inventory', [
            'products' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'total_value' => $totalValue,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount
            ]
        ]);
    }
}