<template>
  <!-- صفحة استرجاع كلمة المرور -->
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
      <h2>استعادة كلمة المرور</h2>
      <p class="subtitle">أدخل بريدك الإلكتروني لاسترجاع كلمة المرور.</p>

      <!-- نموذج طلب استعادة كلمة المرور -->
      <form @submit.prevent="sendReset">
        <div class="form-group">
          <input
            v-model="email"
            type="email"
            placeholder="البريد الإلكتروني"
            required
          />
        </div>
        <button type="submit" class="btn-primary">
          {{ loading ? 'جاري الإرسال...' : 'إرسال رابط الاستعادة' }}
        </button>
      </form>

      <!-- رابط الرجوع إلى صفحة تسجيل الدخول -->
      <div class="auth-footer">
        <router-link to="/login">العودة إلى تسجيل الدخول</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// استيراد الحزم الضرورية من Vue و Vue Router
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import '../assets/styles/auth.css'

const router = useRouter()
const email = ref('')
const loading = ref(false)

// قراءة الثيم الداكن من التخزين المحلي
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

// دالة إرسال طلب إعادة تعيين كلمة المرور
const sendReset = async () => {
  loading.value = true
  await new Promise((resolve) => setTimeout(resolve, 600))
  loading.value = false
  alert('تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.')

  // بعد الإرسال، العودة إلى شاشة الدخول
  router.push('/login')
}

// تبديل الثيم وتحديث التخزين المحلي
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}
</script>