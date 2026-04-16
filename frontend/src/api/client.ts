import axios from 'axios'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

// Injecter le token depuis localStorage
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Sur 401 : vider le token et rediriger vers login (sauf si déjà sur page publique)
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      const publicPaths = ['/info', '/login', '/register']
      if (!publicPaths.includes(window.location.pathname)) {
        window.location.href = '/info'
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient
