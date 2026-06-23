<template>
  <div class="driver-dashboard" dir="rtl">
    <div class="dashboard-bg"></div>

    <div class="dashboard-container">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="driver-profile-card">
          <div class="driver-avatar-large">
            <img :src="driverImage" :alt="driverName" @error="handleImageError" />
            <div class="online-status" :class="{ online: driver?.is_available }"></div>
          </div>
          <h3 class="driver-name">{{ driverName }}</h3>
          <div class="driver-rating">
            <div class="stars">
              <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= Math.round(driverRating) }">★</span>
            </div>
            <span class="rating-value">{{ driverRating.toFixed(1) }}</span>
            <span class="review-count">({{ driverTotalReviews }} تقييم)</span>
          </div>
          <button class="availability-toggle" :class="{ available: driver?.is_available }" @click="toggleAvailability">
            <span class="status-dot"></span>
            {{ driver?.is_available ? 'متاح حالياً' : 'غير متاح' }}
          </button>
        </div>

        <nav class="sidebar-nav">
          <router-link to="/driver/dashboard" class="nav-item" active-class="active">
            <span class="nav-icon">📊</span><span>لوحة التحكم</span>
          </router-link>
          <router-link to="/driver/orders" class="nav-item" active-class="active">
            <span class="nav-icon">📦</span><span>الطلبات</span>
            <span class="badge" v-if="pendingOrdersCount">{{ pendingOrdersCount }}</span>
          </router-link>
          <router-link to="/driver/history" class="nav-item" active-class="active">
            <span class="nav-icon">📜</span><span>سجل التوصيل</span>
          </router-link>
          <router-link to="/driver/reviews" class="nav-item" active-class="active">
            <span class="nav-icon">⭐</span><span>تقييماتي</span>
          </router-link>
          <router-link to="/driver/profile" class="nav-item" active-class="active">
            <span class="nav-icon">👤</span><span>الملف الشخصي</span>
          </router-link>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="main-area">
        <header class="content-header">
          <h1>لوحة تحكم السائق</h1>
          <div class="header-actions">
            <button class="notification-btn" @click="toggleNotifications">
              🔔
              <span class="notification-badge" v-if="unreadCount">{{ unreadCount }}</span>
            </button>
            <button class="logout-btn" @click="logout">🚪</button>
          </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card" v-for="stat in statsCards" :key="stat.label">
            <div class="stat-icon">{{ stat.icon }}</div>
            <div class="stat-info">
              <span class="stat-value">{{ stat.value }}</span>
              <span class="stat-label">{{ stat.label }}</span>
            </div>
          </div>
        </div>

        <!-- ✅ Cash Payment Alert -->
        <div class="cash-alert" v-if="pendingCashOrders.length > 0">
          <div class="cash-alert-header">
            <span>💵 طلبات تنتظر تأكيد استلام المبلغ كاش</span>
            <span class="cash-badge">{{ pendingCashOrders.length }}</span>
          </div>
          <div class="cash-orders-list">
            <div v-for="order in pendingCashOrders" :key="order.id" class="cash-order-item">
              <div class="cash-order-info">
                <span class="cash-order-id">طلب #{{ order.id }}</span>
                <span class="cash-customer">{{ order.user?.first_name }} {{ order.user?.last_name }}</span>
              </div>
              <div class="cash-order-amount">{{ order.total_price }} ₪</div>
              <button class="btn-cash-confirm" @click="confirmCash(order)" :disabled="cashLoading[order.id]">
                {{ cashLoading[order.id] ? '...' : '💰 تأكيد الاستلام' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Pending Orders -->
        <div class="section">
          <div class="section-header">
            <h2>🕐 الطلبات المعلقة</h2>
            <span class="section-badge">{{ pendingOrders.length }}</span>
          </div>
          <div class="orders-list">
            <div v-for="order in pendingOrders" :key="order.id" class="order-card">
              <div class="order-header">
                <div class="order-id"><span class="order-icon">📋</span> طلب #{{ order.id }}</div>
                <div class="order-amount">{{ order.total_price }} ₪</div>
              </div>
              <div class="order-body">
                <div class="order-info">
                  <div class="info-row"><span class="label">👤 العميل:</span><span>{{ order.user?.first_name }} {{ order.user?.last_name }}</span></div>
                  <div class="info-row"><span class="label">📍 العنوان:</span><span class="address-text">{{ order.order_address || 'غير محدد' }}</span></div>
                  <div class="info-row"><span class="label">🏪 المتجر:</span><span>{{ order.provider?.user?.first_name || 'متجر' }}</span></div>
                  <div class="info-row" v-if="order.products?.length"><span class="label">🛒 المنتجات:</span><span>{{ getProductsList(order.products) }}</span></div>
                </div>
                <div class="order-actions">
                  <button class="btn-view" @click="viewOrderDetails(order)">📋 تفاصيل</button>
                  <button class="btn-accept" @click="acceptOrder(order)">✅ قبول</button>
                  <button class="btn-reject" @click="showRejectModal(order)">❌ رفض</button>
                </div>
              </div>
            </div>
            <div v-if="pendingOrders.length === 0" class="empty-state">
              <span>✨</span>
              <p>لا توجد طلبات معلقة</p>
            </div>
          </div>
        </div>

        <!-- In Progress Orders -->
        <div class="section" v-if="inProgressOrders.length > 0">
          <div class="section-header">
            <h2>🚚 الطلبات قيد التوصيل</h2>
            <span class="section-badge in-progress">{{ inProgressOrders.length }}</span>
          </div>
          <div class="orders-list">
            <div v-for="order in inProgressOrders" :key="order.id" class="order-card in-progress-card">
              <div class="order-header">
                <div class="order-id"><span class="order-icon">🚚</span> طلب #{{ order.id }}</div>
                <div class="order-status-badge" :class="getStatusClass(order)">
                  {{ getStatusText(order) }}
                </div>
              </div>
              <div class="order-body">
                <div class="order-info">
                  <div class="info-row"><span class="label">👤 العميل:</span><span>{{ order.user?.first_name }} {{ order.user?.last_name }}</span></div>
                  <div class="info-row"><span class="label">📍 العنوان:</span><span>{{ order.order_address || 'غير محدد' }}</span></div>
                  <div class="info-row"><span class="label">💵 المبلغ:</span><span class="cash-highlight">{{ order.total_price }} ₪</span></div>
                  <!-- ✅ بيج طريقة الدفع -->
                  <div class="info-row" v-if="order.payment_method">
                    <span class="label">💳 الدفع:</span>
                    <span :class="isCashOrder(order) ? 'payment-cash' : 'payment-online'">
                      {{ isCashOrder(order) ? '💵 نقدي' : '💳 إلكتروني' }}
                    </span>
                  </div>
                </div>
                <div class="order-actions">
                  <!-- مقبول → بدء التوصيل أو إلغاء -->
                  <template v-if="isStatus(order, 'accepted')">
                    <button class="btn-start" @click="startDelivery(order)">🚚 بدء التوصيل</button>
                    <button class="btn-cancel-order" @click="showCancelModal(order)">🚫 إلغاء</button>
                  </template>
                  <!-- في الطريق → تأكيد التوصيل (مع modal) أو إلغاء -->
                  <template v-if="isStatus(order, 'on_the_way')">
                    <button class="btn-complete" @click="showCompleteModal(order)">✅ تأكيد التوصيل</button>
                    <button class="btn-cancel-order" @click="showCancelModal(order)">🚫 إلغاء</button>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Deliveries -->
        <div class="section">
          <div class="section-header"><h2>📦 آخر الطلبات الموصلة</h2></div>
          <div class="recent-list">
            <div v-for="delivery in recentDeliveries" :key="delivery.id" class="recent-item">
              <div class="recent-icon">✅</div>
              <div class="recent-info">
                <div class="recent-customer">{{ delivery.user?.first_name }} {{ delivery.user?.last_name }}</div>
                <div class="recent-address">{{ delivery.order_address || 'عنوان غير محدد' }}</div>
              </div>
              <div class="recent-time">{{ formatDate(delivery.updated_at) }}</div>
            </div>
            <div v-if="recentDeliveries.length === 0" class="empty-state">
              <span>📭</span><p>لا توجد توصيلات مكتملة بعد</p>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!--  Order Details Modal  -->
    <transition name="modal">
      <div class="modal-overlay" v-if="selectedOrder" @click.self="selectedOrder = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>تفاصيل الطلب #{{ selectedOrder.id }}</h3>
            <button class="close-btn" @click="selectedOrder = null">✕</button>
          </div>
          <div class="modal-body">
            <div class="details-section">
              <h4>معلومات العميل</h4>
              <p><strong>الاسم:</strong> {{ selectedOrder.user?.first_name }} {{ selectedOrder.user?.last_name }}</p>
              <p><strong>الهاتف:</strong> {{ selectedOrder.user?.phone || 'غير متوفر' }}</p>
              <p><strong>العنوان:</strong> {{ selectedOrder.order_address || 'غير محدد' }}</p>
            </div>
            <div class="details-section">
              <h4>المنتجات</h4>
              <div v-for="product in selectedOrder.products" :key="product.id" class="product-item">
                <span>{{ product.type }} x{{ product.pivot?.quantity || product.quantity }}</span>
                <span>{{ product.pivot?.price || product.price }} ₪</span>
              </div>
              <div class="total-amount">
                <strong>الإجمالي:</strong>
                <strong>{{ selectedOrder.total_price }} ₪</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!--  Rejection Modal  -->
    <transition name="modal">
      <div class="modal-overlay" v-if="rejectOrderData" @click.self="rejectOrderData = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>رفض الطلب #{{ rejectOrderData.id }}</h3>
            <button class="close-btn" @click="rejectOrderData = null">✕</button>
          </div>
          <div class="modal-body">
            <textarea v-model="rejectionReason" placeholder="اكتب سبب الرفض..." class="rejection-input" rows="4"></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="rejectOrderData = null">إلغاء</button>
            <button class="btn-confirm-red" @click="confirmRejection">تأكيد الرفض</button>
          </div>
        </div>
      </div>
    </transition>

    <!--  Cancel Order Modal  -->
    <transition name="modal">
      <div class="modal-overlay" v-if="cancelOrderData" @click.self="cancelOrderData = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>🚫 إلغاء الطلب #{{ cancelOrderData.id }}</h3>
            <button class="close-btn" @click="cancelOrderData = null">✕</button>
          </div>
          <div class="modal-body">
            <p class="cancel-warning">⚠️ هل أنت متأكد من إلغاء هذا الطلب؟ سيتم إشعار الزبون والمتجر.</p>
            <textarea v-model="cancelReason" placeholder="اكتب سبب الإلغاء..." class="rejection-input" rows="4"></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="cancelOrderData = null">تراجع</button>
            <button class="btn-confirm-red" @click="confirmCancel" :disabled="cancelLoading">
              {{ cancelLoading ? 'جاري الإلغاء...' : '🚫 تأكيد الإلغاء' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ──  Complete Delivery Modal (جديد) ───────────────── -->
    <transition name="modal">
      <div class="modal-overlay" v-if="completeOrderData" @click.self="completeOrderData = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>✅ تأكيد إتمام التوصيل #{{ completeOrderData.id }}</h3>
            <button class="close-btn" @click="completeOrderData = null">✕</button>
          </div>
          <div class="modal-body">

            <!-- لو الدفع كاش -->
            <div v-if="isCashOrder(completeOrderData)" class="cash-confirm-box">
              <div class="cash-icon">💵</div>
              <div class="cash-confirm-text">
                <p>هذا الطلب يُدفع <strong>نقداً</strong></p>
                <p class="cash-amount-big">{{ completeOrderData.total_price }} ₪</p>
                <p class="cash-note">تأكد أنك استلمت المبلغ من الزبون قبل إغلاق الطلب</p>
              </div>
            </div>

            <!-- لو الدفع أونلاين -->
            <div v-else class="online-confirm-box">
              <div class="online-icon">💳</div>
              <p>الدفع تم إلكترونياً، هل تأكدت من إيصال الطلب للزبون؟</p>
            </div>

            <!-- ملخص الطلب -->
            <div class="order-summary">
              <div class="summary-row">
                <span>👤 الزبون</span>
                <span>{{ completeOrderData.user?.first_name }} {{ completeOrderData.user?.last_name }}</span>
              </div>
              <div class="summary-row">
                <span>📍 العنوان</span>
                <span>{{ completeOrderData.order_address || 'غير محدد' }}</span>
              </div>
              <div class="summary-row total">
                <span>💰 الإجمالي</span>
                <span>{{ completeOrderData.total_price }} ₪</span>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="completeOrderData = null">تراجع</button>
            <button
              class="btn-confirm-green"
              @click="confirmComplete"
              :disabled="completeLoading"
            >
              <span v-if="completeLoading">جاري الإتمام...</span>
              <span v-else-if="isCashOrder(completeOrderData)">💵 استلمت المبلغ وأتممت التوصيل</span>
              <span v-else>✅ تأكيد إتمام التوصيل</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!--  Notifications Panel  -->
    <transition name="slide">
      <div class="notifications-panel" v-if="showNotifications">
        <div class="panel-header">
          <h3>الإشعارات</h3>
          <button class="close-panel" @click="showNotifications = false">✕</button>
        </div>
        <div class="panel-body">
          <div v-for="notif in notifications" :key="notif.id"
               class="notification-item" :class="{ unread: !notif.is_read }">
            <div class="notif-icon">{{ getNotifIcon(notif.type) }}</div>
            <div class="notif-content">
              <div class="notif-title">{{ notif.title }}</div>
              <div class="notif-message">{{ notif.message }}</div>
              <div class="notif-time">{{ formatTime(notif.created_at) }}</div>
            </div>
            <button v-if="!notif.is_read" class="mark-read" @click="markAsRead(notif)">●</button>
          </div>
          <div v-if="notifications.length === 0" class="empty-notifications">
            <span>📭</span><p>لا توجد إشعارات</p>
          </div>
        </div>
      </div>
    </transition>

    <!--  Toast  -->
    <transition name="toast">
      <div class="toast" :class="toast.type" v-if="toast.show">{{ toast.message }}</div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

//  State 
const driver            = ref(null)
const stats             = ref({ today_orders: 0, total_delivered: 0, rating: 0, total_reviews: 0 })
const pendingOrders     = ref([])
const inProgressOrders  = ref([])
const recentDeliveries  = ref([])
const pendingCashOrders = ref([])
const notifications     = ref([])
const unreadCount       = ref(0)
const showNotifications = ref(false)
const selectedOrder     = ref(null)
const rejectOrderData   = ref(null)
const rejectionReason   = ref('')
const cancelOrderData   = ref(null)
const cancelReason      = ref('')
const cancelLoading     = ref(false)
const cashLoading       = ref({})
const loading           = ref(false)
const toast             = ref({ show: false, message: '', type: 'success' })

//  State الجديد لـ complete modal
const completeOrderData = ref(null)
const completeLoading   = ref(false)

//  Toast helper 
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => (toast.value.show = false), 3000)
}

//  Helper للحالة — يدعم string و object 
const getOrderStatusName = (order) => {
  const status = order.status
  if (!status) return ''
  
  const name = typeof status === 'object' 
    ? (status.name || '') 
    : String(status)
  
  //  حوّل CamelCase → snake_case
  // OnTheWay → on_the_way ، Accepted → accepted ، Completed → completed
  return name
    .replace(/([A-Z])/g, '_$1')   // OnTheWay → _On_The_Way
    .replace(/^_/, '')             // شيل الـ _ من البداية
    .toLowerCase()                 // on_the_way
}

const isStatus = (order, statusName) => {
  return getOrderStatusName(order) === statusName.toLowerCase().trim()
}

const getStatusText = (order) => {
  const statusName = getOrderStatusName(order)
  const map = {
    'pending':    'معلق',
    'accepted':   'مقبول',
    'on_the_way': 'قيد التوصيل',
    'completed':  'مكتمل',
    'Rejected':   'مرفوض',
    'cancelled':  'ملغي'
  }
  return map[statusName] || statusName || 'غير معروف'
}

const getStatusClass = (order) => getOrderStatusName(order)

//  Helper لتحديد إذا الطلب دفعه نقدي
const isCashOrder = (order) => {
  if (!order) return false
  const method = order.payment_method || order.payment?.method || ''
  return String(method).toLowerCase() === 'cash'
}

//  Computed 
const driverName = computed(() => driver.value?.user
  ? `${driver.value.user.first_name} ${driver.value.user.last_name}`
  : 'السائق'
)
const driverImage        = computed(() => driver.value?.user?.image_path || '/default-avatar.png')
const driverRating       = computed(() => Number(stats.value.rating) || 0)
const driverTotalReviews = computed(() => stats.value.total_reviews || 0)
const pendingOrdersCount = computed(() => pendingOrders.value.length)

const statsCards = computed(() => [
  { icon: '📊', value: stats.value.today_orders,     label: 'طلبات اليوم'     },
  { icon: '✅', value: stats.value.total_delivered,   label: 'إجمالي التوصيل' },
  { icon: '⭐', value: driverRating.value.toFixed(1), label: 'التقييم'         },
  { icon: '👍', value: driverTotalReviews.value,      label: 'تقييمات'         },
])

//  API Calls 
const loadDashboard = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/driver/dashboard')
    driver.value           = data.driver
    stats.value            = data.stats              || { today_orders: 0, total_delivered: 0, rating: 0, total_reviews: 0 }
    pendingOrders.value    = data.pending_orders     || []
    inProgressOrders.value = data.in_progress_orders || []
    recentDeliveries.value = data.recent_deliveries  || []

    // fallback لو الـ API ما أرسلت pending_cash_orders
    pendingCashOrders.value = data.pending_cash_orders
      ?? inProgressOrders.value.filter(o =>
          isCashOrder(o) && isStatus(o, 'on_the_way')
        )
  } catch (e) {
    console.error('loadDashboard:', e)
    showToast('فشل تحميل البيانات', 'error')
  } finally {
    loading.value = false
  }
}

