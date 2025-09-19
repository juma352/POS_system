<script setup>
import { useCartStore } from '@/stores/cart'
import axios from 'axios'

const cart = useCartStore()

const checkout = async () => {
  try {
    const res = await axios.post('/api/sales', {
      items: cart.items,
      total: cart.total,
      discount: cart.discount,
      payment_method: cart.paymentMethod
    })
    alert('Sale completed!')
    cart.clearCart()
  } catch (e) {
    alert('Error: ' + e.response.data.error)
  }
}
</script>

<template>
  <div>
    <h2>Cart</h2>
    <ul>
      <li v-for="item in cart.items" :key="item.id">
        {{ item.name }} - {{ item.quantity }} × {{ item.price }} = {{ item.subtotal }}
      </li>
    </ul>
    <p>Total: {{ cart.total }}</p>
    <select v-model="cart.paymentMethod">
      <option value="cash">Cash</option>
      <option value="mpesa">M-Pesa</option>
    </select>
    <button @click="checkout">Checkout</button>
  </div>
</template>
