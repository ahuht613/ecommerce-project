import { createRouter, createWebHistory } from 'vue-router'
import ProductList from '../components/ProductList.vue'
import OrderList from '../components/OrderList.vue'
import CategoryList from '../components/CategoryList.vue'

const routes = [
  {
    path: '/',
    redirect: '/products'
  },
  {
    path: '/products',
    name: 'Products',
    component: ProductList,
    meta: {
      title: 'Quản lý sản phẩm'
    }
  },
  {
    path: '/orders',
    name: 'Orders',
    component: OrderList,
    meta: {
      title: 'Quản lý đơn hàng'
    }
  },
  {
    path: '/categories',
    name: 'Categories',
    component: CategoryList,
    meta: {
      title: 'Quản lý danh mục'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Update page title based on route
router.beforeEach((to, from, next) => {
  if (to.meta.title) {
    document.title = `${to.meta.title} - Admin Panel`
  }
  next()
})

export default router
