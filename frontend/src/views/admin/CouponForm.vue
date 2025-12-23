<template>
  <div class="admin-coupon-form">
    <div class="page-header mb-xl">
      <router-link to="/admin/coupons" class="back-link">← กลับ</router-link>
      <h1 class="page-title">{{ isEdit ? 'แก้ไขคูปอง' : 'สร้างคูปองใหม่' }}</h1>
    </div>

    <div class="form-card card">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">รหัสคูปอง *</label>
          <input 
            type="text" 
            v-model="form.code" 
            class="form-input" 
            placeholder="เช่น MATCHA20"
            required
            style="text-transform: uppercase"
          />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ประเภทส่วนลด *</label>
            <select v-model="form.type" class="form-input" required>
              <option value="percentage">เปอร์เซ็นต์ (%)</option>
              <option value="fixed">จำนวนเงิน (฿)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">ค่าส่วนลด *</label>
            <input 
              type="number" 
              v-model.number="form.value" 
              class="form-input" 
              :placeholder="form.type === 'percentage' ? 'เช่น 10' : 'เช่น 50'"
              required
              min="0"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">ยอดสั่งซื้อขั้นต่ำ (฿)</label>
            <input 
              type="number" 
              v-model.number="form.minOrderAmount" 
              class="form-input" 
              placeholder="0 = ไม่จำกัด"
              min="0"
            />
          </div>
          <div class="form-group">
            <label class="form-label">จำนวนครั้งที่ใช้ได้</label>
            <input 
              type="number" 
              v-model.number="form.usageLimit" 
              class="form-input" 
              placeholder="0 = ไม่จำกัด"
              min="0"
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">วันหมดอายุ</label>
          <input 
            type="date" 
            v-model="form.expiresAt" 
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.active" />
            <span>เปิดใช้งาน</span>
          </label>
        </div>

        <div v-if="error" class="form-error mt-lg">
          {{ error }}
        </div>

        <div class="form-actions mt-xl">
          <router-link to="/admin/coupons" class="btn btn-secondary">ยกเลิก</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'กำลังบันทึก...' : (isEdit ? 'อัพเดท' : 'สร้างคูปอง') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCouponStore } from '../../stores/coupons'
import { useToastStore } from '../../stores/toast'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const couponStore = useCouponStore()
const toastStore = useToastStore()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  code: '',
  type: 'percentage',
  value: 0,
  minOrderAmount: 0,
  usageLimit: 0,
  expiresAt: '',
  active: true
})

const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  loading.value = true

  let result
  if (isEdit.value) {
    result = await couponStore.updateCoupon(route.params.id, form.value)
  } else {
    result = await couponStore.createCoupon(form.value)
  }

  loading.value = false

  if (result.success) {
    toastStore.success(isEdit.value ? 'อัพเดทคูปองสำเร็จ' : 'สร้างคูปองสำเร็จ')
    router.push('/admin/coupons')
  } else {
    error.value = result.error
  }
}

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/coupons/${route.params.id}`)
      if (data.coupon) {
        form.value = {
          code: data.coupon.code,
          type: data.coupon.type,
          value: data.coupon.value,
          minOrderAmount: data.coupon.minOrderAmount,
          usageLimit: data.coupon.usageLimit,
          expiresAt: data.coupon.expiresAt,
          active: data.coupon.active
        }
      }
    } catch (err) {
      error.value = 'ไม่พบคูปอง'
    }
  }
})
</script>

<style scoped>
.admin-coupon-form {
  max-width: 600px;
}

.back-link {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  margin-bottom: var(--space-sm);
  display: inline-block;
}

.back-link:hover {
  color: var(--color-accent);
}

.page-title {
  font-size: var(--font-size-2xl);
}

.form-card {
  padding: var(--space-xl);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-md);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary);
}

.form-error {
  padding: var(--space-md);
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid #f87171;
  border-radius: var(--radius-md);
  color: #f87171;
}

.form-actions {
  display: flex;
  gap: var(--space-md);
  justify-content: flex-end;
  padding-top: var(--space-xl);
  border-top: 1px solid var(--border-subtle);
}
</style>
