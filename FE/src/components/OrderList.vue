<template>
  <div class="orders-section">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Đơn hàng của bạn</h1>
        <p class="page-subtitle">Theo dõi trạng thái và lịch sử đơn hàng</p>
      </div>

      <!-- Empty State -->
      <div v-if="!orders || orders.length === 0" class="empty-orders">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
          </svg>
        </div>
        <h3>Chưa có đơn hàng nào</h3>
        <p>Bạn chưa đặt đơn hàng nào. Hãy khám phá các sản phẩm của chúng tôi!</p>
        <router-link to="/home" class="btn btn-primary">
          Mua sắm ngay
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="orders-list">
        <div
          v-for="order in sortedOrders"
          :key="order.id"
          class="order-card"
        >
          <div class="order-header">
            <div class="order-info">
              <h3 class="order-number">Đơn hàng #{{ order.id }}</h3>
              <span class="order-date">{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="order-status">
              <span class="status-badge" :class="getStatusColor(order.status)">
                {{ formatOrderStatus(order.status) }}
              </span>
            </div>
          </div>

          <div class="order-body">
            <div class="order-details">
              <div class="detail-row">
                <span class="detail-label">Tổng tiền:</span>
                <span class="detail-value total-amount">{{ formatPrice(order.total_amount) }}</span>
              </div>

              <div v-if="order.shipping_address" class="detail-row">
                <span class="detail-label">Địa chỉ giao hàng:</span>
                <span class="detail-value">{{ order.shipping_address }}</span>
              </div>

              <div v-if="order.phone" class="detail-row">
                <span class="detail-label">Số điện thoại:</span>
                <span class="detail-value">{{ formatPhone(order.phone) }}</span>
              </div>

              <div v-if="order.payment_method" class="detail-row">
                <span class="detail-label">Phương thức thanh toán:</span>
                <span class="detail-value">{{ getPaymentMethodText(order.payment_method) }}</span>
              </div>
            </div>

            <!-- Order Items -->
            <div v-if="order.items && order.items.length > 0" class="order-items">
              <h4 class="items-title">Sản phẩm đã đặt:</h4>
              <div class="items-list">
                <div
                  v-for="item in order.items"
                  :key="item.id || item.product_id"
                  class="order-item"
                >
                  <div class="item-info">
                    <span class="item-name">{{ item.product_name || item.name }}</span>
                    <span class="item-quantity">x{{ item.quantity }}</span>
                  </div>
                  <span class="item-price">{{ formatPrice(item.price * item.quantity) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="order-footer">
            <div class="order-actions">
              <button
                @click="showDetailsModal(order)"
                class="btn btn-primary btn-sm"
              >
                Xem chi tiết
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Details Modal -->
    <OrderDetailsModal
      v-if="showOrderDetailsModal && selectedOrder"
      :order="selectedOrder"
      @close="closeDetailsModal"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { formatPrice, formatDate, formatPhone, formatOrderStatus, getStatusColor } from '../utils/formatters.js'
import OrderDetailsModal from './OrderDetailsModal.vue'

const props = defineProps(['orders'])

const showOrderDetailsModal = ref(false)
const selectedOrder = ref(null)

const sortedOrders = computed(() => {
  if (!props.orders) return []
  return [...props.orders].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

function getPaymentMethodText(method) {
  const methods = {
    'cod': 'Thanh toán khi nhận hàng',
    'bank': 'Chuyển khoản ngân hàng',
    'card': 'Thẻ tín dụng'
  }
  return methods[method] || 'Chưa xác định'
}

function showDetailsModal(order) {
  selectedOrder.value = order
  showOrderDetailsModal.value = true
}

function closeDetailsModal() {
  showOrderDetailsModal.value = false
  selectedOrder.value = null
}
</script>

<style scoped>
.orders-section {
  min-height: 100vh;
  padding: var(--space-8) 0;
  background: var(--bg-secondary);
}

.empty-orders {
  text-align: center;
  padding: var(--space-16) var(--space-4);
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
}

.empty-icon {
  margin: 0 auto var(--space-6);
  color: var(--text-muted);
}

.empty-orders h3 {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--space-2);
  color: var(--text-primary);
}

.empty-orders p {
  color: var(--text-secondary);
  margin-bottom: var(--space-6);
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.order-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: box-shadow var(--transition-normal);
}

.order-card:hover {
  box-shadow: var(--shadow-md);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: var(--space-6);
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.order-info h3 {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0 0 var(--space-1);
}

.order-date {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

.status-badge {
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  text-transform: uppercase;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-confirmed {
  background: #dbeafe;
  color: #1e40af;
}

.status-processing {
  background: #e0e7ff;
  color: #3730a3;
}

.status-shipping {
  background: #fde68a;
  color: #92400e;
}

.status-delivered {
  background: #dcfce7;
  color: #166534;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.status-completed {
  background: #dcfce7;
  color: #166534;
}

.order-body {
  padding: var(--space-6);
}

.order-details {
  margin-bottom: var(--space-6);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-3);
}

.detail-row:last-child {
  margin-bottom: 0;
}

.detail-label {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
}

.detail-value {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
  text-align: right;
  max-width: 60%;
  word-break: break-word;
}

.total-amount {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--primary-color);
}

.order-items {
  border-top: 1px solid var(--border-color);
  padding-top: var(--space-4);
}

.items-title {
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  margin: 0 0 var(--space-3);
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-2) var(--space-3);
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
}

.item-info {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  flex: 1;
}

.item-name {
  font-size: var(--font-size-sm);
  color: var(--text-primary);
}

.item-quantity {
  font-size: var(--font-size-xs);
  color: var(--text-muted);
  background: var(--bg-tertiary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-md);
}

.item-price {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
}

.order-footer {
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.order-actions {
  display: flex;
  gap: var(--space-3);
  justify-content: flex-end;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .order-header {
    flex-direction: column;
    gap: var(--space-3);
    align-items: stretch;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-1);
  }

  .detail-value {
    text-align: left;
    max-width: 100%;
  }

  .order-actions {
    flex-direction: column;
  }

  .order-actions .btn {
    width: 100%;
  }
}
</style>
