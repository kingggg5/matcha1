<template>
  <Transition name="toast">
    <div v-if="toastStore.visible" class="toast" :class="toastStore.type">
      <span class="toast-icon">{{ icon }}</span>
      <span class="toast-message">{{ toastStore.message }}</span>
      <button class="toast-close" @click="toastStore.hide">×</button>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useToastStore } from '../stores/toast'

const toastStore = useToastStore()

const icon = computed(() => {
  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
  }
  return icons[toastStore.type] || icons.info
})
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: 30px;
  right: 30px;
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  background: var(--bg-card);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  z-index: var(--z-toast);
  backdrop-filter: blur(20px);
}

.toast.success {
  border-color: var(--color-primary);
}

.toast.success .toast-icon {
  color: var(--color-accent);
}

.toast.error {
  border-color: #dc2626;
}

.toast.error .toast-icon {
  color: #f87171;
}

.toast.warning {
  border-color: var(--color-accent-gold);
}

.toast.warning .toast-icon {
  color: var(--color-accent-gold);
}

.toast.info .toast-icon {
  color: #60a5fa;
}

.toast-icon {
  font-size: 1.25rem;
  font-weight: 700;
}

.toast-message {
  color: var(--text-primary);
}

.toast-close {
  margin-left: var(--space-md);
  color: var(--text-muted);
  font-size: 1.5rem;
  line-height: 1;
  transition: color var(--transition-fast);
}

.toast-close:hover {
  color: var(--text-primary);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(100px);
}
</style>
