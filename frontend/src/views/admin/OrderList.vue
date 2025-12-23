<template>
  <div class="admin-orders">
    <div class="page-header mb-xl">
      <h1 class="page-title">จัดการคำสั่งซื้อ</h1>
    </div>

    <!-- Status Filters -->
    <div class="status-filters mb-lg">
      <button 
        class="filter-btn" 
        :class="{ active: !statusFilter }"
        @click="statusFilter = ''"
      >
        ทั้งหมด
      </button>
      <button 
        v-for="status in statusOptions" 
        :key="status.value"
        class="filter-btn"
        :class="{ active: statusFilter === status.value }"
        @click="statusFilter = status.value"
      >
        {{ status.label }}
      </button>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="empty-state text-center py-3xl">
        <p class="text-secondary">ไม่มีคำสั่งซื้อ</p>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>รหัส</th>
            <th>ลูกค้า</th>
            <th>ยอดรวม</th>
            <th>สถานะ</th>
            <th>วันที่</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td>
              <span class="order-id">#{{ order.id.slice(-6).toUpperCase() }}</span>
            </td>
            <td>
              <div class="customer-info">
                <span class="customer-name">{{ order.customerName || 'ไม่ระบุ' }}</span>
                <span class="customer-phone text-muted">{{ order.customerPhone }}</span>
              </div>
            </td>
            <td class="text-accent">฿{{ formatPrice(order.total) }}</td>
            <td>
              <span class="status-badge" :class="order.status">
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td class="text-muted">{{ formatDate(order.createdAt) }}</td>
            <td>
              <div class="table-actions">
                <button class="action-btn view" @click="viewOrder(order)" title="ดูรายละเอียด">
                  👁️
                </button>
                <select 
                  class="status-select"
                  :value="order.status"
                  @change="updateStatus(order.id, $event.target.value)"
                >
                  <option v-for="s in statusOptions" :key="s.value" :value="s.value">
                    {{ s.label }}
                  </option>
                </select>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click="selectedOrder = null">
      <div class="modal-content card" @click.stop>
        <div class="modal-header">
          <h3>คำสั่งซื้อ #{{ selectedOrder.id.slice(-6).toUpperCase() }}</h3>
          <button class="modal-close" @click="selectedOrder = null">✕</button>
        </div>
        
        <div class="order-detail">
          <div class="detail-section">
            <h4>ข้อมูลลูกค้า</h4>
            <p><strong>ชื่อ:</strong> {{ selectedOrder.customerName }}</p>
            <p><strong>โทร:</strong> {{ selectedOrder.customerPhone }}</p>
            <p><strong>ที่อยู่:</strong> {{ selectedOrder.shippingAddress }}</p>
          </div>

          <div class="detail-section">
            <h4>รายการสินค้า</h4>
            <div v-for="item in selectedOrder.items" :key="item.productId" class="order-item">
              <span>{{ item.name }} x{{ item.quantity }}</span>
              <span>฿{{ formatPrice(item.total) }}</span>
            </div>
          </div>

          <div class="detail-section">
            <h4>สรุป</h4>
            <div class="summary-row">
              <span>ยอดรวม</span>
              <span>฿{{ formatPrice(selectedOrder.subtotal) }}</span>
            </div>
            <div v-if="selectedOrder.discount > 0" class="summary-row">
              <span>ส่วนลด ({{ selectedOrder.couponCode }})</span>
              <span class="text-accent">-฿{{ formatPrice(selectedOrder.discount) }}</span>
            </div>
            <div class="summary-row total">
              <span>รวมทั้งหมด</span>
              <span class="text-accent">฿{{ formatPrice(selectedOrder.total) }}</span>
            </div>
          </div>

          <div v-if="selectedOrder.paymentSlip" class="detail-section">
            <h4>สลิปการโอนเงิน</h4>
            <img :src="getSlipUrl(selectedOrder.paymentSlip)" class="slip-image" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOrderStore } from '../../stores/orders'
import { useToastStore } from '../../stores/toast'

const orderStore = useOrderStore()
const toastStore = useToastStore()

const statusFilter = ref('')
const selectedOrder = ref(null)

const statusOptions = [
  { value: 'pending', label: 'รอชำระ' },
  { value: 'paid', label: 'ชำระแล้ว' },
  { value: 'confirmed', label: 'ยืนยันแล้ว' },
  { value: 'shipped', label: 'จัดส่งแล้ว' },
  { value: 'completed', label: 'สำเร็จ' },
  { value: 'cancelled', label: 'ยกเลิก' }
]

const orders = computed(() => orderStore.orders)
const loading = computed(() => orderStore.loading)

const filteredOrders = computed(() => {
  if (!statusFilter.value) return orders.value
  return orders.value.filter(o => o.status === statusFilter.value)
})

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price || 0)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getStatusLabel(status) {
  const found = statusOptions.find(s => s.value === status)
  return found?.label || status
}

function getSlipUrl(slip) {
  if (slip.startsWith('http')) return slip
  return `http://localhost:8000${slip}`
}

function viewOrder(order) {
  selectedOrder.value = order
}

async function updateStatus(orderId, status) {
  const result = await orderStore.updateStatus(orderId, status)
  if (result.success) {
    toastStore.success('อัพเดทสถานะสำเร็จ')
  } else {
    toastStore.error(result.error)
  }
}

onMounted(() => {
  orderStore.fetchOrders()
})
</script>

<style scoped>
.admin-orders {
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

.status-filters {
  display: flex;
  gap: var(--space-sm);
  flex-wrap: wrap;
}

.filter-btn {
  padding: var(--space-sm) var(--space-md);
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  transition: all var(--transition-fast);
}

.filter-btn:hover {
  border-color: var(--color-primary);
}

.filter-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
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

.order-id {
  font-family: monospace;
  font-weight: 600;
}

.customer-name {
  display: block;
}

.customer-phone {
  font-size: var(--font-size-sm);
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 500;
}

.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.paid { background: #dbeafe; color: #1e40af; }
.status-badge.confirmed { background: #d1fae5; color: #065f46; }
.status-badge.shipped { background: #e0e7ff; color: #3730a3; }
.status-badge.completed { background: #d1fae5; color: #047857; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }

.table-actions {
  display: flex;
  gap: var(--space-sm);
  align-items: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.action-btn:hover {
  background: var(--bg-glass);
}

.status-select {
  padding: 4px 8px;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  color: var(--text-primary);
  font-size: var(--font-size-sm);
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
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  padding: var(--space-xl);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-md);
  border-bottom: 1px solid var(--border-subtle);
}

.modal-close {
  font-size: 1.5rem;
  color: var(--text-muted);
}

.detail-section {
  margin-bottom: var(--space-lg);
}

.detail-section h4 {
  margin-bottom: var(--space-sm);
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: var(--space-sm) 0;
  border-bottom: 1px solid var(--border-subtle);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: var(--space-xs) 0;
}

.summary-row.total {
  font-weight: 600;
  font-size: var(--font-size-lg);
  padding-top: var(--space-sm);
  border-top: 1px solid var(--border-subtle);
}

.slip-image {
  max-width: 100%;
  border-radius: var(--radius-md);
}
</style>
