// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/Dashboard.vue'
import CreateOrderView from '../views/CreateOrder.vue'
import ShowProviderView from '../views/ShowProvider.vue'
import ShowProductView from '../views/ShowProduct.vue'
import ProfileView from '../views/ProfileView.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'

// Driver Views
import DriverDashboard from '../views/DriverDashboard.vue'

const router = createRouter({
    history: createWebHistory(
        import.meta.env.BASE_URL),
    routes: [
        { path: '/', redirect: '/login' },
        { path: '/login', name: 'login', component: LoginView },
        { path: '/register', name: 'register', component: Register },
        { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword },

        // Customer Routes
        { path: '/customer/dashboard', name: 'dashboard', component: DashboardView },
        { path: '/show-provider', name: 'show-provider', component: ShowProviderView },
        { path: '/providers/:id/products', name: 'provider-products', component: ShowProductView },
        { path: '/create-order/:id', name: 'create-order', component: CreateOrderView },
        { path: '/profile', name: 'profile', component: ProfileView },

        // Driver Routes
        { path: '/driver', redirect: '/driver/dashboard' },
        { path: '/driver/dashboard', name: 'driver-dashboard', component: DriverDashboard },
        { path: '/driver/orders', name: 'driver-orders', component: DriverDashboard }, // يمكنك إنشاء مكونات منفصلة
        { path: '/driver/history', name: 'driver-history', component: DriverDashboard },
        { path: '/driver/reviews', name: 'driver-reviews', component: DriverDashboard },
        { path: '/driver/profile', name: 'driver-profile', component: DriverDashboard },
    ]
})

// Navigation Guard 
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const userRole = localStorage.getItem('userRole')
    const requiresRole = to.meta && to.meta.role
    const role = requiresRole ? to.meta.role : null

    if (to.path !== '/login' && to.path !== '/register' && to.path !== '/forgot-password' && !token) {
        next('/login')
    } else if (role && role !== userRole) {
        if (userRole === 'driver') {
            next('/driver/dashboard')
        } else {
            next('/customer/dashboard')
        }
    } else {
        next()
    }
})

export default router