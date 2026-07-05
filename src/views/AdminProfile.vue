<template>
  <div class="profile-container" :class="{ dark: isDark }">
    <div class="glass-bg"></div>

    <div class="profile-card-wrapper admin-theme">
      <!-- زر الرجوع -->
      <button class="back-btn" @click="goBack">
        <span class="icon">➡️</span> لوحة تحكم الإدارة
      </button>

      <!-- الهيدر وتبديل الثيم -->
      <div class="profile-header">
        <div class="theme-toggle">
          <button @click="toggleTheme" class="theme-btn">
            {{ isDark ? '☀️ الوضع الفاتح' : '🌙 الوضع الداكن' }}
          </button>
        </div>
        <h1 class="title">بروفايل مدير النظام 🛡️</h1>
        <p class="subtitle">تعديل معلومات الحساب الشخصية وإعدادات الأمان الفنية للمسؤول</p>
      </div>

      <!-- مؤشرات ورسائل الحالة -->
      <div v-if="loading" class="status-msg loading">
        <span class="spinner">🌀</span> جاري تحميل بيانات المسؤول...
      </div>
      <div v-if="successMsg" class="status-msg success">
        ✨ {{ successMsg }}
      </div>
      <div v-if="errorMsg" class="status-msg error">
        ⚠️ {{ errorMsg }}
      </div>

      <!-- محتوى المدير -->
      <form v-if="!loading" @submit.prevent="saveProfile" class="profile-form">

        <!-- قسم الصورة الشخصية للمدير -->
        <div class="avatar-section">
          <div class="avatar-holder">
            <img 
              :src="avatarPreview || defaultAvatar" 
              alt="صورة المدير" 
              class="avatar-img" 
              @error="handleImageError"
            />
            <label class="avatar-upload-label">
              <span class="camera-icon">📷</span>
              <input type="file" @change="handleImageUpload" accept="image/*" class="file-input" />
            </label>
          </div>
          <h3 class="admin-name-display">{{ form.first_name }} {{ form.last_name }}</h3>
          <span class="admin-badge">مدير النظام العام (Super Admin)</span>
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
            <label>البريد الإلكتروني للإدارة <span class="required">*</span></label>
            <input v-model="form.email" type="email" placeholder="admin@system.com" required />
          </div>

          <div class="form-group">
            <label>رقم الهاتف المباشر</label>
            <input v-model="form.phone" type="tel" placeholder="05xxxxxxxx" />
          </div>

          <div class="form-group">
            <label>العنوان الشخصي</label>
            <input v-model="form.address" type="text" placeholder="المدينة، الحي" />
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
            <span v-else>✅ حفظ إعدادات المسؤول</span>
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
import '../assets/styles/AdminProfile.css'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

//  صورة افتراضية
const defaultAvatar = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="150" height="150"%3E%3Crect width="150" height="150" fill="%236366f1"/%3E%3Ctext x="75" y="90" font-size="60" text-anchor="middle" fill="white" font-family="Arial"%3E👤%3C/text%3E%3C/svg%3E'
const avatarPreview = ref('')
const imageFile = ref(null)

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

