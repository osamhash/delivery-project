<template>
  <span :class="[styles.badge, styles[colorClass]]">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { useCssModule } from 'vue'

const props = defineProps({ status: { type: String, required: true } })
const styles = useCssModule()

const STATUS_MAP = {
  pending:    { label: 'بانتظار التأكيد', color: 'warning' },
  confirmed:  { label: 'تم التأكيد',      color: 'info' },
  preparing:  { label: 'قيد التحضير',     color: 'warning' },
  ready:      { label: 'جاهز للتوصيل',    color: 'purple' },
  on_the_way: { label: 'في الطريق',       color: 'info' },
  delivered:  { label: 'تم التوصيل',      color: 'success' },
  cancelled:  { label: 'ملغي',            color: 'danger' },
}

const label    = computed(() => STATUS_MAP[props.status]?.label || props.status)
const colorClass = computed(() => STATUS_MAP[props.status]?.color || 'warning')
</script>

<style module>
.badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.success { background: var(--success-light); color: #15803D; }
.warning { background: var(--warning-light); color: #B45309; }
.danger  { background: var(--danger-light);  color: #B91C1C; }
.info    { background: var(--info-light);    color: #1D4ED8; }
.purple  { background: var(--purple-light);  color: #6D28D9; }
</style>
