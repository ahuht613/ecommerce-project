<template>
  <div class="profile-section">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Thông tin cá nhân</h1>
        <p class="page-subtitle">Quản lý thông tin tài khoản của bạn</p>
      </div>

      <div class="profile-content">
        <!-- Profile Card -->
        <div class="profile-card">
          <div class="profile-header">
            <div class="avatar-section">
              <img
                :src="userAvatar"
                :alt="profile.full_name"
                class="profile-avatar"
              />
            </div>

            <div class="profile-info">
              <h2 class="profile-name">{{ profile.full_name || 'Chưa cập nhật' }}</h2>
              <p class="profile-email">{{ profile.email || 'Chưa cập nhật' }}</p>
              <div class="profile-stats">
                <div class="stat-item">
                  <span class="stat-label">Thành viên từ</span>
                  <span class="stat-value">{{ formatDate(profile.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Form -->
          <form @submit.prevent="updateProfile" class="profile-form">
            <div class="form-section">
              <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Thông tin cơ bản
              </h3>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Họ và tên *</label>
                  <input
                    v-model="profile.full_name"
                    type="text"
                    class="form-input"
                    placeholder="Nhập họ và tên đầy đủ"
                    required
                  />
                </div>

                <div class="form-group">
                  <label class="form-label">Tên đăng nhập</label>
                  <input
                    :value="profile.username"
                    type="text"
                    class="form-input form-input-disabled"
                    disabled
                    readonly
                    title="Tên đăng nhập không thể thay đổi"
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email *</label>
                <input
                  v-model="profile.email"
                  type="email"
                  class="form-input"
                  placeholder="email@example.com"
                  required
                />
              </div>
            </div>

            <div class="form-section">
              <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Thông tin liên hệ
              </h3>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Số điện thoại</label>
                  <input
                    v-model="profile.phone"
                    type="tel"
                    class="form-input"
                    placeholder="0123456789"
                  />
                </div>

                <div class="form-group">
                  <label class="form-label">Ngày sinh</label>
                  <input
                    v-model="profile.birth_date"
                    type="date"
                    class="form-input"
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Giới tính</label>
                <select v-model="profile.gender" class="form-input">
                  <option value="">Chọn giới tính</option>
                  <option value="male">Nam</option>
                  <option value="female">Nữ</option>
                  <option value="other">Khác</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Địa chỉ</label>
                <textarea
                  v-model="profile.address"
                  class="form-input"
                  placeholder="Nhập địa chỉ của bạn"
                  rows="3"
                ></textarea>
              </div>
            </div>

            <div class="form-section">
              <h3 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Bảo mật
              </h3>

              <div class="security-actions">
                <button
                  type="button"
                  @click="showChangePassword = true"
                  class="btn btn-outline"
                >
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                  Đổi mật khẩu
                </button>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <div v-if="loading" class="spinner"></div>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                  <polyline points="17,21 17,13 7,13 7,21"></polyline>
                  <polyline points="7,3 7,8 15,8"></polyline>
                </svg>
                {{ loading ? 'Đang cập nhật...' : 'Lưu thay đổi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div v-if="showChangePassword" class="modal-overlay" @click.self="showChangePassword = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Đổi mật khẩu</h3>
          <button @click="showChangePassword = false" class="close-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <form @submit.prevent="changePassword" class="modal-body">
          <div class="form-group">
            <label class="form-label">Mật khẩu hiện tại</label>
            <input
              v-model="passwordForm.current"
              type="password"
              class="form-input"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Mật khẩu mới</label>
            <input
              v-model="passwordForm.new"
              type="password"
              class="form-input"
              minlength="6"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Xác nhận mật khẩu mới</label>
            <input
              v-model="passwordForm.confirm"
              type="password"
              class="form-input"
              required
            />
          </div>

          <div class="modal-actions">
            <button type="button" @click="showChangePassword = false" class="btn btn-secondary">
              Hủy
            </button>
            <button type="submit" class="btn btn-primary">
              Đổi mật khẩu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { generateAvatarUrl, formatDate } from '../utils/formatters.js'
import axios from 'axios'

const props = defineProps(['user'])
const emit = defineEmits(['update'])

const profile = ref({ ...props.user })
const loading = ref(false)
const showChangePassword = ref(false)
const passwordForm = ref({
  current: '',
  new: '',
  confirm: ''
})

const userAvatar = computed(() => {
  return generateAvatarUrl(profile.value.full_name, 150)
})



watch(() => props.user, (newUser) => {
  if (newUser) {
    Object.assign(profile.value, newUser)
  }
}, { deep: true })



async function updateProfile() {
  if (loading.value) return

  loading.value = true
  try {
    const updateData = {
      full_name: profile.value.full_name,
      email: profile.value.email,
      phone: profile.value.phone || '',
      address: profile.value.address || '',
      birth_date: profile.value.birth_date || null,
      gender: profile.value.gender || null
    }

    const response = await axios.put('http://localhost/ecommerce-project/BE/api/profile.php', updateData)

    // Update local profile with response data
    Object.assign(profile.value, response.data)
    emit('update', profile.value)

    showToast('Cập nhật thông tin thành công!', 'success')
  } catch (error) {
    console.error('Error updating profile:', error)
    showToast(error.response?.data?.message || 'Lỗi khi cập nhật thông tin', 'error')
  } finally {
    loading.value = false
  }
}

async function changePassword() {
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    showToast('Mật khẩu xác nhận không khớp!', 'error')
    return
  }

  if (passwordForm.value.new.length < 6) {
    showToast('Mật khẩu mới phải có ít nhất 6 ký tự!', 'error')
    return
  }

  try {
    await axios.post('http://localhost/ecommerce-project/BE/api/profile.php', {
      action: 'change_password',
      current_password: passwordForm.value.current,
      new_password: passwordForm.value.new
    })

    showToast('Đổi mật khẩu thành công!', 'success')
    showChangePassword.value = false
    passwordForm.value = { current: '', new: '', confirm: '' }
  } catch (error) {
    console.error('Error changing password:', error)
    showToast(error.response?.data?.message || 'Lỗi khi đổi mật khẩu', 'error')
  }
}



function showToast(message, type = 'success') {
  console.log(`${type.toUpperCase()}: ${message}`)

  // Remove existing toasts
  const existingToasts = document.querySelectorAll('.toast-notification')
  existingToasts.forEach(toast => toast.remove())

  // Create toast notification
  const toast = document.createElement('div')
  toast.className = 'toast-notification'
  toast.textContent = message
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 24px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateX(100%);
    transition: transform 0.3s ease-out;
    max-width: 400px;
    word-wrap: break-word;
    ${type === 'success' ? 'background: #10b981;' : 'background: #ef4444;'}
  `

  document.body.appendChild(toast)

  // Trigger animation
  setTimeout(() => {
    toast.style.transform = 'translateX(0)'
  }, 10)

  // Remove after 4 seconds
  setTimeout(() => {
    toast.style.transform = 'translateX(100%)'
    setTimeout(() => {
      if (toast.parentNode) {
        toast.remove()
      }
    }, 300)
  }, 4000)
}
</script>

<style scoped>
.profile-section {
  min-height: 100vh;
  padding: var(--space-8) 0;
  background: var(--bg-secondary);
}

.profile-content {
  max-width: 800px;
  margin: 0 auto;
}

.profile-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.profile-header {
  display: flex;
  gap: var(--space-6);
  padding: var(--space-8);
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
}

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-3);
}

.profile-avatar {
  width: 150px;
  height: 150px;
  border-radius: var(--radius-full);
  border: 4px solid var(--border-color);
  object-fit: cover;
  box-shadow: var(--shadow-lg);
}





.profile-info {
  flex: 1;
}

.profile-name {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.profile-email {
  font-size: var(--font-size-base);
  color: var(--text-secondary);
  margin: 0 0 var(--space-4);
}

.profile-stats {
  display: flex;
  gap: var(--space-6);
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.stat-label {
  font-size: var(--font-size-xs);
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: var(--font-weight-medium);
}

.stat-value {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
  font-weight: var(--font-weight-semibold);
}

.profile-form {
  padding: var(--space-8);
}

.form-section {
  margin-bottom: var(--space-8);
}

.form-section:last-child {
  margin-bottom: 0;
}

.section-title {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0 0 var(--space-6);
  padding-bottom: var(--space-3);
  border-bottom: 1px solid var(--border-color);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-4);
}

.form-input-disabled {
  background-color: var(--bg-tertiary) !important;
  color: var(--text-muted) !important;
  cursor: not-allowed !important;
  opacity: 0.7;
  border-color: var(--border-color) !important;
}

.form-input-disabled:focus {
  outline: none !important;
  box-shadow: none !important;
  border-color: var(--border-color) !important;
}

.security-actions {
  display: flex;
  gap: var(--space-3);
}

.form-actions {
  display: flex;
  gap: var(--space-4);
  justify-content: flex-end;
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-color);
}

.modal {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  max-width: 500px;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-6);
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  margin: 0;
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.close-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: var(--radius-lg);
  cursor: pointer;
  color: var(--text-muted);
  transition: all var(--transition-fast);
}

.close-btn:hover {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.modal-body {
  padding: var(--space-6);
}

.modal-actions {
  display: flex;
  gap: var(--space-3);
  justify-content: flex-end;
  margin-top: var(--space-6);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .security-actions {
    flex-direction: column;
  }

  .profile-stats {
    justify-content: center;
  }

  .modal-actions {
    flex-direction: column;
  }
}
</style>
