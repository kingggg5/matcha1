<template>
  <div class="product-card" @click="$router.push(`/products/${productId}`)">
    <!-- Wishlist Button -->
    <button 
      class="wishlist-btn" 
      :class="{ active: isInWishlist }"
      @click.stop="toggleWishlist"
      :title="isInWishlist ? 'ลบจากรายการโปรด' : 'เพิ่มในรายการโปรด'"
    >
      {{ isInWishlist ? '❤️' : '🤍' }}
    </button>

    <div class="product-card-image">
      <img :src="imageUrl" :alt="product.name" loading="lazy" />
    </div>
    
    <div class="product-card-body">
      <h3 class="product-card-title">{{ product.name }}</h3>
      
      <div v-if="product.variants && product.variants.length > 0" class="product-card-variants">
        <span 
          v-for="variant in product.variants.slice(0, 3)" 
          :key="getVariantKey(variant)"
          class="product-card-variant"
        >
          {{ getVariantName(variant) }}
        </span>
        <span v-if="product.variants.length > 3" class="product-card-variant">
          +{{ product.variants.length - 3 }}
        </span>
      </div>

      <div class="product-card-footer">
        <div class="product-card-price">
          <template v-if="priceRange.max && priceRange.max !== priceRange.min">
            <span class="price-range">฿{{ formatPrice(priceRange.min) }} - {{ formatPrice(priceRange.max) }}</span>
          </template>
          <template v-else>
            ฿{{ formatPrice(priceRange.min) }}
          </template>
        </div>
        
        <span 
          class="product-card-stock" 
          :class="product.inStock ? 'in-stock' : 'out-of-stock'"
        >
          {{ product.inStock ? 'มีสินค้า' : 'สินค้าหมด' }}
        </span>
      </div>

      <!-- Add to Cart Button -->
      <button 
        class="btn btn-primary btn-sm add-to-cart-btn"
        :class="{ disabled: !product.inStock }"
        @click.stop="handleAddToCart"
        :disabled="loading || !product.inStock"
      >
        {{ loading ? 'กำลังเพิ่ม...' : (product.inStock ? 'เพิ่มลงตะกร้า' : 'สินค้าหมด') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCartStore } from '../stores/cart'
import { useWishlistStore } from '../stores/wishlist'
import { useToastStore } from '../stores/toast'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
const toastStore = useToastStore()
const loading = ref(false)

// Support both id and _id from MongoDB
const productId = computed(() => props.product.id || props.product._id)
const isInWishlist = computed(() => wishlistStore.isInWishlist(productId.value))

// Calculate price range from variants
const priceRange = computed(() => {
  const variants = props.product.variants || []
  if (variants.length === 0) {
    return { min: props.product.price, max: props.product.price }
  }
  
  const prices = variants.map(v => {
    if (typeof v === 'object' && v.price) return v.price
    return props.product.price
  })
  
  return {
    min: Math.min(...prices),
    max: Math.max(...prices)
  }
})

const imageUrl = computed(() => {
  if (props.product.image) {
    if (props.product.image.startsWith('http')) {
      return props.product.image
    }
    return `http://localhost:8000${props.product.image}`
  }
  return 'https://via.placeholder.com/400x400/1a1a1a/4a7c43?text=Matcha'
})

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

function getVariantName(variant) {
  if (typeof variant === 'object' && variant.name) {
    return variant.name
  }
  return variant
}

function getVariantKey(variant) {
  if (typeof variant === 'object' && variant.name) {
    return variant.name
  }
  return variant
}

async function handleAddToCart() {
  if (!props.product.inStock) return
  
  // If product has variants, redirect to detail page to select
  if (props.product.variants && props.product.variants.length > 0) {
    // Use the first variant
    const firstVariant = props.product.variants[0]
    loading.value = true
    const result = await cartStore.addItem({ ...props.product, id: productId.value }, 1, firstVariant)
    loading.value = false
    
    if (result.success) {
      const variantName = typeof firstVariant === 'object' ? firstVariant.name : firstVariant
      toastStore.success(`เพิ่ม ${props.product.name} (${variantName}) ลงตะกร้าแล้ว`)
    } else {
      toastStore.error(result.error || 'ไม่สามารถเพิ่มสินค้าได้')
    }
  } else {
    loading.value = true
    const result = await cartStore.addItem({ ...props.product, id: productId.value }, 1)
    loading.value = false
    
    if (result.success) {
      toastStore.success(`เพิ่ม ${props.product.name} ลงตะกร้าแล้ว`)
    } else {
      toastStore.error(result.error || 'ไม่สามารถเพิ่มสินค้าได้')
    }
  }
}

async function toggleWishlist() {
  const result = await wishlistStore.toggleProduct(productId.value)
  if (result.success) {
    if (wishlistStore.isInWishlist(productId.value)) {
      toastStore.success('เพิ่มในรายการโปรดแล้ว')
    } else {
      toastStore.info('ลบออกจากรายการโปรดแล้ว')
    }
  }
}
</script>

<style scoped>
.product-card {
  cursor: pointer;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

.wishlist-btn {
  position: absolute;
  top: var(--space-sm);
  right: var(--space-sm);
  z-index: 10;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-full);
  font-size: 1.1rem;
  transition: all var(--transition-fast);
  opacity: 0.8;
}

.wishlist-btn:hover {
  opacity: 1;
  transform: scale(1.1);
}

.wishlist-btn.active {
  opacity: 1;
  border-color: #f87171;
}

.product-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-md);
}

.add-to-cart-btn {
  width: 100%;
  margin-top: auto;
}

.add-to-cart-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--bg-glass);
  border-color: var(--border-subtle);
}

.price-range {
  font-size: var(--font-size-base);
}
</style>
