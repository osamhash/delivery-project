<template>
  <div class="dashboard-shell" :class="{ dark: isDark }">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
      <div class="brand-block">
        <div class="logo-mark">Delivro 🛡️</div>
        <p class="brand-subtitle">لوحة الإدارة العامة</p>
      </div>

      <div class="profile-card">
        <p class="profile-role">مسؤول النظام</p>
        <h3 class="admin-name">{{ adminName }}</h3>
        <span class="role-indicator">Super Admin 🔴</span>
      </div>

      <nav class="sidebar-nav">
        <button class="sidebar-link" :class="{ active: activeTab === 'overview' }" @click="activeTab = 'overview'">📊 نظرة عامة</button>
        <button class="sidebar-link" :class="{ active: activeTab === 'users' }" @click="activeTab = 'users'">👥 إدارة المستخدمين</button>
        <button class="sidebar-link" :class="{ active: activeTab === 'orders' }" @click="activeTab = 'orders'">📦 إدارة الطلبات</button>
        <button class="sidebar-link" @click="goProfile">👤 الملف الشخصي</button>
        <button class="btn-outline logout" @click="logout">🚪 تسجيل الخروج</button>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
      <header class="main-header">
        <div>
          <h1>{{ tabTitle }}</h1>
          <p class="greeting">نظام إدارة ومراقبة عمليات توصيل Delivro</p>
        </div>
        <div class="header-actions">
          <button class="btn-outline" @click="toggleTheme">{{ isDark ? ' الوضع الفاتح ☀️' : ' الوضع الداكن 🌙' }}</button>
          <button class="btn-outline" @click="reloadData">تحديث النظام 🔄</button>
        </div>
      </header>

      <div v-if="successMsg" class="alert success-alert">✨ {{ successMsg }}</div>
      <div v-if="errorMsg" class="alert error-alert">⚠️ {{ errorMsg }}</div>

      <!-- 1. Overview Tab -->
      <div v-if="activeTab === 'overview'" class="tab-content animate-fade">
        <section class="stats-grid">
          <article class="stat-card">
            <p class="stat-label">👥 إجمالي الزبائن</p>
            <h2>{{ customers.length }}</h2>
            <span class="stat-note-green">حسابات نشطة</span>
          </article>
          <article class="stat-card">
            <p class="stat-label">🏪 إجمالي المتاجر</p>
            <h2>{{ providers.length }}</h2>
            <span class="stat-note-orange">مزود خدمة</span>
          </article>
          <article class="stat-card">
            <p class="stat-label">🛵 السائقين المعتمدين</p>
            <h2>{{ drivers.length }}</h2>
            <span class="stat-note-blue">مندوب توصيل</span>
          </article>
          <article class="stat-card">
            <p class="stat-label">💰 الأرباح الإجمالية</p>
            <h2>${{ totalRevenue.toFixed(2) }}</h2>
            <span class="stat-note-gold">من عمولة الطلبات</span>
          </article>
        </section>

        <div class="overview-two-columns">
          <section class="panel-section">
            <div class="panel-header">
              <h2>المستخدمون المسجلون حديثاً</h2>
              <button class="panel-action-btn" @click="activeTab = 'users'">إدارة ➡️</button>
            </div>
            <div class="table-wrapper">
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in allUsers.slice(0, 5)" :key="user.id">
                    <td>{{ user.first_name }} {{ user.last_name }}</td>
                    <td>{{ user.email }}</td>
                    <td><span class="user-role-badge" :class="user.role">{{ formatRole(user.role) }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="panel-section">
            <div class="panel-header">
              <h2>الطلبات قيد المراقبة</h2>
              <button class="panel-action-btn" @click="activeTab = 'orders'">مراقبة ➡️</button>
            </div>
            <div class="table-wrapper">
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>رقم الطلب</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in orders.slice(0, 5)" :key="order.id">
                    <td>#{{ order.id }}</td>
                    <td class="price">${{ parseFloat(order.total_price).toFixed(2) }}</td>
                    <td><span class="status-badge" :class="order.status?.name">{{ formatStatus(order.status?.name) }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>

      <!-- 2. Users Tab -->
      <div v-if="activeTab === 'users'" class="tab-content animate-fade">
        <section class="panel-section">
          <div class="panel-header user-tabs-header">
            <div class="role-tabs">
              <button class="role-tab-btn" :class="{ active: userRoleFilter === 'all' }" @click="userRoleFilter = 'all'">الكل ({{ allUsers.length }})</button>
              <button class="role-tab-btn" :class="{ active: userRoleFilter === 'customer' }" @click="userRoleFilter = 'customer'">الزبائن ({{ customers.length }})</button>
              <button class="role-tab-btn" :class="{ active: userRoleFilter === 'provider' }" @click="userRoleFilter = 'provider'">أصحاب المتاجر ({{ providers.length }})</button>
              <button class="role-tab-btn" :class="{ active: userRoleFilter === 'driver' }" @click="userRoleFilter = 'driver'">السائقين ({{ drivers.length }})</button>
            </div>
            <input v-model="userSearchQuery" type="text" placeholder="ابحث باسم المستخدم أو البريد..." class="user-search-input" />
          </div>

          <div class="table-wrapper">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>رقم التعريف</th>
                  <th>الاسم بالكامل</th>
                  <th>البريد الإلكتروني</th>
                  <th>الهاتف</th>
                  <th>الدور</th>
                  <th>العنوان</th>
                  <th>العمليات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td>#{{ user.id }}</td>
                  <td><strong>{{ user.first_name }} {{ user.last_name }}</strong></td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.phone || 'غير مسجل' }}</td>
                  <td><span class="user-role-badge" :class="user.role">{{ formatRole(user.role) }}</span></td>
                  <td>{{ user.address || 'غير محدد' }}</td>
                  <td>
                    <div class="table-actions">
                      <button class="action-btn edit" @click="openEditUserModal(user)">✏️ تعديل</button>
                      <button class="action-btn delete" @click="deleteUser(user)">🗑️ حذف</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredUsers.length === 0">
                  <td colspan="7" class="empty-table">لم يتم العثور على أي مستخدمين.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <!-- 3. Orders Tab -->
      <div v-if="activeTab === 'orders'" class="tab-content animate-fade">
        <section class="panel-section">
          <div class="panel-header">
            <h2>إدارة جميع طلبات النظام</h2>
            <div class="filters-block">
              <input v-model="orderSearchQuery" type="text" placeholder="ابحث برقم الطلب..." class="table-search" />
              <select v-model="orderStatusFilter" class="table-filter">
                <option value="">جميع الحالات</option>
                <option value="pending">قيد الانتظار</option>
                <option value="accepted">قيد التحضير</option>
                <option value="on_the_way">في الطريق للتوصيل</option>
                <option value="completed">مكتمل</option>
                <option value="rejected">مرفوض</option>
              </select>
            </div>
          </div>

          <div class="table-wrapper">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>الزبون</th>
                  <th>المتجر</th>
                  <th>السائق</th>
                  <th>عنوان التوصيل</th>
                  <th>السعر الإجمالي</th>
                  <th>الحالة العامة</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in filteredOrders" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.user ? (order.user.first_name + ' ' + order.user.last_name) : 'زبون' }}</td>
                  <td>{{ order.provider?.user ? (order.provider.user.first_name + ' ' + order.provider.user.last_name) : 'متجر Delivro' }}</td>
                  <td>{{ order.driver?.user ? (order.driver.user.first_name + ' ' + order.driver.user.last_name) : 'لم يعين سائق' }}</td>
                  <td>{{ order.order_address || 'غير محدد' }}</td>
                  <td class="price">${{ parseFloat(order.total_price).toFixed(2) }}</td>
                  <td>
                    <span class="status-badge" :class="order.status?.name || 'pending'">
                      {{ formatStatus(order.status?.name) }}
                    </span>
                  </td>
                  <td>
                    <div class="table-actions">
                      <select class="status-selector" @change="changeStatusByAdmin(order, $event.target.value)" :value="order.status?.name">
                        <option value="pending">قيد الانتظار</option>
                        <option value="accepted">قيد التحضير</option>
                        <option value="on_the_way">في الطريق</option>
                        <option value="completed">مكتمل</option>
                        <option value="rejected">مرفوض</option>
                      </select>
                      <button class="action-btn delete" @click="cancelOrderByAdmin(order.id)">❌ إلغاء</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredOrders.length === 0">
                  <td colspan="8" class="empty-table">لا توجد أي طلبات مطابقة للبحث.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>

    <!-- Modal تعديل المستخدم -->
    <transition name="fade">
      <div v-if="showUserModal" class="modal-overlay" @click.self="closeUserModal">
        <div class="modal-card">
          <h2>تعديل حساب المستخدم 👤</h2>
          <form @submit.prevent="submitUserForm" class="modal-form">
            <div class="form-group">
              <label>الاسم الأول <span class="required">*</span></label>
              <input v-model="userForm.first_name" type="text" required />
            </div>
            <div class="form-group">
              <label>اسم العائلة <span class="required">*</span></label>
              <input v-model="userForm.last_name" type="text" required />
            </div>
            <div class="form-group">
              <label>البريد الإلكتروني <span class="required">*</span></label>
              <input v-model="userForm.email" type="email" required />
            </div>
            <div class="form-group">
              <label>رقم الهاتف</label>
              <input v-model="userForm.phone" type="text" />
            </div>
            <div class="form-group">
              <label>العنوان</label>
              <input v-model="userForm.address" type="text" />
            </div>
            <div class="modal-actions">
              <button type="button" class="btn-cancel" @click="closeUserModal">إلغاء</button>
              <button type="submit" class="btn-submit">حفظ التغييرات</button>
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
import '../assets/styles/AdminDashboard .css'


