<template>
  <div class="fp-shell" dir="rtl">

    <!-- Background -->
    <div class="fp-bg">
      <div class="bg-orb b1"></div>
      <div class="bg-orb b2"></div>
      <div class="bg-grid"></div>
    </div>

    <!-- Card -->
    <div class="fp-card">

      <!-- Logo -->
      <div class="fp-logo">
        <div class="logo-mark">S</div>
        <span class="logo-text">سوق<em>بلس</em></span>
      </div>

      <!-- ── Step 1: Enter Email ── -->
      <transition name="step" mode="out-in">
        <div v-if="step === 1" key="step1" class="step-content">
          <div class="step-icon">🔑</div>
          <h1>نسيت كلمة المرور؟</h1>
          <p>أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين</p>

          <form @submit.prevent="handleSendEmail" novalidate>
            <div class="field-group" :class="{ 'field-error': errors.email }">
              <label>البريد الإلكتروني</label>
              <div class="input-wrap">
                <span class="i-icon">✉️</span>
                <input
                  v-model="email"
                  type="email"
                  placeholder="example@email.com"
                  dir="ltr"
                  autofocus
                  @input="errors.email = ''"
                />
              </div>
              <span class="error-msg" v-if="errors.email">{{ errors.email }}</span>
            </div>

            <transition name="fade">
              <div class="server-error" v-if="serverError">⚠️ {{ serverError }}</div>
            </transition>

            <button type="submit" class="main-btn" :disabled="isLoading">
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>إرسال رابط الاستعادة ←</span>
            </button>
          </form>

          <p class="back-link">
            <router-link to="/login">← العودة لتسجيل الدخول</router-link>
          </p>
        </div>

        <!-- ── Step 2: Email Sent ── -->
        <div v-else-if="step === 2" key="step2" class="step-content center">
          <div class="success-circle">
            <span>📬</span>
          </div>
          <h1>تحقق من بريدك!</h1>
          <p>أرسلنا رابط إعادة تعيين كلمة المرور إلى</p>
          <div class="email-chip">{{ email }}</div>
          <p class="hint-text">لم يصلك الإيميل؟ تحقق من مجلد الـ Spam</p>

          <button class="main-btn outline-btn" @click="resendEmail" :disabled="resendCooldown > 0">
            <span v-if="resendCooldown > 0">إعادة الإرسال بعد {{ resendCooldown }}s</span>
            <span v-else>إعادة إرسال الرابط</span>
          </button>

          <p class="back-link">
            <router-link to="/login">← العودة لتسجيل الدخول</router-link>
          </p>
        </div>

        <!-- ── Step 3: Reset Password (from token in URL) ── -->
        <div v-else-if="step === 3" key="step3" class="step-content">
          <div class="step-icon">🛡️</div>
          <h1>كلمة مرور جديدة</h1>
          <p>اختر كلمة مرور قوية لحماية حسابك</p>

          <form @submit.prevent="handleReset" novalidate>
            <div class="field-group" :class="{ 'field-error': errors.password }">
              <label>كلمة المرور الجديدة</label>
              <div class="input-wrap">
                <span class="i-icon">🔒</span>
                <input
                  v-model="newPassword"
                  :type="showPass ? 'text' : 'password'"
                  placeholder="••••••••"
                  dir="ltr"
                  @input="errors.password = ''"
                />
                <button type="button" class="eye-btn" @click="showPass = !showPass">
                  {{ showPass ? '🙈' : '👁️' }}
                </button>
              </div>
              <!-- Strength -->
              <div class="pass-strength" v-if="newPassword">
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
              <label>تأكيد كلمة المرور</label>
              <div class="input-wrap">
                <span class="i-icon">🔒</span>
                <input
                  v-model="newPasswordConfirm"
                  :type="showPass2 ? 'text' : 'password'"
                  placeholder="••••••••"
                  dir="ltr"
                  @input="errors.password_confirmation = ''"
                />
                <button type="button" class="eye-btn" @click="showPass2 = !showPass2">
                  {{ showPass2 ? '🙈' : '👁️' }}
                </button>
              </div>
              <span class="error-msg" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
            </div>

            <transition name="fade">
              <div class="server-error" v-if="serverError">⚠️ {{ serverError }}</div>
            </transition>

            <button type="submit" class="main-btn" :disabled="isLoading">
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>تعيين كلمة المرور ←</span>
            </button>
          </form>
        </div>

        <!-- ── Step 4: Success ── -->
        <div v-else-if="step === 4" key="step4" class="step-content center">
          <div class="success-circle success-circle--done">
            <span>✅</span>
          </div>
          <h1>تم بنجاح!</h1>
          <p>تم تغيير كلمة مرورك بنجاح. يمكنك الآن تسجيل الدخول.</p>

          <router-link to="/login" class="main-btn" style="text-decoration:none; display:block; text-align:center;">
            تسجيل الدخول ←
          </router-link>
        </div>
      </transition>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route  = useRoute()
