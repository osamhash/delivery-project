<template>
  <div class="ActivRole">
    <!-- <pre>{{ orders }}</pre> -->
    <div class="dashboard-shell" :class="{ dark: isDark }">

      <!-- Sidebar -->
      <aside class="dashboard-sidebar">
        <div class="brand-block">
          <button class="sidebar-link" @click="goProfile">البروفايل</button>
          <button class="btn-outline logout" @click="logout">تسجيل الخروج</button>
          <div class="logo-mark">Delivro</div>
          <p class="brand-subtitle">لوحة تحكم احترافية</p>
        </div>

        <div class="profile-card">
          <p class="profile-role">الدور</p>
          <h3>{{ roleLabel }}</h3>
          <p class="profile-name">{{ userName }}</p>
        </div>

        <nav class="sidebar-nav">
          <button class="sidebar-link active" @click="refreshData">الرئيسية</button>
          <button class="sidebar-link" @click="createOrder">طلب جديد</button>
          <button class="sidebar-link">الملف الشخصي</button>
          <button class="sidebar-link">التقارير</button>
          <button class="sidebar-link">الإعدادات</button>
        </nav>
      </aside>

      <!-- Main -->
      <main class="dashboard-main">

        <!-- Header -->
        <header class="main-header">
<button v-if="!order?.review" @click="openReview(order)">
  ⭐ قيّم
</button>          <div>
            <div class="nav-icons">
            <button @click="goProfile">👤</button>
            <button @click="goOrders">📦</button>
            <button @click="goFavorites">⭐</button>
            <button @click="logout">🚪</button>
          </div>
            <p class="greeting">مرحباً {{ userName }}،</p>
            <h1>{{ headerTitle }}</h1>
          </div>

          <div class="header-actions">
            <button class="btn-outline" @click="createOrder">طلب جديد</button>
            <button class="btn-outline" @click="toggleTheme">{{ themeLabel }}</button>
            <button class="btn-outline" @click="refreshData">تحديث</button>
          </div>
        </header>

        <!-- Stats -->
        <section class="stats-grid">
          <article class="stat-card" v-for="card in stats" :key="card.title">
            <p class="stat-label">{{ card.title }}</p>
            <h2>{{ card.value }}</h2>
          </article>
        </section>

        <!-- 🔥 DATA TABLE CONTROLS -->
        <section class="table-controls">

          <input
            v-model="search"
            type="text"
            placeholder="ابحث برقم الطلب..."
            class="table-search"
          />

          <select v-model="statusFilter" class="table-filter">
            <option value="">كل الحالات</option>
            <option value="pending">قيد الانتظار</option>
            <option value="delivered">تم التوصيل</option>
            <option value="canceled">ملغي</option>
          </select>

        </section>

        <!-- 🔥 DATA TABLE -->
        <section class="table-wrapper">

          <table class="dashboard-table">
            <thead>
              <tr>
                <th @click="sortBy('id')">
                  # <span v-if="sortKey === 'id'">{{ sortOrderIcon }}</span>
                </th>

                <th @click="sortBy('total_price')">
                  السعر <span v-if="sortKey === 'total_price'">{{ sortOrderIcon }}</span>
                </th>

                <th @click="sortBy('status')">
                   مزود الخدمة  <span v-if="sortKey === 'status'">{{ sortOrderIcon }}</span>
                </th>
                <th >
                 الحالة<span v-if="sortKey === 'status'">{{ sortOrderIcon }}</span>
                </th>
                
              </tr>
            </thead>

            <tbody>
              <tr v-for="order in filteredOrders" :key="order.id">
                <td>{{ order.id }}</td>
                <td>{{ order.total_price }}</td>
                <td>
                  <span class="status-badge">
                    {{ order.provider_id }}
                  </span>
                </td>
                <td>
                  <span class="status-badge">
                    {{ order.status?.name }}
                  </span>
                </td>
                <td> <button v-if="!order?.review" class="rate-btn"
                  @click="openReview(order)">⭐ قيّم </button>
                  <span v-else class="rated">
                    ⭐ {{ order.review.rating }}
                  </span>
                </td>
              </tr>
            </tbody>
