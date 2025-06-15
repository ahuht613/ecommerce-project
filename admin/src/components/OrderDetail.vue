<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h2 class="modal-title">Chi tiết đơn hàng #{{ order.id }}</h2>
        <button type="button" class="close-btn" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <!-- Order Info -->
        <div class="info-section">
          <h3>Thông tin đơn hàng</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Mã đơn hàng:</span>
              <span class="value">#{{ order.id }}</span>
            </div>
            <div class="info-item">
              <span class="label">Ngày đặt:</span>
              <span class="value">{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Trạng thái:</span>
              <span class="value">
                <span class="status-badge" :class="order.status">
                  {{ getStatusText(order.status) }}
                </span>
              </span>
            </div>
            <div class="info-item">
              <span class="label">Phương thức thanh toán:</span>
              <span class="value">{{ getPaymentMethodText(order.payment_method) }}</span>
            </div>
          </div>
        </div>

        <!-- Customer Info -->
        <div class="info-section">
          <h3>Thông tin khách hàng</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Họ tên:</span>
              <span class="value">{{ order.full_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Số điện thoại:</span>
              <span class="value">{{ order.phone }}</span>
            </div>
            <div class="info-item full-width">
              <span class="label">Địa chỉ giao hàng:</span>
              <span class="value">{{ order.shipping_address }}</span>
            </div>
            <div class="info-item full-width" v-if="order.notes">
              <span class="label">Ghi chú:</span>
              <span class="value">{{ order.notes }}</span>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="info-section">
          <h3>Sản phẩm đặt hàng</h3>
          <div class="items-table">
            <table class="table">
              <thead>
                <tr>
                  <th>Sản phẩm</th>
                  <th>Số lượng</th>
                  <th>Đơn giá</th>
                  <th>Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in orderItems" :key="item.id">
                  <td>{{ item.product_name }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.price) }}</td>
                  <td>{{ formatCurrency(item.price * item.quantity) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="total-row">
                  <td colspan="3"><strong>Tổng cộng:</strong></td>
                  <td><strong>{{ formatCurrency(order.total_amount) }}</strong></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Status History -->
        <div class="info-section" v-if="statusHistory.length > 0">
          <h3>Lịch sử trạng thái</h3>
          <div class="status-timeline">
            <div 
              v-for="status in statusHistory" 
              :key="status.id"
              class="timeline-item"
            >
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <div class="timeline-status">{{ getStatusText(status.status) }}</div>
                <div class="timeline-date">{{ formatDateTime(status.created_at) }}</div>
                <div class="timeline-notes" v-if="status.notes">{{ status.notes }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="printOrder">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6,9 6,2 18,2 18,9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
          In đơn hàng
        </button>
        <button type="button" class="btn btn-primary" @click="$emit('close')">
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps(['order'])
const emit = defineEmits(['close'])

const orderItems = ref([])
const statusHistory = ref([])

function formatCurrency(amount) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount)
}

function formatDate(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('vi-VN')
}

function formatDateTime(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('vi-VN')
}

function getStatusText(status) {
  const statusMap = {
    pending: 'Chờ xử lý',
    confirmed: 'Đã xác nhận',
    processing: 'Đang xử lý',
    shipping: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy',
    completed: 'Hoàn thành'
  }
  return statusMap[status] || status
}

function getPaymentMethodText(method) {
  const methodMap = {
    cod: 'Thanh toán khi nhận hàng',
    bank: 'Chuyển khoản ngân hàng',
    card: 'Thẻ tín dụng'
  }
  return methodMap[method] || method
}

function closeModal() {
  emit('close')
}

function printOrder() {
  window.print()
}

async function loadOrderDetails() {
  try {
    // Load order items
    const itemsResponse = await axios.get(`http://localhost/ecommerce-project/BE/api/order_items.php?order_id=${props.order.id}`)
    orderItems.value = itemsResponse.data

    // Load status history
    const historyResponse = await axios.get(`http://localhost/ecommerce-project/BE/api/order_status_history.php?order_id=${props.order.id}`)
    statusHistory.value = historyResponse.data
  } catch (error) {
    // Handle error silently or show user-friendly message
  }
}

onMounted(() => {
  if (props.order) {
    loadOrderDetails()
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--admin-space-4);
}

.modal-container {
  background: var(--admin-bg-card);
  border-radius: var(--admin-radius-xl);
  box-shadow: var(--admin-shadow-xl);
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--admin-space-6);
  border-bottom: 1px solid var(--admin-border);
}

.modal-title {
  margin: 0;
  font-size: var(--admin-font-xl);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.close-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: var(--admin-radius-lg);
  background: var(--admin-bg-secondary);
  color: var(--admin-text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--admin-transition-fast);
}

.close-btn:hover {
  background: var(--admin-error);
  color: var(--admin-text-white);
}

.modal-body {
  padding: var(--admin-space-6);
}

.info-section {
  margin-bottom: var(--admin-space-6);
}

.info-section h3 {
  margin: 0 0 var(--admin-space-4);
  font-size: var(--admin-font-lg);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
  border-bottom: 1px solid var(--admin-border);
  padding-bottom: var(--admin-space-2);
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--admin-space-3);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--admin-space-2) 0;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.label {
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-secondary);
}

.value {
  color: var(--admin-text-primary);
  text-align: right;
}

.info-item.full-width .value {
  text-align: left;
  margin-left: var(--admin-space-3);
}

.status-badge {
  padding: var(--admin-space-1) var(--admin-space-3);
  border-radius: var(--admin-radius-full);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-medium);
}

.status-badge.pending {
  background: rgba(251, 191, 36, 0.1);
  color: #d97706;
}

.status-badge.confirmed {
  background: rgba(34, 197, 94, 0.1);
  color: #059669;
}

.status-badge.processing {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.status-badge.shipping {
  background: rgba(14, 165, 233, 0.1);
  color: #0284c7;
}

.status-badge.delivered,
.status-badge.completed {
  background: rgba(34, 197, 94, 0.1);
  color: #059669;
}

.status-badge.cancelled {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.items-table {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: var(--admin-bg-primary);
  border-radius: var(--admin-radius-lg);
  overflow: hidden;
}

.table th,
.table td {
  padding: var(--admin-space-3);
  text-align: left;
  border-bottom: 1px solid var(--admin-border);
}

.table th {
  background: var(--admin-bg-tertiary);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
  font-size: var(--admin-font-sm);
}

.table td:last-child,
.table th:last-child {
  text-align: right;
}

.total-row {
  background: var(--admin-bg-secondary);
}

.status-timeline {
  position: relative;
  padding-left: var(--admin-space-6);
}

.status-timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--admin-border);
}

.timeline-item {
  position: relative;
  margin-bottom: var(--admin-space-4);
}

.timeline-dot {
  position: absolute;
  left: -12px;
  top: 4px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--admin-primary);
  border: 2px solid var(--admin-bg-card);
}

.timeline-content {
  padding-left: var(--admin-space-4);
}

.timeline-status {
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.timeline-date {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-muted);
  margin-top: var(--admin-space-1);
}

.timeline-notes {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-secondary);
  margin-top: var(--admin-space-1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--admin-space-3);
  padding: var(--admin-space-6);
  border-top: 1px solid var(--admin-border);
}

@media (max-width: 768px) {
  .modal-overlay {
    padding: var(--admin-space-2);
  }
  
  .modal-container {
    max-height: 95vh;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: var(--admin-space-4);
  }
  
  .modal-footer {
    flex-direction: column-reverse;
  }
  
  .modal-footer .btn {
    width: 100%;
  }
}
</style>
