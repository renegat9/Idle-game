import { useEffect } from 'react'
import { dashboardApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { useAuthStore } from '../store/authStore'

export function usePolling(intervalMs = 15000) {
  const { updateFromPoll } = useGameStore()
  const { isAuthenticated } = useAuthStore()

  useEffect(() => {
    if (!isAuthenticated) return

    const poll = async () => {
      try {
        const { data } = await dashboardApi.poll()
        updateFromPoll(data)
      } catch {
        // Silencieux — les erreurs 401 sont gérées par l'intercepteur Axios
      }
    }

    const id = setInterval(poll, intervalMs)
    return () => clearInterval(id)
  }, [isAuthenticated, intervalMs, updateFromPoll])
}
