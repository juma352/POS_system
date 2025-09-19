import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    discount: 0,
    paymentMethod: 'cash'
  }),

  getters: {
    total: (state) => {
      return state.items.reduce((sum, item) => sum + item.subtotal, 0) - state.discount
    }
  },

  actions: {
    addToCart(product) {
      const existing = this.items.find(i => i.id === product.id)
      if (existing) {
        existing.quantity++
        existing.subtotal = (existing.price * existing.quantity) - existing.discount
      } else {
        this.items.push({
          id: product.id,
          name: product.name,
          price: product.price,
          quantity: 1,
          discount: 0,
          subtotal: product.price
        })
      }
    },

    updateQuantity(id, qty) {
      const item = this.items.find(i => i.id === id)
      if (item) {
        item.quantity = qty
        item.subtotal = (item.price * item.quantity) - item.discount
      }
    },

    applyDiscount(id, discount) {
      const item = this.items.find(i => i.id === id)
      if (item) {
        item.discount = discount
        item.subtotal = (item.price * item.quantity) - discount
      }
    },

    clearCart() {
      this.items = []
      this.discount = 0
      this.paymentMethod = 'cash'
    }
  }
})
