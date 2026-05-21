<template>
  <div class="providers-page" dir="rtl">

    <!-- ░░ Ambient Background ░░ -->
    <div class="ambient">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
      <div class="noise"></div>
    </div>

    <!-- ░░ Top Nav ░░ -->
    <nav class="top-nav">
      <div class="nav-inner">
        <div class="nav-brand">
          <span class="brand-dot"></span>
          <span class="brand-text">سوق<em>بلس</em></span>
        </div>
        <div class="nav-meta">
          <span class="nav-badge">{{ providers.length }} متجر</span>
        </div>
      </div>
    </nav>

    <!-- ░░ Hero Header ░░ -->
    <div class="header">
      <div class="header-eyebrow">
        <span class="eyebrow-dot"></span>
        <span>متاح الآن</span>
      </div>
      <h1>اختر متجرك<br /><em>المفضّل</em></h1>
      <p>تصفّح أفضل المتاجر وابدأ طلبك في ثوانٍ</p>

      <!-- Search Bar -->
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input
          v-model="searchQuery"
          class="search-input"
          type="text"
          placeholder="ابحث عن متجر..."
        />
        <span v-if="searchQuery" class="search-clear" @click="searchQuery = ''">✕</span>
      </div>

      <!-- Filter Chips -->
      <div class="filter-chips">
        <button
          v-for="f in filterTypes"
          :key="f.value"
          class="chip"
          :class="{ 'chip-active': activeFilter === f.value }"
          @click="activeFilter = f.value"
        >
          {{ f.icon }} {{ f.label }}
        </button>
      </div>
    </div>

    <!-- ░░ Stats Bar ░░ -->
    <div class="stats-bar">
      <div class="stat">
        <span class="stat-num">{{ providers.length }}</span>
        <span class="stat-label">متجر نشط</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">{{ availableCount }}</span>
        <span class="stat-label">متاح الآن</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">15</span>
        <span class="stat-label">دقيقة توصيل</span>
      </div>
    </div>

    <!-- ░░ Loading Skeleton ░░ -->
    <div v-if="loading" class="grid">
      <div v-for="n in 6" :key="n" class="card skeleton-card">
        <div class="sk sk-avatar"></div>
        <div class="sk sk-title"></div>
        <div class="sk sk-sub"></div>
        <div class="sk sk-btn"></div>
      </div>
    </div>

    <!-- ░░ Empty State ░░ -->
    <div v-else-if="filteredProviders.length === 0" class="empty-state">
      <div class="empty-icon">🏪</div>
      <h3>لا توجد متاجر</h3>
      <p>جرّب تغيير الفلتر أو كلمة البحث</p>
      <button class="empty-btn" @click="searchQuery = ''; activeFilter = 'all'">إعادة تعيين</button>
    </div>

    <!-- ░░ Provider Grid ░░ -->
    <div v-else class="grid">
      <div
        v-for="(p, index) in filteredProviders"
        :key="p.id"
        class="card"
        :style="`--delay: ${index * 0.07}s`"
        @click="goToProducts(p.id)"
      >
        <!-- Card glow -->
        <div class="card-glow"></div>

        <!-- Top row: avatar + badge -->
        <div class="card-top">
          <div class="avatar-wrap">
            <img
              :src="getProviderImage(p)"
              :alt="p.user?.first_name"
              @error="handleImgError($event, p)"
            />
            <div class="avatar-ring"></div>
            <span class="online-dot" title="متاح"></span>
          </div>
          <div class="card-badges">
            <span class="badge-type">{{ p.type || 'متجر' }}</span>
            <span class="badge-rating">★ 4.8</span>
          </div>
        </div>

        <!-- Info -->
        <div class="card-info">
          <h3>{{ p.user?.first_name }} {{ p.user?.last_name }}</h3>
          <small>متجر رقم #{{ p.id }}</small>

          <!-- Meta pills -->
          <div class="card-meta">
            <span class="meta-pill">🕐 15 دقيقة</span>
            <span class="meta-pill">🚚 توصيل مجاني</span>
          </div>
        </div>

        <!-- CTA Button -->
        <button class="card-btn">
          <span>عرض المنتجات</span>
          <span class="btn-arrow">←</span>
        </button>
      </div>
    </div>

    <!-- ░░ Footer Note ░░ -->
    <div class="page-footer">
      <span>🔒 دفع آمن ومضمون</span>
      <span class="dot-sep">·</span>
      <span>📦 توصيل سريع</span>
      <span class="dot-sep">·</span>
      <span>⭐ جودة مضمونة</span>
    </div>

  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import '../assets/styles/ShowProvider.css'
const router   = useRouter()
const providers     = ref([])
const loading       = ref(true)
const searchQuery   = ref('')
const activeFilter  = ref('all')

// const filterTypes = [
//   { value: 'all',       label: 'الكل',     icon: '✨' },
//   { value: 'restaurant',label: 'مطاعم',    icon: '🍽️' },
//   { value: 'cafe',      label: 'كافيه',    icon: '☕' },
//   { value: 'grocery',   label: 'بقالة',    icon: '🛒' },
// ]

const availableCount = computed(() => providers.value.length)

const filteredProviders = computed(() => {
  let list = [...providers.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p =>
      p.user?.first_name?.toLowerCase().includes(q) ||
      p.user?.last_name?.toLowerCase().includes(q) ||
      p.type?.toLowerCase().includes(q)
    )
  }

  if (activeFilter.value !== 'all') {
    list = list.filter(p => p.type?.toLowerCase() === activeFilter.value)
  }

  return list
})

function getProviderImage(p) {
  if (p?.user?.image_path && !p?.user?.image_path.includes('null')) {
    // Already full URL from API
    if (p.user.image_path.startsWith('http')) return p.user.image_path
    return `http://127.0.0.1:8000/storage/${p.user.image_path}`
  }
  const name = encodeURIComponent(`${p?.user?.first_name || 'S'} ${p?.user?.last_name || ''}`.trim())
  return `https://ui-avatars.com/api/?name=${name}&background=f59e0b&color=fff&bold=true&size=128`
}

function handleImgError(event, p) {
  const name = encodeURIComponent(`${p?.user?.first_name || 'S'} ${p?.user?.last_name || ''}`.trim())
  event.target.src = `https://ui-avatars.com/api/?name=${name}&background=f59e0b&color=fff&bold=true&size=128`
}

const goToProducts = (id) => {
  router.push({ name: 'create-order', params: { id } })
}

const stats = ref({ total: 0, available: 0, avg_delivery_mins: 15, types: [] })

onMounted(async () => {
  try {
    // جلب المتاجر (يدعم ?search= و ?type= من الباك)
    const [providersRes, statsRes] = await Promise.all([
      api.get('/providers'),
    ])
    providers.value = providersRes.data
    stats.value     = statsRes.data

    // استخدم الأنواع الفعلية من الباك لبناء الفلاتر ديناميكياً
    // filterTypes يصبح computed بدل ثابت:
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

// وغيّر filterTypes من const ثابت إلى computed:
const filterTypes = computed(() => [
  { value: 'all', label: 'الكل', icon: '✨' },
  ...(stats.value.types ?? []).map(t => ({
    value: t,
    label: t,
    icon:  typeEmoji(t),
  }))
])

function typeEmoji(type) {
  const map = {
    restaurant: '🍽️', cafe: '☕', grocery: '🛒',
    bakery: '🥖', pharmacy: '💊', sweets: '🍰',
  }
  return map[type?.toLowerCase()] ?? '🏪'
}
</script>