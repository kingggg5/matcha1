import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useOrderStore = defineStore('orders', () => {
    const orders = ref([])
    const currentOrder = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const stats = ref(null)

    async function fetchOrders() {
        loading.value = true
        try {
            const { data } = await api.get('/orders')
            orders.value = data.orders || []
        } catch (err) {
            error.value = err.response?.data?.error || 'ไม่สามารถโหลดคำสั่งซื้อได้'
        } finally {
            loading.value = false
        }
    }

    async function fetchOrder(id) {
        loading.value = true
        try {
            const { data } = await api.get(`/orders/${id}`)
            currentOrder.value = data.order
            return data.order
        } catch (err) {
            error.value = err.response?.data?.error
            return null
        } finally {
            loading.value = false
        }
    }

    async function createOrder(orderData) {
        loading.value = true
        try {
            const { data } = await api.post('/orders', orderData)
            currentOrder.value = data.order
            orders.value.unshift(data.order)
            return { success: true, order: data.order }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        } finally {
            loading.value = false
        }
    }

    async function uploadSlip(orderId, file) {
        const formData = new FormData()
        formData.append('slip', file)

        try {
            const { data } = await api.post(`/orders/${orderId}/slip`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            currentOrder.value = data.order
            // Update in list
            const index = orders.value.findIndex(o => o.id === orderId)
            if (index !== -1) {
                orders.value[index] = data.order
            }
            return { success: true }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        }
    }

    async function updateStatus(orderId, status) {
        try {
            const { data } = await api.put(`/orders/${orderId}/status`, { status })
            const index = orders.value.findIndex(o => o.id === orderId)
            if (index !== -1) {
                orders.value[index] = data.order
            }
            return { success: true }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        }
    }

    async function fetchStats() {
        try {
            const { data } = await api.get('/admin/stats')
            stats.value = data.stats
            return data.stats
        } catch (err) {
            return null
        }
    }

    return {
        orders,
        currentOrder,
        loading,
        error,
        stats,
        fetchOrders,
        fetchOrder,
        createOrder,
        uploadSlip,
        updateStatus,
        fetchStats
    }
})