<!-- ⭐ REVIEW MODAL -->
<transition name="fade">
  <div v-if="showReview" class="review-overlay" @click.self="closeReview">
    
    <div class="review-modal">
      <h2>تقييم الطلب</h2>

      <!-- ⭐ Stars -->
      <div class="stars">
        <span
          v-for="i in 5"
          :key="i"
          class="star"
          :class="{ active: i <= hoverRating || i <= rating }"
          @mouseover="hoverRating = i"
          @mouseleave="hoverRating = 0"
          @click="rating = i"
        >
          ★
        </span>
      </div>

      <!-- 💬 Comment -->
      <textarea
        v-model="comment"
        placeholder="اكتب ملاحظتك..."
        class="review-textarea"
      ></textarea>

      <!-- 🎯 Actions -->
      <div class="review-actions">
        <button class="btn cancel" @click="closeReview">إلغاء</button>
        <button class="btn submit" @click="submitReview">
          إرسال 🚀
        </button>
      </div>
    </div>

  </div>
</transition>
          </table>

        </section>

      </main>
    </div>
  </div>
</template>

<script setup>
import '../assets/styles/dashboard.css'
import api from '../services/api'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

/* ================= STATE ================= */
const stats = ref([])
const orders = ref([])

const search = ref('')
const statusFilter = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')

const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

const role = ref(localStorage.getItem('delivro_role') || 'customer')
const email = ref(localStorage.getItem('delivro_email') || '')

/* ================= USER ================= */
const userName = computed(() => {
  if (!email.value) return 'صديقنا'
  return email.value.split('@')[0]
})

const themeLabel = computed(() =>
  isDark.value ? 'الوضع الفاتح' : 'الوضع الداكن'
)

/* ================= ROUTER ================= */
const router = useRouter()

const createOrder = () => {
  router.push('/show-provider')
}

/* ================= SORT ================= */
const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const sortOrderIcon = computed(() =>
  sortOrder.value === 'asc' ? '▲' : '▼'
)

/* ================= FILTER + SEARCH + SORT ================= */
const filteredOrders = computed(() => {
  let result = [...orders.value]

  // search
  if (search.value) {
    result = result.filter(o =>
      o.id.toString().includes(search.value)
    )
  }

  // filter
  if (statusFilter.value) {
    result = result.filter(o =>
      o.status?.name?.toLowerCase().includes(statusFilter.value)
    )
  }

  // sort
  if (sortKey.value) {
    result.sort((a, b) => {
      let aVal = a[sortKey.value]
      let bVal = b[sortKey.value]

      if (sortKey.value === 'status') {
        aVal = a.status?.name
        bVal = b.status?.name
      }

      if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return result
})

/* ================= API ================= */
onMounted(async () => {
  try {
    const res = await api.get('/customer/dashboard')
    const data = res.data

    stats.value = [
      { title: 'الطلبات النشطة', value: data.active_orders },
      { title: 'المتاجر المفضلة', value: data.favorites },
      { title: 'المصروف', value: data.rich },
    ]

    orders.value = data.orders
  } catch (err) {
    console.log(err)
  }
})

/* ================= ACTIONS ================= */
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

const refreshData = () => {
  location.reload()
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('delivro_role')
  localStorage.removeItem('delivro_email')

  router.push('/login')
}

  const goProfile = () => router.push('/profile')

  // const orders = ref([])
  const page = ref(1)

  const loadOrders = async () => {
    const res = await api.get(`/orders?page=${page.value}`)
    orders.value = res.data.data
  }
const spendingDiff = computed(() => {
  if (current > last) return '📈'
  if (current < last) return '📉'
  return '➖'
})



const showReview   = ref(false)
const selectedOrder = ref(null)
const rating       = ref(5)
const hoverRating  = ref(0)
const comment      = ref('')

const openReview = (order) => {
  selectedOrder.value = order
  rating.value = 5
  comment.value = ''
  showReview.value = true
}

const closeReview = () => {
  showReview.value = false
}

const submitReview = async () => {
  try {
    await api.post('/reviews', {
      order_id: selectedOrder.value.id,
      rating: rating.value,
      comment: comment.value
    })

    //  حدّث الطلب مباشرة (بدون reload)
    selectedOrder.value.review = {
      rating: rating.value,
      comment: comment.value
    }

    showReview.value = false

  } catch (e) {
    console.error(e)
  }
}

</script>