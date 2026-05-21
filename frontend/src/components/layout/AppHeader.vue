<template>
  <header :class="styles.header">
    <div :class="styles.right">
      <button :class="styles.toggleBtn" @click="$emit('toggleSidebar')" :title="'طيّ القائمة'">
        <Icon :icon="isCollapsed ? 'mdi:menu-open' : 'mdi:menu'" width="22" height="22" />
      </button>
      <div :class="styles.breadcrumb">
        <span :class="styles.pageTitle">{{ pageTitle }}</span>
      </div>
    </div>

    <div :class="styles.left">
      <div :class="styles.greeting">
        أهلاً، <strong>{{ firstName }}</strong> 👋
      </div>
      <div :class="styles.avatar">{{ userInitial }}</div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useCssModule } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '../../stores/auth'

defineProps({ isCollapsed: Boolean })
defineEmits(['toggleSidebar'])

const styles = useCssModule()
const route = useRoute()
const authStore = useAuthStore()

const PAGE_TITLES = {
  'customer-dashboard': 'لوحة التحكم',
  'customer-stores': 'استعراض المحلات',
  'store-detail': 'تفاصيل المحل',
  'customer-cart': 'سلة المشتريات',
  'customer-checkout': 'إتمام الطلب',
  'customer-orders': 'طلباتي',
  'order-tracking': 'تتبع الطلب',
  'customer-profile': 'ملفي الشخصي',
  'customer-addresses': 'عناويني',
  'provider-dashboard': 'لوحة التحكم',
  'provider-orders': 'إدارة الطلبات',
  'provider-menu': 'إدارة المنتجات',
  'provider-earnings': 'الأرباح',
  'driver-dashboard': 'لوحة التحكم',
  'driver-deliveries': 'التوصيلات',
  'delivery-detail': 'تفاصيل التوصيلة',
  'driver-earnings': 'الأرباح',
}

const pageTitle = computed(() => PAGE_TITLES[route.name] || 'Delivro')
const firstName = computed(() => authStore.user?.name?.split(' ')[0] || '')
const userInitial = computed(() => (authStore.user?.name || 'م')[0])
</script>

<style module>
.header {
  height: var(--header-height);
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  position: sticky;
  top: 0;
  z-index: 50;
}

.right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.toggleBtn {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: background-color var(--transition), color var(--transition);
}

.toggleBtn:hover {
  background-color: var(--bg-page);
  color: var(--text-primary);
}

.pageTitle {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

.left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.greeting {
  font-size: 14px;
  color: var(--text-muted);
}

.greeting strong {
  color: var(--text-primary);
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-full);
  background: var(--brand);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
}
</style>
