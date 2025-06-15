<template>
  <div class="modal-overlay" @click.self="closeModal">
    <div class="modal">
      <div class="modal-header">
        <h3>{{ editCategory ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới' }}</h3>
        <button @click="closeModal" class="close-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <form @submit.prevent="saveCategory" class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Tên danh mục *</label>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              placeholder="Nhập tên danh mục"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mô tả</label>
          <textarea
            v-model="form.description"
            class="form-input"
            rows="4"
            placeholder="Nhập mô tả chi tiết về danh mục..."
          ></textarea>
        </div>

        <div class="modal-footer">
          <button type="button" @click="closeModal" class="btn btn-secondary">
            Hủy
          </button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving">Đang lưu...</span>
            <span v-else>{{ editCategory ? 'Cập nhật' : 'Thêm mới' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps(['editCategory'])
const emit = defineEmits(['close', 'saved'])

const form = ref({
  name: '',
  description: ''
})

const saving = ref(false)

// Watch for editCategory changes
watch(() => props.editCategory, (newCategory) => {
  if (newCategory) {
    form.value = {
      name: newCategory.name || '',
      description: newCategory.description || ''
    }
  } else {
    form.value = {
      name: '',
      description: ''
    }
  }
}, { immediate: true })

async function saveCategory() {
  if (!form.value.name.trim()) {
    alert('Vui lòng nhập tên danh mục')
    return
  }

  try {
    saving.value = true

    const categoryData = {
      name: form.value.name.trim(),
      description: form.value.description.trim()
    }

    if (props.editCategory) {
      // Update existing category
      categoryData.id = props.editCategory.id
      await axios.put('http://localhost/ecommerce-project/BE/api/categories.php', categoryData)
    } else {
      // Create new category
      await axios.post('http://localhost/ecommerce-project/BE/api/categories.php', categoryData)
    }

    emit('saved')
    emit('close')
  } catch (error) {
    alert('Có lỗi xảy ra khi lưu danh mục: ' + (error.response?.data?.message || error.message))
  } finally {
    saving.value = false
  }
}

function closeModal() {
  emit('close')
}
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

.modal {
  background: var(--admin-bg-card);
  border-radius: var(--admin-radius-xl);
  box-shadow: var(--admin-shadow-xl);
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--admin-space-6);
  border-bottom: 1px solid var(--admin-border);
  background: var(--admin-bg-secondary);
}

.modal-header h3 {
  margin: 0;
  font-size: var(--admin-font-xl);
  font-weight: var(--admin-font-semibold);
  color: var(--admin-text-primary);
}

.close-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: var(--admin-radius-lg);
  cursor: pointer;
  color: var(--admin-text-muted);
  transition: all var(--admin-transition-fast);
}

.close-btn:hover {
  background: var(--admin-bg-tertiary);
  color: var(--admin-text-primary);
}

.modal-body {
  padding: var(--admin-space-6);
  overflow-y: auto;
  flex: 1;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--admin-space-4);
  margin-bottom: var(--admin-space-4);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--admin-space-2);
}

.form-label {
  font-weight: var(--admin-font-medium);
  color: var(--admin-text-primary);
  font-size: var(--admin-font-sm);
}

.form-input {
  padding: var(--admin-space-3);
  border: 1px solid var(--admin-border);
  border-radius: var(--admin-radius-lg);
  font-size: var(--admin-font-sm);
  transition: all var(--admin-transition-fast);
  background: var(--admin-bg-primary);
  color: var(--admin-text-primary);
}

.form-input:focus {
  outline: none;
  border-color: var(--admin-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-input::placeholder {
  color: var(--admin-text-muted);
}

textarea.form-input {
  resize: vertical;
  min-height: 100px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--admin-space-3);
  padding-top: var(--admin-space-4);
  border-top: 1px solid var(--admin-border);
  margin-top: var(--admin-space-4);
}

.btn {
  padding: var(--admin-space-3) var(--admin-space-4);
  border: none;
  border-radius: var(--admin-radius-lg);
  font-size: var(--admin-font-sm);
  font-weight: var(--admin-font-medium);
  cursor: pointer;
  transition: all var(--admin-transition-fast);
  display: flex;
  align-items: center;
  gap: var(--admin-space-2);
}

.btn-primary {
  background: var(--admin-primary);
  color: var(--admin-text-white);
}

.btn-primary:hover:not(:disabled) {
  background: var(--admin-primary-dark);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--admin-bg-secondary);
  color: var(--admin-text-secondary);
  border: 1px solid var(--admin-border);
}

.btn-secondary:hover {
  background: var(--admin-bg-tertiary);
  color: var(--admin-text-primary);
}

@media (max-width: 768px) {
  .modal {
    margin: var(--admin-space-4);
    max-width: none;
  }

  .modal-header,
  .modal-body {
    padding: var(--admin-space-4);
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
