<template>
  <div class="profile-container" :class="{ dark: isDark }">
    <!-- الخلفية التفاعلية -->
    <div class="glass-bg"></div>

    <div class="profile-card-wrapper">
      <!-- زر العودة -->
      <button class="back-btn" @click="goBack">
        <span class="icon">➡️</span> العودة للوحة التحكم
      </button>

      <!-- الهيدر والرمز التعبيري للثيم -->
      <div class="profile-header">
        <div class="theme-toggle">
          <button @click="toggleTheme" class="theme-btn">
            {{ isDark ? '☀️ الوضع الفاتح' : '🌙 الوضع الداكن' }}
          </button>
        </div>
        <h1 class="title">الملف الشخصي للزبون 👤</h1>
        <p class="subtitle">قم بتحديث معلوماتك الشخصية وصورتك للتواصل بشكل أفضل</p>
      </div>

      <!-- حالة التحميل أو الرسائل -->
      <div v-if="loading" class="status-msg loading">
        <span class="spinner">🌀</span> جاري تحميل البيانات...
      </div>
      <div v-if="successMsg" class="status-msg success">
        ✨ {{ successMsg }}
      </div>
      <div v-if="errorMsg" class="status-msg error">
        ⚠️ {{ errorMsg }}
      </div>

      <!-- محتوى الملف الشخصي -->
      <form v-if="!loading" @submit.prevent="saveProfile" class="profile-form">
        
        <!-- قسم الصورة الشخصية -->
        <div class="avatar-section">
          <div class="avatar-holder">
            <img :src="avatarPreview || defaultAvatar" alt="Avatar" class="avatar-img" />
            <label class="avatar-upload-label">
              <span class="camera-icon">📷</span>
              <input type="file" @change="handleImageUpload" accept="image/*" class="file-input" />
            </label>
          </div>
          <span class="avatar-hint">اضغط على الكاميرا لتغيير الصورة الشخصية</span>
        </div>

        <!-- حقول البيانات الشخصية -->
        <div class="form-grid">
          <div class="form-group">
            <label>الاسم الأول <span class="required">*</span></label>
            <input v-model="form.first_name" type="text" placeholder="الاسم الأول" required />
          </div>

          <div class="form-group">
            <label>الاسم الثاني (اختياري)</label>
            <input v-model="form.second_name" type="text" placeholder="الاسم الثاني" />
          </div>

          <div class="form-group">
            <label>اسم العائلة <span class="required">*</span></label>
            <input v-model="form.last_name" type="text" placeholder="اسم العائلة" required />
          </div>

          <div class="form-group">
            <label>البريد الإلكتروني <span class="required">*</span></label>
            <input v-model="form.email" type="email" placeholder="email@example.com" required />
          </div>

          <div class="form-group">
            <label>رقم الهاتف</label>
            <input v-model="form.phone" type="tel" placeholder="05xxxxxxxx" />
          </div>

          <div class="form-group">
            <label>العنوان بالتفصيل</label>
            <input v-model="form.address" type="text" placeholder="المدينة، الشارع، البناية" />
          </div>

          <div class="form-group">
            <label>تاريخ الميلاد</label>
            <input v-model="form.date_of_birth" type="date" />
          </div>

          <div class="form-group">
            <label>الجنس</label>
            <select v-model="form.gender">
              <option :value="null">غير محدد</option>
              <option :value="1">ذكر 👨</option>
              <option :value="0">أنثى 👩</option>
            </select>
          </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="actions-section">
          <button type="submit" class="save-btn" :disabled="saving">
            <span v-if="saving">💾 جاري الحفظ...</span>
            <span v-else>✅ حفظ التعديلات</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import './CustomerProfile.css'

const router = useRouter()

// الحالات العامة
const loading = ref(true)
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

const defaultAvatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&h=150'
const avatarPreview = ref('')
const imageFile = ref(null)

