<template>
  <aside :class="[styles.sidebar, isCollapsed && styles.collapsed]">
    <div :class="styles.logo">
      <span :class="styles.logoIcon">🛵</span>
      <span v-if="!isCollapsed" :class="styles.logoText">Delivro</span>
    </div>

    <nav :class="styles.nav">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        :class="[styles.navLink, isActive(item.to) && styles.navLinkActive]"
        :title="isCollapsed ? item.label : ''"
      >
        <Icon :icon="item.icon" :class="styles.navIcon" width="20" height="20" />
        <span v-if="!isCollapsed" :class="styles.navLabel">{{ item.label }}</span>
        <span v-if="!isCollapsed && item.badge" :class="styles.badge">{{ item.badge }}</span>
      </RouterLink>
    </nav>

    <div :class="styles.bottom">
      <div :class="styles.userInfo" v-if="!isCollapsed">
        <div :class="styles.avatar">{{ userInitial }}</div>
        <div :class="styles.userDetails">
          <div :class="styles.userName">{{ authStore.user?.name }}</div>
          <div :class="styles.userRole">{{ roleLabel }}</div>
        </div>
      </div>
      <button :class="styles.logoutBtn" @click="logout" :title="'تسجيل الخروج'">
        <Icon icon="mdi:logout" width="20" height="20" />
        <span v-if="!isCollapsed">تسجيل الخروج</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useCssModule } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'

const props = defineProps({ isCollapsed: Boolean })
const styles = useCssModule()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const userInitial = computed(() => (authStore.user?.name || 'م')[0])

const roleLabel = computed(() => {
  const map = { customer: 'عميل', provider: 'مزود', driver: 'سائق' }
  return map[authStore.user?.role] || ''
})

const navItems = computed(() => {
  const role = authStore.user?.role
  if (role === 'customer') {
    return [
      { label: 'لوحة التحكم', icon: 'mdi:view-dashboard-outline', to: '/customer/dashboard' },
      { label: 'المحلات', icon: 'mdi:store-outline', to: '/customer/stores' },
      { label: 'السلة', icon: 'mdi:cart-outline', to: '/customer/cart', badge: cartStore.itemCount || null },
      { label: 'طلباتي', icon: 'mdi:clipboard-list-outline', to: '/customer/orders' },
      { label: 'ملفي الشخصي', icon: 'mdi:account-outline', to: '/customer/profile' },
      { label: 'عناويني', icon: 'mdi:map-marker-outline', to: '/customer/addresses' },
    ]
  }
  if (role === 'provider') {
    return [
      { label: 'لوحة التحكم', icon: 'mdi:view-dashboard-outline', to: '/provider/dashboard' },
      { label: 'الطلبات الواردة', icon: 'mdi:clipboard-check-outline', to: '/provider/orders' },
      { label: 'إدارة المنتجات', icon: 'mdi:package-variant-outline', to: '/provider/menu' },
      { label: 'الأرباح', icon: 'mdi:cash-multiple', to: '/provider/earnings' },
    ]
  }
  if (role === 'driver') {
    return [
      { label: 'لوحة التحكم', icon: 'mdi:view-dashboard-outline', to: '/driver/dashboard' },
      { label: 'التوصيلات', icon: 'mdi:truck-delivery-outline', to: '/driver/deliveries' },
      { label: 'الأرباح', icon: 'mdi:cash-multiple', to: '/driver/earnings' },
    ]
  }
  return []
})

const isActive = (to) => route.path === to || route.path.startsWith(to + '/')

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style module>
.sidebar {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: var(--sidebar-width);
  background-color: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  transition: width var(--transition);
  z-index: 100;
  overflow: hidden;
}

.collapsed {
  width: var(--sidebar-collapsed);
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 20px 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  margin-bottom: 12px;
}

.logoIcon {
  font-size: 22px;
  flex-shrink: 0;
}

.logoText {
  font-size: 20px;
  font-weight: 700;
  color: var(--brand);
  white-space: nowrap;
}

.nav {
  flex: 1;
  padding: 0 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto;
}

.navLink {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 12px;
  border-radius: var(--radius-md);
  color: var(--sidebar-text);
  transition: background-color var(--transition), color var(--transition);
  white-space: nowrap;
  position: relative;
}

.navLink:hover {
  background-color: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.navLinkActive {
  background-color: var(--sidebar-active);
  color: var(--brand);
}

.navIcon {
  flex-shrink: 0;
}

.navLabel {
  font-size: 14px;
  font-weight: 500;
}

.badge {
  margin-right: auto;
  background: var(--brand);
  color: #fff;
  border-radius: var(--radius-full);
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  min-width: 20px;
  text-align: center;
}

.bottom {
  padding: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.userInfo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 4px;
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
  flex-shrink: 0;
}

.userDetails {
  min-width: 0;
}

.userName {
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.userRole {
  color: var(--sidebar-text);
  font-size: 11px;
}

.logoutBtn {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 10px 12px;
  border-radius: var(--radius-md);
  color: var(--sidebar-text);
  font-size: 14px;
  transition: background-color var(--transition), color var(--transition);
  white-space: nowrap;
}

.logoutBtn:hover {
  background-color: var(--danger-light);
  color: var(--danger);
}
</style>
