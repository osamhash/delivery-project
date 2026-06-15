<template>
  <div :class="styles.card" @click="$emit('click')">
    <div :class="styles.header">
      <div>
        <div :class="styles.storeName">{{ order.storeName }}</div>
        <div :class="styles.orderId">رقم الطلب: {{ order.id }}</div>
      </div>
      <StatusBadge :status="order.status" />
    </div>
    <div :class="styles.items">
      <span v-for="item in order.items.slice(0, 2)" :key="item.productId" :class="styles.item">
        {{ item.name }} × {{ item.quantity }}
      </span>
      <span v-if="order.items.length > 2" :class="styles.more">
        +{{ order.items.length - 2 }} منتج آخر
      </span>
    </div>
    <div :class="styles.footer">
      <span :class="styles.date">{{ formatDate(order.createdAt) }}</span>
      <span :class="styles.total">{{ order.total }} ₪</span>
    </div>
  </div>
</template>

<script setup>
import { useCssModule } from 'vue'
import StatusBadge from '../shared/StatusBadge.vue'

defineProps({ order: { type: Object, required: true } })
defineEmits(['click'])
const styles = useCssModule()

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('ar-PS', {
    year: 'numeric', month: 'short', day: 'numeric',
  })
}
</script>

<style module>
.card {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 18px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition: box-shadow var(--transition), transform var(--transition);
}

.card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.storeName {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 3px;
}

.orderId {
  font-size: 12px;
  color: var(--text-muted);
}

.items {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 14px;
}

.item {
  background: var(--bg-page);
  border-radius: var(--radius-full);
  padding: 4px 10px;
  font-size: 12px;
  color: var(--text-muted);
}

.more {
  background: var(--brand-light);
  color: var(--brand);
  border-radius: var(--radius-full);
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
}

.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

.date {
  font-size: 12px;
  color: var(--text-muted);
}

.total {
  font-size: 16px;
  font-weight: 700;
  color: var(--brand);
}
</style>
