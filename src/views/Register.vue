<template>
  <div class="auth-shell" dir="rtl">

    <!--  Visual Side  -->
    <div class="visual-panel">
      <div class="vp-bg"></div>
      <div class="vp-grid"></div>
      <div class="vp-content">
        <div class="vp-logo">
          <span class="logo-mark">S</span>
          <span class="logo-text">سوق<em>بلس</em></span>
        </div>
        <div class="vp-headline">
          <h2>انضم إلى<br />المنصة الأولى<br />للتوصيل</h2>
          <p>آلاف العملاء يثقون بنا يومياً</p>
        </div>
        <div class="vp-steps">
          <div v-for="(s, i) in formSteps" :key="i" class="vs-step"
               :class="{ 'vs-active': currentStep === i, 'vs-done': currentStep > i }">
            <div class="vs-dot">
              <span v-if="currentStep > i">✓</span>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span class="vs-label">{{ s }}</span>
          </div>
        </div>
        <div class="vp-features">
          <div class="vf-item" v-for="f in features" :key="f.text">
            <span class="vf-icon">{{ f.icon }}</span>
            <span>{{ f.text }}</span>
          </div>
        </div>
        <div class="vp-orbs">
          <div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div>
        </div>
      </div>
    </div>

    <!--  Form Side  -->
    <div class="form-panel">
      <div class="form-inner">

        <div class="form-header">
          <div class="step-pill">الخطوة {{ currentStep + 1 }} من {{ formSteps.length }}</div>
          <h1>{{ stepTitles[currentStep] }}</h1>
          <p>{{ stepDescs[currentStep] }}</p>
        </div>

        <!-- ══ STEP 0: Role + Avatar ══ -->
        <div v-show="currentStep === 0" class="step-body">
          <div class="section-block">
            <div class="section-title">اختر دورك <span class="req">*</span></div>
            <div class="role-grid">
              <button v-for="role in roles" :key="role.value" type="button"
                class="role-btn" :class="{ 'role-active': form.role === role.value }"
                @click="form.role = role.value; clearError('role')">
                <span class="role-icon">{{ role.icon }}</span>
                <span class="role-name">{{ role.label }}</span>
                <span class="role-desc">{{ role.desc }}</span>
                <span class="role-check" v-if="form.role === role.value">✓</span>
              </button>
            </div>
            <span class="error-msg" v-if="errors.role">{{ errors.role }}</span>
          </div>

          <div class="section-block">
            <div class="section-title">الصورة الشخصية <span class="optional">(اختياري)</span></div>
            <div class="avatar-upload">
              <div class="avatar-preview" @click="triggerFileInput">
                <img v-if="avatarPreview" :src="avatarPreview" />
                <div v-else class="avatar-placeholder">
                  <span>{{ form.first_name ? form.first_name.charAt(0).toUpperCase() : '📷' }}</span>
                </div>
                <div class="avatar-overlay"><span>📷</span><span>تغيير</span></div>
              </div>
              <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp"
                style="display:none" @change="handleFileChange" />
              <div class="avatar-info">
                <p class="ai-title">رفع صورة شخصية</p>
                <p class="ai-hint">JPG أو PNG — حد أقصى 2 ميجابايت</p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <button type="button" class="upload-btn" @click="triggerFileInput">
                    {{ avatarPreview ? '🔄 تغيير' : '📤 رفع صورة' }}
                  </button>
                  <button type="button" class="remove-btn" v-if="avatarPreview" @click="removeAvatar">🗑️</button>
                </div>
              </div>
            </div>
            <span class="error-msg" v-if="errors.image">{{ errors.image }}</span>
          </div>
        </div>

        <!-- ══ STEP 1: Personal Info ══ -->
        <div v-show="currentStep === 1" class="step-body">
          <div class="field-row">
            <div class="field-group" :class="{ 'field-error': errors.first_name }">
              <label>الاسم الأول <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="i-icon">👤</span>
                <input v-model="form.first_name" type="text" placeholder="محمد" @input="clearError('first_name')" />
              </div>
              <span class="error-msg" v-if="errors.first_name">{{ errors.first_name }}</span>
            </div>
            <div class="field-group" :class="{ 'field-error': errors.last_name }">
              <label>الاسم الأخير <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="i-icon">👤</span>
                <input v-model="form.last_name" type="text" placeholder="الأحمد" @input="clearError('last_name')" />
              </div>
              <span class="error-msg" v-if="errors.last_name">{{ errors.last_name }}</span>
            </div>
          </div>

          <div class="field-group">
            <label>الجنس</label>
            <div class="gender-group">
              <button type="button" class="gender-btn" :class="{ 'gender-active': form.gender === 1 }"
                @click="form.gender = 1">
                <span>👨</span> ذكر
              </button>
              <button type="button" class="gender-btn" :class="{ 'gender-active': form.gender === 0 }"
                @click="form.gender = 0">
                <span>👩</span> أنثى
              </button>
            </div>
          </div>

          <div class="field-group" :class="{ 'field-error': errors.phone }">
            <label>رقم الهاتف</label>
            <div class="input-wrap">
              <span class="i-icon">📱</span>
              <input v-model="form.phone" type="tel" placeholder="05xxxxxxxx" dir="ltr" @input="clearError('phone')" />
            </div>
            <span class="error-msg" v-if="errors.phone">{{ errors.phone }}</span>
          </div>

          <div class="field-group" :class="{ 'field-error': errors.address }">
            <label>العنوان</label>
            <div class="input-wrap">
              <span class="i-icon">📍</span>
              <input v-model="form.address" type="text" placeholder="الرياض، حي النزهة..." @input="clearError('address')" />
            </div>
            <span class="error-msg" v-if="errors.address">{{ errors.address }}</span>
          </div>

          <transition name="slide-down">
            <div class="field-group" v-if="form.role === 'provider'" :class="{ 'field-error': errors.provider_type }">
              <label>نوع المتجر <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="i-icon">🏪</span>
                <select v-model="form.provider_type" @change="clearError('provider_type')">
                  <option value="">اختر نوع المتجر</option>
                  <option value="restaurant">🍽️ مطعم</option>
                  <option value="cafe">☕ كافيه</option>
                  <option value="grocery">🛒 بقالة</option>
                  <option value="bakery">🥖 مخبز</option>
                  <option value="pharmacy">💊 صيدلية</option>
                  <option value="other">📦 أخرى</option>
                </select>
              </div>
              <span class="error-msg" v-if="errors.provider_type">{{ errors.provider_type }}</span>
            </div>
          </transition>
        </div>

        <!--  STEP 2: Account Security  -->
        <div v-show="currentStep === 2" class="step-body">
          <div class="field-group" :class="{ 'field-error': errors.email }">
            <label>البريد الإلكتروني <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="i-icon">✉️</span>
              <input v-model="form.email" type="email" placeholder="example@email.com" dir="ltr" @input="clearError('email')" />
            </div>
            <span class="error-msg" v-if="errors.email">{{ errors.email }}</span>
          </div>

          <div class="field-row">
            <div class="field-group" :class="{ 'field-error': errors.password }">
              <label>كلمة المرور <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="i-icon">🔒</span>
                <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                  placeholder="••••••••" dir="ltr" @input="clearError('password')" />
                <button type="button" class="eye-btn" @click="showPass = !showPass">{{ showPass ? '🙈' : '👁️' }}</button>
              </div>
              <div class="pass-strength" v-if="form.password">
                <div class="ps-bars">
                  <div class="ps-bar" :class="strengthClass(1)"></div>
                  <div class="ps-bar" :class="strengthClass(2)"></div>
                  <div class="ps-bar" :class="strengthClass(3)"></div>
                  <div class="ps-bar" :class="strengthClass(4)"></div>
                </div>
                <span class="ps-label">{{ strengthLabel }}</span>
              </div>
              <span class="error-msg" v-if="errors.password">{{ errors.password }}</span>
            </div>
            <div class="field-group" :class="{ 'field-error': errors.password_confirmation }">
              <label>تأكيد كلمة المرور <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="i-icon">🔒</span>
                <input v-model="form.password_confirmation" :type="showPass2 ? 'text' : 'password'"
                  placeholder="••••••••" dir="ltr" @input="clearError('password_confirmation')" />
                <button type="button" class="eye-btn" @click="showPass2 = !showPass2">{{ showPass2 ? '🙈' : '👁️' }}</button>
              </div>
              <span class="error-msg" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
            </div>
          </div>

          <!-- Summary -->
          <div class="summary-card">
            <div class="sc-title">📋 ملخص بياناتك</div>
            <div class="sc-row">
              <img v-if="avatarPreview" :src="avatarPreview" class="sc-avatar" />
              <div v-else class="sc-placeholder">{{ form.first_name?.charAt(0) || '?' }}</div>
              <div class="sc-info">
                <div class="sc-name">{{ form.first_name }} {{ form.last_name }}</div>
                <div class="sc-meta">
                  <span class="sc-badge">{{ roleLabel }}</span>
                  <span v-if="form.gender === 1">👨 ذكر</span>
                  <span v-else-if="form.gender === 0">👩 أنثى</span>
                  <span v-if="form.phone">📱 {{ form.phone }}</span>
                  <span v-if="form.address">📍 {{ form.address }}</span>
                </div>
              </div>
            </div>
          </div>

          <transition name="fade">
            <div class="server-error" v-if="serverError">⚠️ {{ serverError }}</div>
          </transition>
        </div>

        <!-- Nav Buttons -->
        <div class="nav-btns">
          <button type="button" class="back-btn" v-if="currentStep > 0" @click="currentStep--">← السابق</button>
          <button type="button" class="next-btn" v-if="currentStep < 2" @click="goNext">التالي ←</button>
          <button type="button" class="submit-btn" v-if="currentStep === 2"
            :disabled="isLoading" @click="handleRegister">
            <span v-if="isLoading" class="spinner"></span>
            <span v-else>إنشاء الحساب 🚀</span>
          </button>
        </div>

        <p class="alt-link">لديك حساب؟ <router-link to="/login">تسجيل الدخول</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const currentStep = ref(0)
