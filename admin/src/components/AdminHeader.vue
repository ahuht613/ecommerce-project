<template>
  <header class="admin-header">
    <!-- Left side - Mobile menu button and breadcrumb -->
    <div class="header-left">
      <button
        class="mobile-menu-btn"
        @click="$emit('toggle-sidebar')"
        :class="{ active: sidebarOpen }"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <div class="breadcrumb">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <span class="page-subtitle">{{ pageSubtitle }}</span>
      </div>
    </div>

    <!-- Right side - User menu -->
    <div class="header-right">
      <!-- User Menu -->
      <div class="user-menu" @click="toggleUserMenu" ref="userMenuRef">
        <div class="user-info">
          <div class="user-avatar">
            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.full_name">
            <div v-else class="avatar-placeholder">
              {{ getInitials(user.full_name) }}
            </div>
          </div>
          <div class="user-details">
            <span class="user-name">{{ user.full_name || user.username }}</span>
            <span class="user-role">{{ user.role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span>
          </div>
          <svg class="dropdown-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6,9 12,15 18,9"></polyline>
          </svg>
        </div>

        <!-- Dropdown Menu -->
        <div class="user-dropdown" :class="{ show: showUserMenu }">
          <div class="dropdown-item logout" @click="$emit('logout')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16,17 21,12 16,7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Đăng xuất</span>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

defineProps(['user', 'sidebarOpen'])
defineEmits(['logout', 'toggle-sidebar'])

const route = useRoute()

const pageTitle = computed(() => {
  const titles = {
    '/products': 'Quản lý sản phẩm',
    '/orders': 'Quản lý đơn hàng'
  }
  return titles[route.path] || 'Admin Dashboard'
})

const pageSubtitle = computed(() => {
  const subtitles = {
    '/products': 'Quản lý danh sách sản phẩm trong cửa hàng',
    '/orders': 'Theo dõi và xử lý đơn hàng của khách hàng'
  }
  return subtitles[route.path] || 'Hệ thống quản trị'
})

const showUserMenu = ref(false)
const userMenuRef = ref(null)

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value
}

function getInitials(name) {
  if (!name) return 'A'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.admin-header {
  height: var(--admin-header-height);
  background: var(--admin-bg-primary);
  border-bottom: 1px solid var(--admin-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--admin-space-6);
  box-shadow: var(--admin-shadow-sm);
  position: sticky;
  top: 0;
  z-index: 999;
}

.header-left {
  display: flex;
  align-items: center;
  gap: var(--admin-space-4);
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: var(--admin-text-secondary);
  cursor: pointer;
  padding: var(--admin-space-2);
  border-radius: var(--admin-radius-md);
  transition: all var(--admin-transition-fast);
}

.mobile-menu-btn:hover {
  background: var(--admin-bg-secondary);
  color: var(--admin-text-primary);
}

.breadcrumb {
  display: flex;
  flex-direction: column;
}

.page-title {
  margin: 0;
  font-size: var(--admin-font-xl);
  font-weight: var(--admin-font-bold);
  color: var(--admin-text-primary);
  line-height: 1.2;
}

.page-subtitle {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-secondary);
  margin-top: 2px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: var(--admin-space-4);
}



.user-menu {
  position: relative;
  cursor: pointer;
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
  padding: var(--admin-space-2);
  border-radius: var(--admin-radius-lg);
  transition: all var(--admin-transition-fast);
}

.user-info:hover {
  background: var(--admin-bg-secondary);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--admin-radius-full);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: var(--admin-primary);
  color: var(--admin-text-white);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-semibold);
}

.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-name {
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: var(--admin-font-xs);
  color: var(--admin-text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-icon {
  color: var(--admin-text-secondary);
  transition: transform var(--admin-transition-fast);
}

.user-menu:hover .dropdown-icon {
  transform: rotate(180deg);
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--admin-bg-primary);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-lg);
  box-shadow: var(--admin-shadow-lg);
  min-width: 200px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all var(--admin-transition-fast);
  z-index: 1000;
  margin-top: var(--admin-space-2);
}

.user-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
  padding: var(--admin-space-3) var(--admin-space-4);
  color: var(--admin-text-primary);
  cursor: pointer;
  transition: all var(--admin-transition-fast);
  font-size: var(--admin-font-sm);
}

.dropdown-item:hover {
  background: var(--admin-bg-secondary);
}

.dropdown-item.logout {
  color: var(--admin-error);
}

.dropdown-item.logout:hover {
  background: rgba(220, 38, 38, 0.1);
}

.dropdown-divider {
  height: 1px;
  background: var(--admin-border);
  margin: var(--admin-space-2) 0;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }

  .page-subtitle {
    display: none;
  }

  .user-details {
    display: none;
  }

  .admin-header {
    padding: 0 var(--admin-space-4);
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: var(--admin-font-lg);
  }
}
</style>