const loadNotifications = async () => {
  try {
    const [nRes, cRes] = await Promise.all([
      api.get('/notifications'),
      api.get('/notifications/unread-count'),
    ])
    notifications.value = nRes.data.data || []
    unreadCount.value   = cRes.data.count || 0
  } catch (e) {
    console.error('loadNotifications:', e)
  }
}

//  Availability 
const toggleAvailability = async () => {
  try {
    const { data } = await api.patch('/driver/availability', {
      is_available: !driver.value.is_available
    })
    driver.value.is_available = data.is_available
    showToast(data.is_available ? 'أنت الآن متاح ✅' : 'تم تعيينك كغير متاح')
  } catch (e) {
    showToast('فشل تحديث الحالة', 'error')
  }
}

//  Order Actions 
const acceptOrder = async (order) => {
  try {
    await api.post(`/driver/orders/${order.id}/accept`)
    showToast('تم قبول الطلب بنجاح ✅')
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  }
}

const showRejectModal = (order) => {
  rejectOrderData.value = order
  rejectionReason.value = ''
}

const confirmRejection = async () => {
  if (!rejectionReason.value.trim()) {
    return showToast('الرجاء كتابة سبب الرفض', 'error')
  }
  try {
    await api.post(`/driver/orders/${rejectOrderData.value.id}/reject`, {
      reason: rejectionReason.value
    })
    rejectOrderData.value = null
    rejectionReason.value = ''
    showToast('تم رفض الطلب')
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  }
}