const formSteps   = ['الدور والصورة', 'البيانات الشخصية', 'الحساب والأمان']
const stepTitles  = ['من أنت؟', 'بياناتك الشخصية', 'بيانات الحساب']
const stepDescs   = [
  'اختر دورك وأضف صورتك الشخصية',
  'أدخل بياناتك الأساسية',
  'أدخل بريدك وكلمة المرور لتأمين حسابك',
]

const form = ref({
  first_name: '', last_name: '', email: '', phone: '',
  address: '', password: '', password_confirmation: '',
  role: '', gender: null, provider_type: '',
})

const errors      = ref({})
const serverError = ref('')
const isLoading   = ref(false)
const showPass    = ref(false)
const showPass2   = ref(false)
const fileInput   = ref(null)
const avatarFile  = ref(null)
const avatarPreview = ref('')

const roles = [
  { value: 'customer', icon: '🛍️', label: 'زبون', desc: 'تسوّق واطلب' },
  { value: 'provider', icon: '🏪', label: 'تاجر', desc: 'بِع منتجاتك' },
  { value: 'driver',   icon: '🚗', label: 'سائق', desc: 'وصّل الطلبات' },
]
const features = [
  { icon: '🚀', text: 'توصيل خلال 15 دقيقة' },
  { icon: '🔒', text: 'دفع آمن ومشفّر' },
  { icon: '⭐', text: 'أكثر من 10,000 عميل راضٍ' },
]

