<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

defineProps({
    products: Array
});

const cart = ref([]);
const searchQuery = ref('');
const searchResults = ref([]);
const paymentMethod = ref('cash');
const amountPaid = ref(0);
const customerPhone = ref('');
const discount = ref(0);
const showReceipt = ref(false);
const receiptData = ref(null);
const isProcessing = ref(false);

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => {
    return subtotal.value - discount.value;
});

const change = computed(() => {
    return Math.max(0, amountPaid.value - total.value);
});

const searchProducts = async () => {
    if (searchQuery.value.length > 2) {
        try {
            const response = await axios.get('/pos/search', {
                params: { query: searchQuery.value }
            });
            searchResults.value = response.data;
        } catch (error) {
            console.error('Search error:', error);
        }
    } else {
        searchResults.value = [];
    }
};

const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.product_id === product.id);
    
    if (existingItem) {
        if (existingItem.quantity < product.quantity) {
            existingItem.quantity++;
        } else {
            alert('Insufficient stock');
        }
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            max_quantity: product.quantity
        });
    }
    
    searchQuery.value = '';
    searchResults.value = [];
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateQuantity = (index, quantity) => {
    const item = cart.value[index];
    if (quantity > 0 && quantity <= item.max_quantity) {
        item.quantity = quantity;
    }
};

const processTransaction = async () => {
    if (cart.value.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    if (amountPaid.value < total.value) {
        alert('Insufficient payment amount');
        return;
    }
    
    isProcessing.value = true;
    
    try {
        const response = await axios.post('/pos/transaction', {
            items: cart.value,
            payment_method: paymentMethod.value,
            amount_paid: amountPaid.value,
            customer_phone: customerPhone.value,
            discount: discount.value
        });
        
        receiptData.value = response.data.receipt_data;
        showReceipt.value = true;
        
        // Clear cart and form
        cart.value = [];
        amountPaid.value = 0;
        customerPhone.value = '';
        discount.value = 0;
        
    } catch (error) {
        alert(error.response?.data?.error || 'Transaction failed');
    } finally {
        isProcessing.value = false;
    }
};

const printReceipt = () => {
    window.print();
};

const closeReceipt = () => {
    showReceipt.value = false;
    receiptData.value = null;
};

onMounted(() => {
    // Focus on search input
    document.getElementById('product-search')?.focus();
});
</script>

<template>
    <Head title="POS Terminal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                POS Terminal
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Product Search & Cart -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Search -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="relative">
                                <input
                                    id="product-search"
                                    type="text"
                                    v-model="searchQuery"
                                    @input="searchProducts"
                                    placeholder="Search products by name or barcode..."
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                
                                <!-- Search Results -->
                                <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <div
                                        v-for="product in searchResults"
                                        :key="product.id"
                                        @click="addToCart(product)"
                                        class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-medium">{{ product.name }}</div>
                                                <div class="text-sm text-gray-500">Stock: {{ product.quantity }}</div>
                                            </div>
                                            <div class="text-lg font-bold text-green-600">
                                                KES {{ product.price }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cart -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Shopping Cart</h3>
                            
                            <div v-if="cart.length === 0" class="text-center py-8 text-gray-500">
                                Cart is empty. Search and add products above.
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div
                                    v-for="(item, index) in cart"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium">{{ item.name }}</div>
                                        <div class="text-sm text-gray-500">KES {{ item.price }} each</div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="updateQuantity(index, item.quantity - 1)"
                                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                                            >
                                                -
                                            </button>
                                            <span class="w-8 text-center">{{ item.quantity }}</span>
                                            <button
                                                @click="updateQuantity(index, item.quantity + 1)"
                                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                                            >
                                                +
                                            </button>
                                        </div>
                                        
                                        <div class="font-bold text-green-600 w-20 text-right">
                                            KES {{ (item.price * item.quantity).toFixed(2) }}
                                        </div>
                                        
                                        <button
                                            @click="removeFromCart(index)"
                                            class="text-red-500 hover:text-red-700 p-1"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Panel -->
                    <div class="space-y-6">
                        <!-- Order Summary -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>KES {{ subtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span>Discount:</span>
                                    <input
                                        type="number"
                                        v-model="discount"
                                        min="0"
                                        :max="subtotal"
                                        step="0.01"
                                        class="w-24 p-1 border border-gray-300 rounded text-right"
                                    />
                                </div>
                                
                                <hr class="my-2">
                                
                                <div class="flex justify-between text-xl font-bold">
                                    <span>Total:</span>
                                    <span class="text-green-600">KES {{ total.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Payment</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Payment Method</label>
                                    <select v-model="paymentMethod" class="w-full p-2 border border-gray-300 rounded-lg">
                                        <option value="cash">Cash</option>
                                        <option value="mpesa">M-Pesa</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Amount Paid</label>
                                    <input
                                        type="number"
                                        v-model="amountPaid"
                                        min="0"
                                        step="0.01"
                                        class="w-full p-2 border border-gray-300 rounded-lg"
                                        placeholder="0.00"
                                    />
                                </div>
                                
                                <div v-if="amountPaid > 0" class="flex justify-between text-lg">
                                    <span>Change:</span>
                                    <span class="font-bold">KES {{ change.toFixed(2) }}</span>
                                </div>
                                
                                <div v-if="paymentMethod === 'mpesa'">
                                    <label class="block text-sm font-medium mb-2">Customer Phone</label>
                                    <input
                                        type="tel"
                                        v-model="customerPhone"
                                        placeholder="254712345678"
                                        class="w-full p-2 border border-gray-300 rounded-lg"
                                    />
                                </div>
                                
                                <button
                                    @click="processTransaction"
                                    :disabled="cart.length === 0 || isProcessing || amountPaid < total"
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isProcessing">Processing...</span>
                                    <span v-else>Complete Sale</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Modal -->
        <div v-if="showReceipt" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4 max-h-96 overflow-y-auto">
                <div class="text-center mb-4">
                    <h2 class="text-xl font-bold">RECEIPT</h2>
                    <p class="text-sm text-gray-600">Sale #{{ receiptData?.sale_id }}</p>
                    <p class="text-sm text-gray-600">{{ receiptData?.date }}</p>
                </div>
                
                <div class="border-t border-b py-4 mb-4">
                    <div v-for="item in receiptData?.items" :key="item.name" class="flex justify-between text-sm mb-1">
                        <span>{{ item.name }} x{{ item.quantity }}</span>
                        <span>KES {{ item.subtotal.toFixed(2) }}</span>
                    </div>
                </div>
                
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>KES {{ receiptData?.subtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="receiptData?.discount > 0" class="flex justify-between">
                        <span>Discount:</span>
                        <span>-KES {{ receiptData?.discount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Total:</span>
                        <span>KES {{ receiptData?.total.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Payment:</span>
                        <span class="capitalize">{{ receiptData?.payment_method }}</span>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button
                        @click="printReceipt"
                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700"
                    >
                        Print Receipt
                    </button>
                    <button
                        @click="closeReceipt"
                        class="flex-1 bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .receipt-content, .receipt-content * {
        visibility: visible;
    }
    .receipt-content {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>