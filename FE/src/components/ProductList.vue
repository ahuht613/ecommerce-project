<template>
  <div class="products-section">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Sản phẩm</h1>
      </div>

      <!-- Filters and Search -->
      <div class="filters-section mb-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="search-container">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm sản phẩm..."
              class="form-input search-input"
            />
          </div>

          <div class="flex items-center gap-4">
            <select v-model="sortBy" class="form-input" style="min-width: 150px;">
              <option value="price-asc">Giá thấp đến cao</option>
              <option value="price-desc">Giá cao đến thấp</option>
              <option value="stock">Còn hàng</option>
            </select>

            <div class="view-toggle">
              <button
                @click="viewMode = 'grid'"
                :class="{ active: viewMode === 'grid' }"
                class="view-btn"
                title="Xem dạng lưới"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
              </button>
              <button
                @click="viewMode = 'list'"
                :class="{ active: viewMode === 'list' }"
                class="view-btn"
                title="Xem dạng danh sách"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="8" y1="6" x2="21" y2="6"></line>
                  <line x1="8" y1="12" x2="21" y2="12"></line>
                  <line x1="8" y1="18" x2="21" y2="18"></line>
                  <line x1="3" y1="6" x2="3.01" y2="6"></line>
                  <line x1="3" y1="12" x2="3.01" y2="12"></line>
                  <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid/List -->
      <div v-if="filteredProducts.length > 0"
           class="products-grid"
           :class="{
             'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4': viewMode === 'grid',
             'grid-cols-1': viewMode === 'list'
           }">
        <ProductItem
          v-for="product in filteredProducts"
          :key="product.id"
          :product="product"
          @add-to-cart="$emit('add-to-cart', $event)"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
        </div>
        <h3>Không tìm thấy sản phẩm</h3>
        <p>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ProductItem from './ProductItem.vue'

const props = defineProps(['products'])

const searchQuery = ref('')
const sortBy = ref('price-asc')
const viewMode = ref('grid')

const filteredProducts = computed(() => {
  // Ensure products is always an array
  let filtered = Array.isArray(props.products) ? props.products : []

  // If products is not an array, return empty array
  if (!Array.isArray(filtered)) {
    console.error('Products is not an array:', props.products)
    return []
  }

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(product =>
      product && product.name && product.name.toLowerCase().includes(query) ||
      product && product.description && product.description.toLowerCase().includes(query)
    )
  }

  // Sort
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'price-asc':
        return (a.price || 0) - (b.price || 0)
      case 'price-desc':
        return (b.price || 0) - (a.price || 0)
      case 'stock':
        return (b.stock_quantity || 0) - (a.stock_quantity || 0)
      default:
        return (a.price || 0) - (b.price || 0)
    }
  })

  return filtered
})
</script>

<style scoped>
.products-section {
  min-height: 100vh;
  padding: var(--space-8) 0;
}

.filters-section {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  padding: var(--space-6);
  box-shadow: var(--shadow-sm);
}

.search-input {
  min-width: 300px;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23718096'%3e%3ccircle cx='11' cy='11' r='8'/%3e%3cpath d='m21 21-4.35-4.35'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: left var(--space-3) center;
  background-size: 20px 20px;
  padding-left: calc(var(--space-3) + 20px + var(--space-2));
}

.view-toggle {
  display: flex;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.view-btn {
  padding: var(--space-2) var(--space-3);
  background: var(--bg-primary);
  border: none;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.view-btn:hover {
  background: var(--bg-secondary);
}

.view-btn.active {
  background: var(--primary-color);
  color: var(--text-white);
}

.products-grid {
  display: grid;
  gap: var(--space-6);
}

.empty-state {
  text-align: center;
  padding: var(--space-16) var(--space-4);
  color: var(--text-secondary);
}

.empty-icon {
  margin: 0 auto var(--space-6);
  color: var(--text-muted);
}

.empty-state h3 {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--space-2);
  color: var(--text-primary);
}

.empty-state p {
  font-size: var(--font-size-base);
  margin: 0;
}
</style>
