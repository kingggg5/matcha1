<template>
  <div class="admin-category-form">
    <div class="page-header mb-xl">
      <router-link to="/admin/categories" class="back-link">← กลับ</router-link>
      <h1 class="page-title">{{ isEdit ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่ใหม่' }}</h1>
    </div>

    <div class="form-card card">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">ชื่อหมวดหมู่ *</label>
          <input 
            type="text" 
            v-model="form.name" 
            class="form-input" 
            placeholder="เช่น มัทฉะเกรดพรีเมียม"
            required
            @input="generateSlug"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Slug</label>
          <input 
            type="text" 
            v-model="form.slug" 
            class="form-input" 
            placeholder="matcha-premium"
          />
          <small class="form-hint">ใช้สำหรับ URL (จะสร้างอัตโนมัติจากชื่อ)</small>
        </div>

        <div class="form-group">
          <label class="form-label">รายละเอียด</label>
          <textarea 
            v-model="form.description" 
            class="form-input" 
            placeholder="อธิบายรายละเอียดหมวดหมู่..."
            rows="3"
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">URL รูปภาพ</label>
          <input 
            type="text" 
            v-model="form.image" 
            class="form-input" 
            placeholder="https://example.com/image.jpg"
          />
        </div>

        <div v-if="error" class="form-error mt-lg">
          {{ error }}
        </div>

        <div class="form-actions mt-xl">
          <router-link to="/admin/categories" class="btn btn-secondary">ยกเลิก</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'กำลังบันทึก...' : (isEdit ? 'อัพเดท' : 'สร้างหมวดหมู่') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCategoryStore } from '../../stores/categories'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const categoryStore = useCategoryStore()
const toastStore = useToastStore()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  name: '',
  slug: '',
  description: '',
  image: ''
})

const loading = ref(false)
const error = ref('')

function generateSlug() {
  if (!isEdit.value) {
    form.value.slug = form.value.name
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/[\s-]+/g, '-')
      .trim()
  }
}

async function handleSubmit() {
  error.value = ''
  loading.value = true

  let result
  if (isEdit.value) {
    result = await categoryStore.updateCategory(route.params.id, form.value)
  } else {
    result = await categoryStore.createCategory(form.value)
  }

  loading.value = false

  if (result.success) {
    toastStore.success(isEdit.value ? 'อัพเดทหมวดหมู่สำเร็จ' : 'สร้างหมวดหมู่สำเร็จ')
    router.push('/admin/categories')
  } else {
    error.value = result.error
  }
}

onMounted(async () => {
  if (isEdit.value) {
    await categoryStore.fetchCategory(route.params.id)
    const category = categoryStore.category
    if (category) {
      form.value = {
        name: category.name,
        slug: category.slug,
        description: category.description || '',
        image: category.image || ''
      }
    }
  }
})
</script>

<style scoped>
.admin-category-form {
  max-width: 600px;
}

.back-link {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  margin-bottom: var(--space-sm);
  display: inline-block;
}

.back-link:hover {
  color: var(--color-accent);
}

.page-title {
  font-size: var(--font-size-2xl);
}

.form-card {
  padding: var(--space-xl);
}

.form-hint {
  display: block;
  margin-top: var(--space-xs);
  color: var(--text-muted);
  font-size: var(--font-size-xs);
}

.form-error {
  padding: var(--space-md);
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid #f87171;
  border-radius: var(--radius-md);
  color: #f87171;
}

.form-actions {
  display: flex;
  gap: var(--space-md);
  justify-content: flex-end;
  padding-top: var(--space-xl);
  border-top: 1px solid var(--border-subtle);
}
</style>
