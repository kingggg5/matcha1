<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="container hero-content">
        <div class="hero-text animate-fade-in">
          <span class="hero-badge">Premium Japanese Matcha</span>
          <h1 class="hero-title">
            สัมผัสรสชาติแท้<br />
            <span class="text-gradient">มัทฉะญี่ปุ่น</span>
          </h1>
          <p class="hero-desc">
            คัดสรรมัทฉะคุณภาพสูงจากแหล่งผลิตชั้นนำของประเทศญี่ปุ่น 
            เพื่อประสบการณ์การดื่มที่พิเศษสุดสำหรับคุณ
          </p>
          <div class="hero-actions">
            <router-link to="/products" class="btn btn-primary btn-lg">
              ดูสินค้าทั้งหมด
            </router-link>
            <a href="#featured" class="btn btn-secondary btn-lg">
              สินค้าแนะนำ
            </a>
          </div>
        </div>
        <div class="hero-image animate-fade-in">
          <div class="hero-image-glow"></div>
          <div class="hero-product-showcase">
            <span class="showcase-icon">🍵</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features py-3xl">
      <div class="container">
        <div class="features-grid grid grid-4">
          <div class="feature-card card card-glass">
            <div class="feature-icon">🇯🇵</div>
            <h3 class="feature-title">นำเข้าจากญี่ปุ่น</h3>
            <p class="feature-desc">คัดสรรจากแหล่งผลิตชื่อดัง</p>
          </div>
          <div class="feature-card card card-glass">
            <div class="feature-icon">✨</div>
            <h3 class="feature-title">คุณภาพพรีเมียม</h3>
            <p class="feature-desc">มาตรฐานระดับสากล</p>
          </div>
          <div class="feature-card card card-glass">
            <div class="feature-icon">🚚</div>
            <h3 class="feature-title">จัดส่งรวดเร็ว</h3>
            <p class="feature-desc">ส่งถึงมือภายใน 1-3 วัน</p>
          </div>
          <div class="feature-card card card-glass">
            <div class="feature-icon">💚</div>
            <h3 class="feature-title">รับประกันคุณภาพ</h3>
            <p class="feature-desc">คืนเงินได้ภายใน 7 วัน</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="featured-section py-3xl">
      <div class="container">
        <div class="section-header text-center mb-xl">
          <span class="section-badge">Best Sellers</span>
          <h2 class="section-title">สินค้าขายดี</h2>
          <p class="section-desc text-secondary">มัทฉะคุณภาพที่ได้รับความนิยมสูงสุด</p>
        </div>

        <div v-if="loading" class="products-loading">
          <div class="loading-spinner"></div>
        </div>

        <div v-else-if="products && products.length > 0" class="products-grid grid grid-3">
          <ProductCard 
            v-for="product in products" 
            :key="product.id || product._id" 
            :product="product" 
          />
        </div>

        <div v-else class="products-empty text-center py-2xl">
          <p class="text-secondary">ยังไม่มีสินค้า</p>
        </div>

        <div class="section-action text-center mt-2xl">
          <router-link to="/products" class="btn btn-outline">
            ดูสินค้าทั้งหมด →
          </router-link>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-3xl">
      <div class="container">
        <div class="cta-card card">
          <div class="cta-content">
            <h2 class="cta-title">พร้อมสัมผัสมัทฉะพรีเมียม?</h2>
            <p class="cta-desc text-secondary">
              สมัครสมาชิกวันนี้รับส่วนลด 10% สำหรับการสั่งซื้อครั้งแรก
            </p>
            <router-link to="/register" class="btn btn-primary btn-lg">
              สมัครสมาชิก
            </router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/ProductCard.vue'

const productStore = useProductStore()

const products = computed(() => productStore.products)
const loading = computed(() => productStore.loading)

onMounted(() => {
  productStore.fetchProducts({ limit: 6 })
})
</script>

<style scoped>
.home-page {
  padding-top: 80px;
}

/* Hero Section */
.hero {
  position: relative;
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse at 70% 30%, rgba(45, 90, 39, 0.2) 0%, transparent 50%),
    radial-gradient(ellipse at 30% 70%, rgba(45, 90, 39, 0.15) 0%, transparent 40%);
}

.hero-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-3xl);
  align-items: center;
  position: relative;
  z-index: 1;
}

.hero-badge {
  display: inline-block;
  padding: var(--space-sm) var(--space-lg);
  background: var(--bg-glass);
  border: 1px solid var(--border-accent);
  border-radius: var(--radius-full);
  font-size: var(--font-size-sm);
  color: var(--color-accent);
  margin-bottom: var(--space-lg);
}

.hero-title {
  font-size: clamp(2.5rem, 5vw, 4rem);
  line-height: 1.1;
  margin-bottom: var(--space-lg);
}

.hero-desc {
  font-size: var(--font-size-lg);
  color: var(--text-secondary);
  line-height: 1.8;
  margin-bottom: var(--space-xl);
  max-width: 500px;
}

.hero-actions {
  display: flex;
  gap: var(--space-md);
  flex-wrap: wrap;
}

.hero-image {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-image-glow {
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, var(--color-primary-glow) 0%, transparent 70%);
  filter: blur(60px);
}

.hero-product-showcase {
  position: relative;
  width: 300px;
  height: 300px;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.showcase-icon {
  font-size: 8rem;
}

/* Features */
.feature-card {
  text-align: center;
  padding: var(--space-xl);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: var(--space-md);
}

.feature-title {
  font-size: var(--font-size-lg);
  margin-bottom: var(--space-sm);
}

.feature-desc {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

/* Featured Section */
.section-badge {
  display: inline-block;
  padding: var(--space-xs) var(--space-md);
  background: var(--color-primary);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: var(--space-md);
}

.section-title {
  font-size: var(--font-size-4xl);
  margin-bottom: var(--space-sm);
}

.section-desc {
  font-size: var(--font-size-lg);
}

.products-grid {
  gap: var(--space-xl);
}

.products-loading {
  display: flex;
  justify-content: center;
  padding: var(--space-3xl);
}

/* CTA Section */
.cta-card {
  padding: var(--space-3xl);
  text-align: center;
  background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-secondary-dark) 100%);
  border-color: var(--color-primary);
}

.cta-title {
  font-size: var(--font-size-3xl);
  margin-bottom: var(--space-md);
}

.cta-desc {
  font-size: var(--font-size-lg);
  margin-bottom: var(--space-xl);
}

@media (max-width: 1024px) {
  .hero-content {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .hero-desc {
    margin: 0 auto var(--space-xl);
  }

  .hero-actions {
    justify-content: center;
  }

  .hero-image {
    display: none;
  }
}
</style>
