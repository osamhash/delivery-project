<template>
  <!-- الهيكل العام لصفحة لوحة التحكم -->
  <div class="dashboard-shell" :class="{ dark: isDark }">
    <!-- الشريط الجانبي يحتوي على الشعار والمعلومات الشخصية وروابط التنقل -->
    <aside class="dashboard-sidebar">
      <div class="brand-block">
        <div class="logo-mark">Delivro</div>
        <p class="brand-subtitle">لوحة تحكم احترافية</p>
      </div>

      <!-- بطاقة ملف المستخدم والدور الحالي -->
      <div class="profile-card">
        <p class="profile-role">الدور</p>
        <h3>{{ roleLabel }}</h3>
        <p class="profile-name">{{ userName }}</p>
      </div>

      <!-- روابط التنقل الجانبي (تجميلية حتى الآن) -->
      <nav class="sidebar-nav">
        <button class="sidebar-link active">الرئيسية</button>
        <button class="sidebar-link">الإشعارات</button>
        <button class="sidebar-link">التقارير</button>
        <button class="sidebar-link">الإعدادات</button>
      </nav>
    </aside>

    <!-- المحتوى الرئيسي للوحة التحكم -->
    <main class="dashboard-main">
      <!-- رأس الصفحة يحتوي على الترحيب وأزرار التحكم -->
      <header class="main-header">
        <div>
          <p class="greeting">مرحباً {{ userName }}،</p>
          <h1>{{ headerTitle }}</h1>
        </div>
        <div class="header-actions">
          <button class="btn-outline" @click="toggleTheme">{{ themeLabel }}</button>
          <button class="btn-outline" @click="refreshData">تحديث</button>
        </div>
      </header>

      <!-- شبكة البطاقات الإحصائية -->
      <section class="stats-grid">
        <article class="stat-card" v-for="card in stats" :key="card.title">
          <p class="stat-label">{{ card.title }}</p>
          <h2>{{ card.value }}</h2>
          <p class="stat-note">{{ card.note }}</p>
        </article>
      </section>

      <!-- القسم الرئيسي للمحتوى مع اللوحة والأرقام السريعة -->
      <section class="content-grid">
        <div class="panel">
          <div class="panel-header">
            <h2>{{ panelTitle }}</h2>
            <span class="panel-badge">{{ panelBadge }}</span>
          </div>
          <ul class="overview-list">
            <li v-for="item in panelItems" :key="item.title">
              <div>
                <p class="item-title">{{ item.title }}</p>
                <p class="item-value">{{ item.value }}</p>
              </div>
              <span class="item-tag">{{ item.tag }}</span>
            </li>
          </ul>
        </div>

        <div class="panel chart-panel">
          <div class="panel-header">
            <h2>ملخص سريع</h2>
          </div>
          <div class="chart-grid">
            <div class="mini-chart" v-for="metric in chartMetrics" :key="metric.title">
              <p class="metric-title">{{ metric.title }}</p>
              <h3>{{ metric.value }}</h3>
              <div class="metric-bar">
                <span :style="{ width: metric.progress + '%' }"></span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
// استيراد الأدوات اللازمة من Vue
import { computed, ref } from 'vue'
import '../assets/styles/dashboard.css'

// قراءة الدور والبريد من التخزين المحلي
const role = ref(localStorage.getItem('delivro_role') || 'customer')
const email = ref(localStorage.getItem('delivro_email') || '')

// استخراج اسم المستخدم من البريد الإلكتروني إذا كان موجوداً
const userName = computed(() => {
  if (!email.value) return 'صديقنا'
  return email.value.split('@')[0]
})

// تحويل قيمة الدور المخزنة إلى اسم عربي يعرض في الواجهة
const roleLabel = computed(() => {
  return {
    customer: 'عميل',
    driver: 'سائق',
    marketer: 'مركز تسويق',
  }[role.value] || 'عميل'
})

// تحديد عنوان الصفحة بناءً على الدور
const headerTitle = computed(() => {
  return {
    customer: 'لوحة تحكم العميل',
    driver: 'لوحة تحكم السائق',
    marketer: 'لوحة تحكم مركز التسويق',
  }[role.value] || 'لوحة تحكم العميل'
})

// حالة الثيم الداكن مخزنة محلياً
const isDark = ref(localStorage.getItem('delivro_theme') === 'dark')

// نص الزر الخاص بتبديل الثيم
const themeLabel = computed(() => isDark.value ? 'الوضع الفاتح' : 'الوضع الداكن')

// دالة تبديل الثيم وتحديث التخزين المحلي
const toggleTheme = () => {
  isDark.value = !isDark.value
  localStorage.setItem('delivro_theme', isDark.value ? 'dark' : 'light')
}

// بيانات العرض لكل دور في اللوحة
const dashboardConfig = {
  customer: {
    stats: [
      { title: 'الطلبات النشطة', value: '8', note: 'طلبات قيد التنفيذ اليوم' },
      { title: 'المتاجر المفضلة', value: '12', note: 'محلات محفوظة للشراء السريع' },
      { title: 'التوفير الشهري', value: '٢٣٠ ر.س', note: 'مقارنة بالشهر الماضي' },
    ],
    panelTitle: 'أهم نشاطاتك',
    panelBadge: 'معلومات عامة',
    panelItems: [
      { title: 'طلب جديد', value: 'افتح المتاجر الآن', tag: 'متاح' },
      { title: 'عروض خاصة', value: '5 عروض جديدة', tag: 'مهم' },
      { title: 'سجل الطلبات', value: 'راقب حالة طلبك', tag: 'نشط' },
    ],
    chartMetrics: [
      { title: 'رضا العملاء', value: '97%', progress: 97 },
      { title: 'الإنفاق المتوقع', value: '١٥٠ ر.س', progress: 65 },
      { title: 'الوقت المتوقع', value: '25 دقيقة', progress: 50 },
    ],
  },
  driver: {
    stats: [
      { title: 'التسليمات اليوم', value: '14', note: 'طلبات تم استلامها' },
      { title: 'الإيرادات', value: '١٬٧٢٠ ر.س', note: 'إجمالي اليوم' },
      { title: 'التقييم', value: '4.9/5', note: 'من العملاء خلال الأسبوع' },
    ],
    panelTitle: 'مهام السائق',
    panelBadge: 'الأولوية العالية',
    panelItems: [
      { title: 'طلبات جارية', value: '3 طلبات', tag: 'مباشر' },
      { title: 'وقت التسليم', value: '22 دقيقة', tag: 'متوسط' },
      { title: 'المسافة المتبقية', value: '5 كم', tag: 'قريب' },
    ],
    chartMetrics: [
      { title: 'رضا العملاء', value: '92%', progress: 92 },
      { title: 'استجابة الطلب', value: '8 دقائق', progress: 40 },
      { title: 'الرحلات المكتملة', value: '32 رحلة', progress: 80 },
    ],
  },
  marketer: {
    stats: [
      { title: 'الحملات النشطة', value: '6', note: 'حملات ضمن المنصة' },
      { title: 'العملاء المحتملين', value: '420', note: 'تم استهدافهم هذا الأسبوع' },
      { title: 'نسبة التحويل', value: '18%', note: 'تحسن شهري' },
    ],
    panelTitle: 'تركيز التسويق',
    panelBadge: 'حالة الحملة',
    panelItems: [
      { title: 'حملة جديدة', value: 'ابدأ حملة ترويجية', tag: 'متاح' },
      { title: 'تقارير الأداء', value: 'راجع النتائج الآن', tag: 'مهم' },
      { title: 'شركاء جدد', value: '3 متاجر', tag: 'جديد' },
    ],
    chartMetrics: [
      { title: 'نمو الجمهور', value: '12%', progress: 72 },
      { title: 'تكلفة النقرة', value: '1.7 ر.س', progress: 55 },
      { title: 'معدل الاكتمال', value: '89%', progress: 89 },
    ],
  },
}

// اختيار التصنيف المناسب بناءً على الدور الحالي
const config = ref(dashboardConfig[role.value] || dashboardConfig.customer)

// إعادة تحميل البيانات عند الضغط على زر التحديث
const refreshData = () => {
  config.value = dashboardConfig[role.value] || dashboardConfig.customer
}

// الحقول المستخدمة في العرض داخل الـ template
const stats = computed(() => config.value.stats)
const panelTitle = computed(() => config.value.panelTitle)
const panelBadge = computed(() => config.value.panelBadge)
const panelItems = computed(() => config.value.panelItems)
const chartMetrics = computed(() => config.value.chartMetrics)
</script>