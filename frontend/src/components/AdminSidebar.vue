<template>
  <aside class="admin-sidebar" :class="{ collapsed: collapsed }">
    <div class="sidebar-header">
      <router-link to="/admin" class="sidebar-logo">
        <span class="logo-icon">🍵</span>
        <span v-if="!collapsed" class="logo-text">Admin</span>
      </router-link>
      <button class="collapse-btn" @click="collapsed = !collapsed">
        {{ collapsed ? '→' : '←' }}
      </button>
    </div>

    <nav class="sidebar-nav">
      <router-link to="/admin" class="sidebar-link" exact-active-class="active">
        <span class="link-icon">📊</span>
        <span v-if="!collapsed" class="link-text">Dashboard</span>
      </router-link>

      <router-link to="/admin/orders" class="sidebar-link" active-class="active">
        <span class="link-icon">📋</span>
        <span v-if="!collapsed" class="link-text">คำสั่งซื้อ</span>
      </router-link>

      <router-link to="/admin/products" class="sidebar-link" active-class="active">
        <span class="link-icon">📦</span>
        <span v-if="!collapsed" class="link-text">สินค้า</span>
      </router-link>

      <router-link to="/admin/categories" class="sidebar-link" active-class="active">
        <span class="link-icon">📁</span>
        <span v-if="!collapsed" class="link-text">หมวดหมู่</span>
      </router-link>

      <router-link to="/admin/coupons" class="sidebar-link" active-class="active">
        <span class="link-icon">🎟️</span>
        <span v-if="!collapsed" class="link-text">คูปอง</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <router-link to="/" class="sidebar-link">
        <span class="link-icon">🏠</span>
        <span v-if="!collapsed" class="link-text">กลับหน้าร้าน</span>
      </router-link>

      <button class="sidebar-link logout-link" @click="handleLogout">
        <span class="link-icon">🚪</span>
        <span v-if="!collapsed" class="link-text">ออกจากระบบ</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const collapsed = ref(false)

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 280px;
  height: 100vh;
  background: var(--bg-card);
  border-right: 1px solid var(--border-subtle);
  display: flex;
  flex-direction: column;
  z-index: 100;
  transition: width var(--transition-normal);
}

.admin-sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-lg);
  border-bottom: 1px solid var(--border-subtle);
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  font-weight: 700;
  font-size: var(--font-size-lg);
}

.logo-icon {
  font-size: 1.5rem;
}

.collapse-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-sm);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.collapse-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-accent);
}

.sidebar-nav {
  flex: 1;
  padding: var(--space-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.sidebar-link:hover {
  background: var(--bg-glass);
  color: var(--text-primary);
}

.sidebar-link.active {
  background: var(--color-primary);
  color: white;
}

.link-icon {
  font-size: 1.25rem;
}

.collapsed .sidebar-link {
  justify-content: center;
  padding: var(--space-md);
}

.sidebar-footer {
  padding: var(--space-lg);
  border-top: 1px solid var(--border-subtle);
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
}

.logout-link {
  width: 100%;
  color: #f87171 !important;
}

.logout-link:hover {
  background: rgba(248, 113, 113, 0.1) !important;
}

@media (max-width: 1024px) {
  .admin-sidebar {
    transform: translateX(-100%);
    width: 280px;
  }

  .admin-sidebar.collapsed {
    transform: translateX(0);
    width: 80px;
  }
}
</style>
