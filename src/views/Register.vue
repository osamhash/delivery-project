<template>
  <!-- صفحة إنشاء حساب جديد -->
  <div class="auth-container">
    <div class="auth-card">
      <h2>إنشاء حساب جديد</h2>

      <!-- نموذج التسجيل الأساسي -->
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <input type="text" placeholder="الاسم الكامل" required />
        </div>
        <div class="form-group">
          <input type="email" placeholder="البريد الإلكتروني" required />
        </div>
        <div class="form-group">
          <input type="password" placeholder="كلمة المرور" required />
        </div>
        <button type="submit" class="btn-primary">تسجيل</button>
      </form>
      
      <!-- رابط العودة إلى صفحة تسجيل الدخول -->
      <div class="auth-footer">
        لديك حساب بالفعل؟ <router-link to="/login">تسجيل الدخول</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// استيراد أنماط صفحة المصادقة
import '../assets/styles/auth.css';

// دالة معالجة التسجيل حالياً مجرد طباعة في وحدة التحكم
const handleRegister = () => {
  console.log('تم محاولة التسجيل');
};
</script>