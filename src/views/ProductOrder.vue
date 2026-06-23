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
            <span class="price-badge">{{ maxPrice }} ر.س</span>
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
              <img v-if="product.image_path" :src="product.image_path" :alt="product.type" />
              <span v-else class="thumb-emoji">{{ categoryEmoji(product.type) }}</span>
              <span class="type-badge">{{ product.type }}</span>
            </div>

            <!-- Product Info -->
            <div class="product-body">
              <h3 class="product-name">{{ product.type }}</h3>
              <p class="product-desc">{{ product.description || 'لا يوجد وصف' }}</p>
              <div class="product-footer">
                <span class="product-price">{{ product.price.toFixed(2) }} <em>ر.س</em></span>
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
        <span class="fab-price">{{ totalPrice.toFixed(2) }} ر.س</span>
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
                      <div class="ri-qty">{{ item.qty }} × {{ item.product.price.toFixed(2) }} ر.س</div>
                    </div>
                  </div>
                  <span class="ri-total">{{ (item.qty * item.product.price).toFixed(2) }} ر.س</span>
                </div>
              </div>
              <!-- Total -->
              <div class="receipt-total">
                <span>الإجمالي</span>
                <span class="total-amount">{{ totalPrice.toFixed(2) }} <em>ر.س</em></span>
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
                <span class="summary-amount">{{ totalPrice.toFixed(2) }} ر.س</span>
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
import { ref, computed, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'

// ─── Route 
const route = useRoute()
const providerId = computed(() => route.params.id)
const customerId = 3

// ─── State 
const customer      = ref(null)
const products      = ref([])
const drivers       = ref([])
const cart          = reactive({})

const activeCategory = ref('all')
const sortBy        = ref('')

const showCheckout  = ref(false)
const selectedDriver  = ref(null)
const selectedPayment = ref('')
const isSubmitting  = ref(false)
const successMsg    = ref('')

// ─── Data 
const categories = [
  { value: 'all', label: 'الكل', icon: '✨' },
  { value: 'food', label: 'Food', icon: '🍽️' },
  { value: 'drink', label: 'Drink', icon: '🥤' },
  { value: 'snack', label: 'Snack', icon: '🍰' },
]

const paymentMethods = [
  { value: 'cash',      label: 'نقد',       icon: '💵' },
  { value: 'card',      label: 'بطاقة',     icon: '💳' },
  { value: 'apple_pay', label: 'Apple Pay', icon: '🍎' },
  { value: 'stc_pay',   label: 'STC Pay',   icon: '📱' },
]

// ─── Computed 
const filteredProducts = computed(() => {
  let list = [...products.value]

  if (activeCategory.value !== 'all') {
    list = list.filter(p => p.type === activeCategory.value)
  }

  if (sortBy.value === 'name_asc')   list.sort((a,b) => a.type.localeCompare(b.type))
  if (sortBy.value === 'name_desc')  list.sort((a,b) => b.type.localeCompare(a.type))
  if (sortBy.value === 'price_asc')  list.sort((a,b) => a.price - b.price)
  if (sortBy.value === 'price_desc') list.sort((a,b) => b.price - a.price)

  return list
})

const cartItems = computed(() =>
  Object.values(cart).filter(i => i.qty > 0)
)

const totalQty = computed(() =>
  cartItems.value.reduce((s, i) => s + i.qty, 0)
)

const totalPrice = computed(() =>
  cartItems.value.reduce((s, i) => s + i.qty * i.product.price, 0)
)

const canConfirm = computed(() =>
  cartItems.value.length > 0 && selectedDriver.value && selectedPayment.value
)

// ─── Methods ─
function categoryEmoji(type) {
  const map = { 'مشروبات': '🧃', 'أطعمة': '🍛', 'حلويات': '🍮' }
  return map[type] || '📦'
}

function addToCart(product) {
  if (!cart[product.id]) cart[product.id] = { product, qty: 0 }
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
      user_id: customerId,
      provider_id: providerId.value,
      driver_id: selectedDriver.value.id,
      status_id: 1,
      total_price: totalPrice.value,
      payment_method: selectedPayment.value,
      order_address: customer.value?.address || '',
      products: cartItems.value.map(i => ({
        product_id: i.product.id,
        quantity: i.qty,
        price: i.product.price,
      }))
    }

    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    if (!res.ok) throw new Error('فشل الطلب')

    showCheckout.value = false
    Object.keys(cart).forEach(k => delete cart[k])
    selectedDriver.value = null
    selectedPayment.value = ''
    successMsg.value = 'تم تأكيد طلبك بنجاح! 🎉'

    setTimeout(() => successMsg.value = '', 4000)

  } catch (e) {
    console.error(e)
  } finally {
    isSubmitting.value = false
  }
}

// ─── Load Data 
onMounted(async () => {
  try {
    const userRes = await fetch(`/api/users/${customerId}`)
    customer.value = await userRes.json()

    const prodRes = await fetch(`/api/v1/providers/${providerId.value}/products`)
    const prodData = await prodRes.json()

    products.value = Array.isArray(prodData)
      ? prodData
      : (prodData.data ?? [])

    const drvRes = await fetch('/api/drivers')
    drivers.value = await drvRes.json()

  } catch (e) {
    console.error(e)
  }
})
</script>

<style scoped>
/* ─── Google Fonts  */
@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&family=Cairo:wght@400;600;700;900&display=swap');

/* ─── CSS Variables  */
.app-shell {
  --bg:        #060b14;
  --surface:   #0d1626;
  --surface2:  #111d30;
  --border:    #1e3050;
  --accent:    #38bdf8;
  --accent2:   #0ea5e9;
  --gold:      #f59e0b;
  --success:   #10b981;
  --danger:    #ef4444;
  --text:      #e2e8f0;
  --text-muted:#64748b;
  --text-dim:  #94a3b8;
  --radius:    16px;
  --radius-lg: 24px;
  font-family: 'Tajawal', 'Cairo', sans-serif;
  background:  var(--bg);
  color:       var(--text);
  min-height:  100vh;
  position:    relative;
  overflow-x:  hidden;
}

/* ─── Background Effects ────────────────────────── */
.bg-mesh {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background:
    radial-gradient(ellipse 80% 60% at 10% 0%,   rgba(56,189,248,.07) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 90% 100%,  rgba(14,165,233,.06) 0%, transparent 60%),
    radial-gradient(ellipse 40% 30% at 50% 50%,   rgba(245,158,11,.03) 0%, transparent 70%);
}
.bg-grid {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(56,189,248,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(56,189,248,.03) 1px, transparent 1px);
  background-size: 48px 48px;
}

/* ─── Top Bar ───── */
.top-bar {
  position: sticky; top: 0; z-index: 100;
  background: rgba(6,11,20,.85);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
}
.top-bar-inner {
  max-width: 1200px; margin: 0 auto;
  padding: 14px 24px;
  display: flex; align-items: center; justify-content: space-between;
}
.brand { display: flex; align-items: center; gap: 10px; }
.brand-icon { font-size: 26px; }
.brand-name {
  font-family: 'Cairo', sans-serif;
  font-size: 22px; font-weight: 900;
  color: var(--text); letter-spacing: -0.5px;
}
.brand-name em { color: var(--accent); font-style: normal; }

.customer-chip {
  display: flex; align-items: center; gap: 10px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 40px; padding: 6px 16px 6px 6px;
}
.avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; color: #fff;
}
.customer-info { display: flex; flex-direction: column; }
.customer-name { font-size: 13px; font-weight: 700; color: var(--text); }
.customer-label { font-size: 10px; color: var(--accent); }

/* ─── Main Content  */
.main-content {
  position: relative; z-index: 1;
  max-width: 1200px; margin: 0 auto;
  padding: 28px 24px 120px;
}

/* ─── Controls Bar  */
.controls-bar {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
  margin-bottom: 28px;
}
.category-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.cat-tab {
  display: flex; align-items: center; gap: 6px;
  padding: 9px 18px; border-radius: 40px;
  border: 1.5px solid var(--border);
  background: var(--surface); color: var(--text-dim);
  font-family: 'Tajawal', sans-serif; font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all .25s;
}
.cat-tab:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); }
.cat-tab.active {
  background: var(--accent); border-color: var(--accent);
  color: #0f172a; font-weight: 700;
  box-shadow: 0 4px 20px rgba(56,189,248,.3);
}
.cat-icon { font-size: 16px; }

.filter-group { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.filter-pill {
  display: flex; align-items: center; gap: 8px;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 40px; padding: 8px 16px;
}
.filter-label { font-size: 12px; color: var(--text-muted); }
.pill-select {
  background: transparent; border: none; outline: none;
  color: var(--text); font-family: 'Tajawal', sans-serif;
  font-size: 13px; cursor: pointer;
}
.price-range {
  width: 80px; accent-color: var(--accent);
}
.price-badge {
  font-size: 12px; font-weight: 700; color: var(--accent);
  background: rgba(56,189,248,.1); padding: 2px 8px; border-radius: 20px;
}

/* ─── Products Grid  */
.products-grid { position: relative; }
.grid-inner {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 20px;
}
.product-card {
  background: var(--surface);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden; position: relative;
  transition: border-color .3s, box-shadow .3s, transform .25s;
  cursor: default;
}
.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 48px rgba(0,0,0,.4);
}
.product-card.selected {
  border-color: var(--accent);
  box-shadow: 0 0 40px rgba(56,189,248,.15);
}
.card-glow-bar {
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--accent), transparent);
}

.product-thumb {
  width: 100%; height: 130px;
  background: linear-gradient(135deg, var(--surface2), #0a1628);
  display: flex; align-items: center; justify-content: center;
  position: relative;
}
.product-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-emoji { font-size: 54px; }
.type-badge {
  position: absolute; top: 10px; left: 10px;
  background: rgba(6,11,20,.75); border: 1px solid var(--border);
  color: var(--text-dim); font-size: 11px; font-weight: 600;
  padding: 3px 10px; border-radius: 20px; backdrop-filter: blur(6px);
}

.product-body { padding: 16px; }
.product-name {
  font-size: 16px; font-weight: 800; color: var(--text);
  margin: 0 0 6px; font-family: 'Cairo', sans-serif;
}
.product-desc {
  font-size: 12.5px; color: var(--text-muted); margin: 0 0 14px;
  line-height: 1.6;
}
.product-footer {
  display: flex; align-items: center; justify-content: space-between;
}
.product-price {
  font-size: 18px; font-weight: 900; color: var(--accent);
  font-family: 'Cairo', sans-serif;
}
.product-price em { font-size: 12px; font-style: normal; color: var(--text-muted); }

/* ─── Qty Control ─ */
.qty-control {
  display: flex; align-items: center; gap: 10px;
}
.qty-btn {
  width: 34px; height: 34px; border-radius: 50%;
  border: none; cursor: pointer;
  font-size: 18px; font-weight: 700; line-height: 1;
  display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.qty-btn.plus {
  background: var(--accent); color: #0f172a;
  box-shadow: 0 4px 12px rgba(56,189,248,.3);
}
.qty-btn.plus:hover { transform: scale(1.12); background: var(--accent2); }
.qty-btn.minus {
  background: var(--surface2); color: var(--text);
  border: 1px solid var(--border);
}
.qty-btn.minus:disabled { opacity: .35; cursor: not-allowed; }
.qty-btn.minus:not(:disabled):hover { background: #1e3050; }
.qty-value {
  font-size: 16px; font-weight: 800; color: var(--text);
  min-width: 20px; text-align: center;
}

/* ─── Empty State ─ */
.empty-state {
  grid-column: 1/-1; text-align: center; padding: 64px 24px;
  color: var(--text-muted);
}
.empty-state span { font-size: 48px; display: block; margin-bottom: 12px; }
.empty-state p { font-size: 16px; }

/* ─── Floating Cart Button ──────────────────────── */
.cart-fab {
  position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
  z-index: 200; display: flex; align-items: center; gap: 12px;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  color: #0f172a; border: none; cursor: pointer;
  padding: 16px 28px; border-radius: 50px;
  box-shadow: 0 8px 40px rgba(56,189,248,.4);
  font-family: 'Cairo', sans-serif; font-weight: 800; font-size: 15px;
  transition: transform .2s, box-shadow .2s;
}
.cart-fab:hover { transform: translateX(-50%) translateY(-3px); box-shadow: 0 12px 50px rgba(56,189,248,.5); }
.fab-icon { font-size: 20px; }
.fab-badge {
  background: #0f172a; color: var(--accent);
  font-size: 12px; font-weight: 900;
  width: 24px; height: 24px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
.fab-price { font-size: 14px; }

/* ─── Modal ─────── */
.modal-overlay {
  position: fixed; inset: 0; z-index: 300;
  background: rgba(0,0,0,.75); backdrop-filter: blur(6px);
  display: flex; align-items: flex-end; justify-content: center;
  padding: 0;
}
.modal-sheet {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 28px 28px 0 0;
  width: 100%; max-width: 640px;
  max-height: 92vh;
  display: flex; flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
  background: var(--surface2);
}
.modal-title-group { display: flex; flex-direction: column; }
.modal-title { font-size: 20px; font-weight: 900; color: var(--text); margin: 0; font-family: 'Cairo', sans-serif; }
.modal-subtitle { font-size: 12px; color: var(--text-muted); }
.receipt-icon { font-size: 28px; }
.close-btn {
  width: 36px; height: 36px; border-radius: 50%;
  background: var(--surface); border: 1px solid var(--border);
  color: var(--text-muted); font-size: 14px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.close-btn:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

.modal-body { flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 24px; }

/* ─── Receipt ───── */
.receipt-section { display: flex; flex-direction: column; gap: 12px; }
.section-label {
  font-size: 11px; font-weight: 800; letter-spacing: 1.5px;
  color: var(--accent); text-transform: uppercase;
}
.receipt-items { display: flex; flex-direction: column; gap: 8px; }
.receipt-item {
  display: flex; align-items: center; justify-content: space-between;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 12px; padding: 12px 16px;
}
.ri-left { display: flex; align-items: center; gap: 12px; }
.ri-emoji { font-size: 24px; }
.ri-name { font-size: 14px; font-weight: 700; color: var(--text); }
.ri-qty { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.ri-total { font-size: 15px; font-weight: 800; color: var(--accent); }

.receipt-total {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 16px;
  background: linear-gradient(135deg, rgba(56,189,248,.08), rgba(14,165,233,.05));
  border: 1px solid rgba(56,189,248,.2);
  border-radius: 12px;
  font-weight: 700; font-size: 15px; color: var(--text);
}
.total-amount { font-size: 22px; font-weight: 900; color: var(--accent); font-family: 'Cairo', sans-serif; }
.total-amount em { font-size: 13px; font-style: normal; color: var(--text-muted); }

/* ─── Drivers ───── */
.drivers-list { display: flex; flex-direction: column; gap: 8px; }
.driver-card {
  display: flex; align-items: center; gap: 12px;
  background: var(--surface2); border: 1.5px solid var(--border);
  border-radius: 14px; padding: 12px 16px;
  cursor: pointer; transition: all .2s;
}
.driver-card:hover:not(.driver-unavailable) { border-color: var(--accent); }
.driver-selected { border-color: var(--accent) !important; background: rgba(56,189,248,.06) !important; }
.driver-unavailable { opacity: .45; cursor: not-allowed; }

.driver-avatar {
  width: 42px; height: 42px; border-radius: 50%;
  background: linear-gradient(135deg, #1e3050, #0d1626);
  border: 1.5px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 16px; color: var(--text);
}
.driver-selected .driver-avatar { border-color: var(--accent); background: rgba(56,189,248,.12); }
.driver-info { flex: 1; }
.driver-name { font-size: 14px; font-weight: 700; color: var(--text); display: block; }
.driver-meta { display: flex; gap: 10px; align-items: center; margin-top: 3px; }
.driver-rating { font-size: 12px; color: var(--gold); }
.driver-status { font-size: 11px; font-weight: 600; }
.available { color: var(--success); }
.busy      { color: var(--danger); }
.driver-check {
  width: 24px; height: 24px; border-radius: 50%;
  background: var(--accent); color: #0f172a;
  font-size: 13px; font-weight: 900;
  display: flex; align-items: center; justify-content: center;
}

/* ─── Payment Methods ───────────────────────────── */
.payment-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.pay-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 13px; border-radius: 12px;
  border: 1.5px solid var(--border); background: var(--surface2);
  color: var(--text-muted); font-family: 'Tajawal', sans-serif;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s;
}
.pay-btn:hover { border-color: var(--accent); color: var(--accent); }
.pay-active {
  border-color: var(--accent) !important;
  background: rgba(56,189,248,.1) !important;
  color: var(--accent) !important;
  box-shadow: 0 4px 20px rgba(56,189,248,.15);
}

/* ─── Modal Footer  */
.modal-footer {
  padding: 20px 24px;
  border-top: 1px solid var(--border);
  background: var(--surface2);
}
.order-summary-bar {
  display: flex; gap: 16px; justify-content: space-around;
  margin-bottom: 16px; flex-wrap: wrap;
}
.order-summary-bar > div { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.summary-label { font-size: 10px; color: var(--text-muted); }
.summary-amount { font-size: 18px; font-weight: 900; color: var(--accent); }
.summary-val { font-size: 13px; font-weight: 700; color: var(--text); }

.confirm-btn {
  width: 100%; padding: 16px; border-radius: 16px; border: none;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  color: #0f172a; font-family: 'Cairo', sans-serif;
  font-size: 16px; font-weight: 900; cursor: pointer;
  transition: opacity .2s, transform .2s;
  box-shadow: 0 6px 24px rgba(56,189,248,.3);
}
.confirm-btn:hover:not(:disabled) { transform: translateY(-2px); opacity: .92; }
.confirm-btn:disabled { opacity: .4; cursor: not-allowed; }
.confirm-hint { text-align: center; font-size: 12px; color: var(--danger); margin: 8px 0 0; }

/* ─── Spinner ───── */
.spinner {
  display: inline-block; width: 18px; height: 18px;
  border: 2px solid rgba(0,0,0,.2); border-top-color: #0f172a;
  border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Toast ─────── */
.toast {
  position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%);
  z-index: 500; display: flex; align-items: center; gap: 10px;
  padding: 14px 24px; border-radius: 40px;
  font-weight: 700; font-size: 15px;
  box-shadow: 0 8px 40px rgba(0,0,0,.4);
}
.success-toast {
  background: linear-gradient(135deg, #064e3b, #065f46);
  border: 1px solid #10b981; color: #6ee7b7;
}

/* ─── Transitions ─ */
.card-enter-active, .card-leave-active { transition: all .35s ease; }
.card-enter-from { opacity: 0; transform: scale(.9) translateY(20px); }
.card-leave-to   { opacity: 0; transform: scale(.9); }
.card-move       { transition: transform .35s; }

.bounce-enter-active { animation: bounceIn .4s ease; }
.bounce-leave-active { animation: bounceIn .3s reverse ease; }
@keyframes bounceIn {
  0%   { transform: translateX(-50%) scale(.6); opacity: 0; }
  60%  { transform: translateX(-50%) scale(1.05); opacity: 1; }
  100% { transform: translateX(-50%) scale(1); }
}

.modal-enter-active { transition: all .35s cubic-bezier(.16,1,.3,1); }
.modal-leave-active { transition: all .25s ease; }
.modal-enter-from .modal-sheet { transform: translateY(100%); }
.modal-leave-to   .modal-sheet { transform: translateY(100%); }
.modal-enter-from { opacity: 0; }
.modal-leave-to   { opacity: 0; }

.toast-enter-active { transition: all .4s cubic-bezier(.16,1,.3,1); }
.toast-leave-active { transition: all .25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(20px); }

/* ─── Scrollbar ─── */
.modal-body::-webkit-scrollbar { width: 4px; }
.modal-body::-webkit-scrollbar-track { background: transparent; }
.modal-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

/* ─── Responsive ── */
@media (max-width: 640px) {
  .main-content { padding: 20px 14px 120px; }
  .controls-bar { flex-direction: column; align-items: flex-start; }
  .grid-inner { grid-template-columns: 1fr; }
  .cart-fab { width: calc(100% - 32px); left: 16px; transform: none; }
  .cart-fab:hover { transform: translateY(-3px); }
  .bounce-enter-from, .bounce-leave-to { transform: scale(.6); }
}
</style>
