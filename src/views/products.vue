<template>
  <div class="products">

    <h2>Products</h2>
    <div style="margin-bottom: 15px;">
</div>

    <!-- السعر الكلي -->
    <h3>💰 Total Price: {{ totalPrice }} ILS</h3>

    <div class="grid">
      <div class="card" v-for="product in products" :key="product.name">

        <!-- الصورة -->
        <img :src="getImage(product.image)" />

        <!-- الاسم -->
        <h3>{{ product.name }}</h3>

        <!-- العدد -->
        <p>🛒 Quantity in cart: {{ getCount(product.name) }}</p>

        <!-- السعر -->
        <p>{{ product.price }} ILS</p>

        <!-- زر -->
        <button @click="addToCart(product)">
          Add to Cart
        </button>

      </div>
    </div>

  </div>
</template>

<script>
export default {
  props: ["addToCart", "cartItems", "totalPrice"],

  data() {
    return {
      products: [
        { name: "Apple", price: 5, image: "Apple.jpg" },
        { name: "Banana", price: 4, image: "Banana.jpg" },
        { name: "Milk", price: 6, image: "milk.jpg" },
      ]
    };
  },

  methods: {
    getImage(img) {
      return new URL(`../assets/images/${img}`, import.meta.url).href;
    },

    getCount(name) {
      const item = this.cartItems.find(p => p.name === name);
      return item ? item.count : 0;
    }
  }
};
</script>
<style>
.products {
  text-align: center;
  padding: 20px;
}

.grid {
  display: flex;
  gap: 15px;
  justify-content: center;
}

.card {
  background: #ffffff;
  padding: 15px;
  border-radius: 12px;
  width: 180px;
  transition: 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

.card img {
  width: 100%;
  height: 120px;
  object-fit: contain;
  border-radius: 10px;
}

h3 {
  font-size: 16px;
  margin: 10px 0;
}

p {
  font-size: 13px;
  margin: 5px 0;
}

button {
  background: #2ecc71;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
}

button:hover {
  background: #27ae60;
}
</style>
