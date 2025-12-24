import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])
    const loading = ref(false)
    const error = ref(null)

    const itemCount = computed(() => {
        return items.value.reduce((total, item) => total + item.quantity, 0)
    })

    const totalPrice = computed(() => {
        return items.value.reduce((total, item) => {
            // Use variant price if available, otherwise use product price
            let price = item.product?.price || 0
            if (item.variant && typeof item.variant === 'object' && item.variant.price) {
                price = item.variant.price
            }
            return total + (price * item.quantity)
        }, 0)
    })

    // Load cart from localStorage for guests
    function loadLocalCart() {
        try {
            const saved = localStorage.getItem('matchaking_cart')
            if (saved) {
                const parsed = JSON.parse(saved)
                if (Array.isArray(parsed)) {
                    items.value = parsed
                }
            }
        } catch (e) {
            console.error('Failed to load cart from localStorage:', e)
            localStorage.removeItem('matchaking_cart')
        }
    }

    function saveLocalCart() {
        try {
            localStorage.setItem('matchaking_cart', JSON.stringify(items.value))
        } catch (e) {
            console.error('Failed to save cart to localStorage:', e)
        }
    }

    async function fetchCart() {
        const authStore = useAuthStore()

        if (!authStore.isAuthenticated) {
            loadLocalCart()
            return
        }

        loading.value = true
        error.value = null

        try {
            const response = await api.get('/cart')
            items.value = response.data.cart.items
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to fetch cart'
            loadLocalCart()
        } finally {
            loading.value = false
        }
    }

    async function addItem(product, quantity = 1, variant = null) {
        const authStore = useAuthStore()

        // Get variant name for comparison
        const variantName = variant && typeof variant === 'object' ? variant.name : variant

        if (!authStore.isAuthenticated) {
            // Handle local cart for guests
            const existingIndex = items.value.findIndex(item => {
                const itemVariantName = item.variant && typeof item.variant === 'object'
                    ? item.variant.name
                    : item.variant
                return item.productId === product.id && itemVariantName === variantName
            })

            if (existingIndex !== -1) {
                items.value[existingIndex].quantity += quantity
            } else {
                items.value.push({
                    id: Date.now().toString(),
                    productId: product.id,
                    quantity,
                    variant,
                    product
                })
            }
            saveLocalCart()
            return { success: true }
        }

        loading.value = true
        error.value = null

        try {
            await api.post('/cart/items', {
                productId: product.id,
                quantity,
                variant
            })
            await fetchCart()
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to add item'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function updateItem(itemId, quantity) {
        const authStore = useAuthStore()

        if (!authStore.isAuthenticated) {
            const item = items.value.find(i => i.id === itemId)
            if (item) {
                item.quantity = quantity
                saveLocalCart()
            }
            return { success: true }
        }

        loading.value = true
        error.value = null

        try {
            await api.put(`/cart/items/${itemId}`, { quantity })
            await fetchCart()
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to update item'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function removeItem(itemId) {
        const authStore = useAuthStore()

        if (!authStore.isAuthenticated) {
            items.value = items.value.filter(i => i.id !== itemId)
            saveLocalCart()
            return { success: true }
        }

        loading.value = true
        error.value = null

        try {
            await api.delete(`/cart/items/${itemId}`)
            await fetchCart()
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to remove item'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function clearCart() {
        const authStore = useAuthStore()

        if (!authStore.isAuthenticated) {
            items.value = []
            saveLocalCart()
            return { success: true }
        }

        loading.value = true
        error.value = null

        try {
            await api.delete('/cart')
            items.value = []
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to clear cart'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    return {
        items,
        loading,
        error,
        itemCount,
        totalPrice,
        fetchCart,
        addItem,
        updateItem,
        removeItem,
        clearCart
    }
})