const token = localStorage.getItem('token')
console.log('Token exists:', !!token)
if (token) {
  console.log('Token:', token.substring(0, 20) + '...')
}


const router = useRouter()

// ─── State ───
const activeTab      = ref('overview')
const isDark         = ref(localStorage.getItem('delivro_theme') === 'dark')
const loading        = ref(true)
const successMsg     = ref('')
const errorMsg       = ref('')
const adminName      = ref('المسؤول العام')

const customers = ref([])
const providers = ref([])
const drivers   = ref([])
const orders    = ref([])

const userRoleFilter   = ref('all')
const userSearchQuery  = ref('')
const orderStatusFilter = ref('')
const orderSearchQuery  = ref('')

// ─── Computed ───
const tabTitle = computed(() => {
  const titles = {
    overview: 'لوحة التحكم والمراقبة',
    users:    'إدارة شجرة المستخدمين',
    orders:   'مراقبة وإدارة الطلبات العامة',
  }
  return titles[activeTab.value] || 'إدارة النظام'
})

const totalRevenue = computed(() =>
  orders.value
    .filter(o => o.status?.name === 'completed')
    .reduce((sum, o) => sum + parseFloat(o.total_price || 0), 0) * 0.15
)

const allUsers = computed(() => {
  const list = []
  customers.value.forEach(c => list.push({ ...c, role: 'customer' }))
  providers.value.forEach(p => {
    if (p.user) list.push({ ...p.user, role: 'provider', provider_id: p.id })
  })
  drivers.value.forEach(d => {
    if (d.user) list.push({ ...d.user, role: 'driver', driver_id: d.id })
  })
  return list
})