const roleLabel = computed(() => roles.find(r => r.value === form.value.role)?.label ?? '')

const passwordStrength = computed(() => {
  const p = form.value.password; if (!p) return 0
  let s = 0
  if (p.length >= 6) s++; if (p.length >= 10) s++
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++
  if (/[0-9]/.test(p) && /[^a-zA-Z0-9]/.test(p)) s++
  return s
})
const strengthLabel = computed(() => ['', 'ضعيفة 🔴', 'مقبولة 🟡', 'جيدة 🔵', 'قوية 🟢'][passwordStrength.value])
function strengthClass(l) {
  if (passwordStrength.value < l) return ''
  return ['', 'ps-weak', 'ps-fair', 'ps-good', 'ps-strong'][passwordStrength.value]
}

function triggerFileInput() { fileInput.value?.click() }
function handleFileChange(e) {
  const file = e.target.files[0]; if (!file) return
  if (file.size > 8 * 1024 * 1024) { errors.value.image = 'الصورة أكبر من 4 ميجابايت'; return }
  avatarFile.value = file; avatarPreview.value = URL.createObjectURL(file); clearError('image')
}
function removeAvatar() { avatarFile.value = null; avatarPreview.value = ''; if (fileInput.value) fileInput.value.value = '' }
function clearError(f) { delete errors.value[f]; serverError.value = '' }

