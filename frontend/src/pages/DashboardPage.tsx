import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { dashboardApi, explorationApi, eventsApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import type { SeasonalEvent } from '../types'

export function DashboardPage() {
  const { updateUser } = useAuthStore()
  const { setHeroes, setGold, setOfflineResult, setExploring, offlineResult } = useGameStore()
  const [loading, setLoading] = useState(true)
  const [dashboard, setDashboard] = useState<any>(null)
  const [collecting, setCollecting] = useState(false)
  const [collectResult, setCollectResult] = useState<any>(null)
  const [activeEvents, setActiveEvents] = useState<SeasonalEvent[]>([])
  const [eventModifiers, setEventModifiers] = useState<any>(null)

  useEffect(() => {
    Promise.all([
      dashboardApi.get(),
      eventsApi.current(),
    ]).then(([dashRes, eventsRes]) => {
      const data = dashRes.data
      setDashboard(data)
      setHeroes(data.heroes)
      setGold(data.user.gold)
      updateUser(data.user)
      if (data.offline_result) {
        setOfflineResult(data.offline_result)
      }
      setExploring(data.exploration?.is_active ?? false, data.exploration?.zone_name)
      setActiveEvents(eventsRes.data.active_events ?? [])
      setEventModifiers(eventsRes.data.modifiers)
    }).finally(() => setLoading(false))
  }, [])

  const handleCollect = async () => {
    setCollecting(true)
    setCollectResult(null)
    try {
      const { data } = await explorationApi.collect()
      setCollectResult(data.result)
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

      {/* Seasonal event banner */}
      {activeEvents.length > 0 && (
        <div style={{ background: 'linear-gradient(135deg, #1c1f2e, #2d1b4e)', border: '1px solid #7c3aed', borderRadius: 12, padding: 16, marginBottom: 20 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 8 }}>
            <span style={{ fontSize: 20 }}>🎉</span>
            <span style={{ color: '#c4b5fd', fontWeight: 'bold', fontSize: 16 }}>Événement actif : {activeEvents[0].name}</span>
          </div>
          <p style={{ color: '#94a3b8', fontSize: 13, margin: '0 0 10px', fontStyle: 'italic' }}>{activeEvents[0].flavor_text}</p>
          {eventModifiers && (
            <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
              {eventModifiers.xp_bonus_pct > 0 && <span style={{ background: '#22c55e22', color: '#4ade80', borderRadius: 6, padding: '2px 10px', fontSize: 12 }}>+{eventModifiers.xp_bonus_pct}% XP</span>}
              {eventModifiers.gold_bonus_pct > 0 && <span style={{ background: '#f59e0b22', color: '#fbbf24', borderRadius: 6, padding: '2px 10px', fontSize: 12 }}>+{eventModifiers.gold_bonus_pct}% Or</span>}
              {eventModifiers.loot_bonus_pct > 0 && <span style={{ background: '#3b82f622', color: '#60a5fa', borderRadius: 6, padding: '2px 10px', fontSize: 12 }}>+{eventModifiers.loot_bonus_pct}% Loot</span>}
              {eventModifiers.rare_loot_bonus_pct > 0 && <span style={{ background: '#a855f722', color: '#c084fc', borderRadius: 6, padding: '2px 10px', fontSize: 12 }}>+{eventModifiers.rare_loot_bonus_pct}% Rare+</span>}
            </div>
          )}
        </div>
      )}

      {/* Narrateur */}
      {dashboard?.narrator_comment && <NarratorBubble comment={dashboard.narrator_comment} />}

      {/* Résultat de collecte */}
      {collectResult && (
        <div style={{ background: '#0f2d1a', border: '1px solid #22c55e', borderRadius: 8, padding: 16, marginBottom: 20 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <h3 style={{ color: '#22c55e', margin: 0 }}>Récompenses collectées</h3>
            <button
              onClick={() => setCollectResult(null)}
              style={{ background: 'transparent', border: 'none', color: '#4b5563', cursor: 'pointer', fontSize: 18, lineHeight: 1 }}
            >✕</button>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 14 }}>
            {[
              ['⚔️ Combats',  collectResult.combats_simulated, '#f9fafb'],
              ['✨ XP',        collectResult.xp_gained,         '#818cf8'],
              ['💰 Or',        collectResult.gold_gained,        '#fbbf24'],
            ].map(([label, value, color]) => (
              <div key={label as string} style={{ background: '#111827', borderRadius: 6, padding: '8px 10px', textAlign: 'center' }}>
                <div style={{ color: color as string, fontSize: 18, fontWeight: 'bold' }}>{value as number}</div>
                <div style={{ color: '#6b7280', fontSize: 11 }}>{label as string}</div>
              </div>
            ))}
          </div>

          {collectResult.items_gained?.length > 0 ? (
            <div>
              <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 6 }}>
                {collectResult.items_gained.length} objet(s) ajouté(s) à l'inventaire
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                {collectResult.items_gained.map((item: any, i: number) => (
                  <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 8, background: '#111827', borderRadius: 5, padding: '5px 10px' }}>
                    <span style={{ fontSize: 14 }}>
                      {{ arme: '⚔️', armure: '🛡️', casque: '⛑️', bottes: '👢', accessoire: '💍', truc_bizarre: '🎲' }[item.slot as string] ?? '📦'}
                    </span>
                    <span style={{
                      fontWeight: 'bold', fontSize: 13, flex: 1,
                      color: ({ commun: '#9ca3af', peu_commun: '#4ade80', rare: '#60a5fa', epique: '#a78bfa', legendaire: '#fbbf24', wtf: '#f472b6' } as Record<string,string>)[item.rarity] ?? '#9ca3af',
                    }}>
                      {item.name}
                    </span>
                    <span style={{ color: '#4b5563', fontSize: 11 }}>niv. {item.item_level}</span>
                  </div>
                ))}
              </div>
            </div>
          ) : (
            <div style={{ color: '#4b5563', fontSize: 13, fontStyle: 'italic' }}>
              Aucun objet trouvé. Le Narrateur compatit.
            </div>
          )}

          {collectResult.narrator_comment && (
            <div style={{ marginTop: 12, color: '#6b7280', fontSize: 12, fontStyle: 'italic', borderTop: '1px solid #1f2937', paddingTop: 10 }}>
              "{collectResult.narrator_comment}"
            </div>
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
