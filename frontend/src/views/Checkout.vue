<template>
  <div class="checkout-page">
    <div class="container py-3xl">
      <h1 class="page-title mb-2xl">ชำระเงิน</h1>

      <div class="checkout-grid">
        <!-- Order Summary -->
        <div class="checkout-summary card">
          <h3 class="section-title">สรุปคำสั่งซื้อ</h3>
          
          <div class="order-items">
            <div v-for="item in cartItems" :key="item.id" class="order-item">
              <span class="item-name">{{ item.product?.name }} x{{ item.quantity }}</span>
              <span class="item-price">฿{{ formatPrice((item.product?.price || 0) * item.quantity) }}</span>
            </div>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row">
            <span>ยอดรวม</span>
            <span>฿{{ formatPrice(subtotal) }}</span>
          </div>
          <div v-if="discount > 0" class="summary-row discount">
            <span>ส่วนลด</span>
            <span>-฿{{ formatPrice(discount) }}</span>
          </div>
          <div class="summary-row">
            <span>ค่าจัดส่ง</span>
            <span class="text-accent">ฟรี</span>
          </div>
          <div class="summary-row total">
            <span>รวมทั้งหมด</span>
            <span>฿{{ formatPrice(grandTotal) }}</span>
          </div>
        </div>

        <!-- Payment Info -->
        <div class="checkout-payment card">
          <h3 class="section-title">ข้อมูลการชำระเงิน</h3>

          <!-- Bank Account -->
          <div class="payment-method">
            <h4>โอนเงินผ่านธนาคาร</h4>
            <div class="bank-info">
              <div class="bank-row">
                <span class="bank-label">ธนาคาร:</span>
                <span class="bank-value">กสิกรไทย (KBANK)</span>
              </div>
              <div class="bank-row">
                <span class="bank-label">ชื่อบัญชี:</span>
                <span class="bank-value">บจก. มัทฉะซึกิ</span>
              </div>
              <div class="bank-row">
                <span class="bank-label">เลขบัญชี:</span>
                <span class="bank-value account-number">123-4-56789-0</span>
              </div>
              <div class="bank-row">
                <span class="bank-label">ยอดโอน:</span>
                <span class="bank-value total-amount">฿{{ formatPrice(grandTotal) }}</span>
              </div>
            </div>
          </div>

          <!-- QR Code -->
          <div class="qr-section">
            <h4>หรือสแกน QR Code</h4>
            <div class="qr-placeholder">
              <div class="qr-box">
                <span class="qr-icon">📱</span>
                <p>PromptPay QR</p>
                <p class="qr-number">0812345678</p>
              </div>
            </div>
          </div>

          <!-- Shipping Info -->
          <div class="shipping-form">
            <h4>ข้อมูลจัดส่ง</h4>
            <div class="form-group">
              <label class="form-label">ชื่อผู้รับ</label>
              <input type="text" v-model="form.customerName" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">เบอร์โทรศัพท์</label>
              <input 
                type="tel" 
                v-model="form.customerPhone" 
                class="form-input" 
                pattern="[0-9]*"
                inputmode="numeric"
                @input="form.customerPhone = form.customerPhone.replace(/[^0-9]/g, '')"
                required 
              />
            </div>
            <div class="form-group">
              <label class="form-label">ที่อยู่จัดส่ง</label>
              <textarea v-model="form.shippingAddress" class="form-input" rows="3" required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">หมายเหตุ (ถ้ามี)</label>
              <input type="text" v-model="form.note" class="form-input" />
            </div>
          </div>

          <!-- Create Order & Upload Slip -->
          <div v-if="!currentOrder" class="checkout-action">
            <button 
              class="btn btn-primary w-full"
              @click="createOrder"
              :disabled="creatingOrder || !isFormValid"
            >
              {{ creatingOrder ? 'กำลังสร้างคำสั่งซื้อ...' : 'ยืนยันคำสั่งซื้อ' }}
            </button>
          </div>

          <!-- Upload Slip Section -->
          <div v-else class="slip-upload-section">
            <div class="order-created-notice">
              <span class="notice-icon">✅</span>
              <p>สร้างคำสั่งซื้อ #{{ currentOrder.id.slice(-6).toUpperCase() }} แล้ว</p>
            </div>

            <h4>อัพโหลดสลิปการโอนเงิน</h4>
            
            <div 
              class="slip-dropzone"
              :class="{ 'has-file': slipFile }"
              @click="$refs.slipInput.click()"
              @drop.prevent="handleDrop"
              @dragover.prevent
            >
              <input 
                ref="slipInput"
                type="file" 
                accept="image/*"
                @change="handleFileSelect"
                hidden
              />
              <div v-if="!slipFile" class="dropzone-content">
                <span class="dropzone-icon">📄</span>
                <p>คลิกหรือลากไฟล์มาที่นี่</p>
                <p class="text-muted">รองรับ JPG, PNG, WEBP</p>
              </div>
              <div v-else class="file-preview">
                <img :src="slipPreview" alt="Slip preview" />
                <button class="remove-file" @click.stop="removeFile">✕</button>
              </div>
            </div>

            <button 
              class="btn btn-primary w-full mt-lg"
              @click="uploadSlip"
              :disabled="!slipFile || uploadingSlip"
            >
              {{ uploadingSlip ? 'กำลังอัพโหลด...' : 'ส่งหลักฐานการโอนเงิน' }}
            </button>

            <div v-if="currentOrder.status === 'paid'" class="payment-success">
              <span class="success-icon">🎉</span>
              <h4>ชำระเงินสำเร็จ!</h4>
              <p>เราจะดำเนินการจัดส่งให้เร็วที่สุด</p>
              <router-link to="/products" class="btn btn-secondary mt-md">
                ช้อปต่อ
              </router-link>
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
import { useCartStore } from '../stores/cart'
import { useCouponStore } from '../stores/coupons'
import { useOrderStore } from '../stores/orders'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const cartStore = useCartStore()
const couponStore = useCouponStore()
const orderStore = useOrderStore()
const authStore = useAuthStore()
const toastStore = useToastStore()

const form = ref({
  customerName: '',
  customerPhone: '',
  shippingAddress: '',
  note: ''
})

const slipFile = ref(null)
const slipPreview = ref('')
const creatingOrder = ref(false)
const uploadingSlip = ref(false)

const cartItems = computed(() => cartStore.items)
const subtotal = computed(() => cartStore.totalPrice)
const discount = computed(() => couponStore.discount)
const appliedCoupon = computed(() => couponStore.appliedCoupon)
const grandTotal = computed(() => Math.max(0, subtotal.value - discount.value))
const currentOrder = computed(() => orderStore.currentOrder)

const isFormValid = computed(() => {
  return form.value.customerName && form.value.customerPhone && form.value.shippingAddress
})

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

async function createOrder() {
  if (!isFormValid.value) {
    toastStore.error('กรุณากรอกข้อมูลให้ครบ')
    return
  }

  creatingOrder.value = true
  const orderData = {
    ...form.value,
    couponCode: appliedCoupon.value?.code || null
  }

  const result = await orderStore.createOrder(orderData)
  creatingOrder.value = false

  if (result.success) {
    toastStore.success('สร้างคำสั่งซื้อสำเร็จ')
    couponStore.clearCoupon()
  } else {
    toastStore.error(result.error || 'ไม่สามารถสร้างคำสั่งซื้อได้')
  }
}

function handleFileSelect(e) {
  const file = e.target.files[0]
  if (file) {
    setFile(file)
  }
}

function handleDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    setFile(file)
  }
}

function setFile(file) {
  slipFile.value = file
  slipPreview.value = URL.createObjectURL(file)
}

function removeFile() {
  slipFile.value = null
  slipPreview.value = ''
}

async function uploadSlip() {
  if (!slipFile.value || !currentOrder.value) return

  uploadingSlip.value = true
  const result = await orderStore.uploadSlip(currentOrder.value.id, slipFile.value)
  uploadingSlip.value = false

  if (result.success) {
    toastStore.success('อัพโหลดสลิปสำเร็จ รอตรวจสอบการชำระเงิน')
  } else {
    toastStore.error(result.error || 'ไม่สามารถอัพโหลดได้')
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login?redirect=/checkout')
    return
  }
  
  if (cartItems.value.length === 0 && !currentOrder.value) {
    router.push('/cart')
  }

  // Pre-fill with user data
  if (authStore.user) {
    form.value.customerName = authStore.user.name || ''
  }
})
</script>

<style scoped>
.checkout-page {
  padding-top: 80px;
  min-height: 100vh;
}

.checkout-summary,
.checkout-payment {
  padding: var(--space-xl);
}

.page-title {
  font-size: var(--font-size-3xl);
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: var(--space-2xl); /* Increased from space-xl */
  align-items: start;
}

.section-title {
  font-size: var(--font-size-xl);
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-md);
  border-bottom: 1px solid var(--border-subtle);
}

.order-items {
  margin-bottom: var(--space-lg);
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: var(--space-sm) 0;
  color: var(--text-secondary);
}

.summary-divider {
  height: 1px;
  background: var(--border-subtle);
  margin: var(--space-lg) 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: var(--space-sm);
}

.summary-row.total {
  font-weight: 600;
  font-size: var(--font-size-lg);
  color: var(--color-accent);
  margin-top: var(--space-md);
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-subtle);
}

.summary-row.discount {
  color: var(--color-accent);
}

.payment-method, .qr-section, .shipping-form {
  margin-bottom: var(--space-xl);
}

.payment-method h4, .qr-section h4, .shipping-form h4 {
  margin-bottom: var(--space-md);
  color: var(--text-primary);
}

.bank-info {
  background: var(--bg-glass);
  padding: var(--space-lg);
  border-radius: var(--radius-md);
}

.bank-row {
  display: flex;
  margin-bottom: var(--space-sm);
}

.bank-row:last-child {
  margin-bottom: 0;
}

.bank-label {
  width: 100px;
  color: var(--text-secondary);
}

.bank-value {
  font-weight: 500;
}

.account-number {
  font-family: monospace;
  font-size: var(--font-size-lg);
  color: var(--color-accent);
}

.total-amount {
  font-size: var(--font-size-xl);
  color: var(--color-accent);
}

.qr-placeholder {
  display: flex;
  justify-content: center;
}

.qr-box {
  width: 200px;
  height: 200px;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-primary);
}

.qr-icon {
  font-size: 3rem;
  margin-bottom: var(--space-sm);
}

.qr-number {
  font-family: monospace;
  font-weight: 600;
}

.slip-upload-section {
  margin-top: var(--space-xl);
  padding-top: var(--space-xl);
  border-top: 1px solid var(--border-subtle);
}

.order-created-notice {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md);
  background: rgba(45, 90, 39, 0.2);
  border: 1px solid var(--color-primary);
  border-radius: var(--radius-md);
  margin-bottom: var(--space-lg);
}

.notice-icon {
  font-size: 1.5rem;
}

.slip-dropzone {
  border: 2px dashed var(--border-subtle);
  border-radius: var(--radius-md);
  padding: var(--space-2xl);
  text-align: center;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.slip-dropzone:hover {
  border-color: var(--color-primary);
}

.slip-dropzone.has-file {
  padding: var(--space-md);
}

.dropzone-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: var(--space-sm);
}

.file-preview {
  position: relative;
}

.file-preview img {
  max-width: 100%;
  max-height: 300px;
  border-radius: var(--radius-sm);
}

.remove-file {
  position: absolute;
  top: var(--space-sm);
  right: var(--space-sm);
  width: 30px;
  height: 30px;
  background: rgba(0,0,0,0.7);
  color: white;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

.payment-success {
  text-align: center;
  padding: var(--space-xl);
  background: rgba(45, 90, 39, 0.1);
  border-radius: var(--radius-md);
  margin-top: var(--space-xl);
}

.success-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: var(--space-md);
}

@media (max-width: 1024px) {
  .checkout-grid {
    grid-template-columns: 1fr;
  }
}
</style>
