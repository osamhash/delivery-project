export const KEYS = {
  USERS: 'delivro_users',
  CURRENT_USER: 'delivro_current_user',
  STORES: 'delivro_stores',
  PRODUCTS: 'delivro_products',
  ORDERS: 'delivro_orders',
  CART: 'delivro_cart',
  ADDRESSES: 'delivro_addresses',
  SEEDED: 'delivro_seeded',
}

export const storage = {
  get(key) {
    try {
      const item = localStorage.getItem(key)
      return item ? JSON.parse(item) : null
    } catch {
      return null
    }
  },
  set(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value))
    } catch (e) {
      console.error('Storage error:', e)
    }
  },
  remove(key) {
    localStorage.removeItem(key)
  },
  clear() {
    Object.values(KEYS).forEach((key) => localStorage.removeItem(key))
  },
}
