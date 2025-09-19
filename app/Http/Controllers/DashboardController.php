<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // Today's revenue (sum of today's sales)
        $todayRevenue = Sale::whereDate('created_at', $today)->sum('total');

        // Number of today's orders
        $todayOrders = Sale::whereDate('created_at', $today)->count();

        // Recent 5 orders with items
        $recentOrders = Sale::with('items.product')
            ->latest()
            ->take(5)
            ->get();

        return inertia('Dashboard', [
            'todayRevenue' => $todayRevenue,
            'todayOrders'  => $todayOrders,
            'recentOrders' => $recentOrders,
        ]);
    }
}
