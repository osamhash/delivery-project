<template>
  <div class="profile">
    <img :src="user.image_path" class="avatar"/>

    <h2>{{ user.first_name }} {{ user.last_name }}</h2>

    <p>📞 {{ user.phone }}</p>
    <p>📍 {{ user.address }}</p>

    <div class="spending">
      هذا الشهر: {{ monthly }}
      <span :class="diffClass">{{ diffIcon }}</span>
    </div>
  </div>
</template>

<script>

import api from '../services/api'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const user = ref({})
const monthly = ref(0)
const lastMonth = ref(0)

const diffIcon = computed(() => {
  if (monthly.value > lastMonth.value) return '+'
  if (monthly.value < lastMonth.value) return '-'
  return '='
})

const diffClass = computed(() => ({
  up: monthly.value > lastMonth.value,
  down: monthly.value < lastMonth.value
}))
</script>