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
  response => {
    return response.data
  },
  async error => {
    if (error.response) {
      const { status, data } = error.response
      if (status === 401 || (data && data.message === 'Token无效或已过期')) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        // 调用退出接口
        try {
          await axios.post('/api/admin/logout')
        } catch (e) {
          // 忽略退出接口错误
        }
        window.location.href = '/#/admin/login'
      }
      return Promise.reject(data)
    }
    return Promise.reject(error)
  }
)

export default request
