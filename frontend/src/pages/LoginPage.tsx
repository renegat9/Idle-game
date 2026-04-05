import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { authApi } from '../api/auth'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'

export function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { setAuth } = useAuthStore()
  const { setGold } = useGameStore()
  const navigate = useNavigate()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const { data } = await authApi.login({ email, password })
      setAuth(data.user, data.token)
      setGold(data.user.gold)
      navigate('/dashboard')
    } catch (err: any) {
      setError(err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Erreur de connexion')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div style={{ minHeight: '100vh', background: '#0a0a0f', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
      <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, padding: 32, width: 360 }}>
        <h1 style={{ color: '#7c3aed', textAlign: 'center', marginBottom: 8 }}>🏰</h1>
        <h2 style={{ color: '#f9fafb', textAlign: 'center', marginBottom: 4 }}>Le Donjon des Incompétents</h2>
        <p style={{ color: '#6b7280', textAlign: 'center', marginBottom: 24, fontSize: 13 }}>
          Le Narrateur vous attend. Impatiemment.
        </p>

        {error && (
          <div style={{ background: '#450a0a', border: '1px solid #dc2626', borderRadius: 6, padding: '8px 12px', marginBottom: 16, color: '#fca5a5', fontSize: 13 }}>
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div style={{ marginBottom: 16 }}>
            <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Email</label>
            <input
              type="email" value={email} onChange={(e) => setEmail(e.target.value)} required
              style={{ width: '100%', background: '#1f2937', border: '1px solid #374151', borderRadius: 6, padding: '8px 12px', color: '#f9fafb', fontSize: 14, boxSizing: 'border-box' }}
            />
          </div>
          <div style={{ marginBottom: 24 }}>
            <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Mot de passe</label>
            <input
              type="password" value={password} onChange={(e) => setPassword(e.target.value)} required
              style={{ width: '100%', background: '#1f2937', border: '1px solid #374151', borderRadius: 6, padding: '8px 12px', color: '#f9fafb', fontSize: 14, boxSizing: 'border-box' }}
            />
          </div>
          <button
            type="submit" disabled={loading}
            style={{ width: '100%', background: '#7c3aed', color: 'white', border: 'none', borderRadius: 6, padding: '10px', fontSize: 15, cursor: loading ? 'not-allowed' : 'pointer', opacity: loading ? 0.7 : 1 }}
          >
            {loading ? 'Connexion...' : 'Entrer dans le Donjon'}
          </button>
        </form>

        <p style={{ textAlign: 'center', marginTop: 16, fontSize: 13, color: '#6b7280' }}>
          Pas encore de compte ?{' '}
          <Link to="/register" style={{ color: '#7c3aed' }}>S'inscrire</Link>
        </p>
      </div>
    </div>
  )
}