const startDelivery = async (order) => {
  try {
    await api.post(`/driver/orders/${order.id}/start`)
    showToast('بدأ التوصيل 🚚')
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  }
}

//  Cancel Order 
const showCancelModal = (order) => {
  cancelOrderData.value = order
  cancelReason.value    = ''
}

const confirmCancel = async () => {
  if (!cancelReason.value.trim()) {
    return showToast('الرجاء كتابة سبب الإلغاء', 'error')
  }
  cancelLoading.value = true
  
  try {
    console.log(cancelOrderData.value.id)
    await api.post(`/driver/orders/${cancelOrderData.value.id}/cancel`, {
      reason: cancelReason.value
    })
    
    cancelOrderData.value = null
    cancelReason.value    = ''
    showToast('تم إلغاء الطلب وتم إشعار الزبون 🚫')
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  } finally {
    cancelLoading.value = false
  }
}

//  Complete Delivery (جديد — مع modal + استلام كاش) 
const showCompleteModal = (order) => {
  completeOrderData.value = order
}

const confirmComplete = async () => {
  completeLoading.value = true
  try {
    // 1 غيّر الحالة إلى completed
    await api.post(`/driver/orders/${completeOrderData.value.id}/complete`)

    // 2 لو كاش، أرسل تأكيد استلام المبلغ
    if (isCashOrder(completeOrderData.value)) {
      try {
        await api.post(`/driver/orders/${completeOrderData.value.id}/confirm-cash`)
      } catch (cashErr) {
        // لو الـ endpoint غير موجود أو مش مطلوب — تجاهل
        console.warn('confirm-cash skipped:', cashErr)
      }
    }

    const msg = isCashOrder(completeOrderData.value)
      ? `تم إتمام التوصيل واستلام ${completeOrderData.value.total_price} ₪ نقداً ✅`
      : 'تم إتمام التوصيل بنجاح ✅'

    completeOrderData.value = null
    showToast(msg)
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  } finally {
    completeLoading.value = false
  }
}

