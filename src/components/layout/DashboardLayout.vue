<template>
  <div :class="styles.layout">
    <AppSidebar :isCollapsed="isCollapsed" />
    <div :class="[styles.panel, isCollapsed && styles.panelCollapsed]">
      <AppHeader @toggleSidebar="isCollapsed = !isCollapsed" :isCollapsed="isCollapsed" />
      <main :class="styles.main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterView } from 'vue-router'
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import { useCssModule } from 'vue'

const styles = useCssModule()
const isCollapsed = ref(false)
</script>

<style module>
.layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--bg-page);
}

.panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-right: var(--sidebar-width);
  transition: margin-right var(--transition);
  min-width: 0;
}

.panelCollapsed {
  margin-right: var(--sidebar-collapsed);
}

.main {
  flex: 1;
  padding: 28px;
  overflow-y: auto;
}
</style>
