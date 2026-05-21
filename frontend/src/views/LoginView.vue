<template>
  <!-- الصفحة الرئيسية لتسجيل الدخول -->
  <div class="auth-container" :class="{ dark: isDark }">
    <div class="auth-card">
      <!-- زر تبديل الوضع الضوئي / الداكن -->
      <div class="theme-toggle">
        <button @click="toggleTheme" class="theme-btn">
          {{ isDark ? '☀️ الوضع الفاتح' : '🌙 الوضع الداكن' }}
        </button>
      </div>

      <!-- شعار التطبيق والعنوان -->
      <div class="auth-logo">Delivro 🛵</div>
      <h2>مرحباً بك مجدداً</h2>      

      <!-- نموذج تسجيل الدخول -->
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <input
            v-model="email"
            type="email"
            placeholder="البريد الإلكتروني"
            required
          />
        </div>
        <div class="form-group">
          <input
            v-model="password"
            type="password"
            placeholder="كلمة المرور"
            required
          />
        </div>

        <!-- رابط استعادة كلمة المرور -->
        <div class="form-actions">
          <router-link class="forgot-link" to="/forgot-password">هل نسيت كلمة المرور؟</router-link>
        </div>

        <button type="submit" class="btn-primary">دخول</button>
      </form>

      <!-- رابط الانتقال إلى صفحة التسجيل -->
      <div class="auth-footer">
        ليس لديك حساب؟ <router-link to="/register">إنشاء حساب جديد</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import '../assets/styles/auth.css'

const router = useRouter()

const email = ref('')
const password = ref('')

// الثيم
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

// LOGIN
const handleLogin = async () => {
  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value
    })

    const data = response.data

    localStorage.setItem('token', data.token)
    localStorage.setItem('delivro_role', data.user.role)
    localStorage.setItem('delivro_email', data.user.email)

    const routes = {
      customer: '/customer/dashboard',
      driver: '/driver/dashboard',
      marketer: '/marketer/dashboard'
    }

    router.push(routes[data.user.role] || '/customer/dashboard')

  } catch (error) {
    console.log(error)
    alert(error.response?.data?.message || 'Login failed')
  }
}

// THEME
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}
</script>