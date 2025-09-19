<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const form = useForm({
    product_id: '',
    quantity: 1,
    price: 0,
    payment_method: 'cash',
});

const searchQuery = ref('');
const searchResults = ref([]);
const selectedProduct = ref(null);

const searchProducts = async () => {
    if (searchQuery.value.length > 2) {
        const response = await axios.get(route('products.search', { query: searchQuery.value }));
        searchResults.value = response.data;
    } else {
        searchResults.value = [];
    }
};

watch(searchQuery, (newQuery) => {
    searchProducts();
});

const selectProduct = (product) => {
    selectedProduct.value = product;
    form.product_id = product.id;
    form.price = product.price;
    searchQuery.value = product.name; // Display selected product name in search input
    searchResults.value = []; // Clear search results
};

const submit = () => {
    form.post(route('sales.store'), {
        onFinish: () => {
            form.reset('product_id', 'quantity', 'price');
            selectedProduct.value = null;
            searchQuery.value = '';
        },
    });
};
</script>

<template>
    <Head title="Create Sale" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Sale</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="mb-4 relative">
                                <label for="product_search" class="block text-sm font-medium text-gray-700">Search Product</label>
                                <input
                                    type="text"
                                    id="product_search"
                                    v-model="searchQuery"
                                    @input="searchProducts"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="Search by product name or ID"
                                />
                                <div v-if="searchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                                    <ul class="max-h-60 overflow-auto">
                                        <li
                                            v-for="product in searchResults"
                                            :key="product.id"
                                            @click="selectProduct(product)"
                                            class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                                        >
                                            {{ product.name }} (ID: {{ product.id }}) - ${{ product.price }}
                                        </li>
                                    </ul>
                                </div>
                                <input type="hidden" v-model="form.product_id" />
                                <div v-if="form.errors.product_id" class="text-red-500 text-xs mt-1">{{ form.errors.product_id }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input
                                    type="number"
                                    id="quantity"
                                    v-model="form.quantity"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required
                                    min="1"
                                />
                                <div v-if="form.errors.quantity" class="text-red-500 text-xs mt-1">{{ form.errors.quantity }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                                <input
                                    type="number"
                                    id="price"
                                    v-model="form.price"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required
                                    step="0.01"
                                    min="0"
                                    :disabled="selectedProduct !== null" 
                                /> <!-- Disable if product is selected -->
                                />
                                <div v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                                <div v-if="form.errors.payment_method" class="text-red-500 text-xs mt-1">{{ form.errors.payment_method }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                    :disabled="form.processing || !selectedProduct"
                                >
                                    Create Sale
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>