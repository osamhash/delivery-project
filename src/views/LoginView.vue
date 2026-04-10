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

      <!-- اختيار الدور قبل تسجيل الدخول -->
      <div class="role-toggle">
        <button
          :class="{ active: selectedRole === 'customer' }"
          type="button"
          @click="selectedRole = 'customer'"
        >عميل</button>
        <button
          :class="{ active: selectedRole === 'driver' }"
          type="button"
          @click="selectedRole = 'driver'"
        >سائق</button>
        <button
          :class="{ active: selectedRole === 'marketer' }"
          type="button"
          @click="selectedRole = 'marketer'"
        >مركز تسويق</button>
      </div>

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
// استيراد دوال الحالة من Vue وواجهة التوجيه من vue-router
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import '../assets/styles/auth.css'

const router = useRouter()

// الحالة المرتبطة بمدخلات النموذج
const email = ref('')
const password = ref('')
const selectedRole = ref('customer')

// قراءة الثيم الداكن من التخزين المحلي إن وُجد
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

// تنفيذ عملية تسجيل الدخول وحفظ الدور والبريد محلياً
const handleLogin = () => {
  const userRole = selectedRole.value
  localStorage.setItem('delivro_role', userRole)
  localStorage.setItem('delivro_email', email.value)

  // الانتقال إلى صفحة لوحة التحكم بعد تسجيل الدخول
  router.push('/dashboard')
}

// تبديل ثيم الواجهة وتخزين الحالة محلياً
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}
</script>