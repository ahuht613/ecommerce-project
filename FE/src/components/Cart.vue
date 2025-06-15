<template>
  <div class="cart-section">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Giỏ hàng</h1>
        <p class="page-subtitle">Xem lại các sản phẩm bạn đã chọn</p>
      </div>

      <!-- Empty Cart -->
      <div v-if="cart.length === 0" class="empty-cart">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
        </div>
        <h3>Giỏ hàng trống</h3>
        <p>Hãy thêm một số sản phẩm vào giỏ hàng của bạn</p>
        <router-link to="/home" class="btn btn-primary">
          Tiếp tục mua sắm
        </router-link>
      </div>

      <!-- Cart Items -->
      <div v-else class="cart-content">
        <div class="cart-items">
          <div class="cart-header">
            <h3>Sản phẩm trong giỏ ({{ cart.length }})</h3>
          </div>

          <div class="cart-list">
            <div
              v-for="item in cart"
              :key="item.id"
              class="cart-item"
            >
              <div class="item-image">
                <img
                  :src="getProductImage(item.id)"
                  :alt="item.name"
                  loading="lazy"
                />
              </div>

              <div class="item-details">
                <div class="item-main-row">
                  <div class="item-info">
                    <h4 class="item-name">{{ item.name }}</h4>
                    <p class="item-price">{{ formatPrice(item.price) }}</p>
                  </div>

                  <div class="item-quantity">
                    <button
                      @click="updateQuantity(item.id, item.quantity - 1)"
                      class="quantity-btn"
                      :disabled="item.quantity <= 1"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                      </svg>
                    </button>
                    <span class="quantity">{{ item.quantity }}</span>
                    <button
                      @click="updateQuantity(item.id, item.quantity + 1)"
                      class="quantity-btn"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                      </svg>
                    </button>
                  </div>

                  <div class="item-total">
                    <span class="total-price">{{ formatPrice(item.price * item.quantity) }}</span>
                  </div>

                  <div class="item-actions">
                    <button
                      @click="$emit('remove', item.id)"
                      class="remove-btn"
                      title="Xóa sản phẩm"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3,6 5,6 21,6"></polyline>
                        <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary">
          <div class="summary-card">
            <h3>Tóm tắt đơn hàng</h3>

            <div class="summary-row">
              <span>Tạm tính:</span>
              <span>{{ formatPrice(subtotal) }}</span>
            </div>

            <div class="summary-row">
              <span>Phí vận chuyển:</span>
              <span>{{ formatPrice(shippingFee) }}</span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total-row">
              <span>Tổng cộng:</span>
              <span class="total-amount">{{ formatPrice(total) }}</span>
            </div>

            <button
              @click="$emit('checkout')"
              class="btn btn-primary w-full checkout-btn"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4"></path>
                <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
              </svg>
              Tiến hành thanh toán
            </button>

            <router-link
              to="/home"
              class="btn btn-outline w-full mt-3"
            >
              Tiếp tục mua sắm
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatPrice } from '../utils/formatters.js'

const props = defineProps(['cart'])
const emit = defineEmits(['remove', 'checkout', 'change-tab', 'update-quantity'])

const subtotal = computed(() => props.cart.reduce((s, i) => s + i.price * i.quantity, 0))
const shippingFee = computed(() => subtotal.value > 500000 ? 0 : 30000) // Free shipping over 500k
const total = computed(() => subtotal.value + shippingFee.value)

function getProductImage(productId) {
  // This would ideally come from a products store/prop
  return 'https://via.placeholder.com/80x80?text=Product'
}

function updateQuantity(itemId, newQuantity) {
  if (newQuantity < 1) return
  emit('update-quantity', { itemId, quantity: newQuantity })
}
</script>

<style scoped>
.cart-section {
  min-height: 100vh;
  padding: var(--space-8) 0;
  background: var(--bg-secondary);
}

.empty-cart {
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

.empty-cart h3 {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--space-2);
  color: var(--text-primary);
}

.empty-cart p {
  color: var(--text-secondary);
  margin-bottom: var(--space-6);
}

.cart-content {
  display: grid;
  gap: var(--space-8);
  grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
  .cart-content {
    grid-template-columns: 2fr 1fr;
  }
}

.cart-items {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.cart-header {
  padding: var(--space-6);
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-secondary);
}

.cart-header h3 {
  margin: 0;
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.cart-list {
  padding: var(--space-4);
}

.cart-item {
  display: grid;
  grid-template-columns: 80px 1fr;
  gap: var(--space-4);
  align-items: flex-start;
  padding: var(--space-4);
  border-bottom: 1px solid var(--border-color);
  transition: background-color var(--transition-fast);
}

.cart-item:last-child {
  border-bottom: none;
}

.cart-item:hover {
  background: var(--bg-secondary);
}

.item-image img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
}

.item-details {
  min-width: 0;
  width: 100%;
}

.item-main-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  width: 100%;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-name {
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  margin: 0 0 var(--space-1);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.item-price {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin: 0;
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--space-1);
  flex-shrink: 0;
}

.quantity-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.quantity-btn:hover:not(:disabled) {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  min-width: 32px;
  text-align: center;
  font-weight: var(--font-weight-medium);
}

.item-total {
  text-align: right;
  min-width: 100px;
  flex-shrink: 0;
}

.total-price {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--primary-color);
}

.item-actions {
  flex-shrink: 0;
}

.remove-btn {
  width: 36px;
  height: 36px;
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

.remove-btn:hover {
  background: var(--error-color);
  color: var(--text-white);
}

.cart-summary {
  height: fit-content;
  position: sticky;
  top: calc(80px + var(--space-4));
}

.summary-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  padding: var(--space-6);
}

.summary-card h3 {
  margin: 0 0 var(--space-6);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-4);
  font-size: var(--font-size-base);
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-divider {
  height: 1px;
  background: var(--border-color);
  margin: var(--space-4) 0;
}

.total-row {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.total-amount {
  color: var(--primary-color);
}

.checkout-btn {
  margin-top: var(--space-6);
  font-size: var(--font-size-lg);
  padding: var(--space-4) var(--space-6);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .cart-item {
    grid-template-columns: 60px 1fr;
    gap: var(--space-3);
  }

  .item-main-row {
    flex-direction: column;
    align-items: stretch;
    gap: var(--space-3);
  }

  .item-info {
    order: 1;
  }

  .item-quantity {
    order: 2;
    align-self: flex-start;
  }

  .item-total {
    order: 3;
    text-align: left;
    min-width: auto;
  }

  .item-actions {
    order: 4;
    align-self: flex-end;
  }

  .item-name {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
  }
}

@media (max-width: 480px) {
  .item-main-row {
    gap: var(--space-2);
  }

  .item-quantity {
    padding: var(--space-1);
  }

  .quantity-btn {
    width: 28px;
    height: 28px;
  }

  .remove-btn {
    width: 32px;
    height: 32px;
  }
}
</style>
