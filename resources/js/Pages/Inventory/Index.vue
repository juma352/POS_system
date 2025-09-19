<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Array,
    lowStockProducts: Array,
    outOfStockProducts: Array,
    stats: Object
});

const showAdjustModal = ref(false);
const selectedProduct = ref(null);
const adjustmentForm = useForm({
    adjustment: 0,
    reason: ''
});

const openAdjustModal = (product) => {
    selectedProduct.value = product;
    adjustmentForm.reset();
    showAdjustModal.value = true;
};

const submitAdjustment = () => {
    adjustmentForm.post(route('inventory.adjust', selectedProduct.value.id), {
        onSuccess: () => {
            showAdjustModal.value = false;
            selectedProduct.value = null;
        }
    });
};

const getStockStatus = (product) => {
    if (product.quantity === 0) return 'out-of-stock';
    if (product.quantity <= product.min_stock_level) return 'low-stock';
    return 'in-stock';
};

const getStockStatusColor = (status) => {
    switch (status) {
        case 'out-of-stock': return 'text-red-600 bg-red-100';
        case 'low-stock': return 'text-yellow-600 bg-yellow-100';
        default: return 'text-green-600 bg-green-100';
    }
};
</script>

<template>
    <Head title="Inventory Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Inventory Management
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_products }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Low Stock Items</h3>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.low_stock_count }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Out of Stock</h3>
                        <p class="text-2xl font-bold text-red-600">{{ stats.out_of_stock_count }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Total Value</h3>
                        <p class="text-2xl font-bold text-green-600">KES {{ stats.total_value.toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Alerts -->
                <div v-if="outOfStockProducts.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Out of Stock Alert</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <span v-for="product in outOfStockProducts" :key="product.id" class="text-red-700">
                            {{ product.name }}
                        </span>
                    </div>
                </div>

                <div v-if="lowStockProducts.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Low Stock Warning</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <span v-for="product in lowStockProducts" :key="product.id" class="text-yellow-700">
                            {{ product.name }} ({{ product.quantity }} left)
                        </span>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Product Inventory</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img v-if="product.image" :src="`/storage/${product.image}`" class="w-10 h-10 rounded-lg object-cover mr-3" />
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                                <div class="text-sm text-gray-500">{{ product.barcode }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ product.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ product.min_stock_level }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStockStatusColor(getStockStatus(product))" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getStockStatus(product).replace('-', ' ').toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        KES {{ (product.price * product.quantity).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                            @click="openAdjustModal(product)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                        >
                                            Adjust Stock
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Adjustment Modal -->
        <div v-if="showAdjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Adjust Stock - {{ selectedProduct?.name }}</h3>
                <p class="text-sm text-gray-600 mb-4">Current stock: {{ selectedProduct?.quantity }}</p>
                
                <form @submit.prevent="submitAdjustment">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Adjustment (+/-)</label>
                        )
                        <input
                            type="number"
                            v-model="adjustmentForm.adjustment"
                            class="w-full p-2 border border-gray-300 rounded-lg"
                            placeholder="Enter positive or negative number"
                            required
                        />
                        <div v-if="adjustmentForm.errors.adjustment" class="text-red-500 text-xs mt-1">
                            {{ adjustmentForm.errors.adjustment }}
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Reason</label>
                        <select v-model="adjustmentForm.reason" class="w-full p-2 border border-gray-300 rounded-lg" required>
                            <option value="">Select reason</option>
                            <option value="stock_received">Stock Received</option>
                            <option value="damaged_goods">Damaged Goods</option>
                            <option value="theft">Theft/Loss</option>
                            <option value="expired">Expired Products</option>
                            <option value="inventory_count">Inventory Count Correction</option>
                            <option value="other">Other</option>
                        </select>
                        <div v-if="adjustmentForm.errors.reason" class="text-red-500 text-xs mt-1">
                            {{ adjustmentForm.errors.reason }}
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button
                            type="submit"
                            :disabled="adjustmentForm.processing"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ adjustmentForm.processing ? 'Processing...' : 'Adjust Stock' }}
                        </button>
                        <button
                            type="button"
                            @click="showAdjustModal = false"
                            class="flex-1 bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>