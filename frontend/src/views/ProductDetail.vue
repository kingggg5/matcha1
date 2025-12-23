<template>
  <div class="product-detail-page">
    <div class="container py-3xl">
      <!-- Loading -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-container card text-center py-3xl">
        <h3>ไม่พบสินค้า</h3>
        <p class="text-secondary mb-lg">{{ error }}</p>
        <router-link to="/products" class="btn btn-primary">กลับหน้าสินค้า</router-link>
      </div>

      <!-- Product Detail -->
      <div v-else-if="product" class="product-detail">
        <div class="product-detail-grid">
          <!-- Image -->
          <div class="product-image-section">
            <div class="product-image-main">
              <img :src="imageUrl" :alt="product.name" />
            </div>
          </div>

          <!-- Info -->
          <div class="product-info-section">
            <div class="product-category text-accent mb-sm">
              {{ categoryName }}
            </div>
            
            <h1 class="product-title">{{ product.name }}</h1>
            
            <div class="product-price">
              <template v-if="product.priceMax && product.priceMax !== product.price">
                ฿{{ formatPrice(product.price) }} - {{ formatPrice(product.priceMax) }}
              </template>
              <template v-else>
                ฿{{ formatPrice(product.price) }}
              </template>
            </div>

            <div class="product-stock" :class="product.inStock ? 'in-stock' : 'out-of-stock'">
              {{ product.inStock ? '✓ มีสินค้า พร้อมจัดส่ง' : '✕ สินค้าหมด' }}
            </div>

            <p class="product-description">{{ product.description }}</p>

            <!-- Variants -->
            <div v-if="product.variants && product.variants.length > 0" class="product-variants">
              <label class="variant-label">เลือกขนาด:</label>
              <div class="variant-options">
                <button 
                  v-for="variant in product.variants" 
                  :key="variant"
                  class="variant-btn"
                  :class="{ active: selectedVariant === variant }"
                  @click="selectedVariant = variant"
                >
                  {{ variant }}
                </button>
              </div>
            </div>

            <!-- Quantity -->
            <div class="product-quantity">
              <label class="quantity-label">จำนวน:</label>
              <div class="quantity-control">
                <button class="qty-btn" @click="quantity = Math.max(1, quantity - 1)">-</button>
                <input type="number" v-model.number="quantity" min="1" class="qty-input" />
                <button class="qty-btn" @click="quantity++">+</button>
              </div>
            </div>

            <!-- Actions -->
            <div class="product-actions">
              <button 
                class="btn btn-primary btn-lg"
                :disabled="!product.inStock || addingToCart"
                @click="handleAddToCart"
              >
                {{ addingToCart ? 'กำลังเพิ่ม...' : 'เพิ่มลงตะกร้า' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProductStore } from '../stores/products'
import { useCategoryStore } from '../stores/categories'
import { useCartStore } from '../stores/cart'
import { useToastStore } from '../stores/toast'

const route = useRoute()
const productStore = useProductStore()
const categoryStore = useCategoryStore()
const cartStore = useCartStore()
const toastStore = useToastStore()

const quantity = ref(1)
const selectedVariant = ref('')
const addingToCart = ref(false)

const product = computed(() => productStore.product)
const loading = computed(() => productStore.loading)
const error = computed(() => productStore.error)

const categoryName = computed(() => {
  if (!product.value?.categoryId) return 'มัทฉะ'
  const category = categoryStore.categories.find(c => c.id === product.value.categoryId)
  return category?.name || 'มัทฉะ'
})

const imageUrl = computed(() => {
  if (product.value?.image) {
    if (product.value.image.startsWith('http')) {
      return product.value.image
    }
    return `http://localhost:8000${product.value.image}`
  }
  return 'https://via.placeholder.com/600x600/1a1a1a/4a7c43?text=Matcha'
})

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

async function handleAddToCart() {
  if (!product.value?.inStock) return
  
  addingToCart.value = true
  const result = await cartStore.addItem(product.value, quantity.value, selectedVariant.value)
  addingToCart.value = false
  
  if (result.success) {
    toastStore.success(`เพิ่ม ${product.value.name} ลงตะกร้าแล้ว`)
  } else {
    toastStore.error(result.error || 'ไม่สามารถเพิ่มสินค้าได้')
  }
}

onMounted(() => {
  productStore.fetchProduct(route.params.id)
  categoryStore.fetchCategories()
})

watch(() => route.params.id, (id) => {
  if (id) {
    productStore.fetchProduct(id)
    quantity.value = 1
    selectedVariant.value = ''
  }
})

watch(product, (p) => {
  if (p?.variants?.length > 0) {
    selectedVariant.value = p.variants[0]
  }
})
</script>

<style scoped>
.product-detail-page {
  padding-top: 80px;
  min-height: 100vh;
}

.loading-container,
.error-container {
  max-width: 400px;
  margin: 0 auto;
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: var(--space-3xl);
}

.product-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-3xl);
}

.product-image-main {
  aspect-ratio: 1;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.product-image-main img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-category {
  font-size: var(--font-size-sm);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.product-title {
  font-size: var(--font-size-4xl);
  margin-bottom: var(--space-md);
}

.product-price {
  font-size: var(--font-size-3xl);
  font-weight: 600;
  color: var(--color-accent);
  margin-bottom: var(--space-md);
}

.product-stock {
  font-size: var(--font-size-sm);
  margin-bottom: var(--space-xl);
}

.product-stock.in-stock {
  color: var(--color-accent);
}

.product-stock.out-of-stock {
  color: #f87171;
}

.product-description {
  color: var(--text-secondary);
  line-height: 1.8;
  margin-bottom: var(--space-xl);
}

.product-variants {
  margin-bottom: var(--space-xl);
}

.variant-label,
.quantity-label {
  display: block;
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin-bottom: var(--space-sm);
}

.variant-options {
  display: flex;
  gap: var(--space-sm);
  flex-wrap: wrap;
}

.variant-btn {
  padding: var(--space-sm) var(--space-lg);
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.variant-btn:hover {
  border-color: var(--color-primary);
}

.variant-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.product-quantity {
  margin-bottom: var(--space-xl);
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.qty-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  color: var(--text-primary);
  font-size: 1.25rem;
  transition: all var(--transition-fast);
}

.qty-btn:hover {
  border-color: var(--color-primary);
  background: var(--color-primary);
}

.qty-input {
  width: 60px;
  height: 40px;
  text-align: center;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  color: var(--text-primary);
}

.product-actions .btn {
  min-width: 200px;
}

@media (max-width: 1024px) {
  .product-detail-grid {
    grid-template-columns: 1fr;
    gap: var(--space-xl);
  }
}
</style>
