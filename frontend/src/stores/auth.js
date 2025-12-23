import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const isAuthenticated = computed(() => !!token.value)
    const isAdmin = computed(() => user.value?.role === 'admin')

    function initAuth() {
        const savedToken = localStorage.getItem('token')
        const savedUser = localStorage.getItem('user')

        if (savedToken && savedUser) {
            token.value = savedToken
            user.value = JSON.parse(savedUser)
        }
    }

    async function register(name, email, password) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post('/auth/register', { name, email, password })
            token.value = response.data.token
            user.value = response.data.user

            localStorage.setItem('token', token.value)
            localStorage.setItem('user', JSON.stringify(user.value))

            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Registration failed'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function login(email, password) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post('/auth/login', { email, password })
            token.value = response.data.token
            user.value = response.data.user

            localStorage.setItem('token', token.value)
            localStorage.setItem('user', JSON.stringify(user.value))

            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Login failed'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function fetchUser() {
        if (!token.value) return

        try {
            const response = await api.get('/auth/me')
            user.value = response.data.user
            localStorage.setItem('user', JSON.stringify(user.value))
        } catch (err) {
            logout()
        }
    }

    function logout() {
        user.value = null
        token.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        isAdmin,
        initAuth,
        register,
        login,
        fetchUser,
        logout
    }
})
