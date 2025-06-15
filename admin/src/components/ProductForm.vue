<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h2 class="modal-title">
          {{ editProduct ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới' }}
        </h2>
        <button type="button" class="close-btn" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <form @submit.prevent="save" class="modal-form">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Tên sản phẩm *</label>
            <input
              type="text"
              v-model="form.name"
              class="form-input"
              placeholder="Nhập tên sản phẩm"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Danh mục</label>
            <select v-model="form.category_id" class="form-input">
              <option value="">Chọn danh mục</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Giá bán *</label>
            <input
              type="number"
              v-model="form.price"
              class="form-input"
              placeholder="0"
              min="0"
              step="1000"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Số lượng tồn kho *</label>
            <input
              type="number"
              v-model="form.stock_quantity"
              class="form-input"
              placeholder="0"
              min="0"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mô tả sản phẩm</label>
          <textarea
            v-model="form.description"
            class="form-input"
            rows="4"
            placeholder="Nhập mô tả chi tiết về sản phẩm..."
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Hình ảnh sản phẩm</label>
          <ImageUpload
            v-model="form.image_url"
            type="product"
            placeholder="Chọn ảnh sản phẩm"
            alt="Product image"
            @uploaded="onImageUploaded"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select v-model="form.status" class="form-input">
            <option value="active">Hoạt động</option>
            <option value="inactive">Tạm ngưng</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            Hủy bỏ
          </button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <svg v-if="saving" class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 11-6.219-8.56"></path>
            </svg>
            {{ saving ? 'Đang lưu...' : (editProduct ? 'Cập nhật' : 'Thêm mới') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import ImageUpload from './ImageUpload.vue'

const props = defineProps(['editProduct'])
const emit = defineEmits(['close', 'saved'])

const form = ref({
  name: '',
  description: '',
  price: '',
  stock_quantity: '',
  category_id: '',
  image_url: '',
  status: 'active'
})

const categories = ref([])
const saving = ref(false)

watch(() => props.editProduct, (val) => {
  if (val) {
    Object.assign(form.value, {
      name: val.name || '',
      description: val.description || '',
      price: val.price || '',
      stock_quantity: val.stock_quantity || '',
      category_id: val.category_id || '',
      image_url: val.image_url || '',
      status: val.status || 'active'
    })
  } else {
    form.value = {
      name: '',
      description: '',
      price: '',
      stock_quantity: '',
      category_id: '',
      image_url: '',
      status: 'active'
    }
  }
}, { immediate: true })

async function loadCategories() {
  try {
    const response = await axios.get('http://localhost/ecommerce-project/BE/api/categories.php')
    categories.value = response.data
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

async function save() {
  if (saving.value) return

  saving.value = true
  try {
    const formData = {
      ...form.value,
      price: parseFloat(form.value.price),
      stock_quantity: parseInt(form.value.stock_quantity),
      category_id: form.value.category_id ? parseInt(form.value.category_id) : null
    }



    let response
    if (props.editProduct) {
      const updateData = {
        ...formData,
        id: props.editProduct.id
      }

      response = await axios.put('http://localhost/ecommerce-project/BE/api/products.php', updateData)
    } else {

      response = await axios.post('http://localhost/ecommerce-project/BE/api/products.php', formData)
    }



    emit('saved')
    emit('close')
  } catch (error) {

    alert('Có lỗi xảy ra khi lưu sản phẩm: ' + (error.response?.data?.message || error.message))
  } finally {
    saving.value = false
  }
}

function closeModal() {
  emit('close')
}

function onImageUploaded(data) {
  // Image URL is automatically updated via v-model
}

onMounted(() => {
  loadCategories()
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
  max-width: 600px;
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

.modal-form {
  padding: var(--admin-space-6);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--admin-space-4);
  margin-bottom: var(--admin-space-4);
}

.form-group {
  margin-bottom: var(--admin-space-4);
}

.form-label {
  display: block;
  margin-bottom: var(--admin-space-2);
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
}

.form-input {
  width: 100%;
  padding: var(--admin-space-3);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-lg);
  font-size: var(--admin-font-sm);
  background: var(--admin-bg-primary);
  color: var(--admin-text-primary);
  transition: border-color var(--admin-transition-fast);
}

.form-input:focus {
  outline: none;
  border-color: var(--admin-primary);
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.form-input[type="number"] {
  text-align: right;
}

textarea.form-input {
  resize: vertical;
  min-height: 100px;
}

.image-preview {
  margin-top: var(--admin-space-3);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-lg);
  overflow: hidden;
  background: var(--admin-bg-secondary);
}

.image-preview img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.image-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: var(--admin-text-muted);
  gap: var(--admin-space-2);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--admin-space-3);
  padding-top: var(--admin-space-4);
  border-top: 1px solid var(--admin-border);
  margin-top: var(--admin-space-6);
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Mobile responsive */
@media (max-width: 768px) {
  .modal-overlay {
    padding: var(--admin-space-2);
  }

  .modal-container {
    max-height: 95vh;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .modal-header,
  .modal-form {
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
