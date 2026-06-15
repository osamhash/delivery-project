// src/services/api.js
import axios from 'axios'

const api = axios.create({
    // baseURL: 'http://127.0.0.1:8000/api',
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
})

// إضافة التوكن تلقائياً
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('userRole')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api