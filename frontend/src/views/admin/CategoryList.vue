<template>
  <div class="admin-categories">
    <div class="page-header mb-xl">
      <h1 class="page-title">จัดการหมวดหมู่</h1>
      <router-link to="/admin/categories/new" class="btn btn-primary">
        ➕ เพิ่มหมวดหมู่
      </router-link>
    </div>

    <!-- Categories Table -->
    <div class="card">
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="categories.length === 0" class="empty-state text-center py-3xl">
        <p class="text-secondary">ยังไม่มีหมวดหมู่</p>
        <router-link to="/admin/categories/new" class="btn btn-primary mt-lg">เพิ่มหมวดหมู่แรก</router-link>
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ชื่อหมวดหมู่</th>
            <th>Slug</th>
            <th>รายละเอียด</th>
            <th>การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>
              <span class="category-name">{{ category.name }}</span>
            </td>
            <td>
              <code class="category-slug">{{ category.slug }}</code>
            </td>
            <td>
              <span class="text-muted">{{ truncate(category.description, 50) }}</span>
            </td>
            <td>
              <div class="table-actions">
                <router-link :to="`/admin/categories/${category.id}/edit`" class="action-btn edit">
                  ✏️
                </router-link>
                <button class="action-btn delete" @click="confirmDelete(category)">
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
        <p class="text-secondary">คุณต้องการลบหมวดหมู่ "{{ categoryToDelete?.name }}" ใช่หรือไม่?</p>
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
import { useCategoryStore } from '../../stores/categories'
import { useToastStore } from '../../stores/toast'

const categoryStore = useCategoryStore()
const toastStore = useToastStore()

const deleteModal = ref(false)
const categoryToDelete = ref(null)
const deleting = ref(false)

const categories = computed(() => categoryStore.categories)
const loading = computed(() => categoryStore.loading)

function truncate(text, length) {
  if (!text) return '-'
  return text.length > length ? text.substring(0, length) + '...' : text
}

function confirmDelete(category) {
  categoryToDelete.value = category
  deleteModal.value = true
}

async function handleDelete() {
  if (!categoryToDelete.value) return
  
  deleting.value = true
  const result = await categoryStore.deleteCategory(categoryToDelete.value.id)
  deleting.value = false
  
  if (result.success) {
    toastStore.success('ลบหมวดหมู่สำเร็จ')
    deleteModal.value = false
  } else {
    toastStore.error(result.error || 'ไม่สามารถลบหมวดหมู่ได้')
  }
}

onMounted(() => {
  categoryStore.fetchCategories()
})
</script>

<style scoped>
.admin-categories {
  max-width: 1000px;
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

.category-name {
  font-weight: 500;
}

.category-slug {
  background: var(--bg-glass);
  padding: 2px 8px;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  color: var(--color-accent);
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
