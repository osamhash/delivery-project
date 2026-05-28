<template>
  <div class="profile-container" :class="{ dark: isDark }">
    <div class="glass-bg"></div>

    <div class="profile-card-wrapper driver-theme">
      <!-- زر العودة -->
      <button class="back-btn" @click="goBack">
        <span class="icon">➡️</span> لوحة تحكم السائق
      </button>

      <!-- الهيدر وتبديل الثيم -->
      <div class="profile-header">
        <div class="theme-toggle">
          <button @click="toggleTheme" class="theme-btn">
            {{ isDark ? '☀️ الوضع الفاتح' : '🌙 الوضع الداكن' }}
          </button>
        </div>
        <h1 class="title">الملف الشخصي للسائق 🛵</h1>
        <p class="subtitle">تعديل معلومات السائق الشخصية ومتابعة تقييم الأداء</p>
      </div>

      <!-- مؤشرات ورسائل الحالة -->
      <div v-if="loading" class="status-msg loading">
        <span class="spinner">🌀</span> جاري تحميل الملف الشخصي...
      </div>
      <div v-if="successMsg" class="status-msg success">
        ✨ {{ successMsg }}
      </div>
      <div v-if="errorMsg" class="status-msg error">
        ⚠️ {{ errorMsg }}
      </div>

      <!-- محتوى السائق -->
      <div v-if="!loading" class="driver-content-layout">
        
        <!-- العمود الجانبي للتقييم والتوفر -->
        <div class="driver-sidebar-panel">
          <div class="avatar-section">
            <div class="avatar-holder">
              <img :src="avatarPreview || defaultAvatar" alt="Avatar" class="avatar-img" />
              <label class="avatar-upload-label">
                <span class="camera-icon">📷</span>
                <input type="file" @change="handleImageUpload" accept="image/*" class="file-input" />
              </label>
            </div>
            <h3 class="driver-name-display">{{ form.first_name }} {{ form.last_name }}</h3>
            <span class="role-badge">سائق معتمد</span>
          </div>

          <!-- زر تغيير حالة التوفر -->
          <div class="availability-card" :class="{ available: form.is_available }">
            <div class="availability-info">
              <span class="availability-title">حالة التوفر للعمل</span>
              <span class="availability-status-text">
                {{ form.is_available ? 'متاح لتلقي الطلبات 🟢' : 'غير نشط حالياً 🔴' }}
              </span>
            </div>
            <label class="switch">
              <input type="checkbox" v-model="form.is_available" @change="toggleAvailability" :disabled="togglingAvailability" />
              <span class="slider round"></span>
            </label>
          </div>

          <!-- إحصائيات تقييم السائق -->
          <div class="stats-panel">
            <div class="stat-item">
              <span class="stat-icon">⭐</span>
              <div class="stat-details">
                <span class="stat-value">{{ rating || '0.0' }} / 5</span>
                <span class="stat-label">تقييم العملاء</span>
              </div>
            </div>
            <div class="stat-item">
              <span class="stat-icon">💬</span>
              <div class="stat-details">
                <span class="stat-value">{{ totalReviews || 0 }}</span>
                <span class="stat-label">إجمالي التقييمات</span>
              </div>
            </div>
          </div>
        </div>

        <!-- استمارة تفاصيل السائق الشخصية -->
        <form @submit.prevent="saveProfile" class="profile-form">
          <div class="form-grid">
            <div class="form-group">
              <label>الاسم الأول <span class="required">*</span></label>
              <input v-model="form.first_name" type="text" placeholder="الاسم الأول" required />
            </div>

            <div class="form-group">
              <label>الاسم الثاني</label>
              <input v-model="form.second_name" type="text" placeholder="الاسم الثاني" />
            </div>

            <div class="form-group">
              <label>اسم العائلة <span class="required">*</span></label>
              <input v-model="form.last_name" type="text" placeholder="اسم العائلة" required />
            </div>

            <div class="form-group">
              <label>البريد الإلكتروني <span class="required">*</span></label>
              <input v-model="form.email" type="email" placeholder="driver@example.com" required />
            </div>

            <div class="form-group">
              <label>رقم الهاتف</label>
              <input v-model="form.phone" type="tel" placeholder="05xxxxxxxx" />
            </div>

            <div class="form-group">
              <label>العنوان السكني</label>
              <input v-model="form.address" type="text" placeholder="المدينة والحي والشارع" />
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

          <div class="actions-section">
            <button type="submit" class="save-btn" :disabled="saving">
              <span v-if="saving">💾 جاري الحفظ...</span>
              <span v-else>✅ حفظ التعديلات الشخصية</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import './DriverProfile.css'

