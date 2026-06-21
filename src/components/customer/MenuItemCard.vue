<template>
  <div :class="[styles.card, !item.inStock && styles.outOfStock]">
    <div :class="styles.body">
      <div :class="styles.category">{{ item.category }}</div>
      <h4 :class="styles.name">{{ item.name }}</h4>
      <p :class="styles.description">{{ item.description }}</p>
    </div>
    <div :class="styles.footer">
      <div :class="styles.price">{{ item.price }} ₪</div>
      <div v-if="!item.inStock" :class="styles.outTag">نفد المخزون</div>
      <div v-else-if="quantity === 0" >
        <button :class="styles.addBtn" @click="$emit('add', item)">
          <Icon icon="mdi:plus" width="18" height="18" />
          أضف
        </button>
      </div>
      <div v-else :class="styles.qtyControl">
        <button :class="styles.qtyBtn" @click="$emit('decrease', item)">
          <Icon icon="mdi:minus" width="16" height="16" />
        </button>
        <span :class="styles.qty">{{ quantity }}</span>
        <button :class="styles.qtyBtn" @click="$emit('add', item)">
          <Icon icon="mdi:plus" width="16" height="16" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCssModule } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
  item:     { type: Object, required: true },
  quantity: { type: Number, default: 0 },
})
defineEmits(['add', 'decrease'])
const styles = useCssModule()
</script>

<style module>
.card {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 16px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: box-shadow var(--transition);
}

.card:hover {
  box-shadow: var(--shadow-md);
}

.outOfStock {
  opacity: 0.6;
  pointer-events: none;
}

.body {
  flex: 1;
}

.category {
  font-size: 11px;
  color: var(--brand);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.description {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.5;
}

.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.price {
  font-size: 18px;
  font-weight: 700;
  color: var(--brand);
}

.outTag {
  font-size: 12px;
  color: var(--text-muted);
  background: var(--bg-page);
  padding: 5px 12px;
  border-radius: var(--radius-full);
}

.addBtn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--brand);
  color: #fff;
  border-radius: var(--radius-md);
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  transition: background var(--transition);
}

.addBtn:hover {
  background: var(--brand-dark);
}

.qtyControl {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--brand-light);
  border-radius: var(--radius-md);
  padding: 4px 8px;
}

.qtyBtn {
  color: var(--brand);
  display: flex;
  align-items: center;
  transition: opacity var(--transition);
}

.qtyBtn:hover {
  opacity: 0.7;
}

.qty {
  font-size: 15px;
  font-weight: 700;
  color: var(--brand);
  min-width: 20px;
  text-align: center;
}
</style>
