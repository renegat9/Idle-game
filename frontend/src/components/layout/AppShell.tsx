import { Link, Outlet, useNavigate } from 'react-router-dom'
import { useAuthStore } from '../../store/authStore'
import { useGameStore } from '../../store/gameStore'
import { usePolling } from '../../hooks/usePolling'
import { authApi } from '../../api/auth'

export function AppShell() {
  const { user, logout } = useAuthStore()
  const { gold, unreadEventsCount } = useGameStore()
  const navigate = useNavigate()

  usePolling(15000)

  const handleLogout = async () => {
    try { await authApi.logout() } catch { /* ok */ }
    logout()
    navigate('/login')
  }

  const navStyle = {
    background: '#0f172a',
    borderBottom: '1px solid #1e293b',
    padding: '0 24px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    height: 56,
    position: 'sticky' as const,
    top: 0,
    zIndex: 100,
  }

  const linkStyle = {
    color: '#94a3b8',
    textDecoration: 'none',
    padding: '8px 12px',
    borderRadius: 6,
    fontSize: 14,
    transition: 'color 0.2s',
  }

  return (
    <div style={{ minHeight: '100vh', background: '#0a0a0f', color: '#f1f5f9' }}>
      <nav style={navStyle}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
          <span style={{ color: '#7c3aed', fontWeight: 'bold', marginRight: 16, fontSize: 16 }}>
            🏰 Donjon des Inc.
          </span>
          <Link to="/dashboard" style={linkStyle}>Tableau de bord</Link>
          <Link to="/team" style={linkStyle}>Équipe</Link>
          <Link to="/map" style={linkStyle}>Carte</Link>
          <Link to="/inventory" style={linkStyle}>
            Inventaire
            {unreadEventsCount > 0 && (
              <span style={{
                background: '#7c3aed', color: 'white',
                borderRadius: '50%', padding: '0 5px',
                fontSize: 10, marginLeft: 4,
              }}>
                {unreadEventsCount}
              </span>
            )}
          </Link>
          <Link to="/quests" style={linkStyle}>Quêtes</Link>
          <Link to="/forge" style={linkStyle}>Forge</Link>
          <Link to="/tavern" style={linkStyle}>Taverne</Link>
          <Link to="/shop" style={linkStyle}>Boutique</Link>
          <Link to="/dungeon" style={linkStyle}>Donjon</Link>
          <Link to="/talents" style={linkStyle}>Talents</Link>
          <Link to="/world-boss" style={linkStyle}>Boss Mondial</Link>
          <Link to="/profile" style={linkStyle}>Profil</Link>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <span style={{ color: '#fbbf24', fontSize: 14 }}>
            💰 {(gold || user?.gold || 0).toLocaleString()} or
          </span>
          <span style={{ color: '#6b7280', fontSize: 13 }}>{user?.username}</span>
          <button
            onClick={handleLogout}
            style={{
              background: 'transparent', border: '1px solid #374151',
              color: '#9ca3af', padding: '4px 12px', borderRadius: 6,
              cursor: 'pointer', fontSize: 13,
            }}
          >
            Déconnexion
          </button>
        </div>
      </nav>

      <main style={{ maxWidth: 1200, margin: '0 auto', padding: '24px 16px' }}>
        <Outlet />
      </main>
    </div>
  )
}
