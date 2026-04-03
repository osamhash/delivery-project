<template>
  <div class="navbar">

    <h2 class="logo">Delivery</h2>

    <div class="links">
  <router-link to="/">Products</router-link>

  <router-link to="/cart" class="cart-link">
    🛒 Cart 
    <span class="badge">{{ totalCount }}</span>
  </router-link>
</div>
  </div>

</template>

<script>
export default {
  props: ["cartItems"],

  computed: {
    totalCount() {
      return this.cartItems.reduce((total, p) => {
        return total + p.count;
      }, 0);
    }
  }
};
</script>

<style>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #2ecc71;
  padding: 15px 30px;
  color: white;
}

.links a {
  margin-left: 20px;
  color: white;            /* 🔥 هذا المهم */
  text-decoration: none;
  font-weight: bold;
}

.links a:hover {
  text-decoration: underline;
}
.links a:last-child {
  background: rgba(255,255,255,0.2);
  padding: 5px 10px;
  border-radius: 10px;
}
.cart-link {
  position: relative; /* 🔥 هذا الحل */
  background: rgba(255, 255, 255, 0.17);
  padding: 6px 12px;
  border-radius: 10px;
}

.badge {
  position: absolute;
  top: -4px;
  right: -4px;

  background: red;
  color: white;
  border-radius: 50%;

  font-size: 8px;     /* 🔥 صغر الخط */
  padding: 2px 4px;   /* 🔥 صغر الحجم */

  min-width: 14px;    /* 🔥 يخليها دائرية */
  height: 14px;

  display: flex;
  align-items: center;
  justify-content: center;
}
</style>