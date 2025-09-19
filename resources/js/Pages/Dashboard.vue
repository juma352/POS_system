<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    todayRevenue: Number,
    todayOrders: Number,
    recentOrders: Array
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                POS Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-2">
                    <!-- Today's Revenue -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900">Today's Revenue</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-800">
                            ${{ (todayRevenue ?? 0).toFixed(2) }}
                        </p>
                    </div>

                    <!-- Today's Orders -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900">Today's Orders</h3>
                        <p class="mt-2 text-3xl font-bold text-gray-800">{{ todayOrders ?? 0 }}</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        :href="route('sales.create')"
                        class="p-6 text-center text-white bg-blue-500 rounded-lg shadow-sm hover:bg-blue-600"
                    >
                        <h3 class="text-lg font-medium">New Sale</h3>
                    </Link>
                    <Link
                        :href="route('products.index')"
                        class="p-6 text-center text-white bg-green-500 rounded-lg shadow-sm hover:bg-green-600"
                    >
                        <h3 class="text-lg font-medium">Products</h3>
                    </Link>
                    <Link
                        :href="route('sales.index')"
                        class="p-6 text-center text-white bg-yellow-500 rounded-lg shadow-sm hover:bg-yellow-600"
                    >
                        <h3 class="text-lg font-medium">Orders</h3>
                    </Link>
                </div>

                <!-- Recent Orders -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
                        <ul class="mt-4 space-y-4">
                            <li
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                            >
                                <div>
                                    <p class="font-semibold text-gray-800">Order #{{ order.id }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ order.items?.length ?? 0 }} items
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">${{ order.total }}</p>
                                    <p class="text-sm text-gray-500">{{ order.created_at }}</p>
                                </div>
                            </li>
                            <li v-if="recentOrders.length === 0" class="p-4 text-gray-500">
                                No recent orders found.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
