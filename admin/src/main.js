import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'

const app = createApp(App)

// Lấy token từ localStorage và set cho axios khi app khởi động
const token = localStorage.getItem('admin_token')
if (token) {
  axios.defaults.headers['Authorization'] = `Bearer ${token}`
}

app.use(router)
app.mount('#app')