<template>
  <div class="ActivRole">
    <div class="dashboard-shell" :class="{ dark: isDark }">

      <!-- Sidebar -->
      <aside class="dashboard-sidebar">
        <div class="brand-block">
          <div class="logo-mark">Delivro</div>
          <p class="brand-subtitle">لوحة تحكم الزبون</p>
        </div>

        <div class="profile-card">
          <p class="profile-role">الدور</p>
          <h3>{{ roleLabel }}</h3>
          <p class="profile-name">{{ userName }}</p>
        </div>

        <nav class="sidebar-nav">
          <button class="sidebar-link active" @click="refreshData">الرئيسية</button>
          <button class="sidebar-link" @click="createOrder">طلب جديد</button>
          <button class="sidebar-link" @click="goProfile">الملف الشخصي</button>
          <button class="sidebar-link" @click="goFavorites">المفضلة ⭐</button>
          <button class="sidebar-link logout" @click="logout">تسجيل الخروج</button>
        </nav>
      </aside>

      <!-- Main -->
      <main class="dashboard-main">

        <!-- Header -->
        <header class="main-header">
          <div>
            <p class="greeting">مرحباً {{ userName }}،</p>
            <h1>{{ headerTitle }}</h1>
          </div>
          <div class="header-actions">
            <button class="icon-btn notif-btn" @click="toggleNotifications">
              🔔
              <span class="notif-badge" v-if="unreadCount">{{ unreadCount }}</span>
            </button>
            <button class="btn-outline" @click="createOrder">طلب جديد</button>
            <button class="btn-outline" @click="toggleTheme">{{ themeLabel }}</button>
            <button class="icon-btn" @click="logout">🚪</button>
          </div>
        </header>

        <!-- Stats -->
        <section class="stats-grid">
          <article class="stat-card" v-for="card in stats" :key="card.title">
            <p class="stat-label">{{ card.title }}</p>
            <h2>{{ card.value }}</h2>
          </article>
        </section>

        <!-- Table Controls -->
        <section class="table-controls">
          <input v-model="search" type="text" placeholder="ابحث برقم الطلب..." class="table-search" />
          <select v-model="statusFilter" class="table-filter">
            <option value="">كل الحالات</option>
            <option value="pending">قيد الانتظار</option>
            <option value="accepted">مقبول</option>
            <option value="on_the_way">في الطريق</option>
            <option value="completed">مكتمل</option>
            <option value="cancelled">ملغي</option>
          </select>
        </section>

        <!-- Orders Table -->
        <section class="table-wrapper">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th @click="sortBy('id')"># <span v-if="sortKey === 'id'">{{ sortOrderIcon }}</span></th>
                <th @click="sortBy('total_price')">السعر <span v-if="sortKey === 'total_price'">{{ sortOrderIcon }}</span></th>
                <th>مزود الخدمة</th>
                <th>الحالة</th>
                <th>الدفع</th>
                <th>التقييم</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in filteredOrders" :key="order.id">
                <td>{{ order.id }}</td>
                <td class="price-cell">{{ order.total_price }} ₪</td>
                <td>{{ order.provider?.user?.first_name || `متجر #${order.provider_id}` }}</td>
                <td>
                  <span class="status-badge" :class="order.status?.name">
                    {{ getStatusText(order.status?.name) }}
                  </span>
                </td>
                <td>
                  <span class="payment-badge" :class="order.payment_method">
                    {{ order.payment_method === 'paid' ? '✅ مدفوع' : '⏳ معلق' }}
                  </span>
                </td>
                <td>
                  <!-- زر التقييم: فقط إذا مكتمل ومدفوع -->
                  <button
                    v-if="!order.review && order.status?.name === 'Completed' && order.payment_method === 'paid'"
                    class="rate-btn"
                    @click="openReview(order)"
                  >⭐ قيّم</button>
                  <!-- مكتمل لكن لم يدفع بعد -->
                  <span
                    v-else-if="!order.review && order.status?.name === 'Completed' && order.payment_method !== 'paid'"
                    class="awaiting-payment"
                  >💳 بانتظار الدفع</span>
                  <!-- تم التقييم -->
                  <span v-else-if="order.review" class="rated">⭐ {{ order.review.rating }}</span>
                  <span v-else class="not-available">—</span>
                </td>
              </tr>
              <tr v-if="filteredOrders.length === 0">
                <td colspan="6" class="empty-row">لا توجد طلبات</td>
              </tr>
            </tbody>
          </table>
        </section>

      </main>
    </div>

    <!-- ══════════ REVIEW MODAL ══════════ -->
    <transition name="fade">
      <div v-if="showReview" class="review-overlay" @click.self="closeReview">
        <div class="review-modal">
          <button class="close-review" @click="closeReview">✕</button>

          <!-- Header -->
          <div class="review-header">
            <div class="review-order-badge">#{{ selectedOrder?.id }}</div>
            <h2>كيف كانت تجربتك؟</h2>
            <p class="review-subtitle">تقييمك يساعدنا على تحسين الخدمة</p>
          </div>

          <!-- Step Indicator -->
          <div class="review-steps">
            <div class="step" :class="{ active: reviewStep === 1, done: reviewStep > 1 }">
              <span class="step-icon">🏪</span>
              <span>المتجر</span>
            </div>
            <div class="step-line" :class="{ done: reviewStep > 1 }"></div>
            <div class="step" :class="{ active: reviewStep === 2, done: reviewStep > 2 }">
              <span class="step-icon">🚚</span>
              <span>السائق</span>
            </div>
            <div class="step-line" :class="{ done: reviewStep > 2 }"></div>
            <div class="step" :class="{ active: reviewStep === 3 }">
              <span class="step-icon">✅</span>
              <span>إرسال</span>
            </div>
          </div>

          <!-- Step 1: تقييم المتجر -->
          <div v-if="reviewStep === 1" class="review-step-content">
            <div class="review-section">
              <div class="section-icon-title">
                <span class="big-icon">🏪</span>
                <div>
                  <p class="review-section-title">تقييم المتجر</p>
                  <p class="review-section-sub">{{ selectedOrder?.provider?.user?.first_name || 'المتجر' }}</p>
                </div>
              </div>

              <div class="stars-wrapper">
                <div class="stars">
                  <span
                    v-for="i in 5" :key="`p-${i}`"
                    class="star"
                    :class="{ active: i <= (hoverRating || rating) }"
                    @mouseover="hoverRating = i"
                    @mouseleave="hoverRating = 0"
                    @click="rating = i"
                  >★</span>
                </div>
                <span class="rating-label">{{ ratingLabels[hoverRating || rating] || 'اختر تقييماً' }}</span>
              </div>

              <div class="quick-tags">
                <span class="tag-label">ما رأيك في:</span>
                <div class="tags-row">
                  <button
                    v-for="tag in providerTags"
                    :key="tag"
                    class="quick-tag"
                    :class="{ selected: selectedProviderTags.includes(tag) }"
                    @click="toggleTag(selectedProviderTags, tag)"
                  >{{ tag }}</button>
                </div>
              </div>

              <textarea
                v-model="comment"
                placeholder="أضف تعليقاً على المتجر... (اختياري)"
                class="review-textarea"
                rows="3"
              ></textarea>
            </div>

            <div class="step-actions">
              <button class="btn cancel" @click="closeReview">إلغاء</button>
              <button class="btn next" @click="reviewStep = 2" :disabled="!rating">
                التالي — تقييم السائق ←
              </button>
            </div>
          </div>

          <!-- Step 2: تقييم السائق -->
          <div v-if="reviewStep === 2" class="review-step-content">
            <div class="review-section">
              <div class="section-icon-title">
                <span class="big-icon">🚚</span>
                <div>
                  <p class="review-section-title">تقييم السائق</p>
                  <p class="review-section-sub">
                    {{ selectedOrder?.driver?.user?.first_name || 'السائق' }}
                    {{ selectedOrder?.driver?.user?.last_name || '' }}
                  </p>
                </div>
              </div>

              <div class="stars-wrapper">
                <div class="stars">
                  <span
                    v-for="i in 5" :key="`d-${i}`"
                    class="star"
                    :class="{ active: i <= (hoverDriverRating || driverRating) }"
                    @mouseover="hoverDriverRating = i"
                    @mouseleave="hoverDriverRating = 0"
                    @click="driverRating = i"
                  >★</span>
                </div>
                <span class="rating-label">{{ ratingLabels[hoverDriverRating || driverRating] || 'اختر تقييماً' }}</span>
              </div>

              <div class="quick-tags">
                <span class="tag-label">ما رأيك في:</span>
                <div class="tags-row">
                  <button
                    v-for="tag in driverTags"
                    :key="tag"
                    class="quick-tag"
                    :class="{ selected: selectedDriverTags.includes(tag) }"
                    @click="toggleTag(selectedDriverTags, tag)"
                  >{{ tag }}</button>
                </div>
              </div>

              <textarea
                v-model="driverComment"
                placeholder="أضف تعليقاً على السائق... (اختياري)"
                class="review-textarea"
                rows="3"
              ></textarea>
            </div>

            <div class="step-actions">
              <button class="btn cancel" @click="reviewStep = 1">← رجوع</button>
              <button class="btn next" @click="reviewStep = 3" :disabled="!driverRating">
                معاينة التقييم ←
              </button>
            </div>
          </div>

          <!-- Step 3: ملخص وإرسال -->
          <div v-if="reviewStep === 3" class="review-step-content">
            <div class="review-summary">
              <h3>ملخص تقييمك</h3>

              <div class="summary-item">
                <span class="summary-icon">🏪</span>
                <div class="summary-details">
                  <span class="summary-label">المتجر</span>
                  <div class="summary-stars">
                    <span v-for="i in 5" :key="i" class="star sm" :class="{ active: i <= rating }">★</span>
                  </div>
                  <p class="summary-comment" v-if="comment">{{ comment }}</p>
                  <div class="summary-tags" v-if="selectedProviderTags.length">
                    <span v-for="t in selectedProviderTags" :key="t" class="summary-tag">{{ t }}</span>
                  </div>
                </div>
              </div>

              <div class="summary-item">
                <span class="summary-icon">🚚</span>
                <div class="summary-details">
                  <span class="summary-label">السائق</span>
                  <div class="summary-stars">
                    <span v-for="i in 5" :key="i" class="star sm" :class="{ active: i <= driverRating }">★</span>
                  </div>
                  <p class="summary-comment" v-if="driverComment">{{ driverComment }}</p>
                  <div class="summary-tags" v-if="selectedDriverTags.length">
                    <span v-for="t in selectedDriverTags" :key="t" class="summary-tag">{{ t }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="step-actions">
              <button class="btn cancel" @click="reviewStep = 2">← رجوع</button>
              <button class="btn submit" @click="submitReview" :disabled="reviewLoading">
                <span v-if="reviewLoading" class="mini-spinner"></span>
                <span v-else>إرسال التقييم 🚀</span>
              </button>
            </div>
          </div>

        </div>
      </div>
    </transition>

    <!-- ══════════ NOTIFICATIONS PANEL ══════════ -->
    <transition name="slide-left">
      <div class="notifications-panel" v-if="showNotifications">
        <div class="panel-header">
          <h3>🔔 الإشعارات</h3>
          <button class="close-panel" @click="showNotifications = false">✕</button>
        </div>
        <div class="panel-body">
          <div
            v-for="notif in notifications" :key="notif.id"
            class="notification-item"
            :class="{ unread: !notif.is_read, [notif.type]: true }"
          >
            <div class="notif-icon">{{ getNotifIcon(notif.type) }}</div>
            <div class="notif-content">
              <div class="notif-title">{{ notif.title }}</div>
              <div class="notif-message">{{ notif.message }}</div>
              <div class="notif-time">{{ formatTime(notif.created_at) }}</div>
            </div>
            <button v-if="!notif.is_read" class="mark-read-btn" @click="markAsRead(notif)" title="تعيين كمقروء">●</button>
          </div>
          <div v-if="notifications.length === 0" class="empty-notifications">
            <span>📭</span>
            <p>لا توجد إشعارات</p>
          </div>
        </div>
        <div class="panel-footer" v-if="unreadCount > 0">
          <button class="mark-all-btn" @click="markAllRead">تعيين الكل كمقروء</button>
        </div>
      </div>
    </transition>

    <!-- ══════════ TOAST ══════════ -->
    <transition name="toast">
      <div class="toast" :class="toast.type" v-if="toast.show">{{ toast.message }}</div>
    </transition>
  </div>
</template>

<script setup>
import '../assets/styles/dashboard.css'
import api from '../services/api'
import { onMounted, onUnmounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ── State ──────────────────────────────────────────────────
const stats        = ref([])
const orders       = ref([])
const search       = ref('')
const statusFilter = ref('')
const sortKey      = ref('')
const sortOrder    = ref('asc')
const isDark       = ref(localStorage.getItem('delivro_theme') === 'dark')
const email        = ref(localStorage.getItem('delivro_email') || '')

// Notifications
const notifications     = ref([])
const unreadCount       = ref(0)
const showNotifications = ref(false)
let   notifInterval     = null

// Review
const showReview        = ref(false)
const selectedOrder     = ref(null)
const reviewStep        = ref(1)
const rating            = ref(0)
const hoverRating       = ref(0)
const comment           = ref('')
const driverRating      = ref(0)
const hoverDriverRating = ref(0)
const driverComment     = ref('')
const reviewLoading     = ref(false)
const selectedProviderTags = ref([])
const selectedDriverTags   = ref([])

// Toast
const toast = ref({ show: false, message: '', type: 'success' })

// ── Static Review Data ─────────────────────────────────────
const ratingLabels = {
  1: 'سيء جداً 😞',
  2: 'سيء 😕',
  3: 'مقبول 😐',
  4: 'جيد 😊',
  5: 'ممتاز 🤩'
}
const providerTags = ['جودة الطعام', 'التغليف', 'الدقة في الطلب', 'السعر المناسب', 'التنوع']
const driverTags   = ['سرعة التوصيل', 'الأدب والاحترام', 'الدقة في العنوان', 'النظافة']

// ── Helpers ────────────────────────────────────────────────
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => (toast.value.show = false), 3000)
}