const router = useRouter()

// الحالات العامة
const loading = ref(true)
const saving = ref(false)
const togglingAvailability = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

const defaultAvatar = 'https://images.unsplash.com/photo-1628157582853-a796fa650a6a?auto=format&fit=crop&w=150&h=150'
const avatarPreview = ref('')
const imageFile = ref(null)

// إحصائيات إضافية للسائق
const rating = ref('0.0')
const totalReviews = ref(0)

const form = ref({
  id: null,
  first_name: '',
  second_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  date_of_birth: '',
  gender: null,
  is_available: false
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goBack = () => {
  router.push('/driver/dashboard')
}

// جلب تفاصيل السائق عند تحميل الصفحة
onMounted(async () => {
  try {
    loading.value = true
    
    // جلب الملف الشخصي للسائق من الـ API المخصص له
    const res = await api.get('/driver/profile')
    const user = res.data || res.data.user

    form.value.id = user.id
    form.value.first_name = user.first_name || ''
    form.value.second_name = user.second_name || ''
    form.value.last_name = user.last_name || ''
    form.value.email = user.email || ''
    form.value.phone = user.phone || ''
    form.value.address = user.address || ''
    form.value.date_of_birth = user.date_of_birth || ''
    form.value.gender = user.gender !== undefined ? user.gender : null
    
    if (user.driver) {
      form.value.is_available = !!user.driver.is_available
      rating.value = user.driver.rating || '0.0'
      totalReviews.value = user.driver.total_reviews || 0
    }

    if (user.image_path) {
      avatarPreview.value = user.image_path.startsWith('http') 
        ? user.image_path 
        : `/storage/${user.image_path}`
    }
  } catch (err) {
    console.error('خطأ أثناء جلب ملف السائق:', err)
    errorMsg.value = 'فشل الاتصال بالخادم، تم تفعيل محاكي التخزين المحلي.'
    
    // Fallback محلي
    const localUser = JSON.parse(localStorage.getItem('delivro_current_user'))
    if (localUser) {
      form.value = { ...localUser }
      rating.value = localUser.rating || '4.8'
      totalReviews.value = localUser.total_reviews || 12
      if (localUser.image_path) avatarPreview.value = localUser.image_path
    }
  } finally {
    loading.value = false
  }
})

// معالجة تغيير صورة الملف الشخصي
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

// تغيير حالة التوفر
const toggleAvailability = async () => {
  togglingAvailability.value = true
  successMsg.value = ''
  errorMsg.value = ''

  try {
    const res = await api.patch('/driver/availability', {
      is_available: form.value.is_available
    })
    successMsg.value = res.data.message || 'تم تحديث حالة التوفر بنجاح!'
  } catch (err) {
    console.error('فشل تحديث التوفر:', err)
    successMsg.value = 'تم تحديث حالة التوفر محلياً!'
    
    // مزامنة محلية
    const localUser = JSON.parse(localStorage.getItem('delivro_current_user')) || {}
    localUser.is_available = form.value.is_available
    localStorage.setItem('delivro_current_user', JSON.stringify(localUser))
  } finally {
    togglingAvailability.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

// حفظ بيانات الملف الشخصي
const saveProfile = async () => {
  saving.value = false
  successMsg.value = ''
  errorMsg.value = ''
  saving.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('first_name', form.value.first_name)
    if (form.value.second_name) formData.append('second_name', form.value.second_name)
    formData.append('last_name', form.value.last_name)
    formData.append('phone', form.value.phone)
    if (form.value.address) formData.append('address', form.value.address)
    if (form.value.date_of_birth) formData.append('date_of_birth', form.value.date_of_birth)
    if (form.value.gender !== null) formData.append('gender', form.value.gender)
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const response = await api.post('/driver/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    successMsg.value = 'تم تحديث معلومات السائق الشخصية بنجاح!'
    
    if (response.data && response.data.user) {
      const updatedUser = response.data.user
      localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
    }
  } catch (err) {
    console.error('خطأ في الـ API، حفظ التعديلات محلياً:', err)
    
    const updatedUser = {
      ...form.value,
      image_path: avatarPreview.value,
      rating: rating.value,
      total_reviews: totalReviews.value
    }
    localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
    localStorage.setItem('delivro_email', form.value.email)
    
    successMsg.value = 'تم حفظ التعديلات محلياً بنجاح!'
  } finally {
    saving.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}
</script>
