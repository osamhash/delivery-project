<template>
 <div class="cart">

  <h2>🛒 Cart</h2>

  <!-- 🔥 هون أضفنا container -->
  <div class="cart-container">
    <div v-for="product in cartItems" :key="product.name" class="card">

      <img :src="getImage(product.image)" />
      <h3>{{ product.name }}</h3>

      <div class="controls">
        <button @click="decrease(product)">➖</button>
        <span>{{ product.count }}</span>
        <button @click="increase(product)">➕</button>
      </div>

      <p>{{ product.price }} ILS</p>

      <button class="remove" @click="removeFromCart(product)">
        ❌
      </button>

    </div>
  </div>

  <h3>Total: {{ totalPrice }} ILS</h3>

</div>
</template>


<script>
export default {
  props: ["cartItems", "totalPrice", "increase", "decrease", "removeFromCart"],

  methods: {
    getImage(img) {
      return new URL(`../assets/images/${img}`, import.meta.url).href;
    }
  }
};
</script>
<style>
.cart {
  text-align: center;
  padding: 20px;
}

/* 🔥 خليهم جنب بعض بدل تحت بعض */
.cart {
  text-align: center;
  padding: 20px;

  display: flex;
  flex-direction: column;
  align-items: center; /* 🔥 هذا المهم */
}

/* الكرت */
.card {
  background: white;
  padding: 15px;
  width: 200px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* الصورة */
.card img {
  width: 100%;
  height: 120px;
  object-fit: contain;
  margin-bottom: 10px;
}

/* التحكم */
.controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin: 10px 0;
}

/* الأزرار */
button {
  background: #2ecc71;
  color: white;
  border: none;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
}

button:hover {
  background: #27ae60;
}

/* زر الحذف */
.remove {
  background: red;
  margin-top: 8px;
}
h2 {
  margin-bottom: 20px;
  font-size: 22px;
}
.cart-container {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  justify-content: center;
}
</style>