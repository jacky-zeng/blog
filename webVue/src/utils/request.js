import axios from 'axios'

const request = axios.create({
  baseURL: '/api',
  timeout: 10000
})

request.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

request.interceptors.response.use(
  async response => {
    const { data } = response
    if (data && data.code === 401) {
      const currentPath = window.location.hash.replace(/^#/, '')
      if (currentPath !== '/admin/login') {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        try {
          await request.post('/admin/logout')
        } catch (e) {
        }
        window.location.href = '/#/admin/login'
      }
      return Promise.reject(data)
    }
    return data
  },
  async error => {
    if (error.response) {
      const { status, data } = error.response
      if (status === 401 || (data && (data.code === 401 || data.message === 'Token无效或已过期'))) {
        const currentPath = window.location.hash.replace(/^#/, '')
        if (currentPath !== '/admin/login') {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          try {
            await request.post('/admin/logout')
          } catch (e) {
          }
          window.location.href = '/#/admin/login'
        }
      }
      return Promise.reject(data)
    }
    return Promise.reject(error)
  }
)

export default request
