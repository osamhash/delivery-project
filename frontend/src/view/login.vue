<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>مرحباً بك مجدداً</h2>
      <form @submit.prevent="handleLogin">
        <input type="email" placeholder="البريد الإلكتروني" v-model="email" required />
        <input type="password" placeholder="كلمة المرور" v-model="password" required />
        <button type="submit">دخول</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const email = ref('');
const password = ref('');

const handleLogin = async () => {
  try {
    const response = await axios.post('/customers/login', {
      email: email.value,
      password: password.value
    }, {
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    console.log('Login success:', response.data);
  } catch (error) {
    console.error('Login failed:', error.response.data);
  }
};
</script>