// ── Confirm Cash Payment (من الـ alert العلوي) 
const confirmCash = async (order) => {
  cashLoading.value = { ...cashLoading.value, [order.id]: true }
  try {
    await api.post(`/driver/orders/${order.id}/confirm-cash`)
    showToast(`تم تأكيد استلام ${order.total_price} ₪ 💰`)
    await loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'حدث خطأ', 'error')
  } finally {
    cashLoading.value = { ...cashLoading.value, [order.id]: false }
  }
}

// ── Order Details 
const viewOrderDetails = async (order) => {
  try {
    const { data } = await api.get(`/driver/orders/${order.id}`)
    selectedOrder.value = data
  } catch (e) {
    console.error('viewOrderDetails:', e)
    showToast('فشل تحميل تفاصيل الطلب', 'error')
  }
}

// ── Notifications 
const markAsRead = async (notification) => {
  try {
    await api.post(`/notifications/${notification.id}/read`)
    notification.is_read = true
    if (unreadCount.value > 0) unreadCount.value--
  } catch (e) {
    console.error('markAsRead:', e)
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value && unreadCount.value > 0) {
    api.post('/notifications/read-all').then(() => {
      unreadCount.value = 0
      notifications.value.forEach(n => (n.is_read = true))
    })
  }
}

