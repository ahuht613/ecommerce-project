<template>
  <header class="header">
    <div class="container">
      <div class="header-content">
        <a href="#" class="header-brand">
          🛍️ Shop Online
        </a>

        <!-- Desktop Navigation -->
        <nav class="header-nav">
          <router-link
            to="/home"
            class="nav-item"
            active-class="active"
          >
            Sản phẩm
          </router-link>
          <router-link
            to="/cart"
            class="nav-item cart-button"
            active-class="active"
          >
            Giỏ hàng
            <span v-if="cartCount > 0" class="badge ml-2">{{ cartCount }}</span>
          </router-link>
          <router-link
            to="/orders"
            class="nav-item"
            active-class="active"
          >
            Đơn hàng
          </router-link>
          <router-link
            to="/profile"
            class="nav-item"
            active-class="active"
          >
            Cá nhân
          </router-link>
        </nav>

        <div class="header-actions">
          <!-- User Menu -->
          <div class="user-menu" @click="toggleUserMenu" ref="userMenuRef">
            <div class="user-info">
              <img
                :src="userAvatar"
                :alt="user?.full_name || 'User'"
                class="user-avatar"
              />
              <div class="user-details">
                <span class="user-name">{{ user?.full_name || 'Khách hàng' }}</span>
                <span class="user-role">Khách hàng</span>
              </div>
              <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

          <!-- Mobile menu toggle -->
          <button
            @click="toggleMobileMenu"
            class="mobile-menu-toggle"
            aria-label="Toggle menu"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <nav class="mobile-menu" :class="{ open: mobileMenuOpen }">
        <div class="nav flex-col">
          <router-link
            to="/home"
            @click="closeMobileMenu"
            class="nav-item"
            active-class="active"
          >
            Sản phẩm
          </router-link>
          <router-link
            to="/cart"
            @click="closeMobileMenu"
            class="nav-item"
            active-class="active"
          >
            Giỏ hàng
            <span v-if="cartCount > 0" class="badge ml-auto">{{ cartCount }}</span>
          </router-link>
          <router-link
            to="/orders"
            @click="closeMobileMenu"
            class="nav-item"
            active-class="active"
          >
            Đơn hàng
          </router-link>
          <router-link
            to="/profile"
            @click="closeMobileMenu"
            class="nav-item"
            active-class="active"
          >
            Cá nhân
          </router-link>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { generateAvatarUrl } from '../utils/formatters.js'

const props = defineProps(['cartCount', 'user'])
const emit = defineEmits(['logout'])

const mobileMenuOpen = ref(false)
const showUserMenu = ref(false)
const userMenuRef = ref(null)

const userAvatar = computed(() => {
  return generateAvatarUrl(props.user?.full_name, 40)
})

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value
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
.cart-button {
  position: relative;
}

.nav-item {
  background: none;
  border: none;
  cursor: pointer;
  font-size: inherit;
  font-family: inherit;
}

.mobile-menu .nav-item {
  justify-content: space-between;
  width: 100%;
}

.user-menu {
  position: relative;
  cursor: pointer;
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2);
  border-radius: var(--radius-lg);
  transition: background-color var(--transition-fast);
}

.user-info:hover {
  background-color: var(--bg-secondary);
}

.dropdown-arrow {
  color: var(--text-muted);
  transition: transform var(--transition-fast);
}

.user-menu.show .dropdown-arrow {
  transform: rotate(180deg);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  border: 2px solid var(--border-color);
  object-fit: cover;
  transition: border-color var(--transition-fast);
}

.user-avatar:hover {
  border-color: var(--primary-color);
}

.user-details {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 0;
}

.user-name {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: var(--font-size-xs);
  color: var(--text-muted);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  min-width: 200px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all var(--transition-fast);
}

.user-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  color: var(--text-secondary);
  text-decoration: none;
  cursor: pointer;
  transition: all var(--transition-fast);
  border-radius: var(--radius-md);
  margin: var(--space-1);
}

.dropdown-item:hover {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.dropdown-item.logout {
  color: var(--error-color);
}

.dropdown-item.logout:hover {
  background: var(--error-bg);
  color: var(--error-color);
}

@media (max-width: 768px) {
  .user-details {
    display: none !important;
  }

  .dropdown-arrow {
    display: none;
  }

  .user-dropdown {
    right: -10px;
    min-width: 180px;
  }
}
</style>
