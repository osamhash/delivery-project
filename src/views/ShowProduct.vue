<template>
  <div class="app" dir="rtl">

    <!-- HEADER -->
    <header class="header">
      <h2>Welcome {{ userName }}</h2>

      <div class="cart-icon" ref="cartIcon">
        🛒
        <span class="badge">{{ cartCount }}</span>
      </div>
    </header>

    <!-- PRODUCTS -->
    <div class="grid">

      <div v-for="p in products" :key="p.id" class="card">

       <div class="image-box">
          <img
              class="img"
              :src="p.image_path || placeholder"
              :alt="p.type"
          >
      </div>

        <h4>{{ p.name }}</h4>
        <p>{{ p.description }}</p>

        <div class="price">{{ p.price }}$</div>

        <button class="add-btn" @click="addToCart(p, $event)">
          Add to Cart
        </button>

        <div class="qty" v-if="getQty(p.id)">
          In Cart: {{ getQty(p.id) }}
        </div>

      </div>

    </div>

    <!-- FLOATING BALL (animation) -->
    <div
      v-for="f in flying"
      :key="f.id"
      class="fly-ball"
      :style="f.style"
    >
      🟠
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

const route = useRoute()
const providerId = route.params.id

const userName = "Customer"

const products = ref([])
const cart = ref([])
const flying = ref([])

const cartIcon = ref(null)

// COUNT
const cartCount = computed(() =>
  cart.value.reduce((s, i) => s + i.quantity, 0)
)

// LOAD
const loadProducts = async () => {
  const res = await axios.get(`/api/providers/${providerId}/products`)
  products.value = res.data
}

// GET QTY
const getQty = (id) => {
  const item = cart.value.find(i => i.product_id === id)
  return item ? item.quantity : 0
}

// ADD TO CART + FLY ANIMATION 🚀
const addToCart = (product, event) => {

  // cart logic
  let item = cart.value.find(i => i.product_id === product.id)

  if (!item) {
    cart.value.push({ product_id: product.id, quantity: 1 })
  } else {
    item.quantity++
  }

  // ELEMENT POSITION
  const rect = event.target.getBoundingClientRect()
  const cartRect = cartIcon.value.getBoundingClientRect()

  const id = Date.now()

  flying.value.push({
    id,
    style: {
      position: "fixed",
      left: rect.left + "px",
      top: rect.top + "px",
      transition: "all 0.8s cubic-bezier(.2,.8,.2,1)",
      fontSize: "20px"
    }
  })

  // trigger move
  setTimeout(() => {
    const f = flying.value.find(x => x.id === id)
    if (f) {
      f.style.left = cartRect.left + "px"
      f.style.top = cartRect.top + "px"
      f.style.transform = "scale(0.3)"
      f.style.opacity = "0"
    }
  }, 10)

  // remove
  setTimeout(() => {
    flying.value = flying.value.filter(x => x.id !== id)
  }, 900)
}

onMounted(loadProducts)
</script>

<style>

</style>