const router = useRouter()

// ── State ────────────────────────────────────────
const step               = ref(1)
const email              = ref('')
const newPassword        = ref('')
const newPasswordConfirm = ref('')
const errors             = ref({})
const serverError        = ref('')
const isLoading          = ref(false)
const showPass           = ref(false)
const showPass2          = ref(false)
const resendCooldown     = ref(0)

// Token from URL (for reset step)
const resetToken = ref(route.query.token ?? '')
const resetEmail = ref(route.query.email ?? '')

// If token in URL → go to step 3
onMounted(() => {
  if (resetToken.value && resetEmail.value) {
    email.value = resetEmail.value
    step.value  = 3
  }
})

// ── Password Strength ─────────────────────────────
const passwordStrength = computed(() => {
  const p = newPassword.value
  if (!p) return 0
  let s = 0
  if (p.length >= 6)  s++
  if (p.length >= 10) s++
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++
  if (/[0-9]/.test(p) && /[^a-zA-Z0-9]/.test(p)) s++
  return s
})
const strengthLabel = computed(() =>
  ['', 'ضعيفة', 'مقبولة', 'جيدة', 'قوية جداً'][passwordStrength.value]
)
function strengthClass(level) {
  if (passwordStrength.value < level) return 'ps-empty'
  return ['', 'ps-weak', 'ps-fair', 'ps-good', 'ps-strong'][passwordStrength.value]
}

// ── Step 1: Send Email ────────────────────────────
async function handleSendEmail() {
  errors.value      = {}
  serverError.value = ''
  if (!email.value.trim()) { errors.value.email = 'البريد الإلكتروني مطلوب'; return }
  if (!/\S+@\S+\.\S+/.test(email.value)) { errors.value.email = 'البريد غير صحيح'; return }

  isLoading.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    step.value = 2
    startResendCooldown()
  } catch (err) {
    serverError.value = err.response?.data?.message ?? 'حدث خطأ، حاول مرة أخرى'
  } finally {
    isLoading.value = false
  }
}

// ── Resend ────────────────────────────────────────
function startResendCooldown() {
  resendCooldown.value = 60
  const t = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) clearInterval(t)
  }, 1000)
}

async function resendEmail() {
  if (resendCooldown.value > 0) return
  isLoading.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    startResendCooldown()
  } catch (err) {
    serverError.value = err.response?.data?.message ?? 'حدث خطأ'
  } finally {
    isLoading.value = false
  }
}

