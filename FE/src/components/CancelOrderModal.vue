<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal cancel-order-modal">
      <div class="modal-header">
        <h3>Hủy đơn hàng #{{ order.id }}</h3>
        <button @click="$emit('close')" class="close-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <div class="warning-message">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <div>
            <h4>Bạn có chắc chắn muốn hủy đơn hàng này?</h4>
            <p>Sau khi gửi yêu cầu hủy, admin sẽ xem xét và phản hồi trong thời gian sớm nhất.</p>
          </div>
        </div>

        <form @submit.prevent="submitCancelRequest" class="cancel-form">
          <div class="form-group">
            <label class="form-label">Lý do hủy đơn hàng *</label>
            <div class="reason-options">
              <label 
                v-for="reason in cancelReasons" 
                :key="reason.value"
                class="reason-option"
              >
                <input 
                  type="radio" 
                  name="cancel_reason" 
                  :value="reason.value"
                  v-model="selectedReason"
                  required
                />
                <span>{{ reason.label }}</span>
              </label>
            </div>
          </div>

          <div v-if="selectedReason === 'other'" class="form-group">
            <label class="form-label">Lý do khác</label>
            <textarea 
              v-model="otherReason"
              class="form-input"
              placeholder="Vui lòng mô tả lý do hủy đơn hàng..."
              rows="3"
              required
            ></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Ghi chú thêm (tùy chọn)</label>
            <textarea 
              v-model="additionalNotes"
              class="form-input"
              placeholder="Thêm ghi chú nếu cần..."
              rows="2"
            ></textarea>
          </div>

          <div class="order-summary">
            <h4>Thông tin đơn hàng</h4>
            <div class="summary-row">
              <span>Tổng tiền:</span>
              <span>{{ formatPrice(order.total_amount) }}</span>
            </div>
            <div class="summary-row">
              <span>Ngày đặt:</span>
              <span>{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="summary-row">
              <span>Trạng thái:</span>
              <span class="status-badge" :class="getStatusColor(order.status)">
                {{ formatOrderStatus(order.status) }}
              </span>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="$emit('close')" class="btn btn-secondary">
              Không hủy
            </button>
            <button type="submit" class="btn btn-error" :disabled="loading">
              <div v-if="loading" class="spinner"></div>
              {{ loading ? 'Đang gửi yêu cầu...' : 'Xác nhận hủy' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { formatPrice, formatDate, formatOrderStatus, getStatusColor } from '../utils/formatters.js'

const props = defineProps(['order'])
const emit = defineEmits(['close', 'cancel-order'])

const selectedReason = ref('')
const otherReason = ref('')
const additionalNotes = ref('')
const loading = ref(false)

const cancelReasons = [
  { value: 'changed_mind', label: 'Đổi ý, không muốn mua nữa' },
  { value: 'found_better_price', label: 'Tìm được giá tốt hơn ở nơi khác' },
  { value: 'wrong_product', label: 'Đặt nhầm sản phẩm' },
  { value: 'delivery_too_long', label: 'Thời gian giao hàng quá lâu' },
  { value: 'payment_issue', label: 'Vấn đề về thanh toán' },
  { value: 'other', label: 'Lý do khác' }
]

async function submitCancelRequest() {
  if (loading.value) return
  
  loading.value = true
  
  try {
    const cancelData = {
      order_id: props.order.id,
      reason: selectedReason.value,
      other_reason: selectedReason.value === 'other' ? otherReason.value : '',
      additional_notes: additionalNotes.value,
      requested_at: new Date().toISOString()
    }
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    emit('cancel-order', cancelData)
    emit('close')
  } catch (error) {
    console.error('Error submitting cancel request:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.cancel-order-modal {
  max-width: 600px;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-6);
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-secondary);
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

.warning-message {
  display: flex;
  gap: var(--space-4);
  padding: var(--space-4);
  background: #fef3c7;
  border: 1px solid #fde68a;
  border-radius: var(--radius-lg);
  margin-bottom: var(--space-6);
}

.warning-message svg {
  color: #d97706;
  flex-shrink: 0;
}

.warning-message h4 {
  margin: 0 0 var(--space-2);
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-semibold);
  color: #92400e;
}

.warning-message p {
  margin: 0;
  font-size: var(--font-size-sm);
  color: #92400e;
}

.cancel-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.reason-options {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.reason-option {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.reason-option:hover {
  border-color: var(--border-hover);
  background: var(--bg-secondary);
}

.reason-option:has(input:checked) {
  border-color: var(--primary-color);
  background: var(--primary-light);
}

.reason-option input[type="radio"] {
  margin: 0;
}

.reason-option span {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
}

.order-summary {
  background: var(--bg-secondary);
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
}

.order-summary h4 {
  margin: 0 0 var(--space-3);
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-2);
  font-size: var(--font-size-sm);
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-row span:first-child {
  color: var(--text-secondary);
}

.summary-row span:last-child {
  color: var(--text-primary);
  font-weight: var(--font-weight-medium);
}

.status-badge {
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-md);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
}

.modal-actions {
  display: flex;
  gap: var(--space-3);
  justify-content: flex-end;
}

.btn-error {
  background-color: var(--error-color);
  color: var(--text-white);
  border-color: var(--error-color);
}

.btn-error:hover:not(:disabled) {
  background-color: #dc2626;
  border-color: #dc2626;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .cancel-order-modal {
    margin: var(--space-4);
    max-height: calc(100vh - 2rem);
  }
  
  .modal-header,
  .modal-body {
    padding: var(--space-4);
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .warning-message {
    flex-direction: column;
    text-align: center;
  }
}
</style>
