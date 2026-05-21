<template>
  <button
    :class="[styles.btn, styles[variant], styles[size], loading && styles.loading, fullWidth && styles.fullWidth]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <Icon v-if="loading" icon="mdi:loading" :class="styles.spinner" width="16" height="16" />
    <Icon v-else-if="icon" :icon="icon" width="16" height="16" />
    <slot />
  </button>
</template>

<script setup>
import { useCssModule } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
  variant:   { type: String, default: 'primary' },
  size:      { type: String, default: 'md' },
  icon:      { type: String, default: '' },
  loading:   { type: Boolean, default: false },
  disabled:  { type: Boolean, default: false },
  fullWidth: { type: Boolean, default: false },
})

const styles = useCssModule()
</script>

<style module>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 14px;
  transition: all var(--transition);
  cursor: pointer;
  border: none;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Variants */
.primary {
  background: var(--brand);
  color: #fff;
}
.primary:hover:not(:disabled) {
  background: var(--brand-dark);
}

.secondary {
  background: var(--bg-page);
  color: var(--text-primary);
  border: 1px solid var(--border);
}
.secondary:hover:not(:disabled) {
  background: var(--border);
}

.ghost {
  background: transparent;
  color: var(--text-muted);
}
.ghost:hover:not(:disabled) {
  background: var(--bg-page);
  color: var(--text-primary);
}

.danger {
  background: var(--danger-light);
  color: #B91C1C;
}
.danger:hover:not(:disabled) {
  background: var(--danger);
  color: #fff;
}

.success {
  background: var(--success-light);
  color: #15803D;
}
.success:hover:not(:disabled) {
  background: var(--success);
  color: #fff;
}

/* Sizes */
.sm { padding: 7px 14px; font-size: 13px; }
.md { padding: 10px 20px; }
.lg { padding: 13px 28px; font-size: 16px; }

.fullWidth { width: 100%; }

.loading { pointer-events: none; }

.spinner {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