// ── Step 3: Reset Password ────────────────────────
async function handleReset() {
  errors.value      = {}
  serverError.value = ''

  if (!newPassword.value || newPassword.value.length < 6) {
    errors.value.password = 'كلمة المرور 6 أحرف على الأقل'; return
  }
  if (newPassword.value !== newPasswordConfirm.value) {
    errors.value.password_confirmation = 'كلمتا المرور غير متطابقتين'; return
  }

  isLoading.value = true
  try {
    await api.post('/auth/reset-password', {
      token:                 resetToken.value,
      email:                 resetEmail.value,
      password:              newPassword.value,
      password_confirmation: newPasswordConfirm.value,
    })
    step.value = 4
  } catch (err) {
    const data = err.response?.data
    if (data?.errors) {
      const mapped = {}
      for (const [k, msgs] of Object.entries(data.errors)) mapped[k] = msgs[0]
      errors.value = mapped
    } else {
      serverError.value = data?.message ?? 'الرابط منتهي الصلاحية أو غير صحيح'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Cairo:wght@700;900&display=swap');

.fp-shell {
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Tajawal', sans-serif;
  background: #fafaf8; position: relative; padding: 24px;
  direction: rtl;
}

/* ─── Background ─────────────────────────────────── */
.fp-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.bg-orb {
  position: absolute; border-radius: 50%; filter: blur(100px);
  animation: drift 10s ease-in-out infinite alternate;
}
.b1 { width: 600px; height: 600px; top: -150px; right: -150px; background: rgba(245,158,11,.12); }
.b2 { width: 500px; height: 500px; bottom: -100px; left: -100px; background: rgba(249,115,22,.10); animation-delay: -4s; }
.bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(0,0,0,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,0,0,.04) 1px, transparent 1px);
  background-size: 40px 40px;
}
@keyframes drift {
  from { transform: translate(0,0); }
  to   { transform: translate(20px,-20px) scale(1.05); }
}

/* ─── Card ───────────────────────────────────────── */
.fp-card {
  position: relative; z-index: 1;
  width: 100%; max-width: 460px;
  background: #fff;
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 28px; padding: 44px 40px;
  box-shadow: 0 20px 60px rgba(0,0,0,.1), 0 0 0 1px rgba(255,255,255,.8) inset;
}

/* ─── Logo ───────────────────────────────────────── */
.fp-logo {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 36px;
}
.logo-mark {
  width: 40px; height: 40px; border-radius: 11px;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  color: #fff; font-family: 'Cairo', sans-serif;
  font-size: 20px; font-weight: 900;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 6px 20px rgba(245,158,11,.35);
}
.logo-text {
  font-family: 'Cairo', sans-serif; font-size: 20px; font-weight: 900; color: #111;
}
.logo-text em { color: #f59e0b; font-style: normal; }

/* ─── Step Content ───────────────────────────────── */
.step-content { display: flex; flex-direction: column; gap: 16px; }
.step-content.center { align-items: center; text-align: center; }

.step-icon { font-size: 44px; line-height: 1; }

.step-content h1 {
  font-family: 'Cairo', sans-serif;
  font-size: 26px; font-weight: 900; color: #0a0a0a;
  margin: 0; letter-spacing: -.5px;
}
.step-content p { color: #6b7280; font-size: 14px; margin: 0; line-height: 1.6; }

/* ─── Success Circle ─────────────────────────────── */
.success-circle {
  width: 88px; height: 88px; border-radius: 50%;
  background: linear-gradient(135deg, #fffbeb, #fff7ed);
  border: 2px solid rgba(245,158,11,.3);
  display: flex; align-items: center; justify-content: center;
  font-size: 40px;
  animation: popIn .5s cubic-bezier(.2,.9,.2,1) both;
}
.success-circle--done {
  background: linear-gradient(135deg, #ecfdf5, #d1fae5);
  border-color: rgba(16,185,129,.3);
}
@keyframes popIn {
  from { transform: scale(.5); opacity: 0; }
  to   { transform: scale(1);  opacity: 1; }
}

.email-chip {
  background: #f3f4f6; border: 1px solid #e5e7eb;
  border-radius: 40px; padding: 8px 20px;
  font-size: 14px; font-weight: 700; color: #374151;
  dir: ltr; direction: ltr;
}
.hint-text { font-size: 12px; color: #9ca3af; }

/* ─── Fields ─────────────────────────────────────── */
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-group label { font-size: 13px; font-weight: 700; color: #374151; }

.input-wrap { position: relative; display: flex; align-items: center; }
.i-icon {
  position: absolute; right: 14px; font-size: 15px; pointer-events: none;
}
.input-wrap input {
  width: 100%; padding: 12px 44px 12px 44px;
  border-radius: 12px; border: 1.5px solid #e5e7eb;
  background: #fafaf8; color: #111;
  font-family: 'Tajawal', sans-serif; font-size: 14px;
  outline: none; transition: all .2s; box-sizing: border-box;
}
.input-wrap input:focus {
  border-color: #f59e0b; background: #fff;
  box-shadow: 0 0 0 4px rgba(245,158,11,.1);
}
.field-error .input-wrap input { border-color: #ef4444; }
.eye-btn {
  position: absolute; left: 12px;
  background: none; border: none; cursor: pointer; font-size: 15px; padding: 0;
}
.error-msg { font-size: 12px; color: #ef4444; font-weight: 500; }

/* ─── Password Strength ──────────────────────────── */
.pass-strength { display: flex; align-items: center; gap: 8px; }
.ps-bars       { display: flex; gap: 4px; }
.ps-bar        { width: 28px; height: 4px; border-radius: 4px; background: #e5e7eb; transition: background .3s; }
.ps-weak   { background: #ef4444 !important; }
.ps-fair   { background: #f59e0b !important; }
.ps-good   { background: #3b82f6 !important; }
.ps-strong { background: #10b981 !important; }
.ps-label  { font-size: 11px; color: #6b7280; font-weight: 600; }

/* ─── Buttons ────────────────────────────────────── */
.main-btn {
  width: 100%; padding: 14px;
  border-radius: 14px; border: none;
  background: linear-gradient(135deg, #0a0a0a, #1f1f1f);
  color: #fff; font-family: 'Cairo', sans-serif;
  font-size: 15px; font-weight: 900; cursor: pointer;
  transition: all .25s; overflow: hidden; position: relative;
  box-shadow: 0 4px 16px rgba(0,0,0,.15);
  margin-top: 4px;
}
.main-btn::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  opacity: 0; transition: opacity .25s;
}
.main-btn:hover:not(:disabled)::before { opacity: 1; }
.main-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(245,158,11,.3); }
.main-btn:disabled { opacity: .45; cursor: not-allowed; }
.main-btn span { position: relative; z-index: 1; }

.outline-btn {
  background: transparent !important;
  border: 1.5px solid #e5e7eb !important;
  color: #374151 !important;
  box-shadow: none !important;
}
.outline-btn::before { display: none; }
.outline-btn:hover:not(:disabled) {
  border-color: #f59e0b !important;
  color: #f59e0b !important;
  transform: translateY(-1px);
}

/* ─── Server Error ───────────────────────────────── */
.server-error {
  background: #fef2f2; border: 1px solid #fecaca;
  color: #dc2626; border-radius: 12px;
  padding: 11px 14px; font-size: 13px; font-weight: 600;
}

/* ─── Links ──────────────────────────────────────── */
.back-link { text-align: center; font-size: 13px; }
.back-link a { color: #f59e0b; font-weight: 700; text-decoration: none; }
.back-link a:hover { text-decoration: underline; }

/* ─── Spinner ────────────────────────────────────── */
.spinner {
  display: inline-block; width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,.3);
  border-top-color: #fff; border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Step Transition ────────────────────────────── */
.step-enter-active { transition: all .4s cubic-bezier(.2,.9,.2,1); }
.step-leave-active { transition: all .2s ease; }
.step-enter-from   { opacity: 0; transform: translateX(-20px); }
.step-leave-to     { opacity: 0; transform: translateX(20px); }

.fade-enter-active, .fade-leave-active { transition: opacity .25s; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

@media (max-width: 520px) {
  .fp-card { padding: 28px 20px; }
}
</style>