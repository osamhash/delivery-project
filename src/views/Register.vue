<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>إنشاء حساب جديد</h2>
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
      
      <div class="auth-footer">
        لديك حساب بالفعل؟ <router-link to="/login">تسجيل الدخول</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import '../assets/styles/auth.css';

const handleRegister = () => {
  console.log("تم محاولة التسجيل");
};
</script>