<template>
  <div class="dashboard-shell" :class="{ dark: isDark }">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
      <div class="brand-block">
        <div class="logo-mark">Delivro 🏪</div>
        <p class="brand-subtitle">لوحة تحكم المتجر</p>
      </div>

      <div class="profile-card">
        <p class="profile-role">أهلاً بك،</p>
        <h3 class="owner-name">{{ ownerName }}</h3>
        <p class="profile-name">{{ storeName || 'جاري التحميل...' }}</p>
      </div>

      <nav class="sidebar-nav">
        <button class="sidebar-link" :class="{ active: activeTab === 'stats' }" @click="activeTab = 'stats'">📈 الإحصائيات العامة</button>
        <button class="sidebar-link" :class="{ active: activeTab === 'products' }" @click="activeTab = 'products'">🍔 إدارة المنتجات</button>
        <button class="sidebar-link" :class="{ active: activeTab === 'orders' }" @click="activeTab = 'orders'">📦 الطلبات الواردة</button>
        <button class="sidebar-link" @click="goProfile">👤 الملف الشخصي</button>
        <button class="btn-outline logout" @click="logout">🚪 تسجيل الخروج</button>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
      <header class="main-header">
        <div>
          <h1>{{ tabTitle }}</h1>
          <p class="greeting">أهلاً وسهلاً بك في نظام Delivro التجاري</p>
        </div>
        <div class="header-actions">
          <button class="btn-outline" @click="toggleTheme">{{ isDark ? ' الوضع الفاتح ☀️' : ' الوضع الداكن 🌙' }}</button>
          <button class="btn-outline" @click="reloadData">تحديث 🔄</button>
        </div>
      </header>

      <!-- رسائل التنبيه والنجاح -->
      <div v-if="successMsg" class="alert success-alert">✨ {{ successMsg }}</div>
      <div v-if="errorMsg" class="alert error-alert">⚠️ {{ errorMsg }}</div>

      <!-- 1. قسم الإحصائيات (Stats Tab) -->
      <div v-if="activeTab === 'stats'" class="tab-content animate-fade">
        <section class="stats-grid">
          <article class="stat-card">
            <p class="stat-label">💰 إجمالي المبيعات</p>
            <h2>${{ totalSales.toFixed(2) }}</h2>
            <p class="stat-note">من الطلبات المكتملة</p>
          </article>
          <article class="stat-card">
            <p class="stat-label">📦 الطلبات النشطة</p>
            <h2>{{ activeOrdersCount }}</h2>
            <p class="stat-note">قيد التحضير والتوصيل</p>
          </article>
          <article class="stat-card">
            <p class="stat-label">🍔 إجمالي المنتجات</p>
            <h2>{{ products.length }}</h2>
            <p class="stat-note">النشطة في المتجر</p>
          </article>
          <article class="stat-card">
            <p class="stat-label">⭐ تقييم المتجر</p>
            <h2>4.7 / 5</h2>
            <p class="stat-note">من مراجعات الزبائن</p>
          </article>
        </section>

        <!-- الطلبات الأخيرة سريعة العرض -->
        <section class="panel-section">
          <div class="panel-header">
            <h2>الطلبات الواردة الأخيرة</h2>
            <button class="panel-action-btn" @click="activeTab = 'orders'">عرض الكل ➡️</button>
          </div>
          <div class="table-wrapper">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>الزبون</th>
                  <th>العنوان</th>
                  <th>السعر الإجمالي</th>
                  <th>الحالة</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders.slice(0, 5)" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.user ? (order.user.first_name + ' ' + order.user.last_name) : 'زبون مسجل' }}</td>
                  <td>{{ order.order_address || 'عنوان افتراضي' }}</td>
                  <td class="price">${{ parseFloat(order.total_price).toFixed(2) }}</td>
                  <td><span class="status-badge" :class="order.status?.name">{{ order.status?.name }}</span></td>
                </tr>
                <tr v-if="orders.length === 0">
                  <td colspan="5" class="empty-table">لا توجد طلبات نشطة حالياً.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <!-- 2. قسم إدارة المنتجات (Products Tab) -->
      <div v-if="activeTab === 'products'" class="tab-content animate-fade">
        <section class="panel-section">
          <div class="panel-header">
            <h2>قائمة المأكولات والمنتجات</h2>
            <button class="btn-primary add-product-btn" @click="openAddProductModal">➕ إضافة منتج جديد</button>
          </div>

          <div class="table-wrapper">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>الصورة</th>
                  <th>اسم المنتج</th>
                  <th>الوصف</th>
                  <th>السعر</th>
                  <th>العمليات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in products" :key="product.id">
                  <td>
                    <img :src="product.image_path || defaultProductImage" alt="Product" class="table-product-thumb" />
                  </td>
                  <td><strong>{{ product.type }}</strong></td>
                  <td>{{ product.description || 'لا يوجد وصف للمنتج' }}</td>
                  <td class="price">${{ parseFloat(product.price).toFixed(2) }}</td>
                  <td>
                    <div class="table-actions">
                      <button class="action-btn edit" @click="openEditProductModal(product)">✏️ تعديل</button>
                      <button class="action-btn delete" @click="deleteProduct(product.id)">🗑️ حذف</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="products.length === 0">
                  <td colspan="5" class="empty-table">لا توجد منتجات مسجلة في المتجر حتى الآن.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <!-- 3. قسم إدارة الطلبات (Orders Tab) -->
      <div v-if="activeTab === 'orders'" class="tab-content animate-fade">
        <section class="panel-section">
          <div class="panel-header">
            <h2>الطلبات الواردة للمتجر</h2>
          </div>

          <div class="table-wrapper">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>الزبون</th>
                  <th>العنوان بالتفصيل</th>
                  <th>المنتجات المطلوبة</th>
                  <th>السعر</th>
                  <th>حالة الطلب</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.user ? (order.user.first_name + ' ' + order.user.last_name) : 'زبون' }}</td>
                  <td>{{ order.order_address || 'توصيل للمنزل' }}</td>
                  <td>
                    <ul class="ordered-items-list">
                      <li v-for="p in order.products" :key="p.id">
                        {{ p.type }} (x{{ p.pivot?.quantity || 1 }})
                      </li>
                      <li v-if="!order.products || order.products.length === 0">وجبة أساسية (x1)</li>
                    </ul>
                  </td>
                  <td class="price">${{ parseFloat(order.total_price).toFixed(2) }}</td>
                  <td>
                    <span class="status-badge" :class="order.status?.name || 'pending'">
                      {{ formatStatus(order.status?.name) }}
                    </span>
                  </td>
                  <td>
                    <div class="order-status-actions" v-if="isPendingOrAccepted(order.status?.name)">
                      <button v-if="order.status?.name === 'pending'" class="status-action-btn accept" @click="updateOrderStatus(order, 'accepted')">قبول التحضير 👨‍🍳</button>
                      <button v-if="order.status?.name === 'accepted'" class="status-action-btn complete" @click="updateOrderStatus(order, 'completed')">جاهز للتوصيل 🛵</button>
                    </div>
                    <span v-else class="action-done">مكتمل ✅</span>
                  </td>
                </tr>
                <tr v-if="orders.length === 0">
                  <td colspan="7" class="empty-table">لا توجد طلبات مسجلة للمتجر.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </main>

    <!-- 🚨 MODAL: إضافة/تعديل منتج -->
    <transition name="fade">
      <div v-if="showProductModal" class="modal-overlay" @click.self="closeProductModal">
        <div class="modal-card">
          <h2>{{ editingProduct ? 'تعديل المنتج 🍔' : 'إضافة منتج جديد 🍕' }}</h2>
          <form @submit.prevent="submitProductForm" class="modal-form">
            <div class="form-group">
              <label>اسم المنتج <span class="required">*</span></label>
              <input v-model="productForm.type" type="text" placeholder="اسم الوجبة أو المنتج" required />
            </div>

            <div class="form-group">
              <label>السعر ($) <span class="required">*</span></label>
              <input v-model="productForm.price" type="number" step="0.01" placeholder="السعر" required />
            </div>

            <div class="form-group">
              <label>الوصف</label>
              <textarea v-model="productForm.description" placeholder="مكونات الوجبة أو تفاصيل المنتج"></textarea>
            </div>

            <div class="form-group">
              <label>صورة المنتج</label>
              <div class="modal-image-preview">
                <img :src="productImagePreview || defaultProductImage" alt="Preview" />
                <input type="file" @change="handleProductImageUpload" accept="image/*" />
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-cancel" @click="closeProductModal">إلغاء</button>
              <button type="submit" class="btn-submit">حفظ المنتج</button>
            </div>
          </form>
        </div>
      </div>
    </transition>

  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import '../assets/styles/ProviderDashboard.css'

