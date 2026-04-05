import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from './store/authStore'
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
import { WorldBossPage } from './pages/WorldBossPage'
import { TalentsPage } from './pages/TalentsPage'
import './index.css'

function RequireAuth({ children }: { children: React.ReactNode }) {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated)
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />
}

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route
          path="/"
          element={<RequireAuth><AppShell /></RequireAuth>}
        >
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<DashboardPage />} />
          <Route path="team" element={<TeamPage />} />
          <Route path="map" element={<MapPage />} />
          <Route path="inventory" element={<InventoryPage />} />
          <Route path="quests" element={<QuestPage />} />
          <Route path="forge" element={<ForgePage />} />
          <Route path="tavern" element={<TavernPage />} />
          <Route path="shop" element={<ShopPage />} />
          <Route path="dungeon" element={<DungeonPage />} />
          <Route path="world-boss" element={<WorldBossPage />} />
          <Route path="talents" element={<TalentsPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>
)
