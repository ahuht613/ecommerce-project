<template>
  <div id="app">
    <!-- Login/Register Screen -->
    <LoginRegister v-if="!isLoggedIn" @login-success="onLoginSuccess"/>

    <!-- Main Application -->
    <div v-else class="app-layout">
      <CustomerHeader
        :cartCount="cartCount"
        :user="user"
        @logout="logout"
      />

      <main class="main">
        <transition name="fade" mode="out-in">
          <router-view
            :products="products"
            :cart="cart"
            :orders="orders"
            :user="user"
            @add-to-cart="addToCart"
            @remove="removeFromCart"
            @update-quantity="updateCartQuantity"
            @checkout="showCheckout = true"
            @update="updateProfile"
            @cancel-order="cancelOrder"
            @reorder="reorderItems"
          />
        </transition>
      </main>

      <!-- Checkout Modal -->
      <div v-if="showCheckout" class="modal-overlay" @click.self="showCheckout = false">
        <div class="modal">
          <Checkout @order="placeOrder" @cancel="showCheckout = false"/>
        </div>
      </div>

      <!-- Loading Overlay -->
      <div v-if="loading" class="loading-overlay">
        <div class="loading-content">
          <div class="spinner"></div>
          <p>Đang tải...</p>
        </div>
      </div>

      <!-- Toast Notifications -->
      <div class="toast-container">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast"
          :class="toast.type"
        >
          {{ toast.message }}
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CustomerHeader from './components/CustomerHeader.vue'
import Checkout from './components/Checkout.vue'
import LoginRegister from './components/LoginRegister.vue'
import axios from 'axios'

// Router
const router = useRouter()

// State
const isLoggedIn = ref(false)
const user = ref({})
const products = ref([])
const cart = ref([])
const orders = ref([])
const showCheckout = ref(false)
const loading = ref(false)
const toasts = ref([])

// Computed
const cartCount = computed(() => cart.value.reduce((s, i) => s + i.quantity, 0))

// Toast system
let toastId = 0
function showToast(message, type = 'success') {
  const toast = {
    id: ++toastId,
    message,
    type
  }
  toasts.value.push(toast)

  setTimeout(() => {
    const index = toasts.value.findIndex(t => t.id === toast.id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }, 3000)
}

// Auth functions
async function onLoginSuccess(data) {
  isLoggedIn.value = true
  user.value = data.user
  localStorage.setItem('customer_token', data.token)
  localStorage.setItem('customer_user', JSON.stringify(data.user))
  axios.defaults.headers['Authorization'] = `Bearer ${data.token}`

  // Load fresh profile data
  await loadUserProfile()
  await fetchOrders()
  showToast(`Chào mừng ${data.user.full_name || data.user.name}!`)
}

function logout() {
  isLoggedIn.value = false
  user.value = {}
  localStorage.removeItem('customer_token')
  localStorage.removeItem('customer_user')
  cart.value = []
  delete axios.defaults.headers['Authorization']
  showToast('Đã đăng xuất thành công')
}

// Cart functions
function addToCart(product) {
  const found = cart.value.find(i => i.id === product.id)
  if (found) {
    found.quantity++
    showToast(`Đã tăng số lượng ${product.name}`)
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1
    })
    showToast(`Đã thêm ${product.name} vào giỏ hàng`)
  }
}

function removeFromCart(id) {
  const item = cart.value.find(i => i.id === id)
  cart.value = cart.value.filter(i => i.id !== id)
  if (item) {
    showToast(`Đã xóa ${item.name} khỏi giỏ hàng`)
  }
}

// API functions
async function fetchProducts() {
  try {
    loading.value = true
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/products.php')

    // Ensure we always set an array
    if (Array.isArray(res.data)) {
      products.value = res.data
    } else {
      products.value = []
      showToast('Lỗi: Dữ liệu sản phẩm không hợp lệ', 'error')
    }
  } catch (error) {
    products.value = []
    showToast('Lỗi khi tải sản phẩm', 'error')
  } finally {
    loading.value = false
  }
}

async function fetchOrders() {
  try {
    if (!isLoggedIn.value) {
      orders.value = []
      return
    }
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/orders.php')
    orders.value = res.data
  } catch (error) {
    console.error('Error fetching orders:', error)
    orders.value = []
    // Don't show error toast for orders if not logged in
    if (isLoggedIn.value) {
      showToast('Lỗi khi tải đơn hàng', 'error')
    }
  }
}

