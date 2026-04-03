import { createRouter, createWebHistory } from "vue-router";

import Products from "../views/Products.vue";
import Cart from "../views/Cart.vue";

const routes = [
  { path: "/", component: Products },
  { path: "/cart", component: Cart },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;