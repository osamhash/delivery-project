<template>
  <div class="dashboard-shell" :class="{ dark: isDark }">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
      <div class="brand-block">
        <div class="logo-mark">Delivro 🏪</div>
        <p class="brand-subtitle">لوحة تحكم المتجر</p>
      </div>

      <div class="profile-card">
        <div class="avatar-section">
          <img 
            v-if="providerImage" 
            :src="providerImage" 
            alt="صورة المتجر"
            class="provider-avatar"
          />
          <div v-else class="avatar-placeholder">
            {{ storeName?.charAt(0) || '🏪' }}
          </div>
        </div>
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

      <!--  1. قسم الإحصائيات (Stats Tab) -->
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
            <h2>{{ avgRating.toFixed(1) }} / 5</h2>
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
                  <td class="price">${{ parseFloat(order.total_price || 0).toFixed(2) }}</td>
                  <td><span class="status-badge" :class="order.status?.name || 'Pending'">{{ formatStatus(order.status?.name) }}</span></td>
                </tr>
                <tr v-if="orders.length === 0">
                  <td colspan="5" class="empty-table">لا توجد طلبات نشطة حالياً.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <!--  2. قسم إدارة المنتجات (Products Tab) -->
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
                    <img 
                      :src="getProductImage(product)" 
                      alt="Product" 
                      class="table-product-thumb"
                      @error="handleImageError"
                    />
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

      <!--  3. قسم إدارة الطلبات (Orders Tab) -->
      <div v-if="activeTab === 'orders'" class="tab-content animate-fade">
        <section class="panel-section">
          <div class="panel-header">
            <h2>الطلبات الواردة للمتجر</h2>
            <div class="filters-block">
              <select v-model="orderStatusFilter" class="table-filter">
                <option value="">جميع الحالات</option>
                <option value="Pending">في انتظار القبول</option>
                <option value="Accepted">قيد التحضير</option>
                <option value="OnTheWay">قيد التوصيل</option>
                <option value="Completed">مكتمل ✅</option>
                <option value="Rejected">ملغي ❌</option>
              </select>
            </div>
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
                <tr v-for="order in filteredOrders" :key="order.id">
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
                  <td class="price">${{ parseFloat(order.total_price || 0).toFixed(2) }}</td>
                  <td>
                    <span class="status-badge" :class="order.status?.name || 'Pending'">
                      {{ formatStatus(order.status?.name) }}
                    </span>
                  </td>
                  <td>
                    <!--  الطلبات المكتملة أو الملغية -->
                    <div v-if="order.status?.name === 'Completed' || order.status?.name === 'Rejected'" class="action-done">
                      {{ order.status?.name === 'Completed' ? '✅ مكتمل' : '❌ ملغي' }}
                    </div>
                    
                    <!--  الطلبات النشطة -->
                    <div v-else class="order-status-actions">
                      <button 
                        v-if="order.status?.name === 'Pending'" 
                        class="status-action-btn accept" 
                        @click="updateOrderStatus(order, 'Accepted')"
                      >
                        قبول التحضير 👨‍🍳
                      </button>
                      <button 
                        v-if="order.status?.name === 'Accepted'" 
                        class="status-action-btn complete" 
                        @click="updateOrderStatus(order, 'Completed')"
                      >
                        جاهز للتوصيل 🛵
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredOrders.length === 0">
                  <td colspan="7" class="empty-table">لا توجد طلبات مطابقة للبحث.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </main>

    <!--  MODAL: إضافة/تعديل منتج -->
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

//  الحالات العامة 
const activeTab = ref('stats')
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')
const loading = ref(true)
const successMsg = ref('')
const errorMsg = ref('')

const ownerName = ref('التاجر')
const storeName = ref('')
const providerId = ref(null)
const providerImage = ref(null)
const products = ref([])
const orders = ref([])
const orderStatusFilter = ref('')

//  الصورة الافتراضية للمنتج
const defaultProductImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="150" height="150"%3E%3Crect width="150" height="150" fill="%236366f1"/%3E%3Ctext x="75" y="90" font-size="60" text-anchor="middle" fill="white" font-family="Arial"%3E🍔%3C/text%3E%3C/svg%3E'

//  متغيرات الإحصائيات 
const totalSales = ref(0)
const activeOrdersCount = ref(0)
const avgRating = ref(0)

//  عنوان التبويب 
const tabTitle = computed(() => {
  if (activeTab.value === 'stats') return '📈 لوحة التحكم الإحصائية'
  if (activeTab.value === 'products') return '🍔 إدارة قائمة الطعام والمنتجات'
  if (activeTab.value === 'orders') return '📦 إدارة الطلبات الواردة'
  return 'المتجر'
})

//  تصفية الطلبات حسب الحالة
const filteredOrders = computed(() => {
  if (!orderStatusFilter.value) return orders.value
  return orders.value.filter(o => o.status?.name === orderStatusFilter.value)
})

//  دالة للحصول على رابط الصورة
const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('http')) return imagePath
  
  const baseUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000'
  const cleanPath = imagePath.replace(/^\/?storage\//, '')
  return `${baseUrl}/storage/${cleanPath}?t=${Date.now()}`
}

//  دالة للحصول على صورة المنتج
const getProductImage = (product) => {
  if (product.image_url) return product.image_url
  if (product.image_path) return getImageUrl(product.image_path)
  return defaultProductImage
}

//  معالجة خطأ تحميل الصورة
const handleImageError = (event) => {
  event.target.src = defaultProductImage
}

//  دوال واجهة المستخدم 
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goProfile = () => {
  router.push('/provider/profile')
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  localStorage.removeItem('delivro_email')
  router.push('/login')
}

const reloadData = () => {
  loadDashboardData()
}

//  جلب جميع البيانات من API 
const loadDashboardData = async () => {
  try {
    loading.value = true
    successMsg.value = ''
    errorMsg.value = ''

    const meRes = await api.get('/auth/me')
    const user = meRes.data.user || meRes.data
    ownerName.value = `${user.first_name} ${user.last_name}`

    if (user.image_path) {
      providerImage.value = getImageUrl(user.image_path)
    }

    const currentProvider = user.provider

    if (currentProvider) {
      providerId.value = currentProvider.id
      storeName.value = currentProvider.type || 'متجر Delivro'

      //  جلب المنتجات
      const productsRes = await api.get(`/providers/${currentProvider.id}/products`)
      products.value = productsRes.data.data || productsRes.data || []

      //  جلب الإحصائيات
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

      //  جلب الطلبات
      try {
        const ordersRes = await api.get('/provider/dashboard/all-orders')
        orders.value = ordersRes.data || []
      } catch (ordersErr) {
        console.warn('Orders not available:', ordersErr)
        orders.value = []
      }
    } else {
      errorMsg.value = 'لم يتم العثور على متجر مرتبط بحسابك'
    }
  } catch (err) {
    console.error('خطأ:', err)
    errorMsg.value = err.response?.data?.message || err.message || 'فشل الاتصال بالخادم'
  } finally {
    loading.value = false
  }
}

//  تحديث حالة الطلب 
const updateOrderStatus = async (order, newStatus) => {
  try {
    await api.put(`/provider/orders/${order.id}/status`, { status: newStatus })
    order.status = { name: newStatus }
    successMsg.value = `تم تحديث حالة الطلب #${order.id} إلى ${formatStatus(newStatus)}`
  } catch (err) {
    console.error('فشل التحديث:', err)
    errorMsg.value = err.response?.data?.message || 'حدث خطأ'
  }
  setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 3000)
}

const formatStatus = (statusName) => {
  const map = {
    'Pending': 'في انتظار القبول',
    'Accepted': 'قيد التحضير 👨‍🍳',
    'OnTheWay': 'قيد التوصيل 🚚',
    'Completed': 'مكتمل ✅',
    'cancelled': 'ملغي ❌'
  }
  return map[statusName] || statusName
}

//  إدارة المنتجات 
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
  productImagePreview.value = product.image_url || getImageUrl(product.image_path) || ''
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
      const updatedProduct = response.data.data || response.data
      const index = products.value.findIndex(p => p.id === editingProduct.value.id)
      if (index !== -1) {
        products.value[index] = {
          ...updatedProduct,
          image_url: updatedProduct.image_url || getImageUrl(updatedProduct.image_path)
        }
      }
      successMsg.value = 'تم تعديل المنتج بنجاح!'
    } else {
      const response = await api.post('/provider/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const newProduct = response.data.data || response.data
      products.value.unshift({
        ...newProduct,
        image_url: newProduct.image_url || getImageUrl(newProduct.image_path)
      })
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

//  تحميل أولي 
onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
/*  Styles للصورة في الـ Sidebar */
.profile-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 15px;
  background: var(--card-bg, #ffffff);
  border-radius: 12px;
  margin-bottom: 20px;
  text-align: center;
}

.avatar-section {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  overflow: hidden;
  margin-bottom: 12px;
  border: 3px solid var(--primary-color, #6366f1);
  flex-shrink: 0;
}

.provider-avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary-color, #6366f1);
  color: white;
  font-size: 32px;
  font-weight: bold;
}

.profile-role {
  font-size: 12px;
  color: var(--text-secondary, #64748b);
  margin-bottom: 4px;
}

.owner-name {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary, #1e293b);
  margin: 4px 0;
}

.profile-name {
  font-size: 14px;
  color: var(--text-secondary, #64748b);
}

/*  Styles لصورة المنتج في الجدول */
.table-product-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid var(--border-color, #e2e8f0);
}

/*  Styles لمعاينة الصورة في المودال */
.modal-image-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.modal-image-preview img {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px dashed var(--border-color, #e2e8f0);
}

.modal-image-preview input[type="file"] {
  width: 100%;
  padding: 8px;
  border: 1px solid var(--border-color, #e2e8f0);
  border-radius: 6px;
  background: var(--input-bg, #f8fafc);
  cursor: pointer;
}

/*  Styles للطلبات */
.ordered-items-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.ordered-items-list li {
  font-size: 13px;
  padding: 2px 0;
  color: var(--text-secondary);
}

.order-status-actions {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.status-action-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 500;
}

.status-action-btn.accept {
  background: #22c55e;
  color: white;
}

.status-action-btn.accept:hover {
  background: #16a34a;
  transform: scale(1.05);
}

.status-action-btn.complete {
  background: #3b82f6;
  color: white;
}

.status-action-btn.complete:hover {
  background: #2563eb;
  transform: scale(1.05);
}

.action-done {
  font-weight: 600;
  color: #22c55e;
}

/*  الوضع الداكن */
.dark .profile-card {
  background: #1e293b;
}

.dark .owner-name {
  color: #e2e8f0;
}

.dark .profile-role,
.dark .profile-name {
  color: #94a3b8;
}

.dark .modal-image-preview img {
  border-color: #334155;
}

.dark .modal-image-preview input[type="file"] {
  background: #1e293b;
  border-color: #334155;
  color: #e2e8f0;
}

.dark .ordered-items-list li {
  color: #94a3b8;
}

/*  Responsive */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .table-wrapper {
    overflow-x: auto;
  }
  
  .order-status-actions {
    flex-direction: row;
    flex-wrap: wrap;
  }
}
</style>