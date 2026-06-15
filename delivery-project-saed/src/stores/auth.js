import { defineStore } from 'pinia'
import { storage, KEYS } from '../services/storage'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    currentUser: null,
    isAuthenticated: false,
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated,
    user: (state) => state.currentUser,
    userRole: (state) => state.currentUser?.role || null,
  },

  actions: {
    initAuth() {
      const user = storage.get(KEYS.CURRENT_USER)
      if (user) {
        this.currentUser = user
        this.isAuthenticated = true
      }
    },

    async login(email, password, role) {
      const users = storage.get(KEYS.USERS) || []
      const user = users.find(u => u.email === email && u.password === password && u.role === role)

      if (user) {
        this.currentUser = user
        this.isAuthenticated = true
        storage.set(KEYS.CURRENT_USER, user)
        return { success: true }
      } else {
        return { success: false, message: 'بيانات الدخول غير صحيحة' }
      }
    },

    logout() {
      this.currentUser = null
      this.isAuthenticated = false
      storage.remove(KEYS.CURRENT_USER)
    },
  },
})