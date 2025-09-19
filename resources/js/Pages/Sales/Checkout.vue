<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useCartStore } from "@/stores/cartStore";
import axios from "axios";

const cart = useCartStore();

const checkout = async () => {
    try {
        const res = await axios.post("/api/sales", {
            items: cart.items,
            total: cart.total,
            discount: cart.discount,
            payment_method: cart.paymentMethod,
        });

        alert("Sale completed successfully!");
        cart.clearCart();
    } catch (e) {
        alert("Error: " + (e.response?.data?.error || e.message));
    }
};
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Checkout
            </h2>
        </template>

        <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Cart</h3>
                <ul>
                    <li
                        v-for="item in cart.items"
                        :key="item.id"
                        class="flex justify-between py-2 border-b"
                    >
                        <span>{{ item.name }} (x{{ item.quantity }})</span>
                        <span>${{ item.subtotal }}</span>
                    </li>
                </ul>

                <div class="mt-4 text-right text-xl font-bold">
                    Total: ${{ cart.total }}
                </div>

                <div class="mt-4">
                    <label class="block mb-2 font-medium">Payment Method</label>
                    <select v-model="cart.paymentMethod" class="p-2 border rounded">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                    </select>
                </div>

                <button
                    @click="checkout"
                    class="mt-6 px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Confirm Checkout
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
