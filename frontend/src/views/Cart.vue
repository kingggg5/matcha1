<template>
  <div class="cart-page">
    <div class="container py-3xl">
      <h1 class="page-title mb-2xl">ตะกร้าสินค้า</h1>

      <!-- Empty Cart -->
      <div v-if="items.length === 0" class="cart-empty card text-center py-3xl">
        <div class="empty-icon">🛒</div>
        <h3>ตะกร้าว่างเปล่า</h3>
        <p class="text-secondary mb-lg">ยังไม่มีสินค้าในตะกร้าของคุณ</p>
        <router-link to="/products" class="btn btn-primary">เลือกซื้อสินค้า</router-link>
      </div>

      <!-- Cart Items -->
      <div v-else class="cart-grid">
        <div class="cart-items">
          <div v-for="item in items" :key="item.id" class="cart-item card">
            <div class="cart-item-image">
              <img :src="getImageUrl(item.product)" :alt="item.product?.name" />
            </div>
            
            <div class="cart-item-details">
              <h3 class="cart-item-name">{{ item.product?.name || 'สินค้า' }}</h3>
              <p v-if="item.variant" class="cart-item-variant text-secondary">{{ item.variant }}</p>
              <p class="cart-item-price text-accent">฿{{ formatPrice(item.product?.price || 0) }}</p>
            </div>

            <div class="cart-item-quantity">
              <button class="qty-btn" @click="updateQuantity(item, item.quantity - 1)">-</button>
              <span class="qty-value">{{ item.quantity }}</span>
              <button class="qty-btn" @click="updateQuantity(item, item.quantity + 1)">+</button>
            </div>

            <div class="cart-item-total">
              ฿{{ formatPrice((item.product?.price || 0) * item.quantity) }}
            </div>

            <button class="cart-item-remove" @click="removeItem(item.id)">✕</button>
          </div>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary card">
          <h3 class="summary-title">สรุปคำสั่งซื้อ</h3>
          
          <div class="summary-row">
            <span>จำนวนสินค้า</span>
            <span>{{ itemCount }} ชิ้น</span>
          </div>

          <div class="summary-row">
            <span>ยอดรวม</span>
            <span>฿{{ formatPrice(subtotal) }}</span>
          </div>

          <!-- Coupon Input -->
          <div class="coupon-section">
            <div class="coupon-input-row">
              <input 
                type="text" 
                v-model="couponCode" 
                class="form-input"
                placeholder="รหัสคูปอง"
                :disabled="appliedCoupon"
              />
              <button 
                v-if="!appliedCoupon"
                class="btn btn-secondary"
                @click="applyCoupon"
                :disabled="!couponCode || validatingCoupon"
              >
                {{ validatingCoupon ? '...' : 'ใช้' }}
              </button>
              <button 
                v-else
                class="btn btn-outline"
                @click="removeCoupon"
              >
                ยกเลิก
              </button>
            </div>
            <p v-if="couponError" class="coupon-error">{{ couponError }}</p>
            <p v-if="appliedCoupon" class="coupon-success">
              ✓ ใช้คูปอง {{ appliedCoupon.code }} แล้ว
            </p>
          </div>

          <div v-if="discount > 0" class="summary-row discount">
            <span>ส่วนลด</span>
            <span class="text-accent">-฿{{ formatPrice(discount) }}</span>
          </div>
          
          <div class="summary-row">
            <span>ค่าจัดส่ง</span>
            <span class="text-accent">ฟรี</span>
          </div>
          
          <div class="summary-row total">
            <span>รวมทั้งหมด</span>
            <span class="total-price">฿{{ formatPrice(grandTotal) }}</span>
          </div>

          <button 
            class="btn btn-primary w-full" 
            :disabled="!authStore.isAuthenticated"
            @click="proceedToCheckout"
          >
            {{ authStore.isAuthenticated ? 'ดำเนินการชำระเงิน' : 'กรุณาเข้าสู่ระบบ' }}
          </button>

          <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn btn-secondary w-full mt-md">
            เข้าสู่ระบบ
          </router-link>

          <button v-if="items.length > 0" class="clear-cart-btn mt-lg" @click="clearCart">
            ล้างตะกร้า
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { useCouponStore } from '../stores/coupons'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const couponStore = useCouponStore()
const toastStore = useToastStore()

const couponCode = ref('')
const couponError = ref('')
const validatingCoupon = ref(false)

const items = computed(() => cartStore.items)
const itemCount = computed(() => cartStore.itemCount)
const subtotal = computed(() => cartStore.totalPrice)
const appliedCoupon = computed(() => couponStore.appliedCoupon)
const discount = computed(() => couponStore.discount)
const grandTotal = computed(() => Math.max(0, subtotal.value - discount.value))

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

function getImageUrl(product) {
  if (product?.image) {
    if (product.image.startsWith('http')) return product.image
    return `http://localhost:8000${product.image}`
  }
  return 'https://via.placeholder.com/100x100/1a1a1a/4a7c43?text=M'
}

async function updateQuantity(item, quantity) {
  if (quantity < 1) {
    await removeItem(item.id)
    return
  }
  await cartStore.updateItem(item.id, quantity)
}

async function removeItem(itemId) {
  const result = await cartStore.removeItem(itemId)
  if (result.success) {
    toastStore.success('ลบสินค้าออกจากตะกร้าแล้ว')
  }
}

async function clearCart() {
  const result = await cartStore.clearCart()
  if (result.success) {
    toastStore.success('ล้างตะกร้าแล้ว')
    couponStore.clearCoupon()
  }
}

async function applyCoupon() {
  couponError.value = ''
  validatingCoupon.value = true
  
  const result = await couponStore.validateCoupon(couponCode.value, subtotal.value)
  validatingCoupon.value = false
  
  if (result.success) {
    toastStore.success(`ใช้คูปองสำเร็จ ลด ฿${formatPrice(result.discount)}`)
  } else {
    couponError.value = result.error
  }
}

function removeCoupon() {
  couponStore.clearCoupon()
  couponCode.value = ''
}

function proceedToCheckout() {
  router.push('/checkout')
}

onMounted(() => {
  cartStore.fetchCart()
})
</script>

<style scoped>
.cart-page {
  padding-top: 80px;
  min-height: 100vh;
}

.page-title {
  font-size: var(--font-size-4xl);
}

.cart-empty {
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: var(--space-md);
}

.cart-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: var(--space-xl);
  align-items: start;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr auto auto auto;
  gap: var(--space-lg);
  align-items: center;
  padding: var(--space-lg);
}

.cart-item-image {
  width: 100px;
  height: 100px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--bg-glass);
}

.cart-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cart-item-name {
  font-size: var(--font-size-lg);
  margin-bottom: var(--space-xs);
}

.cart-item-variant {
  font-size: var(--font-size-sm);
}

.cart-item-price {
  font-weight: 600;
}

.cart-item-quantity {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.qty-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  color: var(--text-primary);
  transition: all var(--transition-fast);
}

.qty-btn:hover {
  border-color: var(--color-primary);
  background: var(--color-primary);
}

.qty-value {
  min-width: 30px;
  text-align: center;
}

.cart-item-total {
  font-weight: 600;
  color: var(--color-accent);
  min-width: 100px;
  text-align: right;
}

.cart-item-remove {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.cart-item-remove:hover {
  background: rgba(248, 113, 113, 0.1);
  color: #f87171;
}

.cart-summary {
  position: sticky;
  top: 100px;
  padding: var(--space-xl);
}

.summary-title {
  font-size: var(--font-size-xl);
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-md);
  border-bottom: 1px solid var(--border-subtle);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: var(--space-md);
  color: var(--text-secondary);
}

.summary-row.discount {
  color: var(--color-accent);
}

.summary-row.total {
  margin-top: var(--space-lg);
  padding-top: var(--space-lg);
  border-top: 1px solid var(--border-subtle);
  color: var(--text-primary);
  font-weight: 600;
  margin-bottom: var(--space-xl);
}

.total-price {
  font-size: var(--font-size-xl);
  color: var(--color-accent);
}

.coupon-section {
  margin: var(--space-lg) 0;
  padding: var(--space-md);
  background: var(--bg-glass);
  border-radius: var(--radius-md);
}

.coupon-input-row {
  display: flex;
  gap: var(--space-sm);
}

.coupon-input-row .form-input {
  flex: 1;
}

.coupon-error {
  color: #f87171;
  font-size: var(--font-size-sm);
  margin-top: var(--space-sm);
}

.coupon-success {
  color: var(--color-accent);
  font-size: var(--font-size-sm);
  margin-top: var(--space-sm);
}

.clear-cart-btn {
  width: 100%;
  padding: var(--space-md);
  color: #f87171;
  font-size: var(--font-size-sm);
  text-align: center;
}

.clear-cart-btn:hover {
  text-decoration: underline;
}

@media (max-width: 1024px) {
  .cart-grid {
    grid-template-columns: 1fr;
  }

  .cart-item {
    grid-template-columns: 80px 1fr;
    grid-template-rows: auto auto;
  }

  .cart-item-image {
    width: 80px;
    height: 80px;
    grid-row: span 2;
  }

  .cart-item-quantity,
  .cart-item-total,
  .cart-item-remove {
    grid-column: 2;
  }
}
</style>
