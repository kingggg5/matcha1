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
                <label class="form-label">ราคาพื้นฐาน (฿) *</label>
                <input 
                  type="number" 
                  v-model.number="form.price" 
                  class="form-input" 
                  placeholder="0"
                  min="0"
                  required
                />
                <small class="form-hint">ราคาเมื่อไม่มี variant หรือราคาเริ่มต้น</small>
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

            <!-- Variant Prices Section -->
            <div class="form-group">
              <label class="form-label">ตัวเลือก/ขนาด พร้อมราคา</label>
              
              <div class="variants-list">
                <div v-for="(variant, index) in form.variants" :key="index" class="variant-row">
                  <input 
                    type="text" 
                    v-model="variant.name" 
                    class="form-input variant-name" 
                    placeholder="ชื่อ เช่น 40g, 100g"
                  />
                  <input 
                    type="number" 
                    v-model.number="variant.price" 
                    class="form-input variant-price" 
                    placeholder="ราคา"
                    min="0"
                  />
                  <button type="button" class="btn-icon btn-remove" @click="removeVariant(index)">
                    ✕
                  </button>
                </div>
              </div>
              
              <button type="button" class="btn btn-secondary btn-sm mt-md" @click="addVariant">
                + เพิ่มตัวเลือก
              </button>
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
import { ref, computed, onMounted } from 'vue'
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
  image: '',
  categoryId: '',
  variants: [],
  inStock: true
})

const loading = ref(false)
const error = ref('')

function addVariant() {
  form.value.variants.push({ name: '', price: 0 })
}

function removeVariant(index) {
  form.value.variants.splice(index, 1)
}

async function handleSubmit() {
  error.value = ''
  loading.value = true

  // Filter out empty variants and calculate priceMax
  const variants = form.value.variants.filter(v => v.name && v.name.trim())
  const prices = variants.map(v => v.price || form.value.price)
  const priceMax = prices.length > 0 ? Math.max(...prices, form.value.price) : form.value.price

  const data = { 
    ...form.value,
    variants,
    priceMax
  }

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
      // Convert old string variants to new object format if needed
      let variants = product.variants || []
      if (variants.length > 0 && typeof variants[0] === 'string') {
        // Old format: ['40g', '100g'] -> convert to new format
        variants = variants.map(name => ({ name, price: product.price }))
      }
      
      form.value = {
        name: product.name,
        description: product.description,
        price: product.price,
        image: product.image,
        categoryId: product.categoryId,
        variants: variants,
        inStock: product.inStock
      }
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

.form-hint {
  color: var(--text-muted);
  font-size: var(--font-size-xs);
  margin-top: var(--space-xs);
  display: block;
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

/* Variant Styles */
.variants-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
}

.variant-row {
  display: flex;
  gap: var(--space-sm);
  align-items: center;
}

.variant-name {
  flex: 2;
}

.variant-price {
  flex: 1;
  min-width: 100px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  background: var(--bg-glass);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-icon:hover {
  background: rgba(248, 113, 113, 0.2);
  border-color: #f87171;
  color: #f87171;
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
  
  .variant-row {
    flex-wrap: wrap;
  }
  
  .variant-name {
    flex: 1 1 100%;
  }
  
  .variant-price {
    flex: 1;
  }
}
</style>