// ── Helpers 
const getProductsList = (products) =>
  products?.length
    ? products.map(p => `${p.type} x${p.pivot?.quantity || 1}`).join('، ')
    : 'لا يوجد'

const getNotifIcon = (type) => ({
  order_accepted:    '✅',
  order_rejected:    '❌',
  order_on_the_way:  '🚚',
  order_completed:   '📦',
  order_cancelled:   '🚫',
  payment_confirmed: '💰',
  payment_received:  '💰',
  new_review:        '⭐'
}[type] || '🔔')

const formatDate = (date) =>
  date ? new Date(date).toLocaleDateString('ar-SA') : ''
const formatTime = (date) =>
  date ? new Date(date).toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' }) : ''
const handleImageError = (e) => { e.target.src = '/default-avatar.png' }

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  router.push('/login')
}

// ── Lifecycle 
let interval
onMounted(() => {
  loadDashboard()
  loadNotifications()
  interval = setInterval(() => {
    loadDashboard()
    loadNotifications()
  }, 30000)
})
onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>

<style scoped>

/*  Order Actions Buttons \ */
.order-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-start;
  align-items: center;
}

.order-actions button {
  min-width: 100px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-align: center;
  white-space: nowrap;
}

.btn-view {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
}
.btn-view:hover { background: rgba(255, 255, 255, 0.2); transform: translateY(-2px); }

