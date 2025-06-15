<template>
  <nav class="admin-sidebar" :class="{ open: sidebarOpen }">
    <!-- Logo/Brand -->
    <div class="sidebar-header">
      <div class="brand">
        <div class="brand-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </div>
        <div class="brand-text">
          <h2>Admin Panel</h2>
          <span>E-commerce</span>
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
      <div class="menu-section">
        <h3 class="menu-title">Quản lý</h3>
        <ul class="menu-list">
          <li class="menu-item">
            <router-link
              to="/products"
              class="menu-link"
              active-class="active"
            >
              <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
              </svg>
              <span class="menu-text">Quản lý sản phẩm</span>
            </router-link>
          </li>

          <li class="menu-item">
            <router-link
              to="/categories"
              class="menu-link"
              active-class="active"
            >
              <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h6v6H4z"></path>
                <path d="M14 4h6v6h-6z"></path>
                <path d="M4 14h6v6H4z"></path>
                <path d="M14 14h6v6h-6z"></path>
              </svg>
              <span class="menu-text">Quản lý danh mục</span>
            </router-link>
          </li>

          <li class="menu-item">
            <router-link
              to="/orders"
              class="menu-link"
              active-class="active"
            >
              <svg class="menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
              </svg>
              <span class="menu-text">Quản lý đơn hàng</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="footer-content">
        <div class="version-info">
          <span class="version">v1.0.0</span>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
defineProps(['sidebarOpen'])
defineEmits(['toggle-sidebar'])
</script>

<style scoped>
.admin-sidebar {
  width: var(--admin-sidebar-width);
  background: var(--admin-bg-sidebar);
  color: var(--admin-text-white);
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1000;
  transition: transform var(--admin-transition-normal);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-header {
  padding: var(--admin-space-6);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand {
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
}

.brand-icon {
  width: 40px;
  height: 40px;
  background: var(--admin-primary);
  border-radius: var(--admin-radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--admin-text-white);
}

.brand-text h2 {
  margin: 0;
  font-size: var(--admin-font-lg);
  font-weight: var(--admin-font-bold);
  color: var(--admin-text-white);
}

.brand-text span {
  font-size: var(--admin-font-xs);
  color: var(--admin-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sidebar-menu {
  flex: 1;
  padding: var(--admin-space-4) 0;
}

.menu-section {
  margin-bottom: var(--admin-space-6);
}

.menu-title {
  padding: 0 var(--admin-space-6);
  margin: 0 0 var(--admin-space-3);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.menu-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.menu-item {
  margin: 0;
}

.menu-link {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
  padding: var(--admin-space-3) var(--admin-space-6);
  background: none;
  border: none;
  color: var(--admin-text-light);
  text-decoration: none;
  cursor: pointer;
  transition: all var(--admin-transition-fast);
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-medium);
  position: relative;
}

.menu-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text-white);
}

.menu-link.active {
  background: var(--admin-primary);
  color: var(--admin-text-white);
}

.menu-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--admin-accent);
}

.menu-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
}

.menu-text {
  flex: 1;
  text-align: left;
}

.sidebar-footer {
  padding: var(--admin-space-4) var(--admin-space-6);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-content {
  display: flex;
  align-items: center;
  justify-content: center;
}

.version-info {
  text-align: center;
}

.version {
  font-size: var(--admin-font-xs);
  color: var(--admin-text-light);
  background: rgba(255, 255, 255, 0.1);
  padding: var(--admin-space-1) var(--admin-space-2);
  border-radius: var(--admin-radius-md);
}

/* Mobile responsive */
@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }

  .admin-sidebar.open {
    transform: translateX(0);
  }
}

/* Scrollbar styling */
.admin-sidebar::-webkit-scrollbar {
  width: 4px;
}

.admin-sidebar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.admin-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>
