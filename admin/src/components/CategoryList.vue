<template>
  <div class="category-management">
    <div class="page-header">
      <h1>Quản lý danh mục</h1>
      <button @click="showAddForm" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Thêm danh mục
      </button>
    </div>

    <!-- Categories Table -->
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Mô tả</th>
            <th>Số sản phẩm</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in sortedCategories" :key="category.id">
            <td>{{ category.id }}</td>
            <td>
              <div class="category-name">{{ category.name }}</div>
            </td>
            <td>
              <div class="category-description">{{ category.description || 'Không có mô tả' }}</div>
            </td>
            <td>
              <span class="product-count">{{ category.product_count || 0 }} sản phẩm</span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn-icon" @click="editCategory(category)" title="Chỉnh sửa">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </button>
                <button class="btn-icon danger" @click="deleteCategory(category.id)" title="Xóa">
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

    <!-- Category Form Modal -->
    <CategoryForm
      v-if="showForm"
      :editCategory="editCategoryData"
      @close="closeForm"
      @saved="fetchCategories"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import CategoryForm from './CategoryForm.vue'

const categories = ref([])
const showForm = ref(false)
const editCategoryData = ref(null)

const sortedCategories = computed(() => {
  return [...categories.value].sort((a, b) => a.id - b.id)
})

async function fetchCategories() {
  try {
    const res = await axios.get('http://localhost/ecommerce-project/BE/api/categories.php')
    categories.value = res.data
  } catch (error) {
    categories.value = []
  }
}

function showAddForm() {
  editCategoryData.value = null
  showForm.value = true
}

function editCategory(category) {
  editCategoryData.value = category
  showForm.value = true
}

async function deleteCategory(id) {
  if (confirm('Bạn có chắc chắn muốn xóa danh mục này? Các sản phẩm trong danh mục sẽ chuyển về "Chưa phân loại".')) {
    try {
      await axios.delete(`http://localhost/ecommerce-project/BE/api/categories.php?id=${id}`)
      await fetchCategories()
    } catch (error) {
      alert('Có lỗi xảy ra khi xóa danh mục')
    }
  }
}

function closeForm() {
  showForm.value = false
  editCategoryData.value = null
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
.category-management {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--admin-space-6);
}

.page-header h1 {
  margin: 0;
  font-size: var(--admin-font-2xl);
  font-weight: var(--admin-font-bold);
  color: var(--admin-text-primary);
}

.table-container {
  background: var(--admin-bg-card);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-xl);
  overflow: hidden;
}

.category-name {
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
}

.category-description {
  color: var(--admin-text-secondary);
  font-size: var(--admin-font-sm);
}

.product-count {
  padding: var(--admin-space-1) var(--admin-space-3);
  border-radius: var(--admin-radius-full);
  font-size: var(--admin-font-xs);
  font-weight: var(--admin-font-medium);
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
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
</style>
