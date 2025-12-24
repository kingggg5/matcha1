<template>
  <div class="admin-dashboard">
    <h1 class="page-title mb-2xl">Dashboard</h1>

    <!-- Stats Grid -->
    <div class="stats-grid grid grid-4 mb-2xl">
      <!-- Revenue -->
      <div class="stat-card card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <span class="stat-value text-accent">฿{{ formatPrice(stats.totalRevenue) }}</span>
          <span class="stat-label">รายได้ทั้งหมด</span>
        </div>
      </div>
      
      <!-- Orders -->
      <div class="stat-card card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.totalOrders }}</span>
          <span class="stat-label">คำสั่งซื้อสำเร็จ</span>
        </div>
      </div>
      
      <!-- Pending -->
      <div class="stat-card card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <span class="stat-value text-warning">{{ stats.pendingOrders }}</span>
          <span class="stat-label">รอชำระเงิน</span>
        </div>
      </div>
      
      <!-- Paid -->
      <div class="stat-card card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <span class="stat-value text-accent">{{ stats.paidOrders }}</span>
          <span class="stat-label">ชำระแล้ว</span>
        </div>
      </div>

      <!-- Products -->
      <div class="stat-card card">
        <div class="stat-icon">🍵</div>
        <div class="stat-info">
          <span class="stat-value">{{ productCount }}</span>
          <span class="stat-label">สินค้าทั้งหมด</span>
        </div>
      </div>
      
      <!-- Categories -->
      <div class="stat-card card">
        <div class="stat-icon">📁</div>
        <div class="stat-info">
          <span class="stat-value">{{ categoryCount }}</span>
          <span class="stat-label">หมวดหมู่</span>
        </div>
      </div>

      <!-- Sold Items -->
      <div class="stat-card card">
        <div class="stat-icon">🛒</div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.totalItems }}</span>
          <span class="stat-label">สินค้าขายได้</span>
        </div>
      </div>
    </div>

    <!-- Top Products -->
    <div v-if="stats.productSales && stats.productSales.length > 0" class="section mb-2xl mt-3xl">
      <h2 class="section-title mb-lg">🏆 สินค้าขายดี</h2>
      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>สินค้า</th>
              <th>จำนวนขาย</th>
              <th>รายได้</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(product, index) in stats.productSales.slice(0, 5)" :key="index">
              <td>{{ product.name }}</td>
              <td>{{ product.quantity }} ชิ้น</td>
              <td class="text-accent">฿{{ formatPrice(product.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Daily Sales -->
    <div v-if="stats.dailySales && stats.dailySales.length > 0" class="section mb-2xl mt-3xl">
      <h2 class="section-title mb-lg">📈 ยอดขายรายวัน</h2>
      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>วันที่</th>
              <th>จำนวนออร์เดอร์</th>
              <th>รายได้</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="day in stats.dailySales.slice(0, 7)" :key="day.date">
              <td>{{ day.date }}</td>
              <td>{{ day.orders }}</td>
              <td class="text-accent">฿{{ formatPrice(day.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2 class="section-title mb-lg">การจัดการด่วน</h2>
      <div class="actions-grid grid grid-4">
        <router-link to="/admin/products/new" class="action-card card">
          <span class="action-icon">➕</span>
          <span class="action-text">เพิ่มสินค้า</span>
        </router-link>
        
        <router-link to="/admin/orders" class="action-card card">
          <span class="action-icon">📋</span>
          <span class="action-text">จัดการออร์เดอร์</span>
        </router-link>

        <router-link to="/admin/coupons" class="action-card card">
          <span class="action-icon">🎟️</span>
          <span class="action-text">จัดการคูปอง</span>
        </router-link>
        
        <router-link to="/admin/categories/new" class="action-card card">
          <span class="action-icon">📁</span>
          <span class="action-text">เพิ่มหมวดหมู่</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProductStore } from '../../stores/products'
import { useCategoryStore } from '../../stores/categories'
import { useOrderStore } from '../../stores/orders'

const productStore = useProductStore()
const categoryStore = useCategoryStore()
const orderStore = useOrderStore()

const productCount = ref(0)
const categoryCount = ref(0)
const stats = ref({
  totalRevenue: 0,
  totalOrders: 0,
  totalItems: 0,
  pendingOrders: 0,
  paidOrders: 0,
  productSales: [],
  dailySales: []
})

function formatPrice(price) {
  return new Intl.NumberFormat('th-TH').format(price || 0)
}

onMounted(async () => {
  await productStore.fetchProducts()
  await categoryStore.fetchCategories()
  
  productCount.value = productStore.products.length
  categoryCount.value = categoryStore.categories.length
  
  const salesStats = await orderStore.fetchStats()
  if (salesStats) {
    stats.value = salesStats
  }
})
</script>

<style scoped>
.admin-dashboard {
  max-width: 1200px;
}

.page-title {
  font-size: var(--font-size-3xl);
}

.section-title {
  font-size: var(--font-size-xl);
  margin-top: var(--space-xl);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  padding: var(--space-xl);
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border: 2px solid var(--border-subtle);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-fast);
}

.stat-card:hover .stat-icon {
  border-color: var(--color-primary);
  transform: scale(1.1);
  box-shadow: var(--shadow-glow);
}

.stat-value {
  display: block;
  font-size: var(--font-size-2xl);
  font-weight: 700;
  letter-spacing: -1px;
}

.stat-label {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  white-space: nowrap;
}

.text-warning {
  color: #f59e0b;
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
}

.stats-grid {
  gap: var(--space-xl);
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-xl);
  text-align: center;
  transition: all var(--transition-normal);
}

.action-card:hover {
  border-color: var(--color-primary);
  transform: translateY(-4px);
}

.action-icon {
  font-size: 2rem;
}

.action-text {
  font-weight: 500;
}

@media (max-width: 1024px) {
  .stats-grid.grid-4 {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-xl);
  }
  
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-xl);
  }
}
</style>
