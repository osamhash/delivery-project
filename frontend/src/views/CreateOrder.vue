<template>
    
    <pre >{{ products }}</pre>
  <div class="app-shell" dir="rtl">
    <!--  Background layers  -->
    <div class="bg-mesh"></div>
    <div class="bg-grid"></div>

    <!--  Header  -->
    <header class="top-bar">
      <div class="top-bar-inner">
        <div class="brand">
          <span class="brand-icon">🛒</span>
          <span class="brand-name">سوق<em>بلس</em></span>
          <h1 style="color:white; position:fixed; top:100px; left:20px; z-index:9999;">
  Osama
</h1>
        </div>
        <div class="customer-chip" v-if="customer">
          <div class="avatar">{{ customer.first_name?.charAt(0) }}{{ customer.last_name?.charAt(0) }}</div>
          <div class="customer-info">
            <span class="customer-name">{{ customer.first_name }} {{ customer.last_name }}</span>
            <span class="customer-label">زبون مميز</span>
          </div>
        </div>
      </div>
    </header>

    <!--  Main Content  -->
    <main class="main-content">
      <!-- Filters & Sort Bar -->
      <div class="controls-bar">
        <!-- Category Tabs -->
        <div class="category-tabs">
          <button
            v-for="cat in categories"
            :key="cat.value"
            class="cat-tab"
            :class="{ active: activeCategory === cat.value }"
            @click="activeCategory = cat.value"
          >
            <span class="cat-icon">{{ cat.icon }}</span>
            <span>{{ cat.label }}</span>
          </button>
        </div>

        <!-- Sort & Price Filter -->
        <div class="filter-group">
          <div class="filter-pill">
            <span class="filter-label">الترتيب</span>
            <select v-model="sortBy" class="pill-select">
              <option value="">بدون</option>
              <option value="name_asc">الاسم ↑</option>
              <option value="name_desc">الاسم ↓</option>
              <option value="price_asc">السعر ↑</option>
              <option value="price_desc">السعر ↓</option>
            </select>
          </div>
          <div class="filter-pill">
            <span class="filter-label">حتى</span>
            <input
              type="range"
              v-model.number="maxPrice"
              :min="0"
              :max="maxProductPrice"
              class="price-range"
            />
            <span class="price-badge">{{ maxPrice }} $</span>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="products-grid">
        <transition-group name="card" tag="div" class="grid-inner">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-card"
            :class="{ selected: cart[product.id]?.qty > 0 }"
          >
            <!-- Selection Glow Bar -->
            <div class="card-glow-bar" v-if="cart[product.id]?.qty > 0"></div>

            <!-- Product Image / Emoji -->
            <div class="product-thumb">
              <img v-if="product.image_path" :src="getImage(product.image_path)" :alt="product.type" />
              <span v-else class="thumb-emoji">{{ categoryEmoji(product.type) }}</span>
              <span class="type-badge">{{ product.type }}</span>
            </div>

            <!-- Product Info -->
            <div class="product-body">
              <h3 class="product-name">{{ product.type }}</h3>
              <p class="product-desc">{{ product.description || 'لا يوجد وصف' }}</p>
              <div class="product-footer">
                <span class="product-price">{{ product.price.toFixed(2) }} <em>$</em></span>
                <!-- Qty Controls -->
                <div class="qty-control">
                  <button
                    class="qty-btn minus"
                    :disabled="!cart[product.id]?.qty"
                    @click="removeFromCart(product)"
                  >−</button>
                  <span class="qty-value">{{ cart[product.id]?.qty || 0 }}</span>
                  <button class="qty-btn plus" @click="addToCart(product)">+</button>
                </div>
              </div>
            </div>
          </div>
        </transition-group>

        <!-- Empty State -->
        <div v-if="filteredProducts.length === 0" class="empty-state">
          <span>🔍</span>
          <p>لا توجد منتجات تطابق الفلتر</p>
        </div>
      </div>
    </main>

    <!--  Floating Cart Button  -->
    <transition name="bounce">
      <button v-if="totalQty > 0" class="cart-fab" @click="showCheckout = true">
        <span class="fab-icon">🛍️</span>
        <span class="fab-label">تأكيد الطلب</span>
        <span class="fab-badge">{{ totalQty }}</span>
        <span class="fab-price">{{ totalPrice.toFixed(2) }} $</span>
      </button>
    </transition>

    <!--  Checkout Modal  -->
    <transition name="modal">
      <div class="modal-overlay" v-if="showCheckout" @click.self="showCheckout = false">
        <div class="modal-sheet">

          <!-- Modal Header -->
          <div class="modal-header">
            <button class="close-btn" @click="showCheckout = false">✕</button>
            <div class="modal-title-group">
              <h2 class="modal-title">قسيمة الطلب</h2>
              <span class="modal-subtitle">مراجعة وتأكيد</span>
            </div>
            <span class="receipt-icon">🧾</span>
          </div>

          <div class="modal-body">
            <!-- Order Items -->
            <div class="receipt-section">
              <div class="section-label">المنتجات المختارة</div>
              <div class="receipt-items">
                <div
                  v-for="item in cartItems"
                  :key="item.product.id"
                  class="receipt-item"
                >
                  <div class="ri-left">
                    <span class="ri-emoji">{{ categoryEmoji(item.product.type) }}</span>
                    <div>
                      <div class="ri-name">{{ item.product.type }}</div>
                      <div class="ri-qty">{{ item.qty }} × {{ item.product.price.toFixed(2) }} $</div>
                    </div>
                  </div>
                  <span class="ri-total">{{ (item.qty * item.product.price).toFixed(2) }} $</span>
                </div>
              </div>
              <!-- Total -->
              <div class="receipt-total">
                <span>الإجمالي</span>
                <span class="total-amount">{{ totalPrice.toFixed(2) }} <em>$</em></span>
              </div>
            </div>

            <!-- Driver Selection -->
            <div class="receipt-section">
              <div class="section-label">اختر السائق</div>
              <div class="drivers-list">
                <div
                  v-for="driver in drivers"
                  :key="driver.id"
                  class="driver-card"
                  :class="{
                    'driver-selected': selectedDriver?.id === driver.id,
                    'driver-unavailable': !driver.is_available
                  }"
                  @click="driver.is_available && (selectedDriver = driver)"
                >
                  <div class="driver-avatar">{{ driver.name?.charAt(0) }}</div>
                  <div class="driver-info">
                    <span class="driver-name">{{ driver.name }}</span>
                    <div class="driver-meta">
                      <span class="driver-rating">★ {{ driver.rating }}</span>
                      <span
                        class="driver-status"
                        :class="driver.is_available ? 'available' : 'busy'"
                      >
                        {{ driver.is_available ? '● متاح' : '● مشغول' }}
                      </span>
                    </div>
                  </div>
                  <span class="driver-check" v-if="selectedDriver?.id === driver.id">✓</span>
                </div>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="receipt-section">
              <div class="section-label">طريقة الدفع</div>
              <div class="payment-methods">
                <button
                  v-for="method in paymentMethods"
                  :key="method.value"
                  class="pay-btn"
                  :class="{ 'pay-active': selectedPayment === method.value }"
                  @click="selectedPayment = method.value"
                >
                  <span>{{ method.icon }}</span>
                  <span>{{ method.label }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <div class="order-summary-bar">
              <div>
                <span class="summary-label">المبلغ الإجمالي</span>
                <span class="summary-amount">{{ totalPrice.toFixed(2) }} $</span>
              </div>
              <div>
                <span class="summary-label">السائق</span>
                <span class="summary-val">{{ selectedDriver?.name || '—' }}</span>
              </div>
              <div>
                <span class="summary-label">الدفع</span>
                <span class="summary-val">{{ selectedPayment || '—' }}</span>
              </div>
            </div>
            <div class="receipt-section">
            <div class="section-label">عنوان التوصيل</div>
            <input
              v-model="orderAddress"
              class="address-input"
              type="text"
              placeholder="أدخل عنوان التوصيل..."
            />
            </div>
            <button
              class="confirm-btn"
              :disabled="!canConfirm || isSubmitting"
              @click="submitOrder"
            >
              <span v-if="isSubmitting" class="spinner"></span>
              <span v-else>تأكيد الطلب 🚀</span>
            </button>
            <p class="confirm-hint" v-if="!canConfirm">
              {{ !selectedDriver ? 'اختر سائقاً' : 'اختر طريقة دفع' }}
            </p>
          </div>
        </div>
      </div>
    </transition>

    <!--  Success Toast  -->
    <transition name="toast">
      <div class="toast success-toast" v-if="successMsg">
        <span>✅</span>
        <span>{{ successMsg }}</span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import '../assets/styles/CreateOrder.css'

// ─── Route ───────────────────────────────────────────
const route      = useRoute()
const providerId = computed(() => route.params.id)

// ─── Auth ─────────────────────────────────────────────
function getToken() {
  return localStorage.getItem('token') ?? ''
}

// ─── State ───────────────────────────────────────────
const customerId     = ref(null)   // ← سيُملأ من /api/auth/me
const customer       = ref(null)
const products       = ref([])
const drivers        = ref([])
const cart           = reactive({})
const maxPrice       = ref(9999)

const activeCategory  = ref('all')
const sortBy          = ref('')
const showCheckout    = ref(false)
const selectedDriver  = ref(null)
const selectedPayment = ref('')
const isSubmitting    = ref(false)
const successMsg      = ref('')

// ─── Static Data ─────────────────────────────────────
const categories = [
  { value: 'all',   label: 'الكل',  icon: '✨' },
  { value: 'Food',  label: 'Food',  icon: '🍽️' },
  { value: 'Drink', label: 'Drink', icon: '🥤' },
  { value: 'Snack', label: 'Snack', icon: '🍰' },
]

const paymentMethods = [
  { value: 'cash',      label: 'نقد',       icon: '💵' },
  { value: 'card',      label: 'بطاقة',     icon: '💳' },
  { value: 'apple_pay', label: 'Apple Pay', icon: '🍎' },
  { value: 'stc_pay',   label: 'STC Pay',   icon: '📱' },
]

// ─── Fetch Helper ─────────────────────────────────────
async function apiFetch(url, options = {}) {
  const token = getToken()

  const res = await fetch(url, {
    ...options,
    headers: {
      'Accept':        'application/json',
      'Content-Type':  'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers ?? {}),
    },
  })

  const text = await res.text()

  if (text.trim().startsWith('<')) {
    console.error(`❌ رجع HTML من: ${url} — تحقق من التوكن أو المسار`)
    throw new Error(`HTML_RESPONSE from ${url}`)
  }
  const json = JSON.parse(text)

  if (!res.ok) {
    console.error('❌ API Error:', res.status, json)
    throw new Error(JSON.stringify(json))
  }
  return json
}

// ─── Computed ─────────────────────────────────────────
const maxProductPrice = computed(() =>
  products.value.length
    ? Math.ceil(Math.max(...products.value.map(p => Number(p.price))))
    : 9999
)

watch(maxProductPrice, (val) => { maxPrice.value = val })

const filteredProducts = computed(() => {
  let list = products.value.map(p => ({ ...p, price: Number(p.price) }))

  if (activeCategory.value !== 'all')
    list = list.filter(p => p.type === activeCategory.value)

  list = list.filter(p => p.price <= maxPrice.value)

  if (sortBy.value === 'name_asc')   list.sort((a,b) => a.type.localeCompare(b.type, 'ar'))
  if (sortBy.value === 'name_desc')  list.sort((a,b) => b.type.localeCompare(a.type, 'ar'))
  if (sortBy.value === 'price_asc')  list.sort((a,b) => a.price - b.price)
  if (sortBy.value === 'price_desc') list.sort((a,b) => b.price - a.price)

  return list
})
const orderAddress = ref('')
const cartItems  = computed(() => Object.values(cart).filter(i => i.qty > 0))
const totalQty   = computed(() => cartItems.value.reduce((s,i) => s + i.qty, 0))
const totalPrice = computed(() => cartItems.value.reduce((s,i) => s + i.qty * Number(i.product.price), 0))
// const canConfirm = computed(() => cartItems.value.length > 0 && selectedDriver.value && selectedPayment.value)

const canConfirm = computed(() => cartItems.value.length > 0 && selectedDriver.value && selectedPayment.value && (orderAddress.value.trim() || customer.value?.address)

)
// ─── Methods ──────────────────────────────────────────
function categoryEmoji(type) {
  const map = { 'مشروبات': '🧃', 'أطعمة': '🍛', 'حلويات': '🍮' }
  return map[type] || '📦'
}

function addToCart(product) {
  if (!cart[product.id]) cart[product.id] = { product: { ...product, price: Number(product.price) }, qty: 0 }
  cart[product.id].qty++
}

function removeFromCart(product) {
  if (cart[product.id]?.qty > 0) cart[product.id].qty--
}

async function submitOrder() {
  if (!canConfirm.value) return
  isSubmitting.value = true
  try {
    const payload = {
      user_id:        customerId.value,
      provider_id:    Number(providerId.value),
      driver_id:      selectedDriver.value.id,
      status_id:      1,
      total_price:    totalPrice.value,
      payment_status: selectedPayment.value,
      order_address: customer.value?.address || orderAddress.value || 'غير محدد',
      products: cartItems.value.map(i => ({
        product_id: i.product.id,
        quantity:   i.qty,
        price:      Number(i.product.price),
      }))
    }

    await apiFetch('/api/v1/orders', { method: 'POST', body: JSON.stringify(payload) })

    showCheckout.value   = false
    Object.keys(cart).forEach(k => delete cart[k])
    selectedDriver.value  = null
    selectedPayment.value = ''
    successMsg.value      = 'تم تأكيد طلبك بنجاح! 🎉'
    setTimeout(() => successMsg.value = '', 4000)

  } catch (e) {
    console.error('submitOrder error:', e)
  } finally {
    isSubmitting.value = false
  }
}

const BASE_URL = import.meta.env.VITE_STORAGE_URL || 'http://localhost:8000/storage'

const getImage = (path) => {
  if (!path) return null
  return `${BASE_URL}/${path.replace(/\\/g, '/')}`
}

// ─── Load Data ────────────────────────────────────────
onMounted(async () => {
  const pid   = route.params.id
  const token = getToken()

  console.log('🔑 providerId:', pid)
  console.log('🎫 token:', token ? '✅ موجود' : '❌ غير موجود')

  // ── 0. جيب بيانات الزبون المسجّل من /api/auth/me ──
  try {
    const me = await apiFetch('/api/auth/me')
    // الـ AuthController@me يرجع { user: {...} }
    const userData  = me.user ?? me
    customerId.value = userData.id
    customer.value   = userData

    console.log('👤 customerId:', customerId.value)
    console.log('👤 customer name:', userData.first_name, userData.last_name)
  } catch (e) {
    console.warn('⚠️ me:', e.message)
  }

  // ── 1. المنتجات ────────────────────────────────────
  try {
    const data     = await apiFetch(`/api/v1/providers/${pid}/products`)
    const list     = data.data ?? data
    products.value = list.map(p => ({ ...p, price: Number(p.price) }))
    console.log('✅ products:', products.value.length, 'منتج')
  } catch (e) {
    console.warn('⚠️ products:', e.message)
  }

  // ── 2. السائقون ────────────────────────────────────
  try {
    const data    = await apiFetch('/api/v1/drivers')
    const list    = data.data ?? data
    drivers.value = list.map(d => ({
      ...d,
      name:   d.name ?? `${d.user?.first_name ?? ''} ${d.user?.last_name ?? ''}`.trim(),
      rating: d.rating ?? 4.8,
    }))
    console.log('✅ drivers:', drivers.value.length, 'سائق')
  } catch (e) {
    console.warn('⚠️ drivers:', e.message)
  }
})


</script>

