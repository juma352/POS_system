<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    sales: Object
});
</script>

<template>
    <Head title="Orders" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Orders
            </h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="sale in sales.data" :key="sale.id">
                            <td class="px-6 py-4">#{{ sale.id }}</td>
                            <td class="px-6 py-4">
                                {{ sale.items.length }} items
                            </td>
                            <td class="px-6 py-4">KES {{ sale.total }}</td>
                            <td class="px-6 py-4 capitalize">{{ sale.payment_method }}</td>
                            <td class="px-6 py-4">{{ sale.created_at }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-between">
                    <button
                        v-if="sales.prev_page_url"
                        @click="$inertia.visit(sales.prev_page_url)"
                        class="px-4 py-2 bg-gray-200 rounded"
                    >
                        Previous
                    </button>
                    <button
                        v-if="sales.next_page_url"
                        @click="$inertia.visit(sales.next_page_url)"
                        class="px-4 py-2 bg-gray-200 rounded"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
