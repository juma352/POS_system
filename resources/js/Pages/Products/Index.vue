<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

defineProps({
    products: Array,
});

const destroy = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', id));
    }
};
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Products</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <Link
                                class="px-6 py-2 text-white bg-green-500 rounded-md focus:outline-none"
                                :href="route('products.create')"
                            >
                                Create Product
                            </Link>
                        </div>
                        <table class="table-fixed w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 w-20">No.</th>
                                    <th class="px-4 py-2">Image</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Barcode</th>
                                    <th class="px-4 py-2">Price</th>
                                    <th class="px-4 py-2">Quantity</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in products" :key="product.id">
                                    <td class="border px-4 py-2">{{ index + 1 }}</td>
                                    <td class="border px-4 py-2">
                                        <img v-if="product.image" :src="`/storage/${product.image}`" class="w-20 h-20 object-cover">
                                    </td>
                                    <td class="border px-4 py-2">{{ product.name }}</td>
                                    <td class="border px-4 py-2">{{ product.barcode }}</td>
                                    <td class="border px-4 py-2">${{ product.price }}</td>
                                    <td class="border px-4 py-2">{{ product.quantity }}</td>
                                    <td class="border px-4 py-2">
                                        <Link
                                            tabIndex="1"
                                            class="px-4 py-2 text-sm text-white bg-blue-500 rounded"
                                            :href="route('products.edit', product.id)"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="destroy(product.id)"
                                            tabIndex="-1"
                                            type="button"
                                            class="mx-1 px-4 py-2 text-sm text-white bg-red-500 rounded"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
