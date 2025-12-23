<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card card">
        <div class="auth-header text-center">
          <router-link to="/" class="auth-logo">
            <span class="logo-icon">🍵</span>
            <span class="logo-text">MATCHAZUKI</span>
          </router-link>
          <h1 class="auth-title">สมัครสมาชิก</h1>
          <p class="auth-subtitle text-secondary">สร้างบัญชีเพื่อเริ่มช้อปปิ้ง</p>
        </div>

        <form @submit.prevent="handleRegister" class="auth-form">
          <div class="form-group">
            <label class="form-label">ชื่อ</label>
            <input 
              type="text" 
              v-model="name" 
              class="form-input" 
              placeholder="ชื่อของคุณ"
              required
            />
          </div>

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
              placeholder="อย่างน้อย 6 ตัวอักษร"
              required
              minlength="6"
            />
          </div>

          <div class="form-group">
            <label class="form-label">ยืนยันรหัสผ่าน</label>
            <input 
              type="password" 
              v-model="confirmPassword" 
              class="form-input" 
              placeholder="ยืนยันรหัสผ่าน"
              required
            />
          </div>

          <div v-if="error" class="auth-error">
            {{ error }}
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            {{ loading ? 'กำลังสมัคร...' : 'สมัครสมาชิก' }}
          </button>
        </form>

        <div class="auth-footer text-center">
          <p class="text-secondary">
            มีบัญชีอยู่แล้ว? 
            <router-link to="/login" class="auth-link">เข้าสู่ระบบ</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  error.value = ''
  
  if (password.value !== confirmPassword.value) {
    error.value = 'รหัสผ่านไม่ตรงกัน'
    return
  }

  loading.value = true
  const result = await authStore.register(name.value, email.value, password.value)
  loading.value = false
  
  if (result.success) {
    toastStore.success('สมัครสมาชิกสำเร็จ')
    router.push('/')
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
