<template>
  <div class="orders-page">
    <div class="container py-3xl">
      <div class="page-header mb-2xl">
        <h1 class="page-title">📦 คำสั่งซื้อของฉัน</h1>
        <p class="page-desc text-secondary">ติดตามสถานะการสั่งซื้อ</p>
      </div>

      <!-- Not Logged In -->
      <div v-if="!isAuthenticated" class="empty-state card text-center py-3xl">
        <div class="empty-icon">🔐</div>
        <h3>กรุณาเข้าสู่ระบบ</h3>
        <p class="text-secondary mb-lg">เข้าสู่ระบบเพื่อดูประวัติการสั่งซื้อ</p>
        <router-link to="/login?redirect=/my-orders" class="btn btn-primary">เข้าสู่ระบบ</router-link>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="empty-state card text-center py-3xl">
        <div class="empty-icon">📋</div>
        <h3>ยังไม่มีคำสั่งซื้อ</h3>
        <p class="text-secondary mb-lg">เริ่มช้อปปิ้งได้เลย!</p>
        <router-link to="/products" class="btn btn-primary">ดูสินค้า</router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="orders-list">
        <div v-for="order in orders" :key="order.id" class="order-card card">
          <div class="order-header">
            <div class="order-info">
              <span class="order-id">#{{ order.id.slice(-6).toUpperCase() }}</span>
              <span class="order-date text-muted">{{ formatDate(order.createdAt) }}</span>
            </div>
            <span class="order-status" :class="order.status">
              {{ getStatusLabel(order.status) }}
            </span>
          </div>

          <div class="order-items">
            <div v-for="item in order.items" :key="item.productId" class="order-item">
              <span class="item-name">{{ item.name }}</span>
              <span class="item-qty">x{{ item.quantity }}</span>
              <span class="item-price">฿{{ formatPrice(item.total) }}</span>
            </div>
          </div>

          <div class="order-footer">
            <div class="order-total">
              <span v-if="order.discount > 0" class="discount-badge">
                ลด ฿{{ formatPrice(order.discount) }}
              </span>
              <span class="total-label">รวมทั้งหมด</span>
              <span class="total-value">฿{{ formatPrice(order.total) }}</span>
            </div>
            
            <div class="order-actions">
              <button 
                v-if="order.status === 'pending'"
                class="btn btn-primary btn-sm"
                @click="goToPayment(order)"
              >
                ชำระเงิน
              </button>
              <button 
                class="btn btn-secondary btn-sm"
                @click="viewDetail(order)"
              >
                ดูรายละเอียด
              </button>
            </div>
          </div>

          <!-- Status Timeline -->
          <div class="status-timeline">
            <div 
              v-for="(step, index) in statusSteps" 
              :key="step.status"
              class="timeline-step"
              :class="{ 
                active: isStepActive(order.status, step.status),
                current: order.status === step.status
              }"
            >
              <div class="step-icon">{{ step.icon }}</div>
              <div class="step-label">{{ step.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Detail Modal -->
      <div v-if="selectedOrder" class="modal-overlay" @click="selectedOrder = null">
        <div class="modal-content card" @click.stop>
          <div class="modal-header">
            <h3>คำสั่งซื้อ #{{ selectedOrder.id.slice(-6).toUpperCase() }}</h3>
            <button class="modal-close" @click="selectedOrder = null">✕</button>
          </div>
          
          <div class="modal-body">
            <div class="detail-section">
              <h4>สถานะ</h4>
              <span class="order-status large" :class="selectedOrder.status">
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>

            <div class="detail-section">
              <h4>ที่อยู่จัดส่ง</h4>
              <p>{{ selectedOrder.customerName }}</p>
              <p class="text-muted">{{ selectedOrder.customerPhone }}</p>
              <p class="text-muted">{{ selectedOrder.shippingAddress }}</p>
            </div>

            <div class="detail-section">
              <h4>รายการสินค้า</h4>
              <div v-for="item in selectedOrder.items" :key="item.productId" class="detail-item">
                <span>{{ item.name }} x{{ item.quantity }}</span>
                <span>฿{{ formatPrice(item.total) }}</span>
              </div>
            </div>

            <div class="detail-summary">
              <div class="summary-row">
                <span>ยอดรวม</span>
                <span>฿{{ formatPrice(selectedOrder.subtotal) }}</span>
              </div>
              <div v-if="selectedOrder.discount > 0" class="summary-row">
                <span>ส่วนลด</span>
                <span class="text-accent">-฿{{ formatPrice(selectedOrder.discount) }}</span>
              </div>
              <div class="summary-row total">
                <span>รวมทั้งหมด</span>
                <span>฿{{ formatPrice(selectedOrder.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrderStore } from '../stores/orders'

const router = useRouter()
const authStore = useAuthStore()
const orderStore = useOrderStore()

const selectedOrder = ref(null)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const orders = computed(() => orderStore.orders)
const loading = computed(() => orderStore.loading)

const statusSteps = [
  { status: 'pending', icon: '⏳', label: 'รอชำระ' },
  { status: 'paid', icon: '💳', label: 'ชำระแล้ว' },
  { status: 'confirmed', icon: '✔️', label: 'ยืนยัน' },
  { status: 'shipped', icon: '🚚', label: 'จัดส่ง' },
  { status: 'completed', icon: '✅', label: 'สำเร็จ' }
]

const statusOrder = ['pending', 'paid', 'confirmed', 'shipped', 'completed']

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price || 0)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getStatusLabel(status) {
  const labels = {
    pending: 'รอชำระเงิน',
    paid: 'ชำระแล้ว',
    confirmed: 'ยืนยันแล้ว',
    shipped: 'กำลังจัดส่ง',
    completed: 'เสร็จสิ้น',
    cancelled: 'ยกเลิก'
  }
  return labels[status] || status
}

function isStepActive(currentStatus, stepStatus) {
  if (currentStatus === 'cancelled') return false
  const currentIndex = statusOrder.indexOf(currentStatus)
  const stepIndex = statusOrder.indexOf(stepStatus)
  return stepIndex <= currentIndex
}

function viewDetail(order) {
  selectedOrder.value = order
}

function goToPayment(order) {
  orderStore.currentOrder = order
  router.push('/checkout')
}

onMounted(() => {
  if (isAuthenticated.value) {
    orderStore.fetchOrders()
  }
})
</script>

<style scoped>
.orders-page {
  padding-top: 80px;
  min-height: 100vh;
}

.page-header {
  text-align: center;
}

.page-title {
  font-size: var(--font-size-3xl);
  margin-bottom: var(--space-sm);
}

.empty-state {
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: var(--space-md);
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: var(--space-3xl);
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-lg);
  max-width: 800px;
  margin: 0 auto;
}

.order-card {
  padding: var(--space-xl);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-md);
  border-bottom: 1px solid var(--border-subtle);
}

.order-id {
  font-family: monospace;
  font-weight: 700;
  font-size: var(--font-size-lg);
  margin-right: var(--space-md);
}

.order-status {
  display: inline-block;
  padding: 6px 16px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-sm);
  font-weight: 500;
}

.order-status.pending { background: #fef3c7; color: #92400e; }
.order-status.paid { background: #dbeafe; color: #1e40af; }
.order-status.confirmed { background: #d1fae5; color: #065f46; }
.order-status.shipped { background: #e0e7ff; color: #3730a3; }
.order-status.completed { background: #d1fae5; color: #047857; }
.order-status.cancelled { background: #fee2e2; color: #991b1b; }

.order-status.large {
  font-size: var(--font-size-base);
  padding: 8px 20px;
}

.order-items {
  margin-bottom: var(--space-lg);
}

.order-item {
  display: flex;
  padding: var(--space-sm) 0;
  border-bottom: 1px dashed var(--border-subtle);
}

.item-name {
  flex: 1;
}

.item-qty {
  color: var(--text-muted);
  margin-right: var(--space-lg);
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-lg);
}

.discount-badge {
  display: inline-block;
  padding: 2px 8px;
  background: rgba(45, 90, 39, 0.2);
  color: var(--color-accent);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  margin-right: var(--space-md);
}

.total-label {
  color: var(--text-secondary);
  margin-right: var(--space-sm);
}

.total-value {
  font-size: var(--font-size-xl);
  font-weight: 700;
  color: var(--color-accent);
}

.order-actions {
  display: flex;
  gap: var(--space-sm);
}

/* Status Timeline */
.status-timeline {
  display: flex;
  justify-content: space-between;
  padding-top: var(--space-lg);
  border-top: 1px solid var(--border-subtle);
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  opacity: 0.4;
}

.timeline-step.active {
  opacity: 1;
}

.timeline-step.current .step-icon {
  transform: scale(1.2);
}

.step-icon {
  font-size: 1.5rem;
  margin-bottom: var(--space-xs);
  transition: transform var(--transition-fast);
}

.step-label {
  font-size: var(--font-size-xs);
  color: var(--text-secondary);
}

/* Modal */
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
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-lg);
  border-bottom: 1px solid var(--border-subtle);
}

.modal-close {
  font-size: 1.5rem;
  color: var(--text-muted);
}

.modal-body {
  padding: var(--space-lg);
}

.detail-section {
  margin-bottom: var(--space-lg);
}

.detail-section h4 {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin-bottom: var(--space-sm);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: var(--space-xs) 0;
}

.detail-summary {
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-subtle);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: var(--space-xs) 0;
}

.summary-row.total {
  font-weight: 600;
  font-size: var(--font-size-lg);
  color: var(--color-accent);
}

@media (max-width: 640px) {
  .order-footer {
    flex-direction: column;
    gap: var(--space-md);
  }
  
  .status-timeline {
    flex-wrap: wrap;
    gap: var(--space-sm);
  }
}
</style>