const router = useRouter()

// ==================== الحالات العامة ====================
const activeTab = ref('stats')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')
const loading = ref(true)
const successMsg = ref('')
const errorMsg = ref('')

const ownerName = ref('التاجر')
const storeName = ref('')
const providerId = ref(null)
const products = ref([])
const orders = ref([])

const defaultProductImage = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=150&h=150'

// ==================== متغيرات الإحصائيات ====================
const totalSales = ref(0)
const activeOrdersCount = ref(0)
const avgRating = ref(0)

// ==================== عنوان التبويب ====================
const tabTitle = computed(() => {
  if (activeTab.value === 'stats') return 'لوحة التحكم الإحصائية'
  if (activeTab.value === 'products') return 'إدارة قائمة الطعام والمنتجات'
  if (activeTab.value === 'orders') return 'إدارة الطلبات الواردة'
  return 'المتجر'
})

// ==================== دوال واجهة المستخدم ====================
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goProfile = () => {
  window.location.href = '/provider/profile'
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  localStorage.removeItem('delivro_email')
  window.location.href = '/login'
}

const reloadData = () => {
  loadDashboardData()
}

// ==================== جلب جميع البيانات من API ====================
const loadDashboardData = async () => {
  try {
    loading.value = true
    successMsg.value = ''
    errorMsg.value = ''

    const meRes = await api.get('/auth/me')
    const user = meRes.data.user || meRes.data
    ownerName.value = `${user.first_name} ${user.last_name}`

    const currentProvider = user.provider

    if (currentProvider) {
      providerId.value = currentProvider.id
      storeName.value = currentProvider.type || 'متجر Delivro'

      const productsRes = await api.get(`/providers/${currentProvider.id}/products`)
      products.value = productsRes.data.data || productsRes.data || []

      try {
        const statsRes = await api.get('/provider/dashboard/stats')
        totalSales.value = statsRes.data.total_sales || 0
        activeOrdersCount.value = statsRes.data.active_orders_count || 0
        avgRating.value = statsRes.data.avg_rating || 0
      } catch (statsErr) {
        console.warn('Stats not available:', statsErr)
        totalSales.value = 0
        activeOrdersCount.value = 0
        avgRating.value = 0
      }

      try {
        const ordersRes = await api.get('/provider/dashboard/all-orders')
        orders.value = ordersRes.data || []
      } catch (ordersErr) {
        console.warn('Orders not available:', ordersErr)
        orders.value = []
      }
    } else {
      errorMsg.value = 'لم يتم العثور على متجر مرتبط بحسابك'
      console.error('No provider found for user:', user)
    }
  } catch (err) {
    console.error('خطأ:', err)
    errorMsg.value = err.response?.data?.message || err.message || 'فشل الاتصال بالخادم'
  } finally {
    loading.value = false
  }
}

