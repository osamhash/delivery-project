import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router' // 🔥 هذا السطر مهم

createApp(App).use(router).mount('#app') // 🔥 وهذا كمان