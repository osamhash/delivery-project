<!-- resources/js/views/driver/Dashboard.vue -->
<template>
  <div class="driver-dashboard" dir="rtl">
    <div class="dashboard-bg"></div>
    
    <div class="dashboard-container">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="driver-profile-card">
          <div class="driver-avatar-large">
            <img :src="driverImage" :alt="driverName" @error="handleImageError">
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
            <span class="nav-icon">📊</span>
            <span>لوحة التحكم</span>
          </router-link>
          <router-link to="/driver/orders" class="nav-item" active-class="active">
            <span class="nav-icon">📦</span>
            <span>الطلبات</span>
            <span class="badge" v-if="pendingOrdersCount">{{ pendingOrdersCount }}</span>
          </router-link>
          <router-link to="/driver/history" class="nav-item" active-class="active">
            <span class="nav-icon">📜</span>
            <span>سجل التوصيل</span>
          </router-link>
          <router-link to="/driver/reviews" class="nav-item" active-class="active">
            <span class="nav-icon">⭐</span>
            <span>تقييماتي</span>
          </router-link>
          <router-link to="/driver/profile" class="nav-item" active-class="active">
            <span class="nav-icon">👤</span>
            <span>الملف الشخصي</span>
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
        
        <!-- Pending Orders -->
        <div class="section">
          <div class="section-header">
            <h2>🚚 الطلبات المعلقة</h2>
            <span class="section-badge">{{ pendingOrders.length }}</span>
          </div>
          <div class="orders-list">
            <div v-for="order in pendingOrders" :key="order.id" class="order-card">
              <div class="order-header">
                <div class="order-id">
                  <span class="order-icon">📋</span>
                  طلب #{{ order.id }}
                </div>
                <div class="order-amount">{{ order.total_price }} ر.س</div>
              </div>
              <div class="order-body">
                <div class="order-info">
                  <div class="info-row">
                    <span class="label">👤 العميل:</span>
                    <span>{{ order.user?.first_name }} {{ order.user?.last_name }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">📍 العنوان:</span>
                    <span class="address-text">{{ order.order_address || 'غير محدد' }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">🏪 المتجر:</span>
                    <span>{{ order.provider?.user?.first_name || 'متجر' }}</span>
                  </div>
                  <div class="info-row" v-if="order.products?.length">
                    <span class="label">🛒 المنتجات:</span>
                    <span>{{ getProductsList(order.products) }}</span>
                  </div>
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
                <div class="order-id">
                  <span class="order-icon">🚚</span>
                  طلب #{{ order.id }}
                </div>
                <div class="order-status-badge" :class="order.status?.name">
                  {{ getStatusText(order.status?.name) }}
                </div>
              </div>
              <div class="order-body">
                <div class="order-info">
                  <div class="info-row">
                    <span class="label">👤 العميل:</span>
                    <span>{{ order.user?.first_name }} {{ order.user?.last_name }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">📍 العنوان:</span>
                    <span>{{ order.order_address || 'غير محدد' }}</span>
                  </div>
                </div>
                <div class="order-actions" v-if="order.status?.name === 'accepted'">
                  <button class="btn-start" @click="startDelivery(order)">🚚 بدء التوصيل</button>
                </div>
                <!-- osama -->
                <div class="order-actions" v-if="order.status?.name === 'OnTheWay'">
                  <button class="btn-complete" @click="completeDelivery(order)">✅ تأكيد التوصيل</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Recent Deliveries -->
        <div class="section">
          <div class="section-header">
            <h2>📦 آخر الطلبات الموصلة</h2>
          </div>
          <div class="recent-list">
            <div v-for="delivery in recentDeliveries" :key="delivery.id" class="recent-item">
              <div class="recent-icon">✅</div>
              <div class="recent-info">
                <div class="recent-customer">{{ delivery.user?.first_name }} {{ delivery.user?.last_name }}</div>
                <div class="recent-address">{{ delivery.order_address || 'عنوان غير محدد' }}</div>
              </div>
              <div class="recent-time">{{ formatDate(delivery.updated_at) }}</div>
            </div>
          </div>
        </div>
      </main>
    </div>
    
    <!-- Order Details Modal -->
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
                <span>{{ product.pivot?.price || product.price }} ر.س</span>
              </div>
              <div class="total-amount">
                <strong>الإجمالي:</strong>
                <strong>{{ selectedOrder.total_price }} ر.س</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
    
    <!-- Rejection Modal -->
    <transition name="modal">
      <div class="modal-overlay" v-if="rejectOrderData" @click.self="rejectOrderData = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>رفض الطلب</h3>
            <button class="close-btn" @click="rejectOrderData = null">✕</button>
          </div>
          <div class="modal-body">
            <textarea
              v-model="rejectionReason"
              placeholder="اكتب سبب الرفض..."
              class="rejection-input"
              rows="4"
            ></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="rejectOrderData = null">إلغاء</button>
            <button class="btn-confirm" @click="confirmRejection">تأكيد الرفض</button>
          </div>
        </div>
      </div>
    </transition>
    
    <!-- Notifications Panel -->
    <transition name="slide">
      <div class="notifications-panel" v-if="showNotifications">
        <div class="panel-header">
          <h3>الإشعارات</h3>
          <button class="close-panel" @click="showNotifications = false">✕</button>
        </div>
        <div class="panel-body">
          <div v-for="notif in notifications" :key="notif.id" class="notification-item" :class="{ unread: !notif.is_read }">
            <div class="notif-icon">{{ getNotifIcon(notif.type) }}</div>
            <div class="notif-content">
              <div class="notif-title">{{ notif.title }}</div>
              <div class="notif-message">{{ notif.message }}</div>
              <div class="notif-time">{{ formatTime(notif.created_at) }}</div>
            </div>
            <button v-if="!notif.is_read" class="mark-read" @click="markAsRead(notif)">●</button>
          </div>
          <div v-if="notifications.length === 0" class="empty-notifications">
            <span>📭</span>
            <p>لا توجد إشعارات</p>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
const router = useRouter()

// State
const driver = ref(null)
const stats = ref({
  today_orders: 0,
  total_delivered: 0,
  rating: 0,
  total_reviews: 0
})
const pendingOrders = ref([])
const inProgressOrders = ref([])
const recentDeliveries = ref([])
const notifications = ref([])
const unreadCount = ref(0)
const showNotifications = ref(false)
const selectedOrder = ref(null)
const rejectOrderData = ref(null)
const rejectionReason = ref('')
const loading = ref(false)

// Computed
const driverName = computed(() => {
  return driver.value?.user ? `${driver.value.user.first_name} ${driver.value.user.last_name}` : 'السائق'
})

const driverImage = computed(() => {
  return driver.value?.user?.image_path || '/default-avatar.png'
})

const driverRating = computed(() => stats.value.rating || 0)
const driverTotalReviews = computed(() => stats.value.total_reviews || 0)
const pendingOrdersCount = computed(() => pendingOrders.value.length)

const statsCards = computed(() => [
  { icon: '📊', value: stats.value.today_orders, label: 'طلبات اليوم' },
  { icon: '✅', value: stats.value.total_delivered, label: 'إجمالي التوصيل' },
  { icon: '⭐', value: driverRating.value.toFixed(1), label: 'التقييم' },
  { icon: '👍', value: driverTotalReviews.value, label: 'تقييمات' }
])

// Methods
const loadDashboard = async () => {
  loading.value = true
  try {
    const response = await api.get('/driver/dashboard')
    driver.value = response.data.driver
    stats.value = response.data.stats
    pendingOrders.value = response.data.pending_orders
    inProgressOrders.value = response.data.in_progress_orders
    recentDeliveries.value = response.data.recent_deliveries
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}

const loadNotifications = async () => {
  try {
    const [notificationsRes, countRes] = await Promise.all([
      api.get('/notifications'),
      api.get('/notifications/unread-count')
    ])
    notifications.value = notificationsRes.data.data
    unreadCount.value = countRes.data.count
  } catch (error) {
    console.error('Error loading notifications:', error)
  }
}

const toggleAvailability = async () => {
  try {
    const response = await api.patch('/driver/availability', {
      is_available: !driver.value.is_available
    })
    driver.value.is_available = response.data.is_available
  } catch (error) {
    console.error('Error toggling availability:', error)
  }
}

const acceptOrder = async (order) => {
  try {
    await api.post(`/driver/orders/${order.id}/accept`)
    await loadDashboard()
  } catch (error) {
    console.error('Error accepting order:', error)
    alert(error.response?.data?.message || 'حدث خطأ')
  }
}

const showRejectModal = (order) => {
  rejectOrderData.value = order
  rejectionReason.value = ''
}

const confirmRejection = async () => {
  if (!rejectionReason.value.trim()) {
    alert('الرجاء كتابة سبب الرفض')
    return
  }
  
  try {
    await api.post(`/driver/orders/${rejectOrderData.value.id}/reject`, {
      reason: rejectionReason.value
    })
    rejectOrderData.value = null
    rejectionReason.value = ''
    await loadDashboard()
  } catch (error) {
    console.error('Error rejecting order:', error)
    alert(error.response?.data?.message || 'حدث خطأ')
  }
}

const startDelivery = async (order) => {
  try {
    await api.post(`/driver/orders/${order.id}/start`)
    await loadDashboard()
  } catch (error) {
    console.error('Error starting delivery:', error)
    alert(error.response?.data?.message || 'حدث خطأ')
  }
}

const completeDelivery = async (order) => {
  try {
    await api.post(`/driver/orders/${order.id}/complete`)
    await loadDashboard()
  } catch (error) {
    console.error('Error completing delivery:', error)
    alert(error.response?.data?.message || 'حدث خطأ')
  }
}

const viewOrderDetails = async (order) => {
  try {
    const response = await api.get(`/driver/orders/${order.id}`)
    selectedOrder.value = response.data
  } catch (error) {
    console.error('Error fetching order details:', error)
  }
}

const markAsRead = async (notification) => {
  try {
    await api.post(`/notifications/${notification.id}/read`)
    notification.is_read = true
    unreadCount.value--
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value && unreadCount.value > 0) {
    // Mark all as read when opening
    api.post('/notifications/read-all').then(() => {
      unreadCount.value = 0
      notifications.value.forEach(n => n.is_read = true)
    })
  }
}

const getProductsList = (products) => {
  if (!products || products.length === 0) return 'لا يوجد'
  return products.map(p => `${p.type} x${p.pivot?.quantity || 1}`).join('، ')
}

const getStatusText = (status) => {
  const statusMap = {
    'Pending': 'معلق',
    'Accepted': 'مقبول',
    'OnTheWay': 'قيد التوصيل',
    'Completed': 'مكتمل',
    'Rejected': 'مرفوض'
  }
  return statusMap[status] || status
}

const getNotifIcon = (type) => {
  const iconMap = {
    'order_accepted': '✅',
    'order_rejected': '❌',
    'order_on_the_way': '🚚',
    'order_completed': '📦',
    'new_review': '⭐',
    'payment_received': '💰'
  }
  return iconMap[type] || '🔔'
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('ar-SA')
}

const formatTime = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}

const handleImageError = (e) => {
  e.target.src = '/default-avatar.png'
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('userRole')
  router.push('/login')
}

// Refresh interval
let interval

onMounted(() => {
  loadDashboard()
  loadNotifications()
  interval = setInterval(() => {
    loadDashboard()
    loadNotifications()
  }, 30000) // Refresh every 30 seconds
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>

<style scoped>
/* Styles remain the same as your existing CSS but with RTL support */
.driver-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0f1a 0%, #0d1626 100%);
  direction: rtl;
}

.dashboard-container {
  display: flex;
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  gap: 24px;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  flex-shrink: 0;
}

.driver-profile-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 24px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 24px;
}

.driver-avatar-large {
  position: relative;
  width: 100px;
  height: 100px;
  margin: 0 auto 16px;
}

.driver-avatar-large img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #38bdf8;
}

.online-status {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #ef4444;
  border: 2px solid #0d1626;
}

.online-status.online {
  background: #10b981;
}

.driver-name {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 8px;
}

.driver-rating {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 16px;
}

.stars {
  display: flex;
  gap: 2px;
}

.star {
  color: #4a5568;
  font-size: 16px;
}

.star.filled {
  color: #f59e0b;
}

.rating-value {
  color: #f59e0b;
  font-weight: 700;
}

.review-count {
  color: #94a3b8;
  font-size: 12px;
}

.availability-toggle {
  width: 100%;
  padding: 10px;
  border-radius: 40px;
  border: none;
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s;
}

.availability-toggle.available {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.availability-toggle:not(.available) {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
}

.nav-item:hover {
  background: rgba(56, 189, 248, 0.1);
  color: #38bdf8;
}

.nav-item.active {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border-right: 3px solid #38bdf8;
}

.nav-icon {
  font-size: 20px;
}

.badge {
  background: #ef4444;
  color: white;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-right: auto;
}

/* Main Area */
.main-area {
  flex: 1;
  min-width: 0;
}

.content-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.content-header h1 {
  color: #fff;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.notification-btn, .logout-btn {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 20px;
  cursor: pointer;
  position: relative;
  transition: all 0.3s;
}

.notification-btn:hover, .logout-btn:hover {
  background: rgba(56, 189, 248, 0.15);
  border-color: #38bdf8;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-icon {
  font-size: 36px;
}

.stat-info {
  flex: 1;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 800;
  color: #38bdf8;
}

.stat-label {
  font-size: 13px;
  color: #94a3b8;
}

/* Sections */
.section {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 24px;
  padding: 20px;
  margin-bottom: 24px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  color: #fff;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.section-badge {
  background: #38bdf8;
  color: #0a0f1a;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}

.section-badge.in-progress {
  background: #f59e0b;
}

/* Order Cards */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  transition: all 0.3s;
}

.order-card:hover {
  border-color: #38bdf8;
  transform: translateY(-2px);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.order-id {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #fff;
}

.order-icon {
  font-size: 18px;
}

.order-amount {
  font-size: 18px;
  font-weight: 700;
  color: #38bdf8;
}

.order-status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.order-status-badge.accepted {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
}

.order-status-badge.on_the_way {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
}

.order-body {
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.order-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-row {
  display: flex;
  gap: 8px;
  font-size: 13px;
}

.info-row .label {
  color: #94a3b8;
  min-width: 80px;
}

.address-text {
  color: #38bdf8;
  direction: ltr;
  text-align: right;
}

.order-actions {
  display: flex;
  gap: 10px;
}

.order-actions button {
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-view {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.btn-view:hover {
  background: rgba(255, 255, 255, 0.2);
}

.btn-accept {
  background: #10b981;
  color: white;
}

.btn-accept:hover {
  background: #059669;
  transform: scale(1.02);
}

.btn-reject {
  background: #ef4444;
  color: white;
}

.btn-reject:hover {
  background: #dc2626;
}

.btn-start, .btn-complete {
  background: linear-gradient(135deg, #38bdf8, #0ea5e9);
  color: #0a0f1a;
  font-weight: 700;
}

.btn-start:hover, .btn-complete:hover {
  transform: scale(1.02);
}

/* Recent List */
.recent-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
}

.recent-icon {
  font-size: 24px;
}

.recent-info {
  flex: 1;
}

.recent-customer {
  color: #fff;
  font-weight: 500;
}

.recent-address {
  font-size: 12px;
  color: #94a3b8;
}

.recent-time {
  font-size: 12px;
  color: #64748b;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 48px;
  color: #64748b;
}

.empty-state span {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #0d1626;
  border-radius: 24px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow: auto;
  border: 1px solid rgba(56, 189, 248, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
  color: #fff;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #94a3b8;
}

.modal-body {
  padding: 20px;
}

.details-section {
  margin-bottom: 20px;
}

.details-section h4 {
  color: #38bdf8;
  margin: 0 0 12px 0;
}

.details-section p {
  margin: 8px 0;
  color: #e2e8f0;
}

.product-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.total-amount {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  margin-top: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 16px;
}

.rejection-input {
  width: 100%;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: #fff;
  font-size: 14px;
  resize: vertical;
}

.rejection-input:focus {
  outline: none;
  border-color: #38bdf8;
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-cancel, .btn-confirm {
  flex: 1;
  padding: 12px;
  border-radius: 12px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.btn-confirm {
  background: #ef4444;
  color: white;
}

/* Notifications Panel */
.notifications-panel {
  position: fixed;
  top: 0;
  left: 0;
  width: 380px;
  height: 100vh;
  background: #0d1626;
  border-right: 1px solid rgba(56, 189, 248, 0.2);
  z-index: 1001;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.panel-header h3 {
  color: #fff;
  margin: 0;
}

.close-panel {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #94a3b8;
}

.panel-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  margin-bottom: 8px;
  background: rgba(255, 255, 255, 0.03);
  position: relative;
}

.notification-item.unread {
  background: rgba(56, 189, 248, 0.1);
  border-right: 3px solid #38bdf8;
}

.notif-icon {
  font-size: 24px;
}

.notif-content {
  flex: 1;
}

.notif-title {
  color: #fff;
  font-weight: 600;
  margin-bottom: 4px;
}

.notif-message {
  font-size: 12px;
  color: #94a3b8;
}

.notif-time {
  font-size: 10px;
  color: #64748b;
  margin-top: 4px;
}

.mark-read {
  background: none;
  border: none;
  color: #38bdf8;
  cursor: pointer;
  font-size: 12px;
}

.empty-notifications {
  text-align: center;
  padding: 48px;
  color: #64748b;
}

/* Transitions */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s;
}

.slide-enter-from, .slide-leave-to {
  transform: translateX(-100%);
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
  }
  
  .order-body {
    flex-direction: column;
  }
  
  .order-actions {
    width: 100%;
  }
  
  .order-actions button {
    flex: 1;
  }
  
  .notifications-panel {
    width: 100%;
  }
}
</style>