const toggleTag = (arr, tag) => {
  const idx = arr.indexOf(tag)
  if (idx === -1) arr.push(tag)
  else arr.splice(idx, 1)
}

// ── Computed ───────────────────────────────────────────────
const userName    = computed(() => email.value ? email.value.split('@')[0] : 'صديقنا')
const roleLabel   = computed(() => 'زبون')
const headerTitle = computed(() => 'طلباتك ومشترياتك')
const themeLabel  = computed(() => isDark.value ? 'الوضع الفاتح' : 'الوضع الداكن')
const sortOrderIcon = computed(() => sortOrder.value === 'asc' ? '▲' : '▼')

const filteredOrders = computed(() => {
  let result = [...orders.value]
  if (search.value)
    result = result.filter(o => o.id.toString().includes(search.value))
  if (statusFilter.value)
    result = result.filter(o => o.status?.name?.toLowerCase() === statusFilter.value)
  if (sortKey.value) {
    result.sort((a, b) => {
      let aVal = sortKey.value === 'status' ? a.status?.name : a[sortKey.value]
      let bVal = sortKey.value === 'status' ? b.status?.name : b[sortKey.value]
      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }
  return result
})

// ── Sort ───────────────────────────────────────────────────
const sortBy = (key) => {
  if (sortKey.value === key) sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = key; sortOrder.value = 'asc' }
}

// ── API: Dashboard ─────────────────────────────────────────
const loadDashboard = async () => {
  try {
    const { data } = await api.get('/customer/dashboard')
    stats.value = [
      { title: 'الطلبات النشطة',    value: data.active_orders },
      { title: 'المتاجر المفضلة',   value: data.favorites },
      { title: 'المصروف هذا الشهر', value: `${data.monthly_spending || 0} ₪` },
    ]
    orders.value = data.orders || []
  } catch (e) {
    console.error('loadDashboard:', e)
  }
}

// ── API: Notifications ─────────────────────────────────────
const loadNotifications = async () => {
  try {
    const [nRes, cRes] = await Promise.all([
      api.get('/notifications'),
      api.get('/notifications/unread-count'),
    ])
    notifications.value = nRes.data.data || nRes.data
    unreadCount.value   = cRes.data.count || 0
  } catch (e) {
    console.error('loadNotifications:', e)
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const markAsRead = async (notif) => {
  try {
    await api.post(`/notifications/${notif.id}/read`)
    notif.is_read = true
    if (unreadCount.value > 0) unreadCount.value--
  } catch (e) {
    console.error('markAsRead:', e)
  }
}

const markAllRead = async () => {
  try {
    await api.post('/notifications/read-all')
    notifications.value.forEach(n => (n.is_read = true))
    unreadCount.value = 0
  } catch (e) {
    console.error('markAllRead:', e)
  }
}

// ── Review ─────────────────────────────────────────────────
const openReview = async (order) => {
  try {
    const { data } = await api.get(`/orders/${order.id}`)
    selectedOrder.value = data.data || data
  } catch {
    selectedOrder.value = order
  }
  rating.value               = 0
  hoverRating.value          = 0
  comment.value              = ''
  driverRating.value         = 0
  hoverDriverRating.value    = 0
  driverComment.value        = ''
  reviewStep.value           = 1
  selectedProviderTags.value = []
  selectedDriverTags.value   = []
  showReview.value           = true
}

const closeReview = () => {
  showReview.value = false
  reviewStep.value = 1
}

const submitReview = async () => {
  if (!rating.value) return showToast('الرجاء اختيار تقييم للمتجر', 'error')
  reviewLoading.value = true
  try {
    const providerComment = [
      comment.value,
      selectedProviderTags.value.length ? `✓ ${selectedProviderTags.value.join(' | ')}` : ''
    ].filter(Boolean).join('\n')

    const driverFullComment = [
      driverComment.value,
      selectedDriverTags.value.length ? `✓ ${selectedDriverTags.value.join(' | ')}` : ''
    ].filter(Boolean).join('\n')

    await api.post('/reviews', {
      order_id:       selectedOrder.value.id,
      rating:         rating.value,
      comment:        providerComment,
      driver_rating:  selectedOrder.value.driver ? driverRating.value : null,
      driver_comment: selectedOrder.value.driver ? driverFullComment : null,
    })

    const idx = orders.value.findIndex(o => o.id === selectedOrder.value.id)
    if (idx !== -1) {
      orders.value[idx].review = { rating: rating.value }
    }

    showToast('شكراً! تم إرسال تقييمك بنجاح ⭐')
    closeReview()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ أثناء الإرسال', 'error')
  } finally {
    reviewLoading.value = false
  }
}

// ── Misc ───────────────────────────────────────────────────
const getStatusText = (status) =>
  ({ pending: 'معلق', accepted: 'مقبول', on_the_way: 'في الطريق', completed: 'مكتمل', rejected: 'مرفوض', cancelled: 'ملغي' }[status] || status || '—')

const getNotifIcon = (type) =>
  ({ order_accepted: '✅', order_rejected: '❌', order_on_the_way: '🚚', order_completed: '📦',
     order_cancelled: '🚫', payment_confirmed: '💰', payment_received: '💰', new_review: '⭐' }[type] || '🔔')

const formatTime = (date) => date ? new Date(date).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' }) : ''

const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}
const refreshData = () => { loadDashboard(); loadNotifications() }
const createOrder = () => router.push('/show-provider')
const goProfile   = () => router.push('/customer/profile')
const goFavorites = () => router.push('/customer/favorites')
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  localStorage.removeItem('delivro_email')
  router.push('/login')
}

// ── Lifecycle ──────────────────────────────────────────────
onMounted(() => {
  loadDashboard()
  loadNotifications()
  notifInterval = setInterval(loadNotifications, 20000)
})
onUnmounted(() => { if (notifInterval) clearInterval(notifInterval) })
</script>

<style scoped>
/* ─── shell & layout ─── */
.ActivRole { min-height: 100vh; direction: rtl; }
.dashboard-shell { display: flex; min-height: 100vh; background: #f8fafc; color: #1e293b; }
.dashboard-shell.dark { background: #0f172a; color: #e2e8f0; }

/* ─── sidebar ─── */
.dashboard-sidebar { width: 240px; background: #1e293b; color: #e2e8f0; padding: 24px 16px; display: flex; flex-direction: column; gap: 20px; flex-shrink: 0; }
.brand-block { text-align: center; }
.logo-mark { font-size: 28px; font-weight: 900; color: #38bdf8; letter-spacing: -1px; }
.brand-subtitle { font-size: 12px; color: #64748b; margin: 4px 0 0; }
.profile-card { background: rgba(255,255,255,.05); border-radius: 12px; padding: 16px; text-align: center; }
.profile-role { font-size: 11px; color: #64748b; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 1px; }
.profile-card h3 { margin: 0 0 4px; font-size: 18px; color: #38bdf8; }
.profile-name { font-size: 13px; color: #94a3b8; margin: 0; }
.sidebar-nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.sidebar-link { background: none; border: none; color: #94a3b8; padding: 10px 14px; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; text-align: right; transition: all .2s; }
.sidebar-link:hover, .sidebar-link.active { background: rgba(56,189,248,.15); color: #38bdf8; }
.sidebar-link.logout { color: #f87171; margin-top: auto; }
.sidebar-link.logout:hover { background: rgba(239,68,68,.15); }

/* ─── main ─── */
.dashboard-main { flex: 1; padding: 28px; overflow-y: auto; }
.main-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
.greeting { font-size: 13px; color: #64748b; margin: 0 0 4px; }
.main-header h1 { font-size: 24px; font-weight: 800; margin: 0; }
.header-actions { display: flex; gap: 10px; align-items: center; }
.btn-outline { padding: 8px 18px; border-radius: 10px; border: 1.5px solid #38bdf8; background: none; color: #38bdf8; font-weight: 600; cursor: pointer; transition: all .2s; font-size: 13px; }
.btn-outline:hover { background: #38bdf8; color: #0f172a; }
.icon-btn { width: 40px; height: 40px; border-radius: 10px; border: 1.5px solid rgba(148,163,184,.3); background: none; font-size: 18px; cursor: pointer; position: relative; transition: all .2s; }
.icon-btn:hover { border-color: #38bdf8; background: rgba(56,189,248,.1); }
.notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: #fff; font-size: 10px; padding: 1px 5px; border-radius: 10px; min-width: 16px; font-weight: 700; }

/* ─── stats ─── */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 1px 8px rgba(0,0,0,.06); border: 1px solid #e2e8f0; }
.dashboard-shell.dark .stat-card { background: #1e293b; border-color: #334155; }
.stat-label { font-size: 12px; color: #64748b; margin: 0 0 8px; text-transform: uppercase; letter-spacing: .5px; }
.stat-card h2 { font-size: 28px; font-weight: 800; color: #38bdf8; margin: 0; }

/* ─── table controls ─── */
.table-controls { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.table-search, .table-filter { padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; font-size: 14px; color: #1e293b; outline: none; transition: border-color .2s; }
.dashboard-shell.dark .table-search,
.dashboard-shell.dark .table-filter { background: #1e293b; border-color: #334155; color: #e2e8f0; }
.table-search:focus, .table-filter:focus { border-color: #38bdf8; }
.table-search { flex: 1; min-width: 160px; }

/* ─── table ─── */
.table-wrapper { background: #fff; border-radius: 16px; box-shadow: 0 1px 8px rgba(0,0,0,.06); overflow: hidden; border: 1px solid #e2e8f0; }
.dashboard-shell.dark .table-wrapper { background: #1e293b; border-color: #334155; }
.dashboard-table { width: 100%; border-collapse: collapse; }
.dashboard-table th { padding: 14px 16px; text-align: right; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0; cursor: pointer; user-select: none; }
.dashboard-shell.dark .dashboard-table th { background: #0f172a; border-color: #334155; }
.dashboard-table th:hover { color: #38bdf8; }
.dashboard-table td { padding: 14px 16px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
.dashboard-shell.dark .dashboard-table td { border-color: #1e293b; }
.dashboard-table tr:last-child td { border-bottom: none; }
.dashboard-table tr:hover td { background: rgba(56,189,248,.04); }
.price-cell { font-weight: 700; color: #0ea5e9; }
.empty-row { text-align: center; color: #94a3b8; padding: 32px; }

/* ─── status badges ─── */
.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-badge.pending    { background: rgba(245,158,11,.15); color: #d97706; }
.status-badge.accepted   { background: rgba(56,189,248,.15); color: #0ea5e9; }
.status-badge.on_the_way { background: rgba(139,92,246,.15); color: #7c3aed; }
.status-badge.completed  { background: rgba(16,185,129,.15); color: #059669; }
.status-badge.rejected,
.status-badge.cancelled  { background: rgba(239,68,68,.15); color: #dc2626; }

.payment-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.payment-badge.paid    { background: rgba(16,185,129,.15); color: #059669; }
.payment-badge.pending { background: rgba(245,158,11,.15); color: #d97706; }

.rate-btn { padding: 6px 14px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all .2s; }
.rate-btn:hover { transform: scale(1.05); }
.rated { font-size: 13px; color: #f59e0b; font-weight: 700; }
.not-available { color: #94a3b8; }
.awaiting-payment { font-size: 12px; color: #f59e0b; font-weight: 600; }

/* ─── Review Modal ─── */
.review-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.7); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
.review-modal { background: #0f172a; border-radius: 24px; padding: 32px; width: 100%; max-width: 500px; border: 1px solid rgba(56,189,248,.25); position: relative; max-height: 90vh; overflow-y: auto; }
.close-review { position: absolute; top: 16px; left: 16px; background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer; }

/* Review Header */
.review-header { text-align: center; margin-bottom: 20px; }
.review-order-badge { display: inline-block; background: rgba(56,189,248,.15); color: #38bdf8; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 8px; }
.review-header h2 { color: #fff; margin: 0 0 4px; font-size: 20px; }
.review-subtitle { color: #64748b; font-size: 13px; margin: 0; }

/* Step Indicator */
.review-steps { display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
.step { display: flex; flex-direction: column; align-items: center; gap: 4px; opacity: .4; transition: all .3s; }
.step.active { opacity: 1; }
.step.done { opacity: .7; }
.step-icon { font-size: 20px; }
.step span:last-child { font-size: 11px; color: #94a3b8; }
.step.active span:last-child { color: #38bdf8; font-weight: 700; }
.step-line { width: 40px; height: 2px; background: #1e293b; margin: 0 4px; margin-bottom: 14px; transition: background .3s; }
.step-line.done { background: #38bdf8; }

/* Step Content */
.review-step-content { }
.review-section { margin-bottom: 20px; padding: 16px; background: rgba(255,255,255,.04); border-radius: 16px; border: 1px solid rgba(255,255,255,.06); }
.section-icon-title { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; }
.big-icon { font-size: 36px; }
.review-section-title { color: #94a3b8; font-size: 13px; font-weight: 600; margin: 0 0 2px; }
.review-section-sub { color: #38bdf8; font-size: 12px; margin: 0; }

/* Stars */
.stars-wrapper { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-bottom: 16px; }
.stars { display: flex; gap: 6px; }
.star { font-size: 32px; color: #334155; cursor: pointer; transition: all .15s; line-height: 1; }
.star.active { color: #f59e0b; transform: scale(1.1); }
.star.sm { font-size: 16px; transform: none !important; }
.rating-label { font-size: 13px; color: #94a3b8; min-height: 20px; }

/* Quick Tags */
.quick-tags { margin-bottom: 12px; }
.tag-label { font-size: 12px; color: #64748b; display: block; margin-bottom: 8px; }
.tags-row { display: flex; flex-wrap: wrap; gap: 8px; }
.quick-tag { padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04); color: #94a3b8; font-size: 12px; cursor: pointer; transition: all .2s; }
.quick-tag:hover { border-color: #38bdf8; color: #38bdf8; }
.quick-tag.selected { background: rgba(56,189,248,.15); border-color: #38bdf8; color: #38bdf8; font-weight: 600; }

/* Textarea */
.review-textarea { width: 100%; padding: 10px 12px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 10px; color: #e2e8f0; font-size: 13px; resize: vertical; min-height: 60px; box-sizing: border-box; font-family: inherit; }
.review-textarea:focus { outline: none; border-color: #38bdf8; }

/* Step Actions */
.step-actions { display: flex; gap: 12px; margin-top: 8px; }
.btn { flex: 1; padding: 12px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; font-size: 14px; transition: all .2s; }
.btn.cancel { background: rgba(255,255,255,.08); color: #94a3b8; }
.btn.cancel:hover { background: rgba(255,255,255,.15); }
.btn.next { background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: #0f172a; }
.btn.next:disabled { opacity: .4; cursor: not-allowed; }
.btn.next:not(:disabled):hover { transform: scale(1.02); }
.btn.submit { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
.btn.submit:hover:not(:disabled) { transform: scale(1.02); }
.btn.submit:disabled { opacity: .6; cursor: not-allowed; }

/* Summary */
.review-summary h3 { color: #e2e8f0; text-align: center; margin: 0 0 20px; font-size: 16px; }
.summary-item { display: flex; gap: 12px; padding: 14px; background: rgba(255,255,255,.04); border-radius: 12px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,.06); }
.summary-icon { font-size: 28px; }
.summary-details { flex: 1; }
.summary-label { color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
.summary-stars { display: flex; gap: 3px; margin: 6px 0 4px; }
.summary-comment { color: #cbd5e1; font-size: 13px; margin: 4px 0; line-height: 1.5; }
.summary-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.summary-tag { padding: 3px 10px; background: rgba(56,189,248,.1); color: #38bdf8; border-radius: 12px; font-size: 11px; }

/* Spinner */
.mini-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; vertical-align: middle; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Notifications Panel ─── */
.notifications-panel { position: fixed; top: 0; right: 0; width: 360px; height: 100vh; background: #0f172a; border-left: 1px solid rgba(56,189,248,.2); z-index: 1500; display: flex; flex-direction: column; box-shadow: -4px 0 24px rgba(0,0,0,.4); }
.panel-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #1e293b; }
.panel-header h3 { color: #e2e8f0; margin: 0; font-size: 17px; }
.close-panel { background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer; }
.panel-body { flex: 1; overflow-y: auto; padding: 12px; }
.notification-item { display: flex; gap: 10px; padding: 12px; border-radius: 12px; margin-bottom: 8px; background: rgba(255,255,255,.03); cursor: default; }
.notification-item.unread { background: rgba(56,189,248,.08); border-right: 3px solid #38bdf8; }
.notification-item.order_cancelled { border-right-color: #ef4444 !important; }
.notification-item.payment_confirmed { border-right-color: #f59e0b !important; }
.notif-icon { font-size: 22px; flex-shrink: 0; }
.notif-content { flex: 1; min-width: 0; }
.notif-title { color: #e2e8f0; font-weight: 600; font-size: 13px; margin-bottom: 3px; }
.notif-message { color: #94a3b8; font-size: 12px; line-height: 1.4; }
.notif-time { color: #475569; font-size: 10px; margin-top: 4px; }
.mark-read-btn { background: none; border: none; color: #38bdf8; cursor: pointer; font-size: 14px; flex-shrink: 0; }
.empty-notifications { text-align: center; padding: 48px 24px; color: #475569; }
.empty-notifications span { font-size: 40px; display: block; margin-bottom: 10px; }
.panel-footer { padding: 12px 16px; border-top: 1px solid #1e293b; }
.mark-all-btn { width: 100%; padding: 10px; background: rgba(56,189,248,.1); color: #38bdf8; border: 1px solid rgba(56,189,248,.2); border-radius: 10px; font-weight: 600; cursor: pointer; transition: all .2s; }
.mark-all-btn:hover { background: rgba(56,189,248,.2); }

/* ─── Toast ─── */
.toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); padding: 12px 28px; border-radius: 12px; font-weight: 600; font-size: 14px; z-index: 9999; box-shadow: 0 4px 20px rgba(0,0,0,.3); }
.toast.success { background: #10b981; color: #fff; }
.toast.error   { background: #ef4444; color: #fff; }
.toast-enter-active, .toast-leave-active { transition: all .4s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(12px); }

/* ─── Transitions ─── */
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform .3s; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(100%); }

/* ─── Responsive ─── */
@media (max-width: 768px) {
  .dashboard-shell { flex-direction: column; }
  .dashboard-sidebar { width: 100%; flex-direction: row; flex-wrap: wrap; padding: 12px; }
  .sidebar-nav { flex-direction: row; flex-wrap: wrap; }
  .notifications-panel { width: 100%; }
}
</style>