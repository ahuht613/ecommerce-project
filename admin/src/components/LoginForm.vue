<template>
  <div class="login-container">
    <div class="login-card">
      <!-- Header -->
      <div class="login-header">
        <div class="logo">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </div>
        <h1 class="login-title">Admin Panel</h1>
        <p class="login-subtitle">Đăng nhập vào hệ thống quản trị</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="login" class="login-form">
        <div class="form-group">
          <label class="form-label">Tài khoản</label>
          <div class="input-group">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <input
              type="text"
              v-model="username"
              class="form-input"
              placeholder="Nhập tài khoản"
              required
              :disabled="loading"
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mật khẩu</label>
          <div class="input-group">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <circle cx="12" cy="16" r="1"></circle>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input
              type="password"
              v-model="password"
              class="form-input"
              placeholder="Nhập mật khẩu"
              required
              :disabled="loading"
            />
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="error-message">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          {{ error }}
        </div>

        <!-- Submit Button -->
        <button type="submit" class="login-btn" :disabled="loading">
          <svg v-if="loading" class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12a9 9 0 11-6.219-8.56"></path>
          </svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
            <polyline points="10,17 15,12 10,7"></polyline>
            <line x1="15" y1="12" x2="3" y2="12"></line>
          </svg>
          {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
      </form>

      <!-- Footer -->
      <div class="login-footer">
        <p>© 2024 E-commerce Admin. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['login-success'])
const username = ref('admin')
const password = ref('password')
const error = ref('')
const loading = ref(false)

async function login() {
  if (loading.value) return

  loading.value = true
  error.value = ''

  try {
    const res = await axios.post('http://localhost/ecommerce-project/BE/api/auth.php', {
      action: 'login',
      username: username.value,
      password: password.value
    })

    if (res.data.user.role !== 'admin') {
      error.value = 'Bạn không có quyền truy cập hệ thống quản trị!'
      return
    }

    emit('login-success', res.data)
  } catch (e) {
    error.value = e.response?.data?.message || 'Đăng nhập thất bại. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-accent) 100%);
  padding: var(--admin-space-4);
}

.login-card {
  width: 100%;
  max-width: 400px;
  background: var(--admin-bg-card);
  border-radius: var(--admin-radius-2xl);
  box-shadow: var(--admin-shadow-xl);
  overflow: hidden;
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  text-align: center;
  padding: var(--admin-space-8) var(--admin-space-6) var(--admin-space-6);
  background: var(--admin-bg-secondary);
}

.logo {
  width: 80px;
  height: 80px;
  background: var(--admin-primary);
  border-radius: var(--admin-radius-2xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto var(--admin-space-4);
  color: var(--admin-text-white);
}

.login-title {
  margin: 0 0 var(--admin-space-2);
  font-size: var(--admin-font-2xl);
  font-weight: var(--admin-font-bold);
  color: var(--admin-text-primary);
}

.login-subtitle {
  margin: 0;
  font-size: var(--admin-font-sm);
  color: var(--admin-text-secondary);
}

.login-form {
  padding: var(--admin-space-6);
}

.form-group {
  margin-bottom: var(--admin-space-5);
}

.form-label {
  display: block;
  margin-bottom: var(--admin-space-2);
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
}

.input-group {
  position: relative;
}

.input-icon {
  position: absolute;
  left: var(--admin-space-3);
  top: 50%;
  transform: translateY(-50%);
  color: var(--admin-text-muted);
  z-index: 1;
}

.form-input {
  width: 100%;
  padding: var(--admin-space-3) var(--admin-space-3) var(--admin-space-3) var(--admin-space-10);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-lg);
  font-size: var(--admin-font-sm);
  background: var(--admin-bg-primary);
  color: var(--admin-text-primary);
  transition: all var(--admin-transition-fast);
}

.form-input:focus {
  outline: none;
  border-color: var(--admin-primary);
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.form-input:disabled {
  background: var(--admin-bg-tertiary);
  color: var(--admin-text-muted);
  cursor: not-allowed;
}

.error-message {
  display: flex;
  align-items: center;
  gap: var(--admin-space-2);
  padding: var(--admin-space-3);
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: var(--admin-radius-lg);
  color: var(--admin-error);
  font-size: var(--admin-font-sm);
  margin-bottom: var(--admin-space-4);
}

.login-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--admin-space-2);
  padding: var(--admin-space-4);
  background: var(--admin-primary);
  color: var(--admin-text-white);
  border: none;
  border-radius: var(--admin-radius-lg);
  font-size: var(--admin-font-base);
  font-weight: var(--admin-font-medium);
  cursor: pointer;
  transition: all var(--admin-transition-fast);
}

.login-btn:hover:not(:disabled) {
  background: var(--admin-primary-hover);
  transform: translateY(-1px);
  box-shadow: var(--admin-shadow-lg);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.login-footer {
  padding: var(--admin-space-4) var(--admin-space-6);
  background: var(--admin-bg-tertiary);
  text-align: center;
}

.login-footer p {
  margin: 0;
  font-size: var(--admin-font-xs);
  color: var(--admin-text-muted);
}

/* Mobile responsive */
@media (max-width: 480px) {
  .login-container {
    padding: var(--admin-space-2);
  }

  .login-card {
    max-width: none;
  }

  .login-header {
    padding: var(--admin-space-6) var(--admin-space-4) var(--admin-space-4);
  }

  .login-form {
    padding: var(--admin-space-4);
  }

  .logo {
    width: 60px;
    height: 60px;
  }

  .login-title {
    font-size: var(--admin-font-xl);
  }
}
</style>
