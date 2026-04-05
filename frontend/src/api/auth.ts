import apiClient from './client'
import type { User } from '../types'

export interface LoginResponse {
  token: string
  user: User
  message: string
}

export interface RegisterResponse {
  user: User
  message: string
}

export const authApi = {
  register: (data: { username: string; email: string; password: string; password_confirmation: string }) =>
    apiClient.post<RegisterResponse>('/auth/register', data),

  login: (data: { email: string; password: string }) =>
    apiClient.post<LoginResponse>('/auth/login', data),

  logout: () => apiClient.post('/auth/logout'),
}