//  دالة لبناء رابط الصورة بشكل صحيح
const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  
  // إذا كان الرابط كاملاً (يبدأ بـ http)
  if (imagePath.startsWith('http')) {
    return imagePath
  }
  
  //  بناء الرابط من storage
  const baseUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000'
  // إزالة أي /storage/ مكرر
  const cleanPath = imagePath.replace(/^\/?storage\//, '')
  return `${baseUrl}/storage/${cleanPath}?t=${Date.now()}`
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goBack = () => {
  router.push('/admin/dashboard')
}

//  دالة تعبئة الفورم من بيانات المستخدم
const fillForm = (user) => {
  if (!user) return
  
  form.value.id = user.id || null
  form.value.first_name = user.first_name || ''
  form.value.second_name = user.second_name || ''
  form.value.last_name = user.last_name || ''
  form.value.email = user.email || ''
  form.value.phone = user.phone || ''
  form.value.address = user.address || ''
  form.value.date_of_birth = user.date_of_birth || ''
  form.value.gender = (user.gender !== null && user.gender !== undefined) 
    ? Number(user.gender) 
    : null

  //  تعيين الصورة
  if (user.image_path) {
    avatarPreview.value = getImageUrl(user.image_path)
    console.log('✅ Image URL set:', avatarPreview.value)
  } else {
    avatarPreview.value = defaultAvatar
  }
}

//  معالجة خطأ تحميل الصورة
const handleImageError = () => {
  console.warn('⚠️ Failed to load image, using default')
  avatarPreview.value = defaultAvatar
}

onMounted(async () => {
  try {
    loading.value = true
    const res = await api.get('/auth/me', {
      headers: {
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache'
      }
    })
    
    const user = res.data.user || res.data
    console.log('✅ User data loaded:', user)
    fillForm(user)
    
  } catch (err) {
    console.error('❌ Error loading admin data:', err)
    errorMsg.value = 'تعذر الاتصال بالخادم'
  } finally {
    loading.value = false
  }
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  //  التحقق من حجم الصورة (max 2MB)
  if (file.size > 8 * 1024 * 1024) {
    errorMsg.value = '⚠️ حجم الصورة يجب أن يكون أقل من 8MB'
    setTimeout(() => { errorMsg.value = '' }, 3000)
    return
  }
  
  //  التحقق من نوع الصورة
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    errorMsg.value = '⚠️ نوع الصورة غير مدعوم. استخدم JPG, PNG, GIF أو WEBP'
    setTimeout(() => { errorMsg.value = '' }, 3000)
    return
  }
  
  imageFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
  console.log('✅ Image selected:', file.name)
}

const saveProfile = async () => {
  if (saving.value) return

  saving.value = true
  successMsg.value = ''
  errorMsg.value = ''

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('first_name', form.value.first_name || '')
    formData.append('last_name',  form.value.last_name  || '')
    formData.append('email',      form.value.email      || '')

    if (form.value.second_name)   formData.append('second_name',   form.value.second_name)
    if (form.value.phone)         formData.append('phone',         form.value.phone)
    if (form.value.address)       formData.append('address',       form.value.address)
    if (form.value.date_of_birth) formData.append('date_of_birth', form.value.date_of_birth)
    if (form.value.gender !== null && form.value.gender !== undefined) {
      formData.append('gender', form.value.gender)
    }
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const response = await api.post('/admin/profile', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.status) {
      const updatedUser = response.data.user
      console.log('✅ Profile updated:', updatedUser)

      //  تحديث الفورم بالبيانات الجديدة
      fillForm(updatedUser)

      //  تحديث الـ localStorage
      localStorage.setItem('delivro_current_user', JSON.stringify(updatedUser))
      localStorage.setItem('delivro_email', updatedUser.email)

      //  مسح الصورة المرفوعة
      imageFile.value = null

      successMsg.value = '✅ تم تحديث بيانات المسؤول بنجاح!'
      
      //  إعادة تحميل الصورة من الخادم بعد التحديث
      setTimeout(() => {
        if (updatedUser.image_path) {
          avatarPreview.value = getImageUrl(updatedUser.image_path)
        }
      }, 500)
    }

  } catch (err) {
    console.error('❌ Save error:', err)
    if (err.response?.status === 422) {
      const errors = err.response.data.errors
      errorMsg.value = Object.values(errors).flat().join(' | ')
    } else if (err.response?.status === 403) {
      errorMsg.value = 'غير مصرح — تأكد من صلاحيات المسؤول'
    } else {
      errorMsg.value = err.response?.data?.message || 'فشل حفظ البيانات'
    }
  } finally {
    saving.value = false
    setTimeout(() => {
      successMsg.value = ''
      errorMsg.value = ''
    }, 5000)
  }
}
</script>

<style scoped>
/*  Styles إضافية للصورة */
.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 30px;
}

.avatar-holder {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid var(--primary-color, #6366f1);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
  transition: all 0.3s ease;
}

.avatar-holder:hover {
  transform: scale(1.02);
  box-shadow: 0 12px 35px rgba(99, 102, 241, 0.4);
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-upload-label {
  position: absolute;
  bottom: 5px;
  right: 5px;
  background: var(--primary-color, #6366f1);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.avatar-upload-label:hover {
  transform: scale(1.1);
  background: var(--primary-dark, #4f46e5);
}

.camera-icon {
  font-size: 20px;
}

.file-input {
  display: none;
}

.admin-name-display {
  margin-top: 15px;
  font-size: 24px;
  font-weight: 600;
  color: var(--text-primary, #1e293b);
}

.admin-badge {
  font-size: 14px;
  color: var(--text-secondary, #64748b);
  background: var(--badge-bg, #f1f5f9);
  padding: 4px 16px;
  border-radius: 20px;
  margin-top: 5px;
}

/*  الوضع الداكن */
.dark .admin-badge {
  background: #1e293b;
  color: #94a3b8;
}

.dark .admin-name-display {
  color: #e2e8f0;
}

/*  رسائل الحالة */
.status-msg {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.status-msg.loading {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
  border: 1px solid #6366f1;
}

.status-msg.success {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
  border: 1px solid #22c55e;
}

.status-msg.error {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid #ef4444;
}

.spinner {
  animation: spin 1s linear infinite;
  display: inline-block;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/*  Responsive */
@media (max-width: 768px) {
  .avatar-holder {
    width: 120px;
    height: 120px;
  }
  
  .avatar-upload-label {
    width: 35px;
    height: 35px;
  }
  
  .camera-icon {
    font-size: 16px;
  }
  
  .admin-name-display {
    font-size: 20px;
  }
}
</style>