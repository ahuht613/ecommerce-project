<template>
  <div class="card product-card">
    <div class="product-image-container">
      <img
        :src="product.image_url || 'https://via.placeholder.com/300x200?text=No+Image'"
        :alt="product.name"
        class="product-image"
        loading="lazy"
      />
      <div v-if="product.stock_quantity === 0" class="stock-overlay">
        <span class="stock-badge">Hết hàng</span>
      </div>
      <div v-else-if="product.stock_quantity < 10" class="stock-overlay">
        <span class="stock-badge warning">Còn {{ product.stock_quantity }}</span>
      </div>
    </div>

    <div class="card-body">
      <h3 class="product-title">{{ product.name }}</h3>
      <p class="product-description">{{ product.description }}</p>

      <div class="product-footer">
        <div class="price-container">
          <span class="price">{{ formatPrice(product.price) }}</span>
        </div>

        <button
          @click="$emit('add-to-cart', product)"
          :disabled="product.stock_quantity === 0"
          class="btn btn-primary btn-sm add-to-cart-btn"
          :class="{ 'btn-disabled': product.stock_quantity === 0 }"
        >
          <svg v-if="!product.stock_quantity" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
          {{ product.stock_quantity === 0 ? 'Hết hàng' : 'Thêm vào giỏ' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatPrice } from '../utils/formatters.js'

defineProps(['product'])
</script>

<style scoped>
.product-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: all var(--transition-normal);
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.product-image-container {
  position: relative;
  overflow: hidden;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  aspect-ratio: 4/3;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform var(--transition-normal);
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.stock-overlay {
  position: absolute;
  top: var(--space-3);
  right: var(--space-3);
}

.stock-badge {
  background: var(--error-color);
  color: var(--text-white);
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
}

.stock-badge.warning {
  background: var(--warning-color);
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.product-title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-description {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  line-height: 1.5;
  margin: 0;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  margin-top: auto;
  padding-top: var(--space-3);
  border-top: 1px solid var(--border-color);
}

.price-container {
  display: flex;
  align-items: baseline;
  gap: var(--space-1);
}

.price {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--primary-color);
}

.currency {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

.add-to-cart-btn {
  white-space: nowrap;
  min-width: 120px;
}

.btn-disabled {
  background-color: var(--bg-secondary);
  color: var(--text-muted);
  border-color: var(--border-color);
  cursor: not-allowed;
}

.btn-disabled:hover {
  transform: none;
  box-shadow: none;
}
</style>
