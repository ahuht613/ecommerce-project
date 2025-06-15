import { createRouter, createWebHistory } from 'vue-router'
import ProductList from '../components/ProductList.vue'
import Cart from '../components/Cart.vue'
import OrderList from '../components/OrderList.vue'
import UserProfile from '../components/UserProfile.vue'

const routes = [
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/home',
    name: 'Products',
    component: ProductList,
    meta: { title: 'Sản phẩm' }
  },
  {
    path: '/cart',
    name: 'Cart',
    component: Cart,
    meta: { title: 'Giỏ hàng' }
  },
  {
    path: '/orders',
    name: 'Orders',
    component: OrderList,
    meta: { title: 'Đơn hàng' }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: UserProfile,
    meta: { title: 'Thông tin cá nhân' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Update document title based on route
router.beforeEach((to, from, next) => {
  if (to.meta.title) {
    document.title = `${to.meta.title} - Shop Online`
  }
  next()
})

export default router
