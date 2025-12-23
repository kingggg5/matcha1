import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useWishlistStore = defineStore('wishlist', () => {
    const productIds = ref([])
    const products = ref([])
    const loading = ref(false)

    // Load from localStorage for guests
    function loadLocal() {
        const saved = localStorage.getItem('wishlist')
        if (saved) {
            productIds.value = JSON.parse(saved)
        }
    }

    function saveLocal() {
        localStorage.setItem('wishlist', JSON.stringify(productIds.value))
    }

    async function fetchWishlist() {
        loading.value = true
        try {
            const { data } = await api.get('/wishlist')
            productIds.value = data.wishlist?.productIds || []
            products.value = data.products || []
        } catch (err) {
            // Fallback to local
            loadLocal()
        } finally {
            loading.value = false
        }
    }

    async function addProduct(productId) {
        try {
            await api.post('/wishlist', { productId })
            if (!productIds.value.includes(productId)) {
                productIds.value.push(productId)
            }
            return { success: true }
        } catch (err) {
            // Fallback to local
            if (!productIds.value.includes(productId)) {
                productIds.value.push(productId)
                saveLocal()
            }
            return { success: true }
        }
    }

    async function removeProduct(productId) {
        try {
            await api.delete(`/wishlist/${productId}`)
            productIds.value = productIds.value.filter(id => id !== productId)
            products.value = products.value.filter(p => p.id !== productId)
            return { success: true }
        } catch (err) {
            // Fallback to local
            productIds.value = productIds.value.filter(id => id !== productId)
            saveLocal()
            return { success: true }
        }
    }

    function isInWishlist(productId) {
        return productIds.value.includes(productId)
    }

    async function toggleProduct(productId) {
        if (isInWishlist(productId)) {
            return await removeProduct(productId)
        } else {
            return await addProduct(productId)
        }
    }

    const count = computed(() => productIds.value.length)

    return {
        productIds,
        products,
        loading,
        count,
        fetchWishlist,
        addProduct,
        removeProduct,
        isInWishlist,
        toggleProduct,
        loadLocal
    }
})
