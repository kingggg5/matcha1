<template>
  <nav class="navbar">
    <div class="navbar-container container">
      <!-- Logo -->
      <router-link to="/" class="navbar-logo">
        <span class="logo-icon">🍵</span>
        <span class="logo-text">MATCHA<span class="text-accent">KING</span></span>
      </router-link>

      <!-- Navigation Links -->
      <div class="navbar-links" :class="{ active: menuOpen }">
        <router-link to="/" class="nav-link" @click="menuOpen = false">หน้าหลัก</router-link>
        <router-link to="/products" class="nav-link" @click="menuOpen = false">สินค้า</router-link>
        <router-link v-if="authStore.isAdmin" to="/admin" class="nav-link admin-link" @click="menuOpen = false">
          <span class="admin-icon">⚙️</span> Admin
        </router-link>
      </div>

      <!-- Right Actions -->
      <div class="navbar-actions">
        <!-- Wishlist Button -->
        <router-link to="/wishlist" class="action-btn wishlist-btn" title="รายการโปรด">
          <span class="action-icon">❤️</span>
        </router-link>

        <!-- Cart -->
        <router-link to="/cart" class="cart-btn">
          <span class="cart-icon">🛒</span>
          <span v-if="cartStore.itemCount > 0" class="cart-badge">{{ cartStore.itemCount }}</span>
        </router-link>

        <!-- Auth -->
        <template v-if="authStore.isAuthenticated">
          <div class="user-menu">
            <button class="user-btn" @click="userMenuOpen = !userMenuOpen">
              <span class="user-avatar">{{ authStore.user?.name?.charAt(0) || 'U' }}</span>
            </button>
            <div v-if="userMenuOpen" class="user-dropdown">
              <div class="user-info">
                <span class="user-name">{{ authStore.user?.name }}</span>
                <span class="user-email">{{ authStore.user?.email }}</span>
              </div>
              <router-link to="/wishlist" class="dropdown-item" @click="userMenuOpen = false">
                ❤️ รายการโปรด
              </router-link>
              <router-link to="/my-orders" class="dropdown-item" @click="userMenuOpen = false">
                📦 คำสั่งซื้อของฉัน
              </router-link>
              <router-link v-if="authStore.isAdmin" to="/admin" class="dropdown-item admin-item" @click="userMenuOpen = false">
                ⚙️ จัดการร้าน
              </router-link>
              <button class="dropdown-item logout-btn" @click="handleLogout">🚪 ออกจากระบบ</button>
            </div>
          </div>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-sm btn-secondary">เข้าสู่ระบบ</router-link>
        </template>

        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" @click="menuOpen = !menuOpen">
          <span :class="{ active: menuOpen }"></span>
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const menuOpen = ref(false)
const userMenuOpen = ref(false)

function handleLogout() {
  authStore.logout()
  userMenuOpen.value = false
  router.push('/')
}

function closeMenus(e) {
  if (!e.target.closest('.user-menu')) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeMenus)
  cartStore.fetchCart()
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenus)
})
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: rgba(15, 15, 15, 0.9);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border-subtle);
}

.navbar-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 80px;
}

.navbar-logo {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  font-size: var(--font-size-xl);
  font-weight: 700;
  letter-spacing: 2px;
}

.logo-icon {
  font-size: 2rem;
}

.logo-text {
  color: var(--text-primary);
}

.navbar-links {
  display: flex;
  gap: var(--space-xl);
}

.nav-link {
  font-weight: 500;
  color: var(--text-secondary);
  transition: color var(--transition-fast);
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--color-primary);
  transition: width var(--transition-normal);
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--color-accent);
}

.nav-link:hover::after,
.nav-link.router-link-active::after {
  width: 100%;
}

.admin-link {
  color: var(--color-accent-gold);
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.action-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  transition: all var(--transition-fast);
}

.action-btn:hover {
  border-color: var(--color-primary);
  background: var(--bg-card);
}

.action-icon {
  font-size: 1.1rem;
}

.cart-btn {
  position: relative;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-subtle);
  transition: all var(--transition-fast);
}

.cart-btn:hover {
  border-color: var(--color-primary);
  background: var(--bg-card);
}

.cart-icon {
  font-size: 1.25rem;
}

.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-primary);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: var(--radius-full);
  padding: 0 6px;
}

.user-menu {
  position: relative;
}

.user-btn {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.user-btn:hover {
  transform: scale(1.05);
}

.user-avatar {
  color: white;
  font-weight: 600;
  font-size: 1rem;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  min-width: 200px;
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

.user-info {
  padding: var(--space-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
  border-bottom: 1px solid var(--border-subtle);
  background: rgba(255, 255, 255, 0.02);
}

.user-name {
  display: block;
  font-weight: 600;
  color: var(--text-primary);
}

.user-email {
  display: block;
  font-size: var(--font-size-sm);
  color: var(--text-muted);
}

.dropdown-item {
  width: 100%;
  padding: var(--space-md) var(--space-lg);
  display: flex;
  align-items: center;
  gap: var(--space-md);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
  border: none;
  cursor: pointer;
  font-size: var(--font-size-sm);
}

.dropdown-item:hover {
  background: var(--bg-glass);
  color: var(--text-primary);
  padding-left: calc(var(--space-lg) + 4px);
}

.logout-btn {
  color: #f87171 !important;
  margin-top: var(--space-xs);
  border-top: 1px solid var(--border-subtle);
  padding-top: var(--space-md);
}

.logout-btn:hover {
  background: rgba(248, 113, 113, 0.1);
}

.menu-toggle {
  display: none;
  width: 32px;
  height: 32px;
  position: relative;
}

.menu-toggle span,
.menu-toggle span::before,
.menu-toggle span::after {
  position: absolute;
  width: 24px;
  height: 2px;
  background: var(--text-primary);
  transition: all var(--transition-fast);
}

.menu-toggle span {
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.menu-toggle span::before,
.menu-toggle span::after {
  content: '';
  left: 0;
}

.menu-toggle span::before {
  top: -8px;
}

.menu-toggle span::after {
  top: 8px;
}

.menu-toggle span.active {
  background: transparent;
}

.menu-toggle span.active::before {
  top: 0;
  transform: rotate(45deg);
}

.menu-toggle span.active::after {
  top: 0;
  transform: rotate(-45deg);
}

@media (max-width: 768px) {
  .navbar-links {
    position: fixed;
    top: 80px;
    left: 0;
    right: 0;
    background: var(--bg-dark);
    flex-direction: column;
    padding: var(--space-xl);
    gap: var(--space-lg);
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none;
    transition: all var(--transition-normal);
    border-bottom: 1px solid var(--border-subtle);
  }

  .navbar-links.active {
    transform: translateY(0);
    opacity: 1;
    pointer-events: all;
  }

  .menu-toggle {
    display: block;
  }

  .btn-secondary {
    display: none;
  }
}
</style>
