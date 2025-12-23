<template>
  <div class="wishlist-page">
    <div class="container py-3xl">
      <div class="page-header mb-2xl">
        <h1 class="page-title">❤️ รายการโปรดของฉัน</h1>
        <p class="page-desc text-secondary">สินค้าที่คุณบันทึกไว้</p>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && products.length === 0" class="empty-state card text-center py-3xl">
        <div class="empty-icon">💚</div>
        <h3>ยังไม่มีรายการโปรด</h3>
        <p class="text-secondary mb-lg">กดปุ่มหัวใจ ❤️ บนสินค้าที่ชอบเพื่อบันทึกไว้ดูภายหลัง</p>
        <router-link to="/products" class="btn btn-primary">เลือกดูสินค้า</router-link>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <!-- Products Grid -->
      <div v-else class="wishlist-grid grid grid-4">
        <div v-for="product in products" :key="product.id" class="wishlist-item card">
          <button class="remove-btn" @click="removeFromWishlist(product.id)" title="ลบออกจากรายการโปรด">
            ✕
          </button>
          
          <router-link :to="`/products/${product.id}`" class="product-link">
            <div class="product-image">
              <img :src="getImageUrl(product.image)" :alt="product.name" loading="lazy" />
            </div>
            <div class="product-info">
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-price text-accent">฿{{ formatPrice(product.price) }}</p>
            </div>
          </router-link>

          <button 
            class="btn btn-primary btn-sm w-full mt-md"
            :disabled="!product.inStock"
            @click="addToCart(product)"
          >
            {{ product.inStock ? 'เพิ่มลงตะกร้า' : 'สินค้าหมด' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useWishlistStore } from '../stores/wishlist'
import { useCartStore } from '../stores/cart'
import { useToastStore } from '../stores/toast'
import api from '../services/api'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()
const toastStore = useToastStore()

const loading = ref(true)
const products = ref([])

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

function getImageUrl(image) {
  if (!image) return 'https://via.placeholder.com/400x400/1a1a1a/4a7c43?text=Matcha'
  if (image.startsWith('http')) return image
  return `http://localhost:8000${image}`
}

async function loadProducts() {
  loading.value = true
  
  // Load wishlist product IDs
  wishlistStore.loadLocal()
  
  // Fetch product details
  const productIds = wishlistStore.productIds
  const productList = []
  
  for (const id of productIds) {
    try {
      const { data } = await api.get(`/products/${id}`)
      if (data.product) {
        productList.push(data.product)
      }
    } catch (err) {
      // Product might have been deleted
    }
  }
  
  products.value = productList
  loading.value = false
}

async function removeFromWishlist(productId) {
  await wishlistStore.removeProduct(productId)
  products.value = products.value.filter(p => p.id !== productId)
  toastStore.info('ลบออกจากรายการโปรดแล้ว')
}

async function addToCart(product) {
  const result = await cartStore.addItem(product, 1)
  if (result.success) {
    toastStore.success(`เพิ่ม ${product.name} ลงตะกร้าแล้ว`)
  }
}

onMounted(() => {
  loadProducts()
})
</script>

<style scoped>
.wishlist-page {
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

.wishlist-grid {
  gap: var(--space-lg);
}

.wishlist-item {
  position: relative;
  padding: var(--space-md);
}

.remove-btn {
  position: absolute;
  top: var(--space-sm);
  right: var(--space-sm);
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.5);
  color: white;
  border-radius: var(--radius-full);
  font-size: 0.8rem;
  z-index: 5;
  opacity: 0.7;
  transition: all var(--transition-fast);
}

.remove-btn:hover {
  opacity: 1;
  background: #f87171;
}

.product-link {
  display: block;
}

.product-image {
  aspect-ratio: 1;
  border-radius: var(--radius-md);
  overflow: hidden;
  margin-bottom: var(--space-md);
  background: var(--bg-glass);
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform var(--transition-normal);
}

.product-link:hover .product-image img {
  transform: scale(1.05);
}

.product-name {
  font-size: var(--font-size-base);
  margin-bottom: var(--space-xs);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-clamp: 2;
  overflow: hidden;
}

.product-price {
  font-weight: 600;
}

@media (max-width: 1024px) {
  .wishlist-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .wishlist-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
