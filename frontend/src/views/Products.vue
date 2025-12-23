<template>
  <div class="products-page">
    <div class="container py-3xl">
      <!-- Page Header -->
      <div class="page-header mb-2xl">
        <h1 class="page-title">สินค้าทั้งหมด</h1>
        <p class="page-desc text-secondary">มัทฉะพรีเมียมคุณภาพสูง นำเข้าจากประเทศญี่ปุ่น</p>
      </div>

      <!-- Search & Filters -->
      <div class="filters-section mb-xl">
        <!-- Search -->
        <div class="search-box">
          <input 
            type="text" 
            v-model="searchQuery" 
            class="form-input search-input"
            placeholder="🔍 ค้นหาสินค้า..."
            @input="debouncedSearch"
          />
        </div>

        <!-- Category Filters -->
        <div class="category-filters">
          <button 
            class="filter-btn" 
            :class="{ active: !selectedCategory }"
            @click="selectedCategory = null"
          >
            ทั้งหมด
          </button>
          <button 
            v-for="category in categories" 
            :key="category.id"
            class="filter-btn"
            :class="{ active: selectedCategory === category.id }"
            @click="selectedCategory = category.id"
          >
            {{ category.name }}
          </button>
        </div>

        <!-- Price & Sort -->
        <div class="price-sort-row">
          <div class="price-filters">
            <input 
              type="number" 
              v-model.number="minPrice" 
              class="form-input price-input"
              placeholder="ราคาต่ำสุด"
              @change="applyFilters"
            />
            <span>-</span>
            <input 
              type="number" 
              v-model.number="maxPrice" 
              class="form-input price-input"
              placeholder="ราคาสูงสุด"
              @change="applyFilters"
            />
          </div>

          <select v-model="sortBy" class="form-input sort-select" @change="applyFilters">
            <option value="">เรียงตาม</option>
            <option value="price_asc">ราคา: ต่ำ → สูง</option>
            <option value="price_desc">ราคา: สูง → ต่ำ</option>
          </select>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="products-loading">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="filteredProducts && filteredProducts.length > 0" class="products-grid grid grid-3">
        <ProductCard 
          v-for="product in filteredProducts" 
          :key="product.id || product._id" 
          :product="product" 
        />
      </div>

      <div v-else class="products-empty card text-center py-3xl">
        <div class="empty-icon">📦</div>
        <h3>ไม่พบสินค้า</h3>
        <p class="text-secondary">ลองค้นหาด้วยคำอื่น หรือล้างตัวกรอง</p>
        <button class="btn btn-secondary mt-md" @click="clearFilters">ล้างตัวกรอง</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useProductStore } from '../stores/products'
import { useCategoryStore } from '../stores/categories'
import ProductCard from '../components/ProductCard.vue'

const productStore = useProductStore()
const categoryStore = useCategoryStore()

const searchQuery = ref('')
const selectedCategory = ref(null)
const minPrice = ref(null)
const maxPrice = ref(null)
const sortBy = ref('')

const products = computed(() => productStore.products || [])
const categories = computed(() => categoryStore.categories || [])
const loading = computed(() => productStore.loading)

const filteredProducts = computed(() => products.value || [])

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

function applyFilters() {
  const filters = {}
  
  if (selectedCategory.value) {
    filters.categoryId = selectedCategory.value
  }
  if (searchQuery.value) {
    filters.search = searchQuery.value
  }
  if (minPrice.value) {
    filters.minPrice = minPrice.value
  }
  if (maxPrice.value) {
    filters.maxPrice = maxPrice.value
  }
  if (sortBy.value) {
    filters.sort = sortBy.value
  }
  
  productStore.fetchProducts(filters)
}

function clearFilters() {
  searchQuery.value = ''
  selectedCategory.value = null
  minPrice.value = null
  maxPrice.value = null
  sortBy.value = ''
  productStore.fetchProducts()
}

watch(selectedCategory, () => {
  applyFilters()
})

onMounted(() => {
  productStore.fetchProducts()
  categoryStore.fetchCategories()
})
</script>

<style scoped>
.products-page {
  padding-top: 80px;
  min-height: 100vh;
}

.page-header {
  text-align: center;
}

.page-title {
  font-size: var(--font-size-4xl);
  margin-bottom: var(--space-sm);
}

.page-desc {
  font-size: var(--font-size-lg);
}

.filters-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-lg);
}

.search-box {
  max-width: 500px;
  margin: 0 auto;
}

.search-input {
  width: 100%;
  padding: var(--space-md) var(--space-lg);
  font-size: var(--font-size-lg);
  text-align: center;
}

.category-filters {
  display: flex;
  gap: var(--space-sm);
  flex-wrap: wrap;
  justify-content: center;
}

.filter-btn {
  padding: var(--space-sm) var(--space-lg);
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  transition: all var(--transition-fast);
}

.filter-btn:hover {
  border-color: var(--color-primary);
  color: var(--text-primary);
}

.filter-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.price-sort-row {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-lg);
  flex-wrap: wrap;
}

.price-filters {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.price-input {
  width: 120px;
  text-align: center;
}

.sort-select {
  min-width: 180px;
}

.products-grid {
  gap: var(--space-xl);
}

.products-loading {
  display: flex;
  justify-content: center;
  padding: var(--space-3xl);
}

.products-empty {
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: var(--space-md);
}

@media (max-width: 768px) {
  .price-sort-row {
    flex-direction: column;
  }
  
  .price-input {
    width: 100px;
  }
}
</style>
