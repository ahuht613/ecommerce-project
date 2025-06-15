<template>
  <div class="checkout-modal">
    <div class="checkout-header">
      <h2 class="checkout-title">Thông tin giao hàng</h2>
      <button @click="$emit('cancel')" class="close-btn" type="button">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <form @submit.prevent="submit" class="checkout-form">
      <div class="form-section">
        <h3 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          Địa chỉ giao hàng
        </h3>

        <div class="form-group">
          <label class="form-label">Họ và tên người nhận *</label>
          <input
            v-model="full_name"
            type="text"
            class="form-input"
            placeholder="Nhập họ và tên người nhận hàng"
            required
          />
          <div class="form-hint">
            Họ tên người nhận hàng
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Địa chỉ nhận hàng *</label>
          <textarea
            v-model="shipping_address"
            class="form-input"
            placeholder="Nhập địa chỉ đầy đủ: số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"
            rows="4"
            required
          ></textarea>
          <div class="form-hint">
            Vui lòng nhập địa chỉ chi tiết để đảm bảo giao hàng chính xác
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Số điện thoại liên hệ *</label>
          <input
            v-model="phone"
            type="tel"
            class="form-input"
            placeholder="0123456789"
            pattern="[0-9]{10,11}"
            required
          />
          <div class="form-hint">
            Số điện thoại để shipper liên hệ khi giao hàng
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="3" width="15" height="13"></rect>
            <path d="m16 8 2-2 2 2"></path>
            <path d="M11 6h10"></path>
            <path d="M11 10h10"></path>
            <path d="M11 14h10"></path>
          </svg>
          Phương thức thanh toán
        </h3>

        <div class="payment-methods">
          <label class="payment-option">
            <input
              type="radio"
              name="payment_method"
              value="cod"
              v-model="payment_method"
              class="payment-radio"
            />
            <div class="payment-content">
              <div class="payment-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="1" x2="12" y2="23"></line>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
              </div>
              <div class="payment-details">
                <div class="payment-name">Thanh toán khi nhận hàng (COD)</div>
                <div class="payment-desc">Thanh toán bằng tiền mặt khi nhận hàng</div>
              </div>
            </div>
          </label>

          <label class="payment-option">
            <input
              type="radio"
              name="payment_method"
              value="bank"
              v-model="payment_method"
              class="payment-radio"
            />
            <div class="payment-content">
              <div class="payment-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="1" y="3" width="22" height="18" rx="2" ry="2"></rect>
                  <line x1="1" y1="9" x2="23" y2="9"></line>
                </svg>
              </div>
              <div class="payment-details">
                <div class="payment-name">Chuyển khoản ngân hàng</div>
                <div class="payment-desc">Chuyển khoản trước khi giao hàng</div>
              </div>
            </div>
          </label>
        </div>
      </div>

      <div class="form-section">
        <h3 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 9V5a3 3 0 0 0-6 0v4"></path>
            <rect x="2" y="9" width="20" height="12" rx="2" ry="2"></rect>
          </svg>
          Ghi chú đơn hàng
        </h3>

        <div class="form-group">
          <textarea
            v-model="notes"
            class="form-input"
            placeholder="Ghi chú thêm cho đơn hàng (tùy chọn)"
            rows="3"
          ></textarea>
        </div>
      </div>

      <div class="checkout-actions">
        <button type="button" @click="$emit('cancel')" class="btn btn-secondary">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m15 18-6-6 6-6"></path>
          </svg>
          Quay lại
        </button>

        <button type="submit" class="btn btn-primary" :disabled="loading">
          <div v-if="loading" class="spinner"></div>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 12l2 2 4-4"></path>
            <circle cx="12" cy="12" r="10"></circle>
          </svg>
          {{ loading ? 'Đang xử lý...' : 'Xác nhận đặt hàng' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['order', 'cancel'])

const full_name = ref('')
const shipping_address = ref('')
const phone = ref('')
const payment_method = ref('cod')
const notes = ref('')
const loading = ref(false)

async function submit() {
  if (loading.value) return

  loading.value = true

  try {
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call

    emit('order', {
      full_name: full_name.value,
      shipping_address: shipping_address.value,
      phone: phone.value,
      payment_method: payment_method.value,
      notes: notes.value
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.checkout-modal {
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  background: var(--bg-card);
  border-radius: var(--radius-xl);
}

.checkout-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-6);
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-secondary);
  border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}

.checkout-title {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0;
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

.checkout-form {
  padding: var(--space-6);
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
  margin: 0 0 var(--space-4);
}

.form-hint {
  font-size: var(--font-size-sm);
  color: var(--text-muted);
  margin-top: var(--space-2);
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.payment-option {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  padding: var(--space-4);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.payment-option:hover {
  border-color: var(--border-hover);
  background: var(--bg-secondary);
}

.payment-option:has(.payment-radio:checked) {
  border-color: var(--primary-color);
  background: var(--primary-light);
}

.payment-radio {
  margin-top: var(--space-1);
}

.payment-content {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  flex: 1;
}

.payment-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
  border-radius: var(--radius-lg);
  color: var(--text-secondary);
}

.payment-option:has(.payment-radio:checked) .payment-icon {
  background: var(--primary-color);
  color: var(--text-white);
}

.payment-details {
  flex: 1;
}

.payment-name {
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  margin-bottom: var(--space-1);
}

.payment-desc {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

.checkout-actions {
  display: flex;
  gap: var(--space-4);
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-color);
}

.checkout-actions .btn {
  flex: 1;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .checkout-modal {
    margin: var(--space-4);
    max-height: calc(100vh - 2rem);
  }

  .checkout-header,
  .checkout-form {
    padding: var(--space-4);
  }

  .checkout-actions {
    flex-direction: column;
  }

  .payment-content {
    flex-direction: column;
    gap: var(--space-2);
  }

  .payment-icon {
    width: 32px;
    height: 32px;
  }
}
</style>
