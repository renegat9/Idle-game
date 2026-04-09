import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { authApi } from '../api/auth'

export function RegisterPage() {
  const [form, setForm] = useState({ username: '', email: '', password: '', password_confirmation: '' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const update = (field: string, value: string) => setForm((f) => ({ ...f, [field]: value }))

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await authApi.register(form)
      navigate('/login', { state: { message: 'Compte créé ! Connecte-toi maintenant.' } })
    } catch (err: any) {
      const errors = err.response?.data?.errors
      if (errors) {
        setError(Object.values(errors).flat().join(', '))
      } else {
        setError(err.response?.data?.message || 'Erreur lors de l\'inscription')
      }
    } finally {
      setLoading(false)
    }
  }

  const fieldStyle = { width: '100%', background: '#1f2937', border: '1px solid #374151', borderRadius: 6, padding: '8px 12px', color: '#f9fafb', fontSize: 14, boxSizing: 'border-box' as const }
  const labelStyle = { display: 'block' as const, color: '#9ca3af', marginBottom: 6, fontSize: 13 }

  return (
    <div style={{ minHeight: '100vh', background: '#0a0a0f', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
      <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, padding: 32, width: 400 }}>
        <h2 style={{ color: '#f9fafb', textAlign: 'center', marginBottom: 4 }}>Rejoindre le Donjon</h2>
        <p style={{ color: '#6b7280', textAlign: 'center', marginBottom: 24, fontSize: 13 }}>
          Le Narrateur prend note de ton arrivée.
        </p>

        {error && (
          <div style={{ background: '#450a0a', border: '1px solid #dc2626', borderRadius: 6, padding: '8px 12px', marginBottom: 16, color: '#fca5a5', fontSize: 13 }}>
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          {[
            { field: 'username', label: 'Pseudo', type: 'text' },
            { field: 'email', label: 'Email', type: 'email' },
            { field: 'password', label: 'Mot de passe', type: 'password' },
            { field: 'password_confirmation', label: 'Confirmer le mot de passe', type: 'password' },
          ].map(({ field, label, type }) => (
            <div key={field} style={{ marginBottom: 16 }}>
              <label style={labelStyle}>{label}</label>
              <input type={type} value={form[field as keyof typeof form]} onChange={(e) => update(field, e.target.value)} required style={fieldStyle} />
            </div>
          ))}

          <button type="submit" disabled={loading}
            style={{ width: '100%', background: '#7c3aed', color: 'white', border: 'none', borderRadius: 6, padding: 10, fontSize: 15, cursor: loading ? 'not-allowed' : 'pointer', opacity: loading ? 0.7 : 1, marginTop: 8 }}>
            {loading ? 'Création...' : 'Créer mon compte'}
          </button>
        </form>

        <p style={{ textAlign: 'center', marginTop: 16, fontSize: 13, color: '#6b7280' }}>
          Déjà inscrit ?{' '}
          <Link to="/login" style={{ color: '#7c3aed' }}>Se connecter</Link>
        </p>
      </div>
    </div>
  )
}
