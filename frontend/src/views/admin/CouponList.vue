<template>
  <div class="admin-coupons">
    <div class="page-header mb-xl">
      <h1 class="page-title">จัดการคูปอง</h1>
      <router-link to="/admin/coupons/new" class="btn btn-primary">
        ➕ สร้างคูปอง
      </router-link>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="coupons.length === 0" class="empty-state text-center py-3xl">
        <p class="text-secondary">ยังไม่มีคูปอง</p>
        <router-link to="/admin/coupons/new" class="btn btn-primary mt-lg">สร้างคูปองแรก</router-link>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>รหัสคูปอง</th>
            <th>ประเภท</th>
            <th>ส่วนลด</th>
            <th>ใช้แล้ว</th>
            <th>หมดอายุ</th>
            <th>สถานะ</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="coupon in coupons" :key="coupon.id">
            <td>
              <code class="coupon-code">{{ coupon.code }}</code>
            </td>
            <td>{{ coupon.type === 'percentage' ? 'เปอร์เซ็นต์' : 'จำนวนเงิน' }}</td>
            <td class="text-accent">
              {{ coupon.type === 'percentage' ? `${coupon.value}%` : `฿${formatPrice(coupon.value)}` }}
            </td>
            <td>
              {{ coupon.usedCount }}
              <span v-if="coupon.usageLimit > 0">/ {{ coupon.usageLimit }}</span>
            </td>
            <td class="text-muted">{{ coupon.expiresAt || 'ไม่จำกัด' }}</td>
            <td>
              <span class="status-badge" :class="coupon.active ? 'active' : 'inactive'">
                {{ coupon.active ? 'เปิดใช้งาน' : 'ปิด' }}
              </span>
            </td>
            <td>
              <div class="table-actions">
                <router-link :to="`/admin/coupons/${coupon.id}/edit`" class="action-btn edit">
                  ✏️
                </router-link>
                <button class="action-btn delete" @click="confirmDelete(coupon)">
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Delete Modal -->
    <div v-if="deleteModal" class="modal-overlay" @click="deleteModal = false">
      <div class="modal-content card" @click.stop>
        <h3 class="modal-title">ยืนยันการลบ</h3>
        <p class="text-secondary">คุณต้องการลบคูปอง "{{ couponToDelete?.code }}" ใช่หรือไม่?</p>
        <div class="modal-actions">
          <button class="btn btn-secondary" @click="deleteModal = false">ยกเลิก</button>
          <button class="btn btn-primary" @click="handleDelete" :disabled="deleting">
            {{ deleting ? 'กำลังลบ...' : 'ลบ' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCouponStore } from '../../stores/coupons'
import { useToastStore } from '../../stores/toast'

const couponStore = useCouponStore()
const toastStore = useToastStore()

const deleteModal = ref(false)
const couponToDelete = ref(null)
const deleting = ref(false)

const coupons = computed(() => couponStore.coupons)
const loading = computed(() => couponStore.loading)

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

function confirmDelete(coupon) {
  couponToDelete.value = coupon
  deleteModal.value = true
}

async function handleDelete() {
  if (!couponToDelete.value) return
  
  deleting.value = true
  const result = await couponStore.deleteCoupon(couponToDelete.value.id)
  deleting.value = false
  
  if (result.success) {
    toastStore.success('ลบคูปองสำเร็จ')
    deleteModal.value = false
  } else {
    toastStore.error(result.error || 'ไม่สามารถลบคูปองได้')
  }
}

onMounted(() => {
  couponStore.fetchCoupons()
})
</script>

<style scoped>
.admin-coupons {
  max-width: 1200px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: var(--font-size-2xl);
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: var(--space-3xl);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: var(--space-md) var(--space-lg);
  text-align: left;
  border-bottom: 1px solid var(--border-subtle);
}

.data-table th {
  font-weight: 600;
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
}

.coupon-code {
  background: var(--bg-glass);
  padding: 4px 12px;
  border-radius: var(--radius-sm);
  font-family: monospace;
  font-weight: 600;
  color: var(--color-accent);
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 500;
}

.status-badge.active {
  background: #d1fae5;
  color: #047857;
}

.status-badge.inactive {
  background: var(--bg-glass);
  color: var(--text-muted);
}

.table-actions {
  display: flex;
  gap: var(--space-sm);
}

.action-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.action-btn.edit:hover {
  background: rgba(45, 90, 39, 0.2);
}

.action-btn.delete:hover {
  background: rgba(248, 113, 113, 0.2);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: var(--z-modal);
}

.modal-content {
  max-width: 400px;
  padding: var(--space-xl);
}

.modal-title {
  margin-bottom: var(--space-md);
}

.modal-actions {
  display: flex;
  gap: var(--space-md);
  margin-top: var(--space-xl);
  justify-content: flex-end;
}
</style>
