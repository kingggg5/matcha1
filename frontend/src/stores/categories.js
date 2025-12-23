import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useCategoryStore = defineStore('categories', () => {
    const categories = ref([])
    const category = ref(null)
    const loading = ref(false)
    const error = ref(null)

    async function fetchCategories() {
        loading.value = true
        error.value = null

        try {
            const response = await api.get('/categories')
            categories.value = response.data.categories
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to fetch categories'
        } finally {
            loading.value = false
        }
    }

    async function fetchCategory(id) {
        loading.value = true
        error.value = null
        category.value = null

        try {
            const response = await api.get(`/categories/${id}`)
            category.value = response.data.category
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to fetch category'
        } finally {
            loading.value = false
        }
    }

    async function createCategory(data) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post('/categories', data)
            categories.value.push(response.data.category)
            return { success: true, category: response.data.category }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to create category'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function updateCategory(id, data) {
        loading.value = true
        error.value = null

        try {
            const response = await api.put(`/categories/${id}`, data)
            const index = categories.value.findIndex(c => c.id === id)
            if (index !== -1) {
                categories.value[index] = response.data.category
            }
            return { success: true, category: response.data.category }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to update category'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    async function deleteCategory(id) {
        loading.value = true
        error.value = null

        try {
            await api.delete(`/categories/${id}`)
            categories.value = categories.value.filter(c => c.id !== id)
            return { success: true }
        } catch (err) {
            error.value = err.response?.data?.error || 'Failed to delete category'
            return { success: false, error: error.value }
        } finally {
            loading.value = false
        }
    }

    return {
        categories,
        category,
        loading,
        error,
        fetchCategories,
        fetchCategory,
        createCategory,
        updateCategory,
        deleteCategory
    }
})
