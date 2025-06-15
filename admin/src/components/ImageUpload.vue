<template>
  <div class="image-upload">
    <div class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
      <div v-if="!imageUrl && !uploading" class="upload-placeholder">
        <div class="upload-icon">📷</div>
        <p>{{ placeholder }}</p>
        <p class="upload-hint">Kéo thả ảnh vào đây hoặc click để chọn</p>
      </div>
      
      <div v-if="uploading" class="upload-loading">
        <div class="spinner"></div>
        <p>Đang tải ảnh...</p>
      </div>
      
      <div v-if="imageUrl && !uploading" class="image-preview">
        <img :src="imageUrl" :alt="alt" />
        <div class="image-overlay">
          <button type="button" @click.stop="removeImage" class="remove-btn">✕</button>
          <button type="button" @click.stop="triggerFileInput" class="change-btn">📷</button>
        </div>
      </div>
    </div>
    
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      @change="handleFileSelect"
      style="display: none"
    />
    
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: String,
  type: {
    type: String,
    default: 'product' // 'product' or 'avatar'
  },
  userId: Number,
  placeholder: {
    type: String,
    default: 'Chọn ảnh'
  },
  alt: {
    type: String,
    default: 'Uploaded image'
  }
})

const emit = defineEmits(['update:modelValue', 'uploaded'])

const fileInput = ref(null)
const imageUrl = ref(props.modelValue)
const uploading = ref(false)
const error = ref('')

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  imageUrl.value = newValue
})

function triggerFileInput() {
  fileInput.value?.click()
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    uploadFile(file)
  }
}

function handleDrop(event) {
  const files = event.dataTransfer.files
  if (files.length > 0) {
    uploadFile(files[0])
  }
}

async function uploadFile(file) {
  try {
    error.value = ''
    uploading.value = true
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
      throw new Error('Vui lòng chọn file ảnh')
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      throw new Error('Kích thước file không được vượt quá 5MB')
    }
    
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', props.type)
    
    if (props.userId) {
      formData.append('user_id', props.userId)
    }
    
    const response = await axios.post('http://localhost/ecommerce-project/BE/api/upload.php', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.url) {
      imageUrl.value = response.data.url
      emit('update:modelValue', response.data.url)
      emit('uploaded', response.data)
    }
    
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Lỗi khi tải ảnh'
  } finally {
    uploading.value = false
    // Reset file input
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

function removeImage() {
  imageUrl.value = ''
  emit('update:modelValue', '')
  error.value = ''
}
</script>

<style scoped>
.image-upload {
  width: 100%;
}

.upload-area {
  border: 2px dashed var(--admin-border);
  border-radius: var(--admin-radius-lg);
  padding: var(--admin-space-4);
  text-align: center;
  cursor: pointer;
  transition: all var(--admin-transition-fast);
  position: relative;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--admin-bg-secondary);
}

.upload-area:hover {
  border-color: var(--admin-primary);
  background-color: var(--admin-bg-primary);
}

.upload-placeholder {
  color: var(--admin-text-muted);
}

.upload-icon {
  font-size: 48px;
  margin-bottom: var(--admin-space-2);
}

.upload-hint {
  font-size: var(--admin-font-sm);
  color: var(--admin-text-muted);
  margin-top: var(--admin-space-1);
}

.upload-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--admin-space-2);
  color: var(--admin-text-secondary);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--admin-bg-secondary);
  border-top: 4px solid var(--admin-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.image-preview {
  position: relative;
  width: 100%;
  height: 200px;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--admin-radius-md);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--admin-space-2);
  opacity: 0;
  transition: opacity var(--admin-transition-fast);
  border-radius: var(--admin-radius-md);
}

.image-preview:hover .image-overlay {
  opacity: 1;
}

.remove-btn,
.change-btn {
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--admin-transition-fast);
}

.remove-btn:hover {
  background: var(--admin-error);
  color: white;
}

.change-btn:hover {
  background: var(--admin-primary);
  color: white;
}

.error-message {
  color: var(--admin-error);
  font-size: var(--admin-font-sm);
  margin-top: var(--admin-space-2);
  text-align: center;
}
</style>
