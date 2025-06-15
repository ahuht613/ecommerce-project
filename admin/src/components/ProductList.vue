<template>
  <div class="product-management">
    <!-- Action Bar -->
    <div class="action-bar">
      <button class="btn btn-primary" @click="showForm = true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Thêm sản phẩm
      </button>
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
          placeholder="Tìm kiếm sản phẩm..."
          v-model="searchQuery"
          class="form-input"
        >
      </div>
      <div class="filter-controls">
        <select v-model="categoryFilter" class="form-input">
          <option value="">Tất cả danh mục</option>
          <option v-for="category in categories" :key="category.id" :value="category.name">
            {{ category.name }}
          </option>
        </select>
        <select v-model="stockFilter" class="form-input">
          <option value="">Tất cả trạng thái</option>
          <option value="in-stock">Còn hàng</option>
          <option value="low-stock">Sắp hết</option>
          <option value="out-of-stock">Hết hàng</option>
        </select>
      </div>
    </div>

    <!-- Products Table -->
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Kho</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in filteredProducts" :key="product.id">
            <td>
              <span class="product-id">{{ product.id }}</span>
            </td>
            <td>
              <div class="product-info">
                <div class="product-image">
                  <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                  <div v-else class="image-placeholder">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <circle cx="9" cy="9" r="2"></circle>
                      <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                  </div>
                </div>
                <div class="product-details">
                  <div class="product-name">{{ product.name }}</div>
                  <div class="product-description">{{ product.description || 'Không có mô tả' }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="category-badge">{{ product.category_name || 'Chưa phân loại' }}</span>
            </td>
            <td class="price-cell">{{ formatCurrency(product.price) }}</td>
            <td>
              <span class="stock-badge" :class="getStockStatus(product.stock_quantity)">
                {{ product.stock_quantity }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn-icon" @click="edit(product)" title="Chỉnh sửa">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </button>
                <button class="btn-icon danger" @click="del(product.id)" title="Xóa">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3,6 5,6 21,6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Product Form Modal -->
    <ProductForm
      v-if="showForm"
      :editProduct="editProduct"
      @close="closeForm"
      @saved="fetchProducts"
    />
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import ProductForm from './ProductForm.vue'

const products = ref([])
const categories = ref([])
const showForm = ref(false)
const editProduct = ref(null)
const searchQuery = ref('')
const categoryFilter = ref('')
const stockFilter = ref('')

const filteredProducts = computed(() => {
  let filtered = products.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(query) ||
      (product.description && product.description.toLowerCase().includes(query))
    )
  }

  if (categoryFilter.value) {
    filtered = filtered.filter(product => product.category_name === categoryFilter.value)
  }

  if (stockFilter.value) {
    switch (stockFilter.value) {
      case 'in-stock':
        filtered = filtered.filter(product => product.stock_quantity > 10)
        break
      case 'low-stock':
        filtered = filtered.filter(product => product.stock_quantity > 0 && product.stock_quantity <= 10)
        break
      case 'out-of-stock':
        filtered = filtered.filter(product => product.stock_quantity === 0)
        break
    }
  }

  // Sort by ID ascending
  return filtered.sort((a, b) => a.id - b.id)
})

function formatCurrency(amount) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount)
}



function getStockStatus(quantity) {
  if (quantity === 0) return 'out-of-stock'
  if (quantity <= 10) return 'low-stock'
  return 'in-stock'
}



async function fetchProducts() {
  try {
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/products.php')
    products.value = res.data
  } catch (error) {
    products.value = []
  }
}

async function fetchCategories() {
  try {
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/categories.php')
    categories.value = res.data
  } catch (error) {
    categories.value = []
  }
}

function edit(product) {
  editProduct.value = product
  showForm.value = true
}

async function del(id) {
  if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
    try {
      await axios.delete(`http://localhost/ecommerce-project/BE/api/products.php?id=${id}`)
      await fetchProducts()
    } catch (error) {

      alert('Có lỗi xảy ra khi xóa sản phẩm')
    }
  }
}

function closeForm() {
  showForm.value = false
  editProduct.value = null
}

function onProductSaved() {
  console.log('🎉 Product saved event received')
  showForm.value = false
  editProduct.value = null
  console.log('🔄 Refreshing products list...')
  fetchProducts()
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
})
</script>

<style scoped>
.product-management {
  max-width: 1200px;
  margin: 0 auto;
}

.action-bar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: var(--admin-space-6);
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
  margin-bottom: var(--admin-space-6);
}

.product-info {
  display: flex;
  align-items: center;
  gap: var(--admin-space-3);
}

.product-image {
  width: 50px;
  height: 50px;
  border-radius: var(--admin-radius-lg);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--admin-bg-tertiary);
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--admin-text-muted);
}

.product-details {
  min-width: 0;
  flex: 1;
}

.product-name {
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
  margin-bottom: 2px;
}

.product-description {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.product-id {
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-secondary);
  font-size: var(--admin-font-sm);
  background: var(--admin-bg-secondary);
  padding: var(--admin-space-1) var(--admin-space-2);
  border-radius: var(--admin-radius-md);
}

.category-badge {
  padding: var(--admin-space-1) var(--admin-space-3);
  border-radius: var(--admin-radius-full);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-medium);
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.price-cell {
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.stock-badge {
  padding: var(--admin-space-1) var(--admin-space-3);
  border-radius: var(--admin-radius-full);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-medium);
}

.stock-badge.in-stock {
  background: rgba(34, 197, 94, 0.1);
  color: #059669;
}

.stock-badge.low-stock {
  background: rgba(251, 191, 36, 0.1);
  color: #d97706;
}

.stock-badge.out-of-stock {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
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

.btn-icon.danger:hover {
  background: var(--admin-error);
  color: var(--admin-text-white);
}

@media (max-width: 768px) {
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-controls {
    flex-direction: column;
  }

  .product-description {
    display: none;
  }
}
</style>
