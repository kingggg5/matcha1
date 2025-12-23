<template>
  <div class="admin-product-form">
    <div class="page-header mb-xl">
      <router-link to="/admin/products" class="back-link">← กลับ</router-link>
      <h1 class="page-title">{{ isEdit ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' }}</h1>
    </div>

    <div class="form-card card">
      <form @submit.prevent="handleSubmit">
        <div class="form-grid">
          <!-- Basic Info -->
          <div class="form-section">
            <h3 class="section-title mb-lg">ข้อมูลพื้นฐาน</h3>
            
            <div class="form-group">
              <label class="form-label">ชื่อสินค้า *</label>
              <input 
                type="text" 
                v-model="form.name" 
                class="form-input" 
                placeholder="เช่น Matcha Excellent"
                required
              />
            </div>

            <div class="form-group">
              <label class="form-label">รายละเอียด</label>
              <textarea 
                v-model="form.description" 
                class="form-input" 
                placeholder="อธิบายรายละเอียดสินค้า..."
                rows="4"
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">ราคา (฿) *</label>
                <input 
                  type="number" 
                  v-model.number="form.price" 
                  class="form-input" 
                  placeholder="0"
                  min="0"
                  required
                />
              </div>
              <div class="form-group">
                <label class="form-label">ราคาสูงสุด (฿)</label>
                <input 
                  type="number" 
                  v-model.number="form.priceMax" 
                  class="form-input" 
                  placeholder="0"
                  min="0"
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">หมวดหมู่</label>
              <select v-model="form.categoryId" class="form-input">
                <option value="">-- เลือกหมวดหมู่ --</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Image & Variants -->
          <div class="form-section">
            <h3 class="section-title mb-lg">รูปภาพและตัวเลือก</h3>
            
            <div class="form-group">
              <label class="form-label">URL รูปภาพ</label>
              <input 
                type="text" 
                v-model="form.image" 
                class="form-input" 
                placeholder="https://example.com/image.jpg"
              />
              <div v-if="form.image" class="image-preview mt-md">
                <img :src="form.image" alt="Preview" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">ตัวเลือก/ขนาด (คั่นด้วย comma)</label>
              <input 
                type="text" 
                v-model="variantsInput" 
                class="form-input" 
                placeholder="เช่น 40g, 100g, 500g"
              />
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.inStock" />
                <span>มีสินค้า</span>
              </label>
            </div>
          </div>
        </div>

        <div v-if="error" class="form-error mt-lg">
          {{ error }}
        </div>

        <div class="form-actions mt-xl">
          <router-link to="/admin/products" class="btn btn-secondary">ยกเลิก</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'กำลังบันทึก...' : (isEdit ? 'อัพเดท' : 'สร้างสินค้า') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '../../stores/products'
import { useCategoryStore } from '../../stores/categories'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const categoryStore = useCategoryStore()
const toastStore = useToastStore()

const isEdit = computed(() => !!route.params.id)
const categories = computed(() => categoryStore.categories)

const form = ref({
  name: '',
  description: '',
  price: 0,
  priceMax: 0,
  image: '',
  categoryId: '',
  variants: [],
  inStock: true
})

const variantsInput = ref('')
const loading = ref(false)
const error = ref('')

watch(variantsInput, (val) => {
  form.value.variants = val.split(',').map(v => v.trim()).filter(v => v)
})

async function handleSubmit() {
  error.value = ''
  loading.value = true

  const data = { ...form.value }
  if (!data.priceMax) data.priceMax = data.price

  let result
  if (isEdit.value) {
    result = await productStore.updateProduct(route.params.id, data)
  } else {
    result = await productStore.createProduct(data)
  }

  loading.value = false

  if (result.success) {
    toastStore.success(isEdit.value ? 'อัพเดทสินค้าสำเร็จ' : 'สร้างสินค้าสำเร็จ')
    router.push('/admin/products')
  } else {
    error.value = result.error
  }
}

onMounted(async () => {
  await categoryStore.fetchCategories()
  
  if (isEdit.value) {
    await productStore.fetchProduct(route.params.id)
    const product = productStore.product
    if (product) {
      form.value = {
        name: product.name,
        description: product.description,
        price: product.price,
        priceMax: product.priceMax,
        image: product.image,
        categoryId: product.categoryId,
        variants: product.variants || [],
        inStock: product.inStock
      }
      variantsInput.value = product.variants?.join(', ') || ''
    }
  }
})
</script>

<style scoped>
.admin-product-form {
  max-width: 900px;
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

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-2xl);
}

.section-title {
  font-size: var(--font-size-lg);
  border-bottom: 1px solid var(--border-subtle);
  padding-bottom: var(--space-md);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-md);
}

.image-preview {
  width: 150px;
  height: 150px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary);
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
