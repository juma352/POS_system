<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    dailySales: Array,
    paymentMethods: Array,
    topProducts: Array,
    summary: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-KE');
};
</script>

<template>
    <Head title="Sales Report" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Sales Report
                </h2>
                <Link :href="route('reports.index')" class="text-blue-600 hover:text-blue-800">
                    ← Back to Reports
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_sales }}</p>
                        <p class="text-sm text-gray-600">{{ formatDate(summary.start_date) }} - {{ formatDate(summary.end_date) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.total_revenue) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Average Transaction</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(summary.average_transaction) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Daily Sales Chart -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Daily Sales</h3>
                        <div class="space-y-2">
                            <div v-for="day in dailySales" :key="day.date" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span class="text-sm">{{ formatDate(day.date) }}</span>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(day.revenue) }}</div>
                                    <div class="text-xs text-gray-500">{{ day.transactions }} transactions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Payment Methods</h3>
                        <div class="space-y-3">
                            <div v-for="method in paymentMethods" :key="method.payment_method" class="flex justify-between items-center">
                                <span class="capitalize">{{ method.payment_method }}</span>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(method.total) }}</div>
                                    <div class="text-xs text-gray-500">{{ method.count }} transactions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white p-6 rounded-lg shadow lg:col-span-2">
                        <h3 class="text-lg font-semibold mb-4">Top Selling Products</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Product</th>
                                        <th class="text-right py-2">Quantity Sold</th>
                                        <th class="text-right py-2">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in topProducts" :key="product.name" class="border-b">
                                        <td class="py-2">{{ product.name }}</td>
                                        <td class="text-right py-2">{{ product.total_sold }}</td>
                                        <td class="text-right py-2 font-semibold">{{ formatCurrency(product.revenue) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>