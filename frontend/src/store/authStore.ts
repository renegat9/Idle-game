import { create } from 'zustand'
import type { User } from '../types'

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  setAuth: (user: User, token: string) => void
  updateUser: (user: Partial<User>) => void
  logout: () => void
}

function safeParseUser(raw: string | null): User | null {
  if (!raw || raw === 'undefined' || raw === 'null') return null
  try { return JSON.parse(raw) } catch { return null }
}

const storedToken = localStorage.getItem('auth_token')
const storedUser  = safeParseUser(localStorage.getItem('auth_user'))

export const useAuthStore = create<AuthState>((set) => ({
  user: storedUser,
  token: storedToken,
  isAuthenticated: !!storedToken,

  setAuth: (user, token) => {
    localStorage.setItem('auth_token', token)
    localStorage.setItem('auth_user', JSON.stringify(user))
    set({ user, token, isAuthenticated: true })
  },

  updateUser: (updates) => {
    set((state) => {
      if (!state.user) return state
      const updated = { ...state.user, ...updates }
      localStorage.setItem('auth_user', JSON.stringify(updated))
      return { user: updated }
    })
  },

  logout: () => {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    set({ user: null, token: null, isAuthenticated: false })
    window.location.href = '/info'
  },
}))
