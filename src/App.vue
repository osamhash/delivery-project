<template>
  <Navbar :cartItems="cartItems" />

  <router-view
    :cartItems="cartItems"
    :addToCart="addToCart"
    :removeFromCart="removeFromCart"
    :increase="increase"
    :decrease="decrease"
    :totalPrice="totalPrice"
  />
</template>

<script>
import Navbar from "./components/Navbar.vue";

export default {
  components: { Navbar },

  data() {
    return {
      cartItems: [],
    };
  },

  methods: {
    addToCart(product) {
      const existing = this.cartItems.find(p => p.name === product.name);

      if (existing) {
        existing.count++;
      } else {
        this.cartItems.push({ ...product, count: 1 });
      }
    },

    removeFromCart(product) {
      this.cartItems = this.cartItems.filter(p => p.name !== product.name);
    },

    increase(product) {
      const item = this.cartItems.find(p => p.name === product.name);
      if (item) item.count++;
    },

    decrease(product) {
      const item = this.cartItems.find(p => p.name === product.name);
      if (item && item.count > 1) {
        item.count--;
      } else {
        this.removeFromCart(product);
      }
    }
  },

  computed: {
    totalPrice() {
      return this.cartItems.reduce((total, p) => {
        return total + p.price * p.count;
      }, 0);
    }
  }
};
</script>