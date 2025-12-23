<template>
  <div id="app">
    <Navbar v-if="!isAdminRoute" />
    <AdminSidebar v-if="isAdminRoute && authStore.isAdmin" />
    <main :class="{ 'admin-layout': isAdminRoute }">
      <router-view />
    </main>
    <Footer v-if="!isAdminRoute" />
    <Toast />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
import AdminSidebar from './components/AdminSidebar.vue'
import Toast from './components/Toast.vue'

const route = useRoute()
const authStore = useAuthStore()

const isAdminRoute = computed(() => route.path.startsWith('/admin'))

onMounted(() => {
  authStore.initAuth()
})
</script>

<style scoped>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}

main.admin-layout {
  margin-left: 280px;
  padding: var(--space-xl);
  min-height: 100vh;
}

@media (max-width: 1024px) {
  main.admin-layout {
    margin-left: 0;
    padding: var(--space-md);
    padding-top: 80px;
  }
}
</style>
