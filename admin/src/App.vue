<template>
  <div id="app">
    <!-- Login Screen -->
    <LoginForm v-if="!isLoggedIn" @login-success="onLoginSuccess"/>

    <!-- Admin Dashboard -->
    <div v-else class="admin-layout">
      <!-- Sidebar -->
      <AdminSidebar
        :sidebarOpen="sidebarOpen"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Main Content Area -->
      <div class="admin-main">
        <!-- Header -->
        <AdminHeader
          :user="user"
          :sidebarOpen="sidebarOpen"
          @logout="logout"
          @toggle-sidebar="toggleSidebar"
        />

        <!-- Content -->
        <main class="admin-content">
          <transition name="fade" mode="out-in">
            <router-view />
          </transition>
        </main>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import AdminHeader from './components/AdminHeader.vue'
import AdminSidebar from './components/AdminSidebar.vue'
import LoginForm from './components/LoginForm.vue'

const isLoggedIn = ref(false)
const user = ref({})
const sidebarOpen = ref(true)

function onLoginSuccess(data) {
  isLoggedIn.value = true
  user.value = data.user
  localStorage.setItem('admin_token', data.token)
  localStorage.setItem('admin_user', JSON.stringify(data.user))
  // Set axios default header if needed
}

function logout() {
  isLoggedIn.value = false
  user.value = {}
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

// Check for existing session on mount
onMounted(() => {
  const token = localStorage.getItem('admin_token')
  const userData = localStorage.getItem('admin_user')

  if (token && userData) {
    try {
      isLoggedIn.value = true
      user.value = JSON.parse(userData)
    } catch (error) {
      // Invalid data, clear storage
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_user')
    }
  }
})
</script>

<style scoped>
/* Transition animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--admin-transition-normal);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .admin-layout {
    position: relative;
  }
}
</style>
