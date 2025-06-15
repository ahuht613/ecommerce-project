<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal order-details-modal">
      <div class="modal-header">
        <h3>Chi tiết đơn hàng #{{ order.id }}</h3>
        <button @click="$emit('close')" class="close-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <!-- Order Status Timeline -->
        <div class="status-timeline">
          <h4>Tiến trình đơn hàng</h4>
          <div class="timeline">
            <div 
              v-for="(status, index) in orderTimeline" 
              :key="index"
              class="timeline-item"
              :class="{ 
                active: status.active, 
                completed: status.completed,
                current: status.current 
              }"
            >
              <div class="timeline-marker">
                <svg v-if="status.completed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 12l2 2 4-4"></path>
                  <circle cx="12" cy="12" r="10"></circle>
                </svg>
                <div v-else class="timeline-dot"></div>
              </div>
              <div class="timeline-content">
                <div class="timeline-title">{{ status.title }}</div>
                <div class="timeline-description">{{ status.description }}</div>
                <div v-if="status.timestamp" class="timeline-time">
                  {{ formatDate(status.timestamp) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Information -->
        <div class="order-info-section">
          <h4>Thông tin đơn hàng</h4>
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Mã đơn hàng:</span>
              <span class="info-value">#{{ order.id }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Ngày đặt hàng:</span>
              <span class="info-value">{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Trạng thái:</span>
              <span class="status-badge" :class="getStatusColor(order.status)">
                {{ formatOrderStatus(order.status) }}
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Phương thức thanh toán:</span>
              <span class="info-value">{{ getPaymentMethodText(order.payment_method) }}</span>
            </div>
          </div>
        </div>

        <!-- Shipping Information -->
        <div class="shipping-info-section">
          <h4>Thông tin giao hàng</h4>
          <div class="shipping-details">
            <div class="shipping-item">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <div>
                <div class="shipping-label">Địa chỉ giao hàng:</div>
                <div class="shipping-value">{{ order.shipping_address || 'Chưa cập nhật' }}</div>
              </div>
            </div>
            <div class="shipping-item">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              <div>
                <div class="shipping-label">Số điện thoại:</div>
                <div class="shipping-value">{{ formatPhone(order.phone) || 'Chưa cập nhật' }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="order-items-section">
          <h4>Sản phẩm đã đặt</h4>
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Đang tải sản phẩm...</p>
          </div>
          <div v-else-if="orderItems.length === 0" class="empty-state">
            <p>Không có sản phẩm nào trong đơn hàng này.</p>
          </div>
          <div v-else class="items-list">
            <div
              v-for="item in orderItems"
              :key="item.id"
              class="order-item"
            >
              <div class="item-image">
                <img
                  :src="item.product_image || 'https://via.placeholder.com/60x60?text=Product'"
                  :alt="item.product_name"
                />
              </div>
              <div class="item-details">
                <div class="item-name">{{ item.product_name }}</div>
                <div class="item-price">{{ formatPrice(item.price) }} x {{ item.quantity }}</div>
              </div>
              <div class="item-total">
                {{ formatPrice(item.price * item.quantity) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary-section">
          <h4>Tóm tắt đơn hàng</h4>
          <div class="summary-details">
            <div class="summary-row">
              <span>Tạm tính:</span>
              <span>{{ formatPrice(calculateSubtotal()) }}</span>
            </div>
            <div class="summary-row">
              <span>Phí vận chuyển:</span>
              <span>{{ formatPrice(calculateShipping()) }}</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row total-row">
              <span>Tổng cộng:</span>
              <span>{{ formatPrice(order.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="notes-section">
          <h4>Ghi chú</h4>
          <div class="notes-content">
            {{ order.notes }}
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="$emit('close')" class="btn btn-secondary">
          Đóng
        </button>
        <button 
          v-if="canCancelOrder(order.status)"
          @click="$emit('request-cancel')"
          class="btn btn-outline"
        >
          Yêu cầu hủy đơn
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { formatPrice, formatDate, formatPhone, formatOrderStatus, getStatusColor } from '../utils/formatters.js'
import axios from 'axios'

const props = defineProps(['order'])
const emit = defineEmits(['close', 'request-cancel'])

const orderItems = ref([])
const loading = ref(false)

const orderTimeline = computed(() => {
  const currentStatus = props.order.status
  const timeline = [
    {
      title: 'Đơn hàng đã được đặt',
      description: 'Đơn hàng của bạn đã được tiếp nhận',
      status: 'pending',
      timestamp: props.order.created_at
    },
    {
      title: 'Đã xác nhận',
      description: 'Đơn hàng đã được xác nhận và chuẩn bị',
      status: 'confirmed'
    },
    {
      title: 'Đang xử lý',
      description: 'Đơn hàng đang được chuẩn bị và đóng gói',
      status: 'processing'
    },
    {
      title: 'Đang giao hàng',
      description: 'Đơn hàng đang trên đường giao đến bạn',
      status: 'shipping'
    },
    {
      title: 'Đã giao hàng',
      description: 'Đơn hàng đã được giao thành công',
      status: 'delivered'
    }
  ]

  const statusOrder = ['pending', 'confirmed', 'processing', 'shipping', 'delivered']
  const currentIndex = statusOrder.indexOf(currentStatus)

  return timeline.map((item, index) => ({
    ...item,
    completed: index < currentIndex,
    current: index === currentIndex,
    active: index <= currentIndex
  }))
})

function getPaymentMethodText(method) {
  const methods = {
    'cod': 'Thanh toán khi nhận hàng',
    'bank': 'Chuyển khoản ngân hàng',
    'card': 'Thẻ tín dụng'
  }
  return methods[method] || 'Chưa xác định'
}

function canCancelOrder(status) {
  return ['pending', 'confirmed'].includes(status)
}

async function loadOrderItems() {
  try {
    loading.value = true
    const response = await axios.get(`http://localhost/ecommerce-project/BE/api/order_items.php?order_id=${props.order.id}`)
    orderItems.value = response.data
  } catch (error) {
    orderItems.value = []
  } finally {
    loading.value = false
  }
}

function calculateSubtotal() {
  if (!orderItems.value.length) return props.order.total_amount
  return orderItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
}

function calculateShipping() {
  const subtotal = calculateSubtotal()
  return subtotal > 500000 ? 0 : 30000
}

onMounted(() => {
  if (props.order?.id) {
    loadOrderItems()
  }
})
</script>

<style scoped>
.order-details-modal {
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
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
  font-size: var(--font-size-xl);
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
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.modal-body h4 {
  margin: 0 0 var(--space-4);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

/* Timeline Styles */
.timeline {
  position: relative;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--border-color);
}

.timeline-item {
  position: relative;
  display: flex;
  gap: var(--space-4);
  margin-bottom: var(--space-6);
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: relative;
  z-index: 1;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 2px;
}

.timeline-item.completed .timeline-marker {
  background: var(--success-color);
  color: var(--text-white);
}

.timeline-item.current .timeline-marker {
  background: var(--primary-color);
}

.timeline-dot {
  width: 8px;
  height: 8px;
  background: var(--border-color);
  border-radius: 50%;
}

.timeline-item.active .timeline-dot {
  background: var(--text-white);
  border: 2px solid var(--primary-color);
}

.timeline-content {
  flex: 1;
}

.timeline-title {
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin-bottom: var(--space-1);
}

.timeline-description {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin-bottom: var(--space-1);
}

.timeline-time {
  font-size: var(--font-size-xs);
  color: var(--text-muted);
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--space-4);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-3);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
}

.info-label {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
}

.info-value {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
  font-weight: var(--font-weight-medium);
}

/* Shipping Info */
.shipping-details {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.shipping-item {
  display: flex;
  gap: var(--space-3);
  padding: var(--space-4);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
}

.shipping-item svg {
  color: var(--text-secondary);
  flex-shrink: 0;
  margin-top: 2px;
}

.shipping-label {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
  margin-bottom: var(--space-1);
}

.shipping-value {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
}

/* Order Items */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-8);
  text-align: center;
  color: var(--text-secondary);
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: var(--space-3);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.order-item {
  display: flex;
  gap: var(--space-4);
  align-items: center;
  padding: var(--space-4);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
}

.item-image img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: var(--radius-lg);
}

.item-details {
  flex: 1;
}

.item-name {
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  margin-bottom: var(--space-1);
}

.item-price {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

.item-total {
  font-weight: var(--font-weight-semibold);
  color: var(--primary-color);
}

/* Order Summary */
.summary-details {
  background: var(--bg-secondary);
  padding: var(--space-4);
  border-radius: var(--radius-lg);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-3);
  font-size: var(--font-size-sm);
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-divider {
  height: 1px;
  background: var(--border-color);
  margin: var(--space-3) 0;
}

.total-row {
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.total-row span:last-child {
  color: var(--primary-color);
}

/* Notes */
.notes-content {
  padding: var(--space-4);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  font-size: var(--font-size-sm);
  color: var(--text-primary);
  line-height: 1.6;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  gap: var(--space-3);
  justify-content: flex-end;
  padding: var(--space-6);
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.status-badge {
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-md);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .order-details-modal {
    margin: var(--space-4);
    max-height: calc(100vh - 2rem);
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: var(--space-4);
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .order-item {
    flex-direction: column;
    text-align: center;
  }
}
</style>
