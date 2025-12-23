import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useCouponStore = defineStore('coupon', () => {
    const coupons = ref([])
    const loading = ref(false)
    const error = ref(null)
    const appliedCoupon = ref(null)
    const discount = ref(0)

    async function fetchCoupons() {
        loading.value = true
        try {
            const { data } = await api.get('/coupons')
            coupons.value = data.coupons || []
        } catch (err) {
            error.value = err.response?.data?.error || 'ไม่สามารถโหลดคูปองได้'
        } finally {
            loading.value = false
        }
    }

    async function validateCoupon(code, orderAmount) {
        try {
            const { data } = await api.post('/coupons/validate', { code, orderAmount })
            if (data.valid) {
                appliedCoupon.value = data.coupon
                discount.value = data.discount
                return { success: true, discount: data.discount }
            }
            return { success: false, error: 'คูปองไม่ถูกต้อง' }
        } catch (err) {
            appliedCoupon.value = null
            discount.value = 0
            return { success: false, error: err.response?.data?.error || 'คูปองไม่ถูกต้อง' }
        }
    }

    async function createCoupon(couponData) {
        try {
            const { data } = await api.post('/coupons', couponData)
            coupons.value.unshift(data.coupon)
            return { success: true }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        }
    }

    async function updateCoupon(id, couponData) {
        try {
            const { data } = await api.put(`/coupons/${id}`, couponData)
            const index = coupons.value.findIndex(c => c.id === id)
            if (index !== -1) {
                coupons.value[index] = data.coupon
            }
            return { success: true }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        }
    }

    async function deleteCoupon(id) {
        try {
            await api.delete(`/coupons/${id}`)
            coupons.value = coupons.value.filter(c => c.id !== id)
            return { success: true }
        } catch (err) {
            return { success: false, error: err.response?.data?.error }
        }
    }

    function clearCoupon() {
        appliedCoupon.value = null
        discount.value = 0
    }

    return {
        coupons,
        loading,
        error,
        appliedCoupon,
        discount,
        fetchCoupons,
        validateCoupon,
        createCoupon,
        updateCoupon,
        deleteCoupon,
        clearCoupon
    }
})
