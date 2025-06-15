<template>
  <div class="order-management">
    <!-- Stats Cards -->

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12,6 12,12 16,14"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ orderStats.pending }}</h3>
          <p class="stat-label">Chờ xử lý</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon confirmed">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20,6 9,17 4,12"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ orderStats.confirmed }}</h3>
          <p class="stat-label">Đã xác nhận</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon shipping">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 3h5v5"></path>
            <path d="M8 3H3v5"></path>
            <path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"></path>
            <path d="M21 3l-7.828 7.828A4 4 0 0 0 12 13.657V22"></path>
          </svg>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ orderStats.shipping }}</h3>
          <p class="stat-label">Đang giao</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon delivered">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ orderStats.delivered }}</h3>
          <p class="stat-label">Hoàn thành</p>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
      <div class="search-box">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="M21 21l-4.35-4.35"></path>
        </svg>
        <input
          type="text"
          placeholder="Tìm kiếm theo ID, khách hàng..."
          v-model="searchQuery"
          class="form-input"
        >
      </div>
      <div class="filter-controls">
        <select v-model="statusFilter" class="form-input">
          <option value="">Tất cả trạng thái</option>
          <option value="pending">Chờ xử lý</option>
          <option value="confirmed">Đã xác nhận</option>
          <option value="processing">Đang xử lý</option>
          <option value="shipping">Đang giao</option>
          <option value="delivered">Đã giao</option>
          <option value="cancelled">Đã hủy</option>
          <option value="completed">Hoàn thành</option>
        </select>
        <input type="date" v-model="dateFilter" class="form-input">
      </div>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Đơn hàng</th>
            <th>Khách hàng</th>
            <th>Sản phẩm</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td>
              <div class="order-info">
                <div class="order-id">#{{ order.id }}</div>
                <div class="order-method">{{ getPaymentMethodText(order.payment_method) }}</div>
              </div>
            </td>
            <td>
              <div class="customer-info">
                <div class="customer-name">{{ order.full_name }}</div>
                <div class="customer-phone">{{ order.phone || 'Không có SĐT' }}</div>
              </div>
            </td>
            <td>
              <div class="items-info">
                <span class="items-count">{{ order.items_count || 0 }} sản phẩm</span>
              </div>
            </td>
            <td class="price-cell">{{ formatCurrency(order.total_amount) }}</td>
            <td>
              <select
                v-model="order.status"
                @change="updateStatus(order)"
                class="status-select"
                :class="order.status"
              >
                <option value="pending">Chờ xử lý</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="processing">Đang xử lý</option>
                <option value="shipping">Đang giao</option>
                <option value="delivered">Đã giao</option>
                <option value="cancelled">Đã hủy</option>
                <option value="completed">Hoàn thành</option>
              </select>
            </td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button class="btn-icon" @click="viewOrder(order)" title="Xem chi tiết">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
                <button class="btn-icon" @click="printOrder(order)" title="In đơn hàng">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 6,2 18,2 18,9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetail
      v-if="showOrderDetail && selectedOrder"
      :order="selectedOrder"
      @close="closeOrderDetail"
    />
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import OrderDetail from './OrderDetail.vue'

const orders = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')
const showOrderDetail = ref(false)
const selectedOrder = ref(null)

const orderStats = computed(() => {
  const stats = {
    pending: 0,
    confirmed: 0,
    shipping: 0,
    delivered: 0
  }

  orders.value.forEach(order => {
    if (order.status === 'pending') stats.pending++
    else if (order.status === 'confirmed') stats.confirmed++
    else if (order.status === 'shipping') stats.shipping++
    else if (order.status === 'delivered' || order.status === 'completed') stats.delivered++
  })

  return stats
})

const filteredOrders = computed(() => {
  let filtered = orders.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(order =>
      order.id.toString().includes(query) ||
      order.full_name.toLowerCase().includes(query) ||
      (order.phone && order.phone.includes(query))
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(order => order.status === statusFilter.value)
  }

  if (dateFilter.value) {
    filtered = filtered.filter(order => {
      const orderDate = new Date(order.created_at).toISOString().split('T')[0]
      return orderDate === dateFilter.value
    })
  }

  return filtered
})

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

function getPaymentMethodText(method) {
  const methodMap = {
    cod: 'Thanh toán khi nhận hàng',
    bank: 'Chuyển khoản',
    card: 'Thẻ tín dụng'
  }
  return methodMap[method] || method
}



