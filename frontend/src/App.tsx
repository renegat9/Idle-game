import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { useAuthStore } from './store/authStore'
import apiClient from './api/client'
import { AppShell } from './components/layout/AppShell'
import { LoginPage } from './pages/LoginPage'
import { RegisterPage } from './pages/RegisterPage'
import { DashboardPage } from './pages/DashboardPage'
import { TeamPage } from './pages/TeamPage'
import { MapPage } from './pages/MapPage'
import { InventoryPage } from './pages/InventoryPage'
import { QuestPage } from './pages/QuestPage'
import { ForgePage } from './pages/ForgePage'
import { TavernPage } from './pages/TavernPage'
import { ShopPage } from './pages/ShopPage'
import { DungeonPage } from './pages/DungeonPage'
import { TalentsPage } from './pages/TalentsPage'
import { WorldBossPage } from './pages/WorldBossPage'
import { ProfilePage } from './pages/ProfilePage'
import { ConsumablesPage } from './pages/ConsumablesPage'
import { LandingPage } from './pages/LandingPage'

function RequireAuth({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  return isAuthenticated ? <>{children}</> : <Navigate to="/" replace />
}

function RequireGuest({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  return isAuthenticated ? <Navigate to="/dashboard" replace /> : <>{children}</>
}

/**
 * Vérifie le token au démarrage. Si invalide, le supprime avant de rendre les routes.
 * Évite la boucle: token expiré → /dashboard → 401 → /login.
 */
function AuthInitializer({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, logout } = useAuthStore()
  const [ready, setReady] = useState(!isAuthenticated)

  useEffect(() => {
    if (!isAuthenticated) { setReady(true); return }
    apiClient.get('/game/poll')
      .then(() => setReady(true))
      .catch(() => { logout(); setReady(true) })
  }, [])

  if (!ready) return (
    <div className="game-loading">
      <div className="game-loading-spinner" />
      <div className="game-loading-text">Chargement…</div>
    </div>
  )
  return <>{children}</>
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthInitializer>
      <Routes>
        {/* Landing page publique */}
        <Route path="/" element={<RequireGuest><LandingPage /></RequireGuest>} />

        {/* Auth routes (redirect to dashboard if already logged in) */}
        <Route path="/login" element={<RequireGuest><LoginPage /></RequireGuest>} />
        <Route path="/register" element={<RequireGuest><RegisterPage /></RequireGuest>} />

        {/* Protected game routes */}
        <Route element={<RequireAuth><AppShell /></RequireAuth>}>
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/team" element={<TeamPage />} />
          <Route path="/map" element={<MapPage />} />
          <Route path="/inventory" element={<InventoryPage />} />
          <Route path="/quests" element={<QuestPage />} />
          <Route path="/forge" element={<ForgePage />} />
          <Route path="/tavern" element={<TavernPage />} />
          <Route path="/shop" element={<ShopPage />} />
          <Route path="/dungeon" element={<DungeonPage />} />
          <Route path="/talents" element={<TalentsPage />} />
          <Route path="/world-boss" element={<WorldBossPage />} />
          <Route path="/consumables" element={<ConsumablesPage />} />
          <Route path="/profile" element={<ProfilePage />} />
        </Route>

        {/* Fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
      </AuthInitializer>
    </BrowserRouter>
  )
}
