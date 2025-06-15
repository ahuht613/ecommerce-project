<template>
  <div class="auth-container">
    <div class="auth-background">
      <div class="auth-overlay"></div>
    </div>

    <div class="auth-content">
      <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
          <div class="brand-logo">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
          </div>
          <h1 class="auth-title">{{ register ? 'Tạo tài khoản' : 'Đăng nhập' }}</h1>
          <p class="auth-subtitle">
            {{ register ? 'Tham gia cộng đồng mua sắm của chúng tôi' : 'Chào mừng bạn quay trở lại' }}
          </p>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="alert alert-error">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          {{ error }}
        </div>

        <!-- Success Alert -->
        <div v-if="successMessage" class="alert alert-success">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 12l2 2 4-4"></path>
            <circle cx="12" cy="12" r="10"></circle>
          </svg>
          {{ successMessage }}
        </div>

        <!-- Login Form -->
        <form v-if="!register" @submit.prevent="login" class="auth-form">
          <div class="form-group">
            <label class="form-label">Tài khoản</label>
            <input
              v-model="username"
              type="text"
              class="form-input"
              placeholder="Nhập tên đăng nhập"
              required
              autocomplete="username"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Mật khẩu</label>
            <div class="password-input">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                placeholder="Nhập mật khẩu"
                required
                autocomplete="current-password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="password-toggle"
              >
                <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            <div v-if="loading" class="spinner"></div>
            {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
          </button>

          <div class="auth-footer">
            <p>Chưa có tài khoản?
              <button type="button" @click="switchToRegister" class="link-button">
                Đăng ký ngay
              </button>
            </p>
          </div>
        </form>

        <!-- Register Form -->
        <form v-else @submit.prevent="signup" class="auth-form">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tài khoản *</label>
              <input
                v-model="username"
                type="text"
                class="form-input"
                placeholder="Tên đăng nhập"
                required
                autocomplete="username"
              />
            </div>

            <div class="form-group">
              <label class="form-label">Email *</label>
              <input
                v-model="email"
                type="email"
                class="form-input"
                placeholder="email@example.com"
                required
                autocomplete="email"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Họ và tên *</label>
            <input
              v-model="full_name"
              type="text"
              class="form-input"
              placeholder="Nhập họ và tên đầy đủ"
              required
              autocomplete="name"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Số điện thoại</label>
              <input
                v-model="phone"
                type="tel"
                class="form-input"
                placeholder="0123456789"
                autocomplete="tel"
              />
            </div>

            <div class="form-group">
              <label class="form-label">Mật khẩu *</label>
              <div class="password-input">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input"
                  placeholder="Tối thiểu 6 ký tự"
                  required
                  minlength="6"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="password-toggle"
                >
                  <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Địa chỉ</label>
            <textarea
              v-model="address"
              class="form-input"
              placeholder="Nhập địa chỉ của bạn"
              rows="3"
              autocomplete="address-line1"
            ></textarea>
          </div>

          <div class="form-actions">
            <button type="button" @click="switchToLogin" class="btn btn-secondary">
              Quay lại
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <div v-if="loading" class="spinner"></div>
              {{ loading ? 'Đang đăng ký...' : 'Đăng ký' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['login-success'])

// State
const register = ref(false)
const loading = ref(false)
const showPassword = ref(false)
const error = ref('')
const successMessage = ref('')

// Form data
const username = ref('')
const password = ref('')
const email = ref('')
const full_name = ref('')
const phone = ref('')
const address = ref('')

// Clear messages when switching forms
function clearMessages() {
  error.value = ''
  successMessage.value = ''
}

function switchToRegister() {
  register.value = true
  clearMessages()
  clearForm()
}

function switchToLogin() {
  register.value = false
  clearMessages()
  clearForm()
}

function clearForm() {
  username.value = ''
  password.value = ''
  email.value = ''
  full_name.value = ''
  phone.value = ''
  address.value = ''
  showPassword.value = false
}

async function login() {
  if (loading.value) return

  try {
    loading.value = true
    clearMessages()

    const res = await axios.post('http://localhost/ecommerce-project/BE/api/auth.php', {
      action: 'login',
      username: username.value,
      password: password.value
    })

    emit('login-success', res.data)
  } catch (e) {
    error.value = e.response?.data?.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.'
  } finally {
    loading.value = false
  }
}

async function signup() {
  if (loading.value) return

  try {
    loading.value = true
    clearMessages()

    await axios.post('http://localhost/ecommerce-project/BE/api/auth.php', {
      action: 'register',
      username: username.value,
      password: password.value,
      email: email.value,
      full_name: full_name.value,
      phone: phone.value,
      address: address.value
    })

    successMessage.value = 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.'

    // Switch to login form after successful registration
    setTimeout(() => {
      register.value = false
      clearForm()
      successMessage.value = ''
    }, 2000)

  } catch (e) {
    error.value = e.response?.data?.message || 'Đăng ký thất bại. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: var(--space-4);
}

.auth-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--bg-gradient);
  z-index: 1;
}

.auth-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
}

.auth-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 500px;
}

.auth-card {
  background: var(--bg-card);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-xl);
  padding: var(--space-8);
  backdrop-filter: blur(10px);
}

.auth-header {
  text-align: center;
  margin-bottom: var(--space-8);
}

.brand-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: var(--primary-color);
  color: var(--text-white);
  border-radius: var(--radius-2xl);
  margin-bottom: var(--space-4);
}

.auth-title {
  font-size: var(--font-size-3xl);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.auth-subtitle {
  font-size: var(--font-size-base);
  color: var(--text-secondary);
  margin: 0;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-4);
}

@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

.password-input {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: var(--space-3);
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: var(--space-1);
  border-radius: var(--radius-md);
  transition: color var(--transition-fast);
}

.password-toggle:hover {
  color: var(--text-primary);
}

.form-actions {
  display: flex;
  gap: var(--space-3);
}

.form-actions .btn {
  flex: 1;
}

.auth-footer {
  text-align: center;
  margin-top: var(--space-6);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-color);
}

.auth-footer p {
  margin: 0;
  color: var(--text-secondary);
}

.link-button {
  background: none;
  border: none;
  color: var(--primary-color);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  text-decoration: underline;
  padding: 0;
  font-size: inherit;
}

.link-button:hover {
  color: var(--primary-hover);
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .auth-card {
    padding: var(--space-6);
  }

  .auth-title {
    font-size: var(--font-size-2xl);
  }

  .brand-logo {
    width: 60px;
    height: 60px;
  }
}
</style>
