import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { dashboardApi, explorationApi, eventsApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { HeroCard } from '../components/hero/HeroCard'
import { ItemImage } from '../components/ui/ItemImage'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import type { SeasonalEvent } from '../types'

const RARITY_COLOR: Record<string, string> = {
  commun: '#9ca3af', peu_commun: '#4ade80', rare: '#60a5fa',
  epique: '#a78bfa', legendaire: '#fbbf24', wtf: '#f472b6',
}

export function DashboardPage() {
  const { updateUser } = useAuthStore()
  const { setHeroes, setGold, setOfflineResult, setExploring } = useGameStore()
  const [loading, setLoading] = useState(true)
  const [dashboard, setDashboard] = useState<any>(null)
  const [collecting, setCollecting] = useState(false)
  const [stopping, setStopping] = useState(false)
  const [collectResult, setCollectResult] = useState<any>(null)
  const [activeEvents, setActiveEvents] = useState<SeasonalEvent[]>([])
  const [eventModifiers, setEventModifiers] = useState<any>(null)

  useEffect(() => {
    Promise.all([dashboardApi.get(), eventsApi.current()])
      .then(([dashRes, eventsRes]) => {
        const data = dashRes.data
        setDashboard(data)
        setHeroes(data.heroes)
        setGold(data.user.gold)
        updateUser(data.user)
        if (data.offline_result) setOfflineResult(data.offline_result)
        setExploring(data.exploration?.is_active ?? false, data.exploration?.zone_name)
        setActiveEvents(eventsRes.data.active_events ?? [])
        setEventModifiers(eventsRes.data.modifiers)
      })
      .finally(() => setLoading(false))
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

  const handleStop = async () => {
    setStopping(true)
    setCollectResult(null)
    try {
      const { data } = await explorationApi.stop()
      setCollectResult(data.result)
      setOfflineResult(data.result)
      setExploring(false)
      setDashboard((prev: any) => prev ? { ...prev, exploration: { is_active: false } } : prev)
    } catch (err: any) {
      // silently ignore
    } finally {
      setStopping(false)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement du donjon…</div>
      </div>
    )
  }

  return (
    <div>
      {/* Page Title */}
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px', color: '#f9fafb' }}>
          🏰 Tableau de Bord
        </h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Vue d'ensemble de votre expédition incompétente
        </p>
      </div>

      {/* Narrator */}
      {dashboard?.narrator_comment && <NarratorBubble comment={dashboard.narrator_comment} />}

      {/* Seasonal Event Banner */}
      {activeEvents.length > 0 && (
        <div className="game-panel game-panel-magic anim-slide-in" style={{ marginBottom: 20 }}>
          <div style={{ padding: '14px 16px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 8 }}>
              <span style={{ fontSize: 24 }}>🎉</span>
              <div>
                <div className="game-title" style={{ color: '#c4b5fd', fontSize: 15 }}>
                  {activeEvents[0].name}
                </div>
                <p style={{ color: '#9ca3af', fontSize: 12, margin: '2px 0 0', fontStyle: 'italic' }}>
                  {activeEvents[0].flavor_text}
                </p>
              </div>
            </div>
            {eventModifiers && (
              <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginTop: 8 }}>
                {eventModifiers.xp_bonus_pct > 0 && (
                  <span className="badge" style={{ background: '#052e16', color: '#86efac', border: '1px solid #166534' }}>
                    ✨ +{eventModifiers.xp_bonus_pct}% XP
                  </span>
                )}
                {eventModifiers.gold_bonus_pct > 0 && (
                  <span className="badge" style={{ background: '#1a0d00', color: '#fbbf24', border: '1px solid #b45309' }}>
                    💰 +{eventModifiers.gold_bonus_pct}% Or
                  </span>
                )}
                {eventModifiers.loot_bonus_pct > 0 && (
                  <span className="badge" style={{ background: '#0c1a33', color: '#93c5fd', border: '1px solid #1d4ed8' }}>
                    🎒 +{eventModifiers.loot_bonus_pct}% Loot
                  </span>
                )}
                {eventModifiers.rare_loot_bonus_pct > 0 && (
                  <span className="badge" style={{ background: '#1a0733', color: '#d8b4fe', border: '1px solid #6d28d9' }}>
                    💎 +{eventModifiers.rare_loot_bonus_pct}% Rare+
                  </span>
                )}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Exploration Status */}
      <GamePanel
        icon={dashboard?.exploration?.is_active ? '🟢' : '⭕'}
        title={dashboard?.exploration?.is_active ? 'Exploration en cours' : 'Aucune exploration'}
        variant={dashboard?.exploration?.is_active ? 'success' : 'default'}
        style={{ marginBottom: 20 }}
        noPadding
      >
        <div style={{ padding: '14px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          {dashboard?.exploration?.is_active ? (
            <>
              <div>
                <div style={{ color: '#22c55e', fontSize: 14, fontWeight: 600 }}>
                  <span className="anim-explore-pulse" style={{ marginRight: 8 }}>●</span>
                  {dashboard.exploration.zone_name}
                </div>
                <div style={{ color: '#4b5563', fontSize: 12, marginTop: 2 }}>
                  Vos héros explorent courageusement (enfin, c'est relatif)
                </div>
              </div>
              <div style={{ display: 'flex', gap: 8 }}>
                <GameButton variant="gold" icon="💰" onClick={handleCollect} loading={collecting} disabled={stopping}>
                  Collecter
                </GameButton>
                <GameButton variant="danger" icon="🛑" onClick={handleStop} loading={stopping} disabled={collecting}>
                  Arrêter
                </GameButton>
              </div>
            </>
          ) : (
            <>
              <span style={{ color: '#6b7280', fontSize: 13 }}>
                Vos héros attendent des ordres. En sirotant du café.
              </span>
              <Link to="/map" style={{ textDecoration: 'none' }}>
                <GameButton variant="secondary" icon="🗺️">Aller à la Carte</GameButton>
              </Link>
            </>
          )}
        </div>
      </GamePanel>

      {/* Collect Result */}
      {collectResult && (
        <GamePanel icon="🎁" title="Récompenses collectées" variant="success" style={{ marginBottom: 20 }} className="anim-slide-in">
          {/* Stats */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 10, marginBottom: 16 }}>
            {[
              ['⚔️', 'Combats', collectResult.combats_simulated, '#f87171'],
              ['✨', 'XP', collectResult.xp_gained, '#818cf8'],
              ['💰', 'Or', collectResult.gold_gained, '#fbbf24'],
            ].map(([icon, label, value, color]) => (
              <div key={label as string} style={{
                background: '#0d1117', borderRadius: 8, padding: '12px 10px',
                textAlign: 'center', border: '1px solid #1a1f2e',
              }}>
                <div style={{ fontSize: 18, marginBottom: 4 }}>{icon as string}</div>
                <div style={{ color: color as string, fontSize: 20, fontWeight: 700, fontFamily: 'var(--font-title)' }}>
                  {value as number}
                </div>
                <div style={{ color: '#6b7280', fontSize: 11 }}>{label as string}</div>
              </div>
            ))}
          </div>

          {/* Items gained */}
          {collectResult.items_gained?.length > 0 ? (
            <div>
              <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 10 }}>
                🎒 {collectResult.items_gained.length} objet(s) ajouté(s) à l'inventaire
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                {collectResult.items_gained.map((item: any, i: number) => (
                  <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10, background: '#0d1117', borderRadius: 6, padding: '6px 10px' }}>
                    <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={36} name={item.name} />
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ color: RARITY_COLOR[item.rarity] ?? '#9ca3af', fontWeight: 700, fontSize: 13, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {item.name}
                      </div>
                      <div style={{ color: '#6b7280', fontSize: 11 }}>Niv. {item.item_level}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ) : (
            <div style={{ color: '#4b5563', fontSize: 13, fontStyle: 'italic', textAlign: 'center', padding: '8px 0' }}>
              Aucun objet trouvé. Le Narrateur compatit. (Non, il ne compatit pas.)
            </div>
          )}

          {collectResult.narrator_comment && (
            <>
              <div className="game-divider" />
              <div style={{ color: '#c4b5fd', fontSize: 12, fontStyle: 'italic' }}>
                « {collectResult.narrator_comment} »
              </div>
            </>
          )}

          <div style={{ marginTop: 12, textAlign: 'right' }}>
            <GameButton variant="ghost" size="sm" onClick={() => setCollectResult(null)}>
              Fermer ✕
            </GameButton>
          </div>
        </GamePanel>
      )}

      {/* Heroes */}
      {dashboard?.heroes?.length > 0 ? (
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
            <h2 className="game-title" style={{ margin: 0, fontSize: 18 }}>⚔️ Mon Équipe</h2>
            <Link to="/team" style={{ textDecoration: 'none' }}>
              <GameButton variant="ghost" size="sm">Gérer l'équipe</GameButton>
            </Link>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 16 }}>
            {dashboard.heroes.map((hero: any) => (
              <HeroCard key={hero.id} hero={hero} />
            ))}
          </div>
        </div>
      ) : (
        <GamePanel variant="default" style={{ textAlign: 'center', padding: 40 }}>
          <div style={{ fontSize: 48, marginBottom: 12 }}>🏚️</div>
          <p style={{ color: '#6b7280', marginBottom: 16 }}>
            Aucun héros. Le Narrateur attend que tu te décides.
          </p>
          <Link to="/team" style={{ textDecoration: 'none' }}>
            <GameButton variant="primary" icon="⚔️">Recruter des Héros</GameButton>
          </Link>
        </GamePanel>
      )}
    </div>
  )
}
