import { Link, Outlet, useNavigate, useLocation } from 'react-router-dom'
import { useAuthStore } from '../../store/authStore'
import { useGameStore } from '../../store/gameStore'
import { usePolling } from '../../hooks/usePolling'
import { authApi } from '../../api/auth'

const NAV_ITEMS = [
  { to: '/dashboard',  icon: '🏠', label: 'Tableau de Bord' },
  { to: '/team',       icon: '⚔️', label: 'Équipe' },
  { to: '/map',        icon: '🗺️', label: 'Carte du Monde' },
  { to: '/inventory',  icon: '🎒', label: 'Inventaire', badge: true },
  { to: '/quests',     icon: '📜', label: 'Quêtes' },
  { to: '/forge',      icon: '🔨', label: 'Forge de Gérard' },
  { to: '/tavern',     icon: '🍺', label: 'Taverne' },
  { to: '/shop',       icon: '🛒', label: 'Boutique' },
  { to: '/consumables',icon: '🧪', label: 'Consommables' },
  { to: '/dungeon',    icon: '🏚️', label: 'Donjon' },
  { to: '/talents',    icon: '✨', label: 'Talents' },
  { to: '/world-boss', icon: '🐉', label: 'Boss Mondial' },
  { to: '/profile',    icon: '👤', label: 'Profil' },
]

export function AppShell() {
  const { user, logout } = useAuthStore()
  const { gold, unreadEventsCount, narratorComment, isExploring, currentZoneName } = useGameStore()
  const navigate = useNavigate()
  const location = useLocation()

  usePolling(15000)

  const handleLogout = async () => {
    try { await authApi.logout() } catch { /* ok */ }
    logout()
    navigate('/login')
  }

  const displayGold = (gold || user?.gold || 0).toLocaleString()

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: '#0a0a0f' }}>

      {/* ── Sidebar ── */}
      <aside className="sidebar">

        {/* Logo */}
        <div className="sidebar-logo">
          <div style={{ fontSize: 22, marginBottom: 6 }}>🏰</div>
          <div className="sidebar-logo-text">Le Donjon<br />des Incompétents</div>
        </div>

        {/* Resources */}
        <div className="sidebar-resources">
          <div className="sidebar-resource">
            <span style={{ fontSize: 16 }}>💰</span>
            <span style={{ color: '#fbbf24', fontWeight: 600, fontSize: 14 }}>{displayGold} or</span>
          </div>
          {user?.level && (
            <div className="sidebar-resource">
              <span style={{ fontSize: 14 }}>⭐</span>
              <span style={{ color: '#9ca3af', fontSize: 13 }}>
                {user.username} — Niv. {user.level}
              </span>
            </div>
          )}
          {isExploring && (
            <div className="sidebar-resource" style={{ marginTop: 4 }}>
              <span className="anim-explore-pulse" style={{ fontSize: 10, color: '#22c55e' }}>●</span>
              <span style={{ color: '#4ade80', fontSize: 11 }}>
                {currentZoneName ?? 'En exploration'}
              </span>
            </div>
          )}
        </div>

        {/* Navigation */}
        <nav className="sidebar-nav">
          {NAV_ITEMS.map(({ to, icon, label, badge }) => {
            const isActive = location.pathname === to
            return (
              <Link
                key={to}
                to={to}
                className={`sidebar-nav-link${isActive ? ' active' : ''}`}
              >
                <span className="nav-icon">{icon}</span>
                <span>{label}</span>
                {badge && unreadEventsCount > 0 && (
                  <span className="sidebar-nav-badge">{unreadEventsCount}</span>
                )}
              </Link>
            )
          })}
        </nav>

        {/* Mini narrator in sidebar */}
        {narratorComment && (
          <div className="sidebar-narrator">
            <div style={{
              background: '#12122a',
              border: '1px solid #4c1d95',
              borderLeft: '3px solid #7c3aed',
              borderRadius: 6,
              padding: '8px 10px',
            }}>
              <div style={{ fontSize: 9, color: '#7c3aed', fontFamily: 'var(--font-title)', letterSpacing: '0.1em', textTransform: 'uppercase', marginBottom: 4 }}>
                📖 Le Narrateur
              </div>
              <div style={{ fontSize: 11, color: '#c4b5fd', fontStyle: 'italic', lineHeight: 1.4 }}>
                {narratorComment.length > 120 ? narratorComment.slice(0, 117) + '…' : narratorComment}
              </div>
            </div>
          </div>
        )}

        {/* Footer */}
        <div className="sidebar-footer">
          <button
            onClick={handleLogout}
            className="game-btn game-btn-ghost game-btn-sm"
            style={{ width: '100%', justifyContent: 'center' }}
          >
            Déconnexion
          </button>
        </div>
      </aside>

      {/* ── Main Content ── */}
      <main className="main-content" style={{ flex: 1 }}>
        <div className="page-container">
          <Outlet />
        </div>
      </main>
    </div>
  )
}
