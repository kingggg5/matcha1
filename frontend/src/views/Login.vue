<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card card">
        <div class="auth-header text-center">
          <router-link to="/" class="auth-logo">
            <span class="logo-icon">🍵</span>
            <span class="logo-text">MATCHAKING</span>
          </router-link>
          <h1 class="auth-title">เข้าสู่ระบบ</h1>
          <p class="auth-subtitle text-secondary">ยินดีต้อนรับกลับมา</p>
        </div>

        <form @submit.prevent="handleLogin" class="auth-form">
          <div class="form-group">
            <label class="form-label">อีเมล</label>
            <input 
              type="email" 
              v-model="email" 
              class="form-input" 
              placeholder="your@email.com"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">รหัสผ่าน</label>
            <input 
              type="password" 
              v-model="password" 
              class="form-input" 
              placeholder="••••••••"
              required
            />
          </div>

          <div v-if="error" class="auth-error">
            {{ error }}
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
          </button>
        </form>

        <div class="auth-footer text-center">
          <p class="text-secondary">
            ยังไม่มีบัญชี? 
            <router-link to="/register" class="auth-link">สมัครสมาชิก</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toastStore = useToastStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  error.value = ''
  loading.value = true
  
  const result = await authStore.login(email.value, password.value)
  loading.value = false
  
  if (result.success) {
    toastStore.success('เข้าสู่ระบบสำเร็จ')
    
    // Check if there's a redirect query param
    const redirect = route.query.redirect
    
    if (redirect) {
      // If redirect was to admin and user is admin, go there
      if (redirect.startsWith('/admin') && authStore.isAdmin) {
        router.push(redirect)
      } else if (redirect.startsWith('/admin') && !authStore.isAdmin) {
        // User tried to access admin but is not admin
        router.push('/')
      } else {
        router.push(redirect)
      }
    } else {
      // No redirect, go to admin if admin, otherwise home
      if (authStore.isAdmin) {
        router.push('/admin')
      } else {
        router.push('/')
      }
    }
  } else {
    error.value = result.error
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-xl);
}

.auth-container {
  width: 100%;
  max-width: 420px;
}

.auth-card {
  padding: var(--space-2xl);
}

.auth-header {
  margin-bottom: var(--space-2xl);
}

.auth-logo {
  display: inline-flex;
  align-items: center;
  gap: var(--space-sm);
  font-size: var(--font-size-xl);
  font-weight: 700;
  letter-spacing: 2px;
  margin-bottom: var(--space-lg);
}

.logo-icon {
  font-size: 2rem;
}

.auth-title {
  font-size: var(--font-size-2xl);
  margin-bottom: var(--space-sm);
}

.auth-form {
  margin-bottom: var(--space-xl);
}

.auth-error {
  padding: var(--space-md);
  background: rgba(248, 113, 113, 0.1);
  border: 1px solid #f87171;
  border-radius: var(--radius-md);
  color: #f87171;
  font-size: var(--font-size-sm);
  margin-bottom: var(--space-lg);
}

.auth-footer {
  padding-top: var(--space-lg);
  border-top: 1px solid var(--border-subtle);
}

.auth-link {
  color: var(--color-accent);
  font-weight: 500;
}

.auth-link:hover {
  text-decoration: underline;
}
</style>