async function fetchOrders() {
  try {
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/orders.php')

    if (Array.isArray(res.data)) {
      orders.value = res.data
    } else {
      orders.value = []
    }
  } catch (error) {
    orders.value = []
  }
}

async function updateStatus(order) {
  try {
    await axios.put('http://localhost/ecommerce-project/BE/api/orders.php', {
      id: order.id,
      status: order.status
    })
    console.log(`Order ${order.id} status updated to ${order.status}`)
  } catch (error) {
    console.error('Error updating order status:', error)
    // Revert the status change
    await fetchOrders()
  }
}

function viewOrder(order) {
  selectedOrder.value = order
  showOrderDetail.value = true
}

function closeOrderDetail() {
  showOrderDetail.value = false
  selectedOrder.value = null
}

function printOrder(order) {
  console.log('Print order:', order)
  // Implement print functionality
}



onMounted(fetchOrders)
</script>

<style scoped>
.order-management {
  max-width: 1200px;
  margin: 0 auto;
}



.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--admin-space-4);
  margin-bottom: var(--admin-space-6);
}

.stat-card {
  background: var(--admin-bg-card);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-xl);
  padding: var(--admin-space-4);
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
  box-shadow: var(--admin-shadow-sm);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--admin-radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon.pending { background: var(--admin-warning); }
.stat-icon.confirmed { background: var(--admin-success); }
.stat-icon.shipping { background: var(--admin-accent); }
.stat-icon.delivered { background: var(--admin-primary); }

.stat-content {
  flex: 1;
}

.stat-number {
  margin: 0 0 var(--admin-space-1);
  font-size: var(--admin-font-xl);
  font-weight: var(--admin-font-bold);
  color: var(--admin-text-primary);
}

.stat-label {
  margin: 0;
  font-size: var(--admin-font-sm);
  color: var(--admin-text-secondary);
}

.filters-section {
  display: flex;
  gap: var(--admin-space-4);
  margin-bottom: var(--admin-space-6);
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: var(--admin-space-3);
  top: 50%;
  transform: translateY(-50%);
  color: var(--admin-text-muted);
}

.search-box .form-input {
  padding-left: var(--admin-space-10);
}

.filter-controls {
  display: flex;
  gap: var(--admin-space-3);
}

.filter-controls .form-input {
  min-width: 150px;
}

.table-container {
  background: var(--admin-bg-card);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-xl);
  overflow: hidden;
}

.order-info {
  display: flex;
  flex-direction: column;
}

.order-id {
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.order-method {
  font-size: var(--admin-font-xs);
  color: var(--admin-text-muted);
}

.customer-info {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
}

.customer-phone {
  font-size: var(--admin-font-xs);
  color: var(--admin-text-muted);
}

.items-info {
  display: flex;
  align-items: center;
}

.items-count {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-secondary);
  background: var(--admin-bg-secondary);
  padding: var(--admin-space-1) var(--admin-space-2);
  border-radius: var(--admin-radius-md);
}

.price-cell {
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.status-select {
  padding: var(--admin-space-1) var(--admin-space-2);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-md);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-medium);
  background: var(--admin-bg-primary);
  cursor: pointer;
}

.status-select.pending {
  background: rgba(251, 191, 36, 0.1);
  color: #d97706;
  border-color: rgba(251, 191, 36, 0.3);
}

.status-select.confirmed {
  background: rgba(34, 197, 94, 0.1);
  color: #059669;
  border-color: rgba(34, 197, 94, 0.3);
}

.status-select.processing {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border-color: rgba(59, 130, 246, 0.3);
}

.status-select.shipping {
  background: rgba(14, 165, 233, 0.1);
  color: #0284c7;
  border-color: rgba(14, 165, 233, 0.3);
}

.status-select.delivered,
.status-select.completed {
  background: rgba(34, 197, 94, 0.1);
  color: #059669;
  border-color: rgba(34, 197, 94, 0.3);
}

.status-select.cancelled {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.3);
}

.action-buttons {
  display: flex;
  gap: var(--admin-space-2);
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: var(--admin-radius-md);
  background: var(--admin-bg-secondary);
  color: var(--admin-text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--admin-transition-fast);
}

.btn-icon:hover {
  background: var(--admin-primary);
  color: var(--admin-text-white);
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-controls {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
