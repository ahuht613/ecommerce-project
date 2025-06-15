// Utility functions for formatting data

/**
 * Format price to Vietnamese currency format
 * @param {number} price - The price to format
 * @returns {string} Formatted price string
 */
export function formatPrice(price) {
  if (price === null || price === undefined || isNaN(price)) {
    return '0 VND'
  }
  
  return new Intl.NumberFormat('vi-VN', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(price) + ' VND'
}

/**
 * Format date to Vietnamese format
 * @param {string|Date} date - The date to format
 * @returns {string} Formatted date string
 */
export function formatDate(date) {
  if (!date) return ''
  
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return ''
  
  return new Intl.DateTimeFormat('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(dateObj)
}

/**
 * Format date to short Vietnamese format (only date)
 * @param {string|Date} date - The date to format
 * @returns {string} Formatted date string
 */
export function formatDateShort(date) {
  if (!date) return ''
  
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return ''
  
  return new Intl.DateTimeFormat('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).format(dateObj)
}

/**
 * Generate avatar URL from user name
 * @param {string} name - User's name
 * @param {number} size - Avatar size in pixels
 * @returns {string} Avatar URL
 */
export function generateAvatarUrl(name, size = 40) {
  if (!name) return `https://ui-avatars.com/api/?name=User&size=${size}&background=2d3748&color=ffffff&rounded=true`

  const initials = name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .substring(0, 2)
    .toUpperCase()

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&size=${size}&background=2d3748&color=ffffff&rounded=true`
}

/**
 * Format order status to Vietnamese
 * @param {string} status - Order status
 * @returns {string} Formatted status
 */
export function formatOrderStatus(status) {
  const statusMap = {
    'pending': 'Đang chờ xử lý',
    'confirmed': 'Đã xác nhận',
    'processing': 'Đang xử lý',
    'shipping': 'Đang giao hàng',
    'delivered': 'Đã giao hàng',
    'cancelled': 'Đã hủy',
    'completed': 'Hoàn thành'
  }
  
  return statusMap[status] || 'Đã đặt hàng'
}

/**
 * Get status color class
 * @param {string} status - Order status
 * @returns {string} CSS class name
 */
export function getStatusColor(status) {
  const colorMap = {
    'pending': 'status-pending',
    'confirmed': 'status-confirmed',
    'processing': 'status-processing',
    'shipping': 'status-shipping',
    'delivered': 'status-delivered',
    'cancelled': 'status-cancelled',
    'completed': 'status-completed'
  }
  
  return colorMap[status] || 'status-pending'
}

/**
 * Format phone number
 * @param {string} phone - Phone number
 * @returns {string} Formatted phone number
 */
export function formatPhone(phone) {
  if (!phone) return ''
  
  // Remove all non-digits
  const cleaned = phone.replace(/\D/g, '')
  
  // Format as Vietnamese phone number
  if (cleaned.length === 10) {
    return cleaned.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3')
  } else if (cleaned.length === 11) {
    return cleaned.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3')
  }
  
  return phone
}