// حقول النموذج المتوافقة مع قاعدة البيانات
const form = ref({
  id: null,
  first_name: '',
  second_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  date_of_birth: '',
  gender: null
})

// تبديل الوضع الداكن والفاتح
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

// العودة
const goBack = () => {
  router.push('/customer/dashboard')
}

// جلب تفاصيل المستخدم عند تحميل الصفحة
onMounted(async () => {
  try {
    loading.value = true
    // جلب بيانات الحساب
    const res = await api.get('/auth/me')
    const user = res.data.user || res.data

    form.value.id = user.id
    form.value.first_name = user.first_name || ''
    form.value.second_name = user.second_name || ''
    form.value.last_name = user.last_name || ''
    form.value.email = user.email || ''
    form.value.phone = user.phone || ''
    form.value.address = user.address || ''
    form.value.date_of_birth = user.date_of_birth || ''
    form.value.gender = user.gender !== undefined ? user.gender : null

    if (user.image_path) {
      avatarPreview.value = user.image_path.startsWith('http') 
        ? user.image_path 
        : `/storage/${user.image_path}`
    }
  } catch (err) {
    console.error('خطأ أثناء جلب الملف الشخصي:', err)
    errorMsg.value = 'فشل جلب تفاصيل الحساب، سيتم تشغيل نظام التخزين المحلي المحاكي.'
    
    // Fallback: جلب البيانات محلياً في حال تعثر الـ API
    const localUser = JSON.parse(localStorage.getItem('delivro_current_user'))
    if (localUser) {
      form.value = { ...localUser }
      if (localUser.image_path) avatarPreview.value = localUser.image_path
    }
  } finally {
    loading.value = false
  }
})

// معالجة تغيير الصورة الشخصية وعرض المعاينة الفورية
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

// حفظ بيانات الملف الشخصي
const saveProfile = async () => {
  saving.value = false
  successMsg.value = ''
  errorMsg.value = ''
  saving.value = true

  try {
    // 1. إذا كان هناك صورة جديدة، نرفعها أو نحولها لمعاينة
    let uploadedImagePath = form.value.image_path || ''
    
    // إرسال طلب التحديث للـ API الفعلي
    const id = form.value.id || localStorage.getItem('delivro_user_id')
    
    const formData = new FormData()
    formData.append('_method', 'PUT') // للتعامل مع الـ PUT في Laravel عند إرسال FormData
    formData.append('first_name', form.value.first_name)
    if (form.value.second_name) formData.append('second_name', form.value.second_name)
    formData.append('last_name', form.value.last_name)
    formData.append('email', form.value.email)
    if (form.value.phone) formData.append('phone', form.value.phone)
    if (form.value.address) formData.append('address', form.value.address)
    if (form.value.date_of_birth) formData.append('date_of_birth', form.value.date_of_birth)
    if (form.value.gender !== null) formData.append('gender', form.value.gender)
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    let response;
    if (id) {
      response = await api.post(`/customers/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      // تجربة العنوان المباشر
      response = await api.post('/customer/profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    successMsg.value = 'تم تحديث ملفك الشخصي بنجاح!'
    
    // تحديث البيانات المحلية للتطبيق
    if (response && response.data.data) {
      const updatedUser = response.data.data
      localStorage.setItem('delivro_email', updatedUser.email)
      localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
    }
  } catch (err) {
    console.error('خطأ في الـ API، سيتم التحديث محلياً:', err)
    
    // Fallback: تحديث محلي في localStorage
    const updatedUser = {
      ...form.value,
      image_path: avatarPreview.value
    }
    localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
    localStorage.setItem('delivro_email', form.value.email)
    
    successMsg.value = 'تم حفظ التعديلات محلياً بنجاح (وضع عدم الاتصال)!'
  } finally {
    saving.value = false
    // إخفاء رسالة النجاح بعد 3 ثوانٍ
    setTimeout(() => {
      successMsg.value = ''
    }, 3000)
  }
}
</script>