async function placeOrder(data) {
  try {
    loading.value = true

    // Validate required data
    if (!data.phone || !data.shipping_address) {
      showToast('Vui lòng điền đầy đủ thông tin giao hàng', 'error')
      return
    }

    if (!cart.value || cart.value.length === 0) {
      showToast('Giỏ hàng trống', 'error')
      return
    }

    if (!isLoggedIn.value) {
      showToast('Vui lòng đăng nhập để đặt hàng', 'error')
      return
    }

    const orderData = {
      full_name: data.full_name || user.value?.full_name || 'Customer',
      phone: data.phone,
      shipping_address: data.shipping_address,
      payment_method: data.payment_method || 'cod',
      total_amount: cart.value.reduce((s, i) => s + (i.price * i.quantity), 0),
      notes: data.notes || '',
      items: cart.value.map(i => ({
        product_id: i.id,
        product_name: i.name,
        quantity: i.quantity,
        price: i.price
      }))
    }

    await axios.post('http://localhost/ecommerce-project/BE/api/orders.php', orderData)

    cart.value = []
    showCheckout.value = false
    await fetchOrders()

    // Redirect to orders page
    router.push('/orders')
    showToast('Đặt hàng thành công! Đơn hàng đang chờ xử lý.')
  } catch (error) {
    showToast('Lỗi khi đặt hàng: ' + (error.response?.data?.message || error.message), 'error')
  } finally {
    loading.value = false
  }
}

function updateCartQuantity({ itemId, quantity }) {
  const item = cart.value.find(i => i.id === itemId)
  if (item) {
    item.quantity = quantity
    showToast(`Đã cập nhật số lượng ${item.name}`)
  }
}

async function loadUserProfile() {
  try {
    const response = await axios.get('http://localhost/ecommerce-project/BE/api/profile.php')
    user.value = response.data
    localStorage.setItem('customer_user', JSON.stringify(response.data))
  } catch (error) {
    console.error('Error loading profile:', error)
  }
}

function updateProfile(updatedProfile) {
  user.value = { ...user.value, ...updatedProfile }
  localStorage.setItem('customer_user', JSON.stringify(user.value))
  showToast('Thông tin cá nhân đã được cập nhật!', 'success')
}

function cancelOrder() {
  // TODO: Implement cancel order API
  showToast('Chức năng hủy đơn hàng đang được phát triển', 'warning')
}

function reorderItems(order) {
  // Add order items back to cart
  if (order.items && order.items.length > 0) {
    order.items.forEach(item => {
      const existingItem = cart.value.find(i => i.id === item.product_id)
      if (existingItem) {
        existingItem.quantity += item.quantity
      } else {
        cart.value.push({
          id: item.product_id,
          name: item.product_name || item.name,
          price: item.price,
          quantity: item.quantity
        })
      }
    })
    showToast('Đã thêm sản phẩm vào giỏ hàng')
  }
}

// Check for existing session on mount
onMounted(async () => {
  const token = localStorage.getItem('customer_token')
  const userData = localStorage.getItem('customer_user')

  if (token && userData) {
    try {
      axios.defaults.headers['Authorization'] = `Bearer ${token}`
      isLoggedIn.value = true
      user.value = JSON.parse(userData)

      // Load fresh profile data
      await loadUserProfile()
      await fetchOrders()
    } catch (error) {
      // Token might be expired, clear storage
      localStorage.removeItem('customer_token')
      localStorage.removeItem('customer_user')
      delete axios.defaults.headers['Authorization']
    }
  }

  await fetchProducts()
})
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main {
  flex: 1;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-normal);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Loading overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--bg-overlay);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: var(--z-modal);
}

.loading-content {
  background: var(--bg-card);
  padding: var(--space-8);
  border-radius: var(--radius-xl);
  text-align: center;
  box-shadow: var(--shadow-xl);
}

.loading-content .spinner {
  width: 2rem;
  height: 2rem;
  margin: 0 auto var(--space-4);
}

.loading-content p {
  margin: 0;
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
}

/* Toast notifications */
.toast-container {
  position: fixed;
  top: var(--space-4);
  right: var(--space-4);
  z-index: var(--z-tooltip);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  max-width: 400px;
}

.toast {
  padding: var(--space-4) var(--space-6);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  font-weight: var(--font-weight-medium);
  animation: slideIn 0.3s ease-out;
}

.toast.success {
  background: var(--success-color);
  color: var(--text-white);
}

.toast.error {
  background: var(--error-color);
  color: var(--text-white);
}

.toast.warning {
  background: var(--warning-color);
  color: var(--text-white);
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .toast-container {
    left: var(--space-4);
    right: var(--space-4);
    max-width: none;
  }

  .loading-content {
    margin: var(--space-4);
    padding: var(--space-6);
  }
}
</style>