// ==================== تحديث حالة الطلب ====================
const updateOrderStatus = async (order, newStatus) => {
  try {
    await api.put(`/provider/orders/${order.id}/status`, { status: newStatus })
    order.status = { name: newStatus }
    successMsg.value = `تم تحديث حالة الطلب #${order.id}`
  } catch (err) {
    console.error('فشل التحديث:', err)
    errorMsg.value = err.response?.data?.message || 'حدث خطأ'
  }
  setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 3000)
}

const formatStatus = (statusName) => {
  const map = {
    'pending': 'في انتظار القبول',
    'accepted': 'تم القبول - قيد التحضير',
    'on_the_way': 'قيد التوصيل',
    'completed': 'مكتمل ✅',
    'cancelled': 'ملغي ❌'
  }
  return map[statusName] || statusName
}

const isPendingOrAccepted = (statusName) => {
  return statusName === 'pending' || statusName === 'accepted'
}

// ==================== إدارة المنتجات ====================
const showProductModal = ref(false)
const editingProduct = ref(null)
const productImagePreview = ref('')
const productImageFile = ref(null)

const productForm = ref({
  type: '',
  price: 0,
  description: ''
})

const openAddProductModal = () => {
  editingProduct.value = null
  productForm.value = { type: '', price: 0, description: '' }
  productImagePreview.value = ''
  productImageFile.value = null
  showProductModal.value = true
}