.btn-accept { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
.btn-accept:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.4); }

.btn-reject { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; }
.btn-reject:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.4); }

.btn-start { background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: #0a0f1a; font-weight: 700; }
.btn-start:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(56,189,248,0.4); }

.btn-complete { background: linear-gradient(135deg, #10b981, #059669); color: #fff; font-weight: 700; }
.btn-complete:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.4); }

.btn-cancel-order {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3) !important;
}
.btn-cancel-order:hover { background: rgba(239, 68, 68, 0.3); transform: translateY(-2px); }

@media (max-width: 600px) {
  .order-actions { width: 100%; justify-content: center; }
  .order-actions button { flex: 1; min-width: 0; font-size: 12px; padding: 8px 10px; }
}

/* ────────── Order Card ────────── */
.order-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
}
.order-card:hover { border-color: #38bdf8; transform: translateY(-2px); }

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.order-id { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #fff; font-size: 16px; }
.order-amount { font-size: 20px; font-weight: 800; color: #f59e0b; }

.order-body {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}
.order-info { flex: 2; min-width: 200px; }
.info-row { display: flex; gap: 12px; font-size: 14px; padding: 6px 0; color: #e2e8f0; }
.info-row .label { color: #94a3b8; min-width: 85px; font-weight: 500; }
.address-text { color: #38bdf8; word-break: break-word; }

/* طريقة الدفع */
.payment-cash  { color: #f59e0b; font-weight: 600; }
.payment-online { color: #38bdf8; font-weight: 600; }

.order-status-badge { padding: 5px 14px; border-radius: 30px; font-size: 13px; font-weight: 600; text-align: center; }
.order-status-badge.accepted  { background: rgba(56,189,248,0.2); color: #38bdf8; }
.order-status-badge.on_the_way { background: rgba(139,92,246,0.2); color: #8b5cf6; }
.order-status-badge.completed  { background: rgba(16,185,129,0.2); color: #10b981; }
.order-icon { font-size: 20px; }

@media (max-width: 768px) {
  .order-body { flex-direction: column; align-items: stretch; }
  .order-actions { justify-content: center; margin-top: 8px; }
}

/* ────────── Base ────────── */
.driver-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0f1a 0%, #0d1626 100%);
  direction: rtl;
}
.dashboard-container { display: flex; max-width: 1400px; margin: 0 auto; padding: 20px; gap: 24px; }

/* ────────── Sidebar ────────── */
.sidebar { width: 280px; flex-shrink: 0; }
.driver-profile-card {
  background: rgba(255,255,255,.05);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 24px;
  text-align: center;
  border: 1px solid rgba(255,255,255,.1);
  margin-bottom: 24px;
}
.driver-avatar-large { position: relative; width: 100px; height: 100px; margin: 0 auto 16px; }
.driver-avatar-large img { width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid #38bdf8; }
.online-status { position:absolute; bottom:4px; right:4px; width:16px; height:16px; border-radius:50%; background:#ef4444; border:2px solid #0d1626; }
.online-status.online { background:#10b981; }
.driver-name { font-size:18px; font-weight:700; color:#fff; margin:0 0 8px; }
.driver-rating { display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px; }
.stars { display:flex; gap:2px; }
.star { color:#4a5568; font-size:16px; }
.star.filled { color:#f59e0b; }
.rating-value { color:#f59e0b; font-weight:700; }
.review-count { color:#94a3b8; font-size:12px; }
.availability-toggle { width:100%; padding:10px; border-radius:40px; border:none; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .3s; }
.availability-toggle.available { background:rgba(16,185,129,.2); color:#10b981; }
.availability-toggle:not(.available) { background:rgba(239,68,68,.2); color:#ef4444; }
.status-dot { width:8px; height:8px; border-radius:50%; background:currentColor; }
.sidebar-nav { display:flex; flex-direction:column; gap:8px; }
.nav-item { display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:12px; background:transparent; border:none; color:#94a3b8; font-size:14px; font-weight:500; cursor:pointer; transition:all .3s; text-decoration:none; }
.nav-item:hover, .nav-item.active { background:rgba(56,189,248,.15); color:#38bdf8; }
.nav-item.active { border-right:3px solid #38bdf8; }
.nav-icon { font-size:20px; }
.badge { background:#ef4444; color:#fff; font-size:11px; padding:2px 6px; border-radius:10px; margin-right:auto; }

/* ────────── Main ────────── */
.main-area { flex:1; min-width:0; }
.content-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.content-header h1 { color:#fff; font-size:24px; font-weight:700; margin:0; }
.header-actions { display:flex; gap:12px; }
.notification-btn, .logout-btn { width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); font-size:20px; cursor:pointer; position:relative; transition:all .3s; }
.notification-btn:hover, .logout-btn:hover { background:rgba(56,189,248,.15); border-color:#38bdf8; }
.notification-badge { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; font-size:11px; padding:2px 6px; border-radius:10px; min-width:18px; }

/* ────────── Stats ────────── */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:24px; }
.stat-card { background:rgba(255,255,255,.05); backdrop-filter:blur(10px); border-radius:20px; padding:20px; display:flex; align-items:center; gap:16px; border:1px solid rgba(255,255,255,.1); }
.stat-icon { font-size:36px; }
.stat-value { display:block; font-size:28px; font-weight:800; color:#38bdf8; }
.stat-label { font-size:13px; color:#94a3b8; }

/* ────────── Cash Alert ────────── */
.cash-alert { background: linear-gradient(135deg,rgba(245,158,11,.15),rgba(245,158,11,.05)); border:1px solid rgba(245,158,11,.4); border-radius:16px; padding:16px 20px; margin-bottom:24px; }
.cash-alert-header { display:flex; justify-content:space-between; align-items:center; font-weight:700; color:#f59e0b; margin-bottom:12px; }
.cash-badge { background:#f59e0b; color:#0a0f1a; padding:2px 10px; border-radius:20px; font-size:12px; }
.cash-orders-list { display:flex; flex-direction:column; gap:10px; }
.cash-order-item { display:flex; align-items:center; gap:12px; background:rgba(0,0,0,.2); padding:12px 16px; border-radius:10px; }
.cash-order-info { flex:1; }
.cash-order-id { display:block; font-weight:600; color:#fff; font-size:14px; }
.cash-customer { font-size:12px; color:#94a3b8; }
.cash-order-amount { font-size:18px; font-weight:800; color:#f59e0b; min-width:80px; text-align:center; }
.btn-cash-confirm { padding:8px 16px; background:linear-gradient(135deg,#f59e0b,#d97706); color:#0a0f1a; border:none; border-radius:8px; font-weight:700; cursor:pointer; transition:all .3s; }
.btn-cash-confirm:hover { transform:scale(1.03); }
.btn-cash-confirm:disabled { opacity:.5; cursor:not-allowed; }
.cash-highlight { color:#f59e0b; font-weight:700; }

/* ────────── Sections ────────── */
.section { background:rgba(255,255,255,.03); border-radius:24px; padding:20px; margin-bottom:24px; border:1px solid rgba(255,255,255,.05); }
.section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.section-header h2 { color:#fff; font-size:18px; font-weight:600; margin:0; }
.section-badge { background:#38bdf8; color:#0a0f1a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.section-badge.in-progress { background:#f59e0b; }
.orders-list { display:flex; flex-direction:column; gap:16px; }

/* ────────── Recent ────────── */
.recent-list { display:flex; flex-direction:column; gap:12px; }
.recent-item { display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(255,255,255,.03); border-radius:12px; border:1px solid rgba(255,255,255,.05); }
.recent-icon { font-size:24px; }
.recent-info { flex:1; }
.recent-customer { font-weight:600; color:#fff; font-size:14px; }
.recent-address { font-size:12px; color:#94a3b8; margin-top:2px; }
.recent-time { font-size:12px; color:#64748b; }

/* ────────── Empty State ────────── */
.empty-state { text-align:center; padding:40px 20px; color:#94a3b8; }
.empty-state span { font-size:48px; display:block; margin-bottom:12px; }
.empty-state p { font-size:14px; margin:0; }

/* ────────── Modals ────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}
.modal-content {
  background: #0d1626;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.modal-header h3 { color:#fff; font-size:18px; font-weight:700; margin:0; }
.close-btn { background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer; padding:4px; }
.close-btn:hover { color:#fff; }
.modal-body { padding:24px; }
.modal-footer { display:flex; gap:12px; justify-content:flex-end; padding:16px 24px; border-top:1px solid rgba(255,255,255,0.08); }

.rejection-input {
  width: 100%;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  color: #fff;
  padding: 12px;
  font-size: 14px;
  resize: vertical;
  box-sizing: border-box;
}
.rejection-input:focus { outline: none; border-color: #38bdf8; }

.cancel-warning { color:#f59e0b; font-size:14px; margin:0 0 16px; }

.btn-cancel {
  padding: 10px 20px;
  background: rgba(255,255,255,0.08);
  color: #94a3b8;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}
.btn-cancel:hover { background: rgba(255,255,255,0.15); color:#fff; }

.btn-confirm-red {
  padding: 10px 20px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}
.btn-confirm-red:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.4); }
.btn-confirm-red:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ✅ Complete Modal */
.cash-confirm-box {
  display: flex;
  align-items: center;
  gap: 16px;
  background: rgba(245,158,11,0.1);
  border: 1px solid rgba(245,158,11,0.3);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}
.cash-icon { font-size: 40px; flex-shrink: 0; }
.cash-confirm-text p { margin: 4px 0; color: #e2e8f0; font-size: 14px; }
.cash-amount-big { font-size: 28px !important; font-weight: 800; color: #f59e0b !important; }
.cash-note { font-size: 12px !important; color: #94a3b8 !important; }

.online-confirm-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(56,189,248,0.1);
  border: 1px solid rgba(56,189,248,0.3);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
  color: #e2e8f0;
  font-size: 14px;
}
.online-icon { font-size: 32px; flex-shrink: 0; }

.order-summary {
  display: flex;
  flex-direction: column;
  gap: 0;
  background: rgba(0,0,0,0.2);
  border-radius: 10px;
  overflow: hidden;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #94a3b8;
  padding: 10px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.summary-row:last-child { border-bottom: none; }
.summary-row span:last-child { color: #e2e8f0; font-weight: 500; }
.summary-row.total { color: #f59e0b; font-weight: 700; font-size: 16px; }
.summary-row.total span:last-child { color: #f59e0b; }

.btn-confirm-green {
  padding: 10px 20px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 14px;
  white-space: nowrap;
}
.btn-confirm-green:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.4); }
.btn-confirm-green:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/*  Details Modal  */
.details-section { margin-bottom: 20px; }
.details-section h4 { color:#38bdf8; font-size:15px; font-weight:600; margin:0 0 12px; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.08); }
.details-section p { color:#e2e8f0; font-size:14px; margin:6px 0; }
.details-section p strong { color:#94a3b8; margin-left:8px; }
.product-item { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.05); font-size:14px; color:#e2e8f0; }
.total-amount { display:flex; justify-content:space-between; padding:12px 0 0; font-size:16px; color:#f59e0b; }

/*  Notifications  */
.notifications-panel {
  position: fixed;
  top: 0;
  left: 0;
  width: 360px;
  height: 100vh;
  background: #0d1626;
  border-right: 1px solid rgba(255,255,255,0.1);
  z-index: 999;
  display: flex;
  flex-direction: column;
}
.panel-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.08); }
.panel-header h3 { color:#fff; margin:0; font-size:18px; }
.close-panel { background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer; }
.panel-body { flex:1; overflow-y:auto; padding:16px; }
.notification-item { display:flex; gap:12px; padding:12px; border-radius:10px; margin-bottom:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); }
.notification-item.unread { background:rgba(56,189,248,0.05); border-color:rgba(56,189,248,0.15); }
.notif-icon { font-size:24px; flex-shrink:0; }
.notif-content { flex:1; }
.notif-title { font-weight:600; color:#fff; font-size:14px; }
.notif-message { font-size:13px; color:#94a3b8; margin-top:2px; }
.notif-time { font-size:11px; color:#64748b; margin-top:4px; }
.mark-read { background:none; border:none; color:#38bdf8; cursor:pointer; font-size:16px; }
.empty-notifications { text-align:center; padding:40px; color:#94a3b8; }
.empty-notifications span { font-size:40px; display:block; margin-bottom:12px; }

/*  Toast  */
.toast {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  z-index: 9999;
  white-space: nowrap;
}
.toast.success { background: linear-gradient(135deg,#10b981,#059669); color:#fff; }
.toast.error   { background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }

/*  Transitions  */
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity:0; transform:scale(0.9); }

.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }

.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity:0; transform: translate(-50%, 20px); }
</style>