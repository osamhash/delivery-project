<template>
  <div :class="styles.card" @click="$emit('click')">
    <div :class="styles.cover" :style="{ background: categoryStyle.gradient }">
      <span :class="styles.coverEmoji">{{ categoryStyle.icon }}</span>
      <span v-if="!store.isOpen" :class="styles.closedOverlay">مغلق</span>
    </div>
    <div :class="styles.body">
      <div :class="styles.topRow">
        <h3 :class="styles.name">{{ store.name }}</h3>
        <span :class="styles.categoryTag">{{ store.categoryLabel }}</span>
      </div>
      <div :class="styles.meta">
        <span :class="styles.metaItem">
          <Icon icon="mdi:star" width="14" height="14" :class="styles.star" />
          {{ store.rating }}
          <span :class="styles.reviewCount">({{ store.reviewCount }})</span>
        </span>
        <span :class="styles.dot">·</span>
        <span :class="styles.metaItem">
          <Icon icon="mdi:clock-outline" width="14" height="14" />
          {{ store.deliveryTime }}
        </span>
      </div>
      <div :class="styles.footer">
        <span :class="styles.delivery">
          <Icon icon="mdi:bike-fast" width="14" height="14" />
          رسوم التوصيل: {{ store.deliveryFee }} ₪
        </span>
        <span :class="styles.minOrder">الحد الأدنى: {{ store.minOrder }} ₪</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCssModule } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({ store: { type: Object, required: true } })
defineEmits(['click'])
const styles = useCssModule()

const CATEGORY_STYLES = {
  pharmacy:    { gradient: 'linear-gradient(135deg, #667eea, #764ba2)', icon: '💊' },
  supermarket: { gradient: 'linear-gradient(135deg, #11998e, #38ef7d)', icon: '🛒' },
  electronics: { gradient: 'linear-gradient(135deg, #f093fb, #f5576c)', icon: '💻' },
  fashion:     { gradient: 'linear-gradient(135deg, #4facfe, #00f2fe)', icon: '👗' },
  default:     { gradient: 'linear-gradient(135deg, #fa709a, #fee140)', icon: '🏪' },
}

const categoryStyle = computed(
  () => CATEGORY_STYLES[props.store.category] || CATEGORY_STYLES.default,
)
</script>

<style module>
.card {
  background: var(--surface);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  cursor: pointer;
  transition: transform var(--transition), box-shadow var(--transition);
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.cover {
  height: 130px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.coverEmoji {
  font-size: 52px;
}

.closedOverlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  font-weight: 700;
}

.body {
  padding: 16px;
}

.topRow {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
}

.name {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
}

.categoryTag {
  background: var(--brand-light);
  color: var(--brand);
  border-radius: var(--radius-full);
  padding: 3px 10px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.meta {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--text-muted);
  font-size: 13px;
  margin-bottom: 12px;
}

.metaItem {
  display: flex;
  align-items: center;
  gap: 4px;
}

.star {
  color: #FBBF24;
}

.reviewCount {
  color: var(--text-muted);
  font-size: 11px;
}

.dot {
  color: var(--border);
}

.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

.delivery {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-muted);
}

.minOrder {
  font-size: 12px;
  color: var(--text-muted);
}
</style>
