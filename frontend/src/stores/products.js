import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useProductStore = defineStore('products', () => {
    const products = ref([])
    const product = ref(null)
    const loading = ref(false)
    const error = ref(null)

    async function fetchProducts(filters = {}) {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()
            if (filters.categoryId) params.append('categoryId', filters.categoryId)
            if (filters.inStock !== undefined) params.append('inStock', filters.inStock)
            if (filters.limit) params.append('limit', filters.limit)
            if (filters.search) params.append('search', filters.search)
            if (filters.minPrice) params.append('minPrice', filters.minPrice)
            if (filters.maxPrice) params.append('maxPrice', filters.maxPrice)
            if (filters.sort) params.append('sort', filters.sort)

            const response = await api.get(`/products?${params.toString()}`)
            products.value = response.data?.products || []
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to fetch products'
            products.value = []
        } finally {
            loading.value = false
        }
    }

    async function fetchProduct(id) {
        loading.value = true
        error.value = null
        product.value = null

        try {
            const response = await api.get(`/products/${id}`)
            product.value = response.data.product
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to fetch product'
        } finally {
            loading.value = false
        }
    }

    async function createProduct(data) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post('/products', data)
            products.value.unshift(response.data.product)
            return { success: true, product: response.data.product }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to create product'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function updateProduct(id, data) {
        loading.value = true
        error.value = null

        try {
            const response = await api.put(`/products/${id}`, data)
            const index = products.value.findIndex(p => p.id === id)
            if (index !== -1) {
                products.value[index] = response.data.product
            }
            return { success: true, product: response.data.product }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to update product'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function deleteProduct(id) {
        loading.value = true
        error.value = null

        try {
            await api.delete(`/products/${id}`)
            products.value = products.value.filter(p => p.id !== id)
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to delete product'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    return {
        products,
        product,
        loading,
        error,
        fetchProducts,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct
    }
})
