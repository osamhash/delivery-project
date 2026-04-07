<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>مرحباً بك مجدداً</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <input type="email" placeholder="البريد الإلكتروني" required />
        </div>
        <div class="form-group">
          <input type="password" placeholder="كلمة المرور" required />
        </div>
        <button type="submit" class="btn-primary">دخول</button>
      </form>
      
      <div class="auth-footer">
        ليس لديك حساب؟ <router-link to="/register">إنشاء حساب جديد</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import '../assets/styles/auth.css';

const handleLogin = () => {
  console.log("تم محاولة تسجيل الدخول");
};
</script>