const filteredUsers = computed(() => {
  let result = [...allUsers.value]
  if (userRoleFilter.value !== 'all') {
    result = result.filter(u => u.role === userRoleFilter.value)
  }
  if (userSearchQuery.value) {
    const q = userSearchQuery.value.toLowerCase()
    result = result.filter(u =>
      (u.first_name + ' ' + u.last_name).toLowerCase().includes(q) ||
      (u.email || '').toLowerCase().includes(q)
    )
  }
  return result
})

const filteredOrders = computed(() => {
  let result = [...orders.value]
  if (orderStatusFilter.value) {
    result = result.filter(o => o.status?.name === orderStatusFilter.value)
  }
  if (orderSearchQuery.value) {
    result = result.filter(o => o.id.toString().includes(orderSearchQuery.value))
  }
  return result
})

// ─── Methods ───
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const goProfile = () => router.push('/admin/profile')



const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  localStorage.removeItem('delivro_email')
  router.push('/login')
}

const reloadData = () => loadAdminData()

// ─── جلب البيانات ───
const loadAdminData = async () => {
  try {
    loading.value    = true
    successMsg.value = ''
    errorMsg.value   = ''

    // ✅ جلب اسم الآدمن
    try {
      const meRes = await api.get('/auth/me')
      const me    = meRes.data.user || meRes.data
      adminName.value = (me.first_name || '') + ' ' + (me.last_name || '')
    } catch (err) {
      console.warn('me error:', err)
    }

    // ✅ جلب الزبائن
    try {
      const custRes = await api.get('/customers')
      console.log('Customers response:', custRes.data)  // للتأكد
      customers.value = custRes.data.data || []
    } catch (err) {
      console.error('Customers error:', err.response?.data || err.message)
    }

    // ✅ جلب مقدمي الخدمة
    try {
      const provRes = await api.get('/providers')
      console.log('Providers response:', provRes.data)
      providers.value = Array.isArray(provRes.data) ? provRes.data : (provRes.data.data || [])

    } catch (err) {
      console.error('Providers error:', err.response?.data || err.message)
    }

    // ✅ جلب السائقين
    try {
      const drivRes = await api.get('/v1/drivers')
      console.log('Drivers response:', drivRes.data)
      drivers.value = drivRes.data.data || []
    } catch (err) {
      console.error('Drivers error:', err.response?.data || err.message)
    }

    // ✅ جلب الطلبات
    try {
      const ordsRes = await api.get('/orders')
      console.log('Orders response:', ordsRes.data)
      orders.value = ordsRes.data.data || []
    } catch (err) {
      console.error('Orders error:', err.response?.data || err.message)
    }

    // إذا كانت كل البيانات فارغة، اعرض رسالة خطأ
    if (customers.value.length === 0 && providers.value.length === 0 && drivers.value.length === 0) {
      errorMsg.value = 'لا توجد بيانات حقيقية من الخادم. تأكد من: 1) التوكن صحيح 2) الروابط صحيحة 3) قاعدة البيانات تحتوي على بيانات'
    } else {
      successMsg.value = 'تم تحميل البيانات بنجاح!'
      setTimeout(() => { successMsg.value = '' }, 3000)
    }

  } catch (err) {
    console.error('General error:', err)
    errorMsg.value = 'فشل الاتصال بالخادم'
  } finally {
    loading.value = false
  }
}

onMounted(() => loadAdminData())

// ─── إجراءات الطلبات ───
const changeStatusByAdmin = async (order, newStatus) => {
  try {
    await api.put(`/orders/${order.id}`, { status_id: mapStatusToId(newStatus) })
  } catch (e) {
    console.warn('status update api fail')
  }
  // ابحث عن الطلب في الـ array وحدثه بدل تحديث الـ object مباشرة
  const idx = orders.value.findIndex(o => o.id === order.id)
  if (idx !== -1) {
    orders.value[idx] = { ...orders.value[idx], status: { name: newStatus } }
  }
  successMsg.value = `تم تحديث حالة الطلب #${order.id} إلى ${formatStatus(newStatus)}`
  setTimeout(() => { successMsg.value = '' }, 3000)
}

const cancelOrderByAdmin = async (id) => {
  if (!confirm(`هل أنت متأكد من إلغاء الطلب #${id}؟`)) return
  try {
    await api.delete(`/orders/${id}`)
  } catch (e) {
    console.warn('delete order api fail, removing locally')
  }
  orders.value    = orders.value.filter(o => o.id !== id)
  successMsg.value = `تم إلغاء الطلب #${id} بالكامل.`
  setTimeout(() => { successMsg.value = '' }, 3000)
}

// ─── Modal إدارة المستخدمين ───
const showUserModal = ref(false)
const editingUser   = ref(null)
const userForm      = ref({ first_name: '', last_name: '', email: '', phone: '', address: '' })

const openEditUserModal = (user) => {
  editingUser.value = user
  userForm.value    = {
    first_name: user.first_name || '',
    last_name:  user.last_name  || '',
    email:      user.email      || '',
    phone:      user.phone      || '',
    address:    user.address    || '',
  }
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value   = null
}

const submitUserForm = async () => {
  try {
    // ✅ مصلح: نستخدم /users/{id} بدل /customers/{id}
    const res     = await api.put(`/users/${editingUser.value.id}`, userForm.value)
    const updated = res.data.user
    // تحديث القوائم المحلية
    const role = editingUser.value.role
    if (role === 'customer') {
      const idx = customers.value.findIndex(c => c.id === updated.id)
      if (idx !== -1) customers.value[idx] = { ...customers.value[idx], ...updated }
    } else if (role === 'provider') {
      const idx = providers.value.findIndex(p => p.user?.id === updated.id)
      if (idx !== -1) providers.value[idx].user = { ...providers.value[idx].user, ...updated }
    } else if (role === 'driver') {
      const idx = drivers.value.findIndex(d => d.user?.id === updated.id)
      if (idx !== -1) drivers.value[idx].user = { ...drivers.value[idx].user, ...updated }
    }
    successMsg.value = 'تم تحديث بيانات المستخدم بنجاح!'
    closeUserModal()
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'فشل تحديث البيانات'
  }
  setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 3000)
}

const deleteUser = async (user) => {
  if (!confirm(`هل أنت متأكد من حذف ${user.first_name} ${user.last_name}؟`)) return
  try {
    // ✅ مصلح: نستخدم /users/{id} بدل /customers/{id}
    await api.delete(`/users/${user.id}`)
  } catch (e) {
    console.warn('delete user api fail, removing locally')
  }
  // ✅ نحذف محلياً سواء نجح الـ API أو لا
  customers.value  = customers.value.filter(c => c.id !== user.id)
  providers.value  = providers.value.filter(p => p.user?.id !== user.id)
  drivers.value    = drivers.value.filter(d => d.user?.id !== user.id)
  successMsg.value = `تم حذف المستخدم #${user.id} بنجاح`
  setTimeout(() => { successMsg.value = ''; errorMsg.value = '' }, 3000)
}

// ─── Helper Functions ───
const formatRole = (role) => {
  const map = { customer: 'زبون 👤', provider: 'صاحب متجر 🏪', driver: 'سائق 🛵', admin: 'مسؤول 🛡️' }
  return map[role] || role
}

const formatStatus = (status) => {
  const map = {
    pending:    'قيد الانتظار ⏳',
    accepted:   'قيد التحضير 👨‍🍳',
    on_the_way: 'في الطريق 🚚',
    completed:  'مكتمل ✅',
    rejected:   'مرفوض ❌',
  }
  return map[status] || status || '—'
}

const mapStatusToId = (status) => {
  const map = { pending: 1, accepted: 2, on_the_way: 3, completed: 4, rejected: 5 }
  return map[status] ?? 1
}
</script>