import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { dashboardApi, explorationApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'

export function DashboardPage() {
  const { updateUser } = useAuthStore()
  const { setHeroes, setGold, setOfflineResult, setExploring, offlineResult } = useGameStore()
  const [loading, setLoading] = useState(true)
  const [dashboard, setDashboard] = useState<any>(null)
  const [collecting, setCollecting] = useState(false)

  useEffect(() => {
    dashboardApi.get()
      .then(({ data }) => {
        setDashboard(data)
        setHeroes(data.heroes)
        setGold(data.user.gold)
        updateUser(data.user)
        if (data.offline_result) {
          setOfflineResult(data.offline_result)
        }
        setExploring(data.exploration?.is_active ?? false, data.exploration?.zone_name)
      })
      .finally(() => setLoading(false))
  }, [])

  const handleCollect = async () => {
    setCollecting(true)
    try {
      const { data } = await explorationApi.collect()
      setOfflineResult(data.result)
      setGold(data.user.gold)
      updateUser(data.user)
      setHeroes(data.heroes as any)
    } finally {
      setCollecting(false)
    }
  }

  if (loading) {
    return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>
  }

  return (
    <div>
      <h1 style={{ color: '#f9fafb', marginBottom: 8 }}>🏰 Tableau de Bord</h1>

      {/* Narrateur */}
      {dashboard?.narrator_comment && <NarratorBubble comment={dashboard.narrator_comment} />}

      {/* Résultat offline */}
      {offlineResult && offlineResult.combats_simulated > 0 && (
        <div style={{
          background: '#0f2d1a', border: '1px solid #22c55e', borderRadius: 8,
          padding: 16, marginBottom: 20,
        }}>
          <h3 style={{ color: '#22c55e', marginTop: 0 }}>Pendant ton absence...</h3>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12, marginBottom: 12 }}>
            {[
              ['Combats', offlineResult.combats_simulated],
              ['XP gagnée', offlineResult.xp_gained],
              ['Or gagné', offlineResult.gold_gained],
            ].map(([label, value]) => (
              <div key={label} style={{ background: '#111827', borderRadius: 6, padding: '8px 12px', textAlign: 'center' }}>
                <div style={{ color: '#f9fafb', fontSize: 20, fontWeight: 'bold' }}>{value}</div>
                <div style={{ color: '#6b7280', fontSize: 12 }}>{label}</div>
              </div>
            ))}
          </div>
          {offlineResult.items_gained.length > 0 && (
            <p style={{ color: '#fbbf24', fontSize: 13, margin: 0 }}>
              +{offlineResult.items_gained.length} objet(s) trouvé(s) dans l'inventaire
            </p>
          )}
        </div>
      )}

      {/* Statut exploration */}
      <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 8, padding: 16, marginBottom: 20 }}>
        {dashboard?.exploration?.is_active ? (
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div>
              <span style={{ color: '#22c55e', fontSize: 13 }}>● En exploration</span>
              <span style={{ color: '#9ca3af', fontSize: 13, marginLeft: 8 }}>
                {dashboard.exploration.zone_name}
              </span>
            </div>
            <button
              onClick={handleCollect} disabled={collecting}
              style={{
                background: '#7c3aed', color: 'white', border: 'none',
                borderRadius: 6, padding: '8px 16px', cursor: collecting ? 'not-allowed' : 'pointer',
                opacity: collecting ? 0.7 : 1,
              }}
            >
              {collecting ? 'Collecte...' : 'Collecter les récompenses'}
            </button>
          </div>
        ) : (
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ color: '#6b7280', fontSize: 13 }}>● Pas d'exploration en cours</span>
            <Link to="/map"
              style={{ background: '#374151', color: '#d1d5db', textDecoration: 'none', padding: '8px 16px', borderRadius: 6, fontSize: 13 }}>
              Aller à la Carte
            </Link>
          </div>
        )}
      </div>

      {/* Héros */}
      {dashboard?.heroes?.length > 0 ? (
        <div>
          <h2 style={{ color: '#f9fafb', marginBottom: 12 }}>Mon Équipe</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))', gap: 16 }}>
            {dashboard.heroes.map((hero: any) => (
              <div key={hero.id} style={{
                background: '#111827', border: '1px solid #1f2937', borderRadius: 8, padding: 16,
              }}>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <h3 style={{ margin: 0, color: '#f9fafb' }}>{hero.name}</h3>
                  <span style={{ background: '#374151', color: '#d1d5db', padding: '2px 8px', borderRadius: 4, fontSize: 12 }}>Niv. {hero.level}</span>
                </div>
                <p style={{ color: '#6b7280', fontSize: 13, margin: '6px 0 8px' }}>{hero.race?.name} • {hero.class?.name}</p>
                <div style={{ background: '#374151', borderRadius: 4, height: 6, marginBottom: 4 }}>
                  <div style={{
                    background: '#22c55e',
                    width: `${hero.max_hp > 0 ? Math.round(hero.current_hp / hero.max_hp * 100) : 0}%`,
                    height: '100%', borderRadius: 4,
                  }} />
                </div>
                <p style={{ color: '#4b5563', fontSize: 11, margin: 0 }}>{hero.current_hp}/{hero.max_hp} PV</p>
              </div>
            ))}
          </div>
        </div>
      ) : (
        <div style={{ textAlign: 'center', padding: 40, background: '#111827', borderRadius: 8, border: '1px dashed #374151' }}>
          <p style={{ color: '#6b7280' }}>Aucun héros. Le Narrateur attend que tu te décides.</p>
          <Link to="/team"
            style={{ background: '#7c3aed', color: 'white', textDecoration: 'none', padding: '10px 20px', borderRadius: 6, fontSize: 14 }}>
            Créer un Héros
          </Link>
        </div>
      )}
    </div>
  )
}