const openEditProductModal = (product) => {
  editingProduct.value = product
  productForm.value = {
    type: product.type,
    price: product.price,
    description: product.description || ''
  }
  productImagePreview.value = product.image_path || ''
  productImageFile.value = null
  showProductModal.value = true
}

const closeProductModal = () => {
  showProductModal.value = false
}

const handleProductImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    productImageFile.value = file
    productImagePreview.value = URL.createObjectURL(file)
  }
}

const submitProductForm = async () => {
  try {
    const formData = new FormData()
    formData.append('type', productForm.value.type)
    formData.append('price', productForm.value.price)
    if (productForm.value.description) {
      formData.append('description', productForm.value.description)
    }
    if (productImageFile.value) {
      formData.append('image', productImageFile.value)
    }

    if (editingProduct.value) {
      formData.append('_method', 'PUT')
      const response = await api.post(`/provider/products/${editingProduct.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const updatedProduct = response.data
      const index = products.value.findIndex(p => p.id === editingProduct.value.id)
      if (index !== -1) products.value[index] = updatedProduct
      successMsg.value = 'تم تعديل المنتج بنجاح!'
    } else {
      const response = await api.post('/provider/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const newProduct = response.data
      products.value.unshift(newProduct)
      successMsg.value = 'تمت إضافة منتج جديد للقائمة!'
    }
    closeProductModal()
  } catch (err) {
    console.error('فشل حفظ المنتج:', err)
    errorMsg.value = err.response?.data?.message || 'حدث خطأ أثناء حفظ المنتج'
  } finally {
    setTimeout(() => {
      successMsg.value = ''
      errorMsg.value = ''
    }, 3000)
  }
}

const deleteProduct = async (id) => {
  if (!confirm('هل أنت متأكد من حذف هذا المنتج نهائياً من قائمتك؟')) return

  try {
    await api.delete(`/provider/products/${id}`)
    products.value = products.value.filter(p => p.id !== id)
    successMsg.value = 'تم حذف المنتج بنجاح'
  } catch (err) {
    console.error('فشل حذف المنتج:', err)
    errorMsg.value = err.response?.data?.message || 'حدث خطأ أثناء حذف المنتج'
  } finally {
    setTimeout(() => {
      successMsg.value = ''
      errorMsg.value = ''
    }, 3000)
  }
}

// ==================== تحميل أولي ====================
onMounted(() => {
  loadDashboardData()
})
</script>