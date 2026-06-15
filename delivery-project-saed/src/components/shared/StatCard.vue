<template>
  <div :class="styles.card">
    <div :class="[styles.iconWrap, styles[color]]">
      <Icon :icon="icon" width="22" height="22" />
    </div>
    <div :class="styles.body">
      <div :class="styles.value">{{ value }}</div>
      <div :class="styles.label">{{ label }}</div>
    </div>
    <div v-if="trend !== undefined" :class="[styles.trend, trend >= 0 ? styles.trendUp : styles.trendDown]">
      <Icon :icon="trend >= 0 ? 'mdi:trending-up' : 'mdi:trending-down'" width="16" height="16" />
      {{ Math.abs(trend) }}%
    </div>
  </div>
</template>

<script setup>
import { useCssModule } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
  label:  { type: String, required: true },
  value:  { type: [String, Number], required: true },
  icon:   { type: String, required: true },
  color:  { type: String, default: 'brand' },
  trend:  { type: Number, default: undefined },
})

const styles = useCssModule()
</script>

<style module>
.card {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  transition: box-shadow var(--transition);
}

.card:hover {
  box-shadow: var(--shadow-md);
}

.iconWrap {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand   { background: var(--brand-light);   color: var(--brand); }
.success { background: var(--success-light); color: var(--success); }
.warning { background: var(--warning-light); color: #B45309; }
.info    { background: var(--info-light);    color: var(--info); }
.purple  { background: var(--purple-light);  color: var(--purple); }

.body {
  flex: 1;
  min-width: 0;
}

.value {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

.label {
  font-size: 13px;
  color: var(--text-muted);
  margin-top: 2px;
}

.trend {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: var(--radius-full);
}

.trendUp   { background: var(--success-light); color: #15803D; }
.trendDown { background: var(--danger-light);  color: #B91C1C; }
</style>
