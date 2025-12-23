<template>
  <div class="admin-products">
    <div class="page-header mb-xl">
      <h1 class="page-title">จัดการสินค้า</h1>
      <router-link to="/admin/products/new" class="btn btn-primary">
        ➕ เพิ่มสินค้า
      </router-link>
    </div>

    <!-- Products Table -->
    <div class="card">
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="products.length === 0" class="empty-state text-center py-3xl">
        <p class="text-secondary">ยังไม่มีสินค้า</p>
        <router-link to="/admin/products/new" class="btn btn-primary mt-lg">เพิ่มสินค้าแรก</router-link>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>รูปภาพ</th>
            <th>ชื่อสินค้า</th>
            <th>ราคา</th>
            <th>สถานะ</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="table-image">
                <img :src="getImageUrl(product)" :alt="product.name" />
              </div>
            </td>
            <td>
              <div class="product-name">{{ product.name }}</div>
              <div class="product-desc text-muted">{{ truncate(product.description, 50) }}</div>
            </td>
            <td>
              <span class="text-accent">฿{{ formatPrice(product.price) }}</span>
              <span v-if="product.priceMax !== product.price" class="text-muted">
                - {{ formatPrice(product.priceMax) }}
              </span>
            </td>
            <td>
              <span class="badge" :class="product.inStock ? 'badge-success' : 'badge-error'">
                {{ product.inStock ? 'มีสินค้า' : 'หมด' }}
              </span>
            </td>
            <td>
              <div class="table-actions">
                <router-link :to="`/admin/products/${product.id}/edit`" class="action-btn edit">
                  ✏️
                </router-link>
                <button class="action-btn delete" @click="confirmDelete(product)">
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
        <p class="text-secondary">คุณต้องการลบสินค้า "{{ productToDelete?.name }}" ใช่หรือไม่?</p>
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
import { useProductStore } from '../../stores/products'
import { useToastStore } from '../../stores/toast'

const productStore = useProductStore()
const toastStore = useToastStore()

const deleteModal = ref(false)
const productToDelete = ref(null)
const deleting = ref(false)

const products = computed(() => productStore.products)
const loading = computed(() => productStore.loading)

function getImageUrl(product) {
  if (product.image) {
    if (product.image.startsWith('http')) return product.image
    return `http://localhost:8000${product.image}`
  }
  return 'https://via.placeholder.com/50x50/1a1a1a/4a7c43?text=M'
}

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price)
}

function truncate(text, length) {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

function confirmDelete(product) {
  productToDelete.value = product
  deleteModal.value = true
}

async function handleDelete() {
  if (!productToDelete.value) return
  
  deleting.value = true
  const result = await productStore.deleteProduct(productToDelete.value.id)
  deleting.value = false
  
  if (result.success) {
    toastStore.success('ลบสินค้าสำเร็จ')
    deleteModal.value = false
  } else {
    toastStore.error(result.error || 'ไม่สามารถลบสินค้าได้')
  }
}

onMounted(() => {
  productStore.fetchProducts()
})
</script>

<style scoped>
.admin-products {
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
  text-transform: uppercase;
}

.data-table tr:hover {
  background: var(--bg-glass);
}

.table-image {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: var(--bg-glass);
}

.table-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-name {
  font-weight: 500;
  margin-bottom: 2px;
}

.product-desc {
  font-size: var(--font-size-sm);
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
