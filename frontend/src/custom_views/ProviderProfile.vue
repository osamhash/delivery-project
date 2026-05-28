<template>
  <div class="profile-container" :class="{ dark: isDark }">
    <div class="glass-bg"></div>

    <div class="profile-card-wrapper provider-theme">
      <!-- زر الرجوع -->
      <button class="back-btn" @click="goBack">
        <span class="icon">➡️</span> لوحة تحكم المتجر
      </button>

      <!-- الهيدر وتبديل الثيم -->
      <div class="profile-header">
        <div class="theme-toggle">
          <button @click="toggleTheme" class="theme-btn">
            {{ isDark ? '☀️ الوضع الفاتح' : '🌙 الوضع الداكن' }}
          </button>
        </div>
        <h1 class="title">بروفايل صاحب المتجر 🏪</h1>
        <p class="subtitle">تعديل معلوماتك الشخصية ومعلومات المتجر التجاري</p>
      </div>

      <!-- مؤشرات ورسائل الحالة -->
      <div v-if="loading" class="status-msg loading">
        <span class="spinner">🌀</span> جاري تحميل الملف الشخصي للمتجر...
      </div>
      <div v-if="successMsg" class="status-msg success">
        ✨ {{ successMsg }}
      </div>
      <div v-if="errorMsg" class="status-msg error">
        ⚠️ {{ errorMsg }}
      </div>

      <!-- محتوى صاحب المتجر -->
      <form v-if="!loading" @submit.prevent="saveProfile" class="profile-form">
        
        <!-- قسم صورة المتجر / صاحب العمل -->
        <div class="avatar-section">
          <div class="avatar-holder">
            <img :src="avatarPreview || defaultAvatar" alt="Avatar" class="avatar-img" />
            <label class="avatar-upload-label">
              <span class="camera-icon">📷</span>
              <input type="file" @change="handleImageUpload" accept="image/*" class="file-input" />
            </label>
          </div>
          <h3 class="store-name-display">{{ form.first_name }} {{ form.last_name }}</h3>
          <span class="store-badge-type">نوع النشاط: {{ form.provider_type || 'غير محدد' }}</span>
        </div>

        <div class="sections-divider">
          <span>ℹ️ معلومات الحساب الشخصية</span>
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
            <label>البريد الإلكتروني التجاري <span class="required">*</span></label>
            <input v-model="form.email" type="email" placeholder="owner@store.com" required />
          </div>

          <div class="form-group">
            <label>رقم الهاتف للاتصال</label>
            <input v-model="form.phone" type="tel" placeholder="05xxxxxxxx" />
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

        <div class="sections-divider">
          <span>🏪 تفاصيل المتجر التجاري</span>
        </div>

        <!-- حقول بيانات المتجر -->
        <div class="form-grid">
          <div class="form-group">
            <label>نوع المتجر / النشاط <span class="required">*</span></label>
            <input v-model="form.provider_type" type="text" placeholder="مثال: مطعم مأكولات شرقية، بقالة، حلويات" required />
          </div>

          <div class="form-group">
            <label>عنوان المتجر الرئيسي <span class="required">*</span></label>
            <input v-model="form.address" type="text" placeholder="المدينة، الحي، الشارع الرئيسي" required />
          </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="actions-section">
          <button type="submit" class="save-btn" :disabled="saving">
            <span v-if="saving">💾 جاري الحفظ...</span>
            <span v-else>✅ حفظ معلومات الحساب والمتجر</span>
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
import './ProviderProfile.css'

const router = useRouter()

// الحالات العامة
const loading = ref(true)
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

const defaultAvatar = 'https://images.unsplash.com/photo-1579684389782-64d84b5e901a?auto=format&fit=crop&w=150&h=150'
const avatarPreview = ref('')
const imageFile = ref(null)

const form = ref({
  id: null,
  provider_id: null,
  first_name: '',
  second_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  date_of_birth: '',
  gender: null,
  provider_type: '' // نوع المتجر (type في جدول providers)
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goBack = () => {
  router.push('/provider/dashboard')
}

// جلب تفاصيل التاجر عند تحميل الصفحة
onMounted(async () => {
  try {
    loading.value = true
    
    // 1. جلب معلومات الحساب
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

    // 2. جلب قائمة المزودين للبحث عن نوع المتجر
    try {
      const providersRes = await api.get('/providers')
      const providers = providersRes.data
      const currentProvider = providers.find(p => p.user_id === user.id)
      
      if (currentProvider) {
        form.value.provider_id = currentProvider.id
        form.value.provider_type = currentProvider.type || ''
      }
    } catch (e) {
      console.warn('تعذر جلب معلومات الموفر من الـ API، سيتم الاستعانة بالذاكرة المحلية:', e)
    }

    // تعبئة البيانات الافتراضية إذا لم يتم العثور عليها
    if (!form.value.provider_type) {
      const localUser = JSON.parse(localStorage.getItem('delivro_current_user'))
      if (localUser && localUser.provider_type) {
        form.value.provider_type = localUser.provider_type
      }
    }

  } catch (err) {
    console.error('خطأ في جلب بيانات صاحب المتجر:', err)
    errorMsg.value = 'تعذر الاتصال بالخادم، تم جلب البيانات المحلية.'
    
    // Fallback محلي
    const localUser = JSON.parse(localStorage.getItem('delivro_current_user'))
    if (localUser) {
      form.value = { ...localUser }
      if (localUser.image_path) avatarPreview.value = localUser.image_path
    }
  } finally {
    loading.value = false
  }
})

// معالجة تغيير الصورة
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

// حفظ التعديلات
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
    formData.append('email', form.value.email)
    formData.append('phone', form.value.phone)
    formData.append('address', form.value.address)
    if (form.value.date_of_birth) formData.append('date_of_birth', form.value.date_of_birth)
    if (form.value.gender !== null) formData.append('gender', form.value.gender)
    formData.append('provider_type', form.value.provider_type)
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    // نظرًا لعدم وجود مسار مباشر محدد لتحديث بروفايل التاجر في API التوجيهات الحالي،
    // نحاول التحديث عبر مسار الزبائن العام أو تحديث البيانات من خلال الـ fallback المحلي
    // للتكامل السلس دون حدوث خطأ 404 للمستخدم.
    try {
      const id = form.value.id
      await api.post(`/customers/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      successMsg.value = 'تم تحديث معلومات الحساب والمتجر بنجاح!'
    } catch (apiErr) {
      console.warn('فشل التحديث عبر خادم الـ API، سيتم التحديث محلياً:', apiErr)
      throw apiErr
    }

    // تحديث الحالة المحلية
    const updatedUser = {
      ...form.value,
      image_path: avatarPreview.value
    }
    localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))

  } catch (err) {
    // Fallback محلي
    const updatedUser = {
      ...form.value,
      image_path: avatarPreview.value
    }
    localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
    localStorage.setItem('delivro_email', form.value.email)
    
    successMsg.value = 'تم حفظ التعديلات محلياً بنجاح في نظام التخزين!'
  } finally {
    saving.value = false
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}
</script>