function validateStep(s) {
  const e = {}
  if (s === 0 && !form.value.role) e.role = 'يرجى اختيار دورك'
  if (s === 1) {
    if (!form.value.first_name.trim()) e.first_name = 'الاسم الأول مطلوب'
    if (!form.value.last_name.trim())  e.last_name  = 'الاسم الأخير مطلوب'
    if (form.value.role === 'provider' && !form.value.provider_type) e.provider_type = 'نوع المتجر مطلوب'
  }
  if (s === 2) {
    if (!form.value.email.trim()) e.email = 'البريد مطلوب'
    else if (!/\S+@\S+\.\S+/.test(form.value.email)) e.email = 'البريد غير صحيح'
    if (!form.value.password) e.password = 'كلمة المرور مطلوبة'
    else if (form.value.password.length < 6) e.password = '6 أحرف على الأقل'
    if (form.value.password !== form.value.password_confirmation) e.password_confirmation = 'كلمتا المرور غير متطابقتين'
  }
  errors.value = e; return Object.keys(e).length === 0
}

function goNext() { if (validateStep(currentStep.value)) currentStep.value++ }

async function handleRegister() {
  if (!validateStep(2)) return
  
  //  تحقق إضافي للتاجر
  if (form.value.role === 'provider' && !form.value.provider_type) {
    errors.value = { provider_type: 'نوع المتجر مطلوب للتاجر' }
    currentStep.value = 1
    return
  }
  
  isLoading.value = true
  serverError.value = ''
  
  //  طباعة البيانات المرسلة للتأكد
  console.log('📤 Sending registration data:', {
    first_name: form.value.first_name,
    last_name: form.value.last_name,
    email: form.value.email,
    role: form.value.role,
    password: '***',
    password_confirmation: form.value.password_confirmation ? '***' : 'MISSING',
    phone: form.value.phone || '(empty)',
    address: form.value.address || '(empty)',
    gender: form.value.gender,
    provider_type: form.value.provider_type || '(empty)',
    has_image: !!avatarFile.value
  })
  
  try {
    const fd = new FormData()
    fd.append('first_name', form.value.first_name.trim())
    fd.append('last_name', form.value.last_name.trim())
    fd.append('email', form.value.email.trim().toLowerCase())
    fd.append('password', form.value.password)
    fd.append('password_confirmation', form.value.password_confirmation)
    fd.append('role', form.value.role)
    
    if (form.value.phone) fd.append('phone', form.value.phone)
    if (form.value.address) fd.append('address', form.value.address)
    if (form.value.gender !== null && form.value.gender !== '') fd.append('gender', form.value.gender)
    if (form.value.provider_type) fd.append('provider_type', form.value.provider_type)
    if (avatarFile.value) fd.append('image', avatarFile.value)

    const res = await api.post('/auth/register', fd, { 
      headers: { 'Content-Type': 'multipart/form-data' } 
    })
    
    console.log('✅ Registration success:', res.data)
    
    const data = res.data
    localStorage.setItem('token', data.token)
    localStorage.setItem('delivro_role', data.user?.role || form.value.role)
    localStorage.setItem('delivro_email', data.user?.email || form.value.email)
    localStorage.setItem('delivro_current_user', JSON.stringify(data.user))

    const map = { 
      customer: '/customer/dashboard', 
      provider: '/provider/dashboard', 
      driver: '/driver/dashboard' 
    }
    router.push(map[form.value.role] ?? '/login')
    
  } catch (err) {
    console.log('🔴 Registration error:', err.response?.status, err.response?.data)
    
    const data = err.response?.data
    
    //  عرض الأخطاء التفصيلية
    if (data?.errors) {
      const mapped = {}
      for (const [key, messages] of Object.entries(data.errors)) {
        mapped[key] = messages[0]
        console.log(`❌ ${key}: ${messages[0]}`)
      }
      errors.value = mapped
      
      //  تحديد أي تبويب فيه الخطأ
      if (mapped.role || mapped.image) currentStep.value = 0
      else if (mapped.first_name || mapped.last_name || mapped.provider_type || mapped.phone || mapped.address) {
        currentStep.value = 1
      } else {
        currentStep.value = 2
      }
      
    } else if (data?.message) {
      serverError.value = data.message
    } else {
      serverError.value = 'حدث خطأ، يرجى المحاولة لاحقاً'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Cairo:wght@700;900&display=swap');

.auth-shell { display: flex; min-height: 100vh; font-family: 'Tajawal', sans-serif; background: #fafaf8; direction: rtl; }

.visual-panel { width: 400px; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow: hidden; background: #0a0a0a; }
.vp-bg {
  position: absolute; inset: 0;
  background: radial-gradient(ellipse 70% 60% at 30% 20%, rgba(245,158,11,.28) 0%, transparent 60%),
              radial-gradient(ellipse 50% 50% at 80% 80%, rgba(249,115,22,.18) 0%, transparent 60%), #0a0a0a;
}
.vp-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(245,158,11,.06) 1px,transparent 1px), linear-gradient(90deg,rgba(245,158,11,.06) 1px,transparent 1px); background-size: 40px 40px; }
.vp-content { position: relative; z-index: 1; padding: 44px 36px; height: 100%; display: flex; flex-direction: column; gap: 28px; }
.vp-logo { display: flex; align-items: center; gap: 12px; }
.logo-mark { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg,#f59e0b,#f97316); color: #fff; font-family: 'Cairo',sans-serif; font-size: 22px; font-weight: 900; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(245,158,11,.4); }
.logo-text { font-family: 'Cairo',sans-serif; font-size: 22px; font-weight: 900; color: #fff; }
.logo-text em { color: #f59e0b; font-style: normal; }
.vp-headline h2 { font-family: 'Cairo',sans-serif; font-size: 30px; font-weight: 900; color: #fff; line-height: 1.2; margin: 0 0 10px; letter-spacing: -1px; }
.vp-headline p { color: rgba(255,255,255,.5); font-size: 14px; margin: 0; }

.vp-steps { display: flex; flex-direction: column; gap: 10px; }
.vs-step { display: flex; align-items: center; gap: 12px; opacity: .35; transition: opacity .3s; }
.vs-step.vs-active { opacity: 1; }
.vs-step.vs-done   { opacity: .65; }
.vs-dot { width: 28px; height: 28px; border-radius: 50%; border: 2px solid rgba(255,255,255,.25); color: rgba(255,255,255,.5); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; transition: all .3s; flex-shrink: 0; }
.vs-active .vs-dot { border-color: #f59e0b; background: #f59e0b; color: #fff; }
.vs-done   .vs-dot { border-color: #10b981; background: #10b981; color: #fff; }
.vs-label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.75); }

.vp-features { display: flex; flex-direction: column; gap: 12px; margin-top: auto; }
.vf-item { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.55); font-size: 13px; }
.vf-icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.2); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }

.vp-orbs { position: absolute; bottom: 0; left: 0; right: 0; height: 160px; pointer-events: none; }
.orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: .35; animation: float 8s ease-in-out infinite alternate; }
.o1 { width: 180px; height: 180px; background: #f59e0b; bottom: -60px; left: -40px; }
.o2 { width: 130px; height: 130px; background: #f97316; bottom: 0; right: 10px; animation-delay: -3s; }
.o3 { width: 90px;  height: 90px;  background: #fbbf24; bottom: 30px; left: 50%; animation-delay: -5s; }
@keyframes float { from { transform: translate(0,0); } to { transform: translate(12px,-12px) scale(1.08); } }

.form-panel { flex: 1; overflow-y: auto; display: flex; justify-content: center; padding: 44px 24px 60px; }
.form-inner { width: 100%; max-width: 560px; }

.form-header { margin-bottom: 28px; }
.step-pill { display: inline-block; background: rgba(245,158,11,.1); color: #b45309; border: 1px solid rgba(245,158,11,.25); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-bottom: 10px; }
.form-header h1 { font-family: 'Cairo',sans-serif; font-size: 28px; font-weight: 900; color: #0a0a0a; margin: 0 0 6px; letter-spacing: -.5px; }
.form-header p { color: #6b7280; font-size: 14px; margin: 0; }

.step-body { display: flex; flex-direction: column; gap: 20px; margin-bottom: 24px; }
.section-block { display: flex; flex-direction: column; gap: 10px; }
.section-title { font-size: 13px; font-weight: 700; color: #374151; }
.req      { color: #ef4444; }
.optional { font-size: 11px; color: #9ca3af; font-weight: 400; }

.role-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.role-btn { position: relative; display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 16px 8px; border-radius: 16px; border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer; transition: all .2s; text-align: center; overflow: hidden; }
.role-btn:hover { border-color: #f59e0b; transform: translateY(-2px); }
.role-active { border-color: #f59e0b !important; background: #fffbeb !important; box-shadow: 0 4px 20px rgba(245,158,11,.15); }
.role-icon { font-size: 26px; }
.role-name { font-size: 13px; font-weight: 800; color: #111; }
.role-desc { font-size: 11px; color: #9ca3af; }
.role-check { position: absolute; top: 7px; left: 7px; width: 17px; height: 17px; border-radius: 50%; background: #f59e0b; color: #fff; font-size: 9px; font-weight: 900; display: flex; align-items: center; justify-content: center; }

.avatar-upload { display: flex; align-items: center; gap: 20px; }
.avatar-preview { width: 90px; height: 90px; border-radius: 50%; border: 2.5px dashed #d1d5db; overflow: hidden; cursor: pointer; position: relative; flex-shrink: 0; background: #f9fafb; transition: border-color .2s; }
.avatar-preview:hover { border-color: #f59e0b; }
.avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #9ca3af; }
.avatar-overlay { position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,.5); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; opacity: 0; transition: opacity .2s; color: #fff; font-size: 11px; font-weight: 600; }
.avatar-preview:hover .avatar-overlay { opacity: 1; }
.avatar-info { display: flex; flex-direction: column; gap: 6px; }
.ai-title { font-size: 13px; font-weight: 700; color: #374151; margin: 0; }
.ai-hint  { font-size: 11px; color: #9ca3af; margin: 0; }
.upload-btn { padding: 7px 14px; border-radius: 8px; border: 1.5px solid #e5e7eb; background: #fff; color: #374151; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Tajawal',sans-serif; transition: all .2s; }
.upload-btn:hover { border-color: #f59e0b; color: #b45309; }
.remove-btn { padding: 7px 12px; border-radius: 8px; border: 1.5px solid #fecaca; background: #fef2f2; color: #dc2626; font-size: 12px; cursor: pointer; font-family: 'Tajawal',sans-serif; }

.gender-group { display: flex; gap: 12px; }
.gender-btn { flex: 1; padding: 12px; border-radius: 12px; border: 1.5px solid #e5e7eb; background: #fff; display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Tajawal',sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; color: #374151; }
.gender-btn:hover { border-color: #f59e0b; }
.gender-active { border-color: #f59e0b !important; background: #fffbeb !important; color: #b45309 !important; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-group label { font-size: 13px; font-weight: 700; color: #374151; }
.input-wrap { position: relative; display: flex; align-items: center; }
.i-icon { position: absolute; right: 14px; font-size: 15px; pointer-events: none; }
.input-wrap input, .input-wrap select { width: 100%; padding: 12px 42px 12px 16px; border-radius: 12px; border: 1.5px solid #e5e7eb; background: #fff; color: #111; font-family: 'Tajawal',sans-serif; font-size: 14px; outline: none; transition: all .2s; box-sizing: border-box; appearance: none; }
.input-wrap input:focus, .input-wrap select:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245,158,11,.1); }
.field-error .input-wrap input, .field-error .input-wrap select { border-color: #ef4444; }
.eye-btn { position: absolute; left: 12px; background: none; border: none; cursor: pointer; font-size: 15px; padding: 0; }
.error-msg { font-size: 12px; color: #ef4444; font-weight: 500; }

.pass-strength { display: flex; align-items: center; gap: 8px; margin-top: 2px; }
.ps-bars { display: flex; gap: 4px; }
.ps-bar  { width: 26px; height: 4px; border-radius: 4px; background: #e5e7eb; transition: background .3s; }
.ps-weak   { background: #ef4444 !important; }
.ps-fair   { background: #f59e0b !important; }
.ps-good   { background: #3b82f6 !important; }
.ps-strong { background: #10b981 !important; }
.ps-label  { font-size: 11px; color: #6b7280; font-weight: 600; }

.summary-card { background: linear-gradient(135deg,#fffbeb,#fff7ed); border: 1px solid rgba(245,158,11,.25); border-radius: 16px; padding: 16px; }
.sc-title { font-size: 12px; font-weight: 800; color: #92400e; margin-bottom: 12px; }
.sc-row { display: flex; align-items: center; gap: 14px; }
.sc-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #f59e0b; flex-shrink: 0; }
.sc-placeholder { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg,#f59e0b,#f97316); color: #fff; font-weight: 900; font-size: 18px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sc-name { font-size: 15px; font-weight: 800; color: #111; margin-bottom: 6px; }
.sc-meta { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.sc-badge { background: #f59e0b; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.sc-meta span { font-size: 12px; color: #6b7280; }

.server-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 12px; padding: 12px 16px; font-size: 13px; font-weight: 600; }

.nav-btns { display: flex; gap: 12px; margin-bottom: 16px; }
.back-btn { padding: 13px 20px; border-radius: 14px; border: 1.5px solid #e5e7eb; background: #fff; color: #374151; font-family: 'Tajawal',sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .2s; }
.back-btn:hover { border-color: #9ca3af; }
.next-btn, .submit-btn { flex: 1; padding: 14px; border-radius: 14px; border: none; background: linear-gradient(135deg,#0a0a0a,#1f1f1f); color: #fff; font-family: 'Cairo',sans-serif; font-size: 15px; font-weight: 900; cursor: pointer; transition: all .25s; position: relative; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.15); }
.next-btn::before, .submit-btn::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg,#f59e0b,#f97316); opacity: 0; transition: opacity .25s; }
.next-btn:hover::before, .submit-btn:hover:not(:disabled)::before { opacity: 1; }
.next-btn:hover, .submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(245,158,11,.3); }
.submit-btn:disabled { opacity: .45; cursor: not-allowed; }
.next-btn span, .submit-btn span { position: relative; z-index: 1; }
.spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.alt-link { text-align: center; font-size: 14px; color: #6b7280; margin: 0; }
.alt-link a { color: #f59e0b; font-weight: 700; text-decoration: none; }

.slide-down-enter-active { transition: all .3s ease; }
.slide-down-leave-active { transition: all .2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
.fade-enter-active, .fade-leave-active { transition: opacity .25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 900px) {
  .auth-shell { flex-direction: column; }
  .visual-panel { width: 100%; height: auto; position: relative; }
  .vp-content { padding: 20px 24px; flex-direction: row; align-items: center; gap: 16px; }
  .vp-headline, .vp-steps, .vp-features, .vp-orbs { display: none; }
  .form-panel { padding: 24px 16px 48px; }
  .field-row  { grid-template-columns: 1fr; }
  .role-grid  { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 480px) {
  .role-grid    { grid-template-columns: 1fr; }
  .gender-group { flex-direction: column; }
}
</style>