// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/Dashboard.vue'
import CustomerProfile from '../views/CustomerProfile.vue'
import CreateOrderView from '../views/CreateOrder.vue'
import ShowProviderView from '../views/ShowProvider.vue'
import ShowProductView from '../views/ShowProduct.vue'
import ProfileView from '../views/ProfileView.vue'
import Register from '../views/Register.vue'
import ForgotPassword from '../views/ForgotPassword.vue'

// Driver Views
import DriverDashboard from '../views/DriverDashboard.vue'
import DriverProfile from '../views/DriverProfile.vue'

// Provider 
import ProviderDashboard from '../views/ProviderDashboard.vue'
import ProviderProfile from '../views/ProviderProfile.vue'

//Admin
import AdminDashboard from '../views/AdminDashboard.vue'
import AdminProfile from '../views/AdminProfile.vue'




const router = createRouter({
    history: createWebHistory(
        import.meta.env.BASE_URL),
    routes: [
        { path: '/', redirect: '/login' },
        { path: '/login', name: 'login', component: LoginView },
        { path: '/register', name: 'register', component: Register },
        { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword },

        // Customer Routes
        { path: '/customer/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true, role: 'customer' } },
        { path: '/show-provider', name: 'show-provider', component: ShowProviderView, meta: { requiresAuth: true, role: 'customer' } },
        { path: '/providers/:id/products', name: 'provider-products', component: ShowProductView, meta: { requiresAuth: true, role: 'customer' } },
        { path: '/create-order/:id', name: 'create-order', component: CreateOrderView, meta: { requiresAuth: true, role: 'customer' } },
        //{ path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
        {
            path: '/customer/profile',
            name: 'customer-profile',
            component: CustomerProfile,
            meta: { requiresAuth: true, role: 'customer' }
        },

        // Driver Routes
        { path: '/driver', redirect: '/driver/dashboard' },

        { path: '/driver/dashboard', name: 'driver-dashboard', component: DriverDashboard, meta: { requiresAuth: true, role: 'driver' } },
        { path: '/driver/orders', name: 'driver-orders', component: DriverDashboard, meta: { requiresAuth: true, role: 'driver' } },
        { path: '/driver/history', name: 'driver-history', component: DriverDashboard, meta: { requiresAuth: true, role: 'driver' } },
        { path: '/driver/reviews', name: 'driver-reviews', component: DriverDashboard, meta: { requiresAuth: true, role: 'driver' } },
        { path: '/driver/profile', name: 'driver-profile', component: DriverProfile, meta: { requiresAuth: true, role: 'driver' } },

        // Provider Routes
        { path: '/provider/dashboard', name: 'provider-dashboard', component: ProviderDashboard, meta: { requiresAuth: true, role: 'provider' } },
        { path: '/provider/profile', name: 'provider-profile', component: ProviderProfile, meta: { requiresAuth: true, role: 'provider' } },
        // { path: '/provider/profile', name: 'provider-profile', component: ProviderProfile, meta: { requiresAuth: true, role: 'provider' } },
        // 404 Not Found
        { path: '/:pathMatch(.*)*', redirect: '/login' },

        //Admin Route
        { path: '/admin', redirect: '/admin/dashboard' },
        {
            path: '/admin/profile', // 
            name: 'admin-profile',
            component: () =>
                import ('../views/AdminProfile.vue'),
            meta: {
                requiresAuth: true,
                role: 'admin'
            }
        }, {
            path: '/admin/dashboard',
            name: 'admin-dashboard',
            component: AdminDashboard,
            meta: {
                requiresAuth: true,
                role: 'admin'
            }
        },

    ]
})

// Navigation Guard - بدون next() callback (الطريقة الحديثة)
router.beforeEach((to, from) => {
    const token = localStorage.getItem('token')
    const userRole = localStorage.getItem('delivro_role')
    const requiresAuth = to.meta && to.meta.requiresAuth
    const requiredRole = to.meta && to.meta.role

    // 1. إذا كان المسار يتطلب مصادقة والمستخدم غير مسجل
    if (requiresAuth && !token) {
        return '/login'
    }

    // 2. إذا كان المسار يتطلب دور معين والمستخدم ليس لديه هذا الدور
    if (requiredRole && requiredRole !== userRole) {
        if (userRole === 'provider') return '/provider/dashboard'
        if (userRole === 'driver') return '/driver/dashboard'
        if (userRole === 'customer') return '/customer/dashboard'
        return '/login'
    }

    // 3. إذا كان المستخدم مسجل ويحاول الذهاب إلى login أو register
    if ((to.path === '/login' || to.path === '/register') && token) {
        // تعطيل التوجيه التلقائي - دعه يبقى في login
        return true // هذا يسمح له برؤية login حتى لو مسجل دخوله
    }

    // 4. السماح بالمتابعة
    return true
})

export default router