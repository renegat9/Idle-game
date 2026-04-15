import { useEffect, useState } from 'react'
import { zoneApi, explorationApi, reputationApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { GameButton } from '../components/ui/GameButton'
import { ZoneBackground, ELEMENT_ICON, ELEMENT_COLOR } from '../components/ui/ZoneBackground'
import { StatBar } from '../components/ui/StatBar'
import type { Zone, ZoneReputation } from '../types'

const REPUTATION_TIER_COLORS: Record<string, string> = {
  étranger: '#6b7280', neutre: '#9ca3af', ami: '#22c55e',
  honore: '#3b82f6', revere: '#a855f7', exalte: '#f59e0b',
}


export function MapPage() {
  const { setZones, setExploring } = useGameStore()
  const [zones, setLocalZones] = useState<Zone[]>([])
  const [reputations, setReputations] = useState<Record<number, ZoneReputation>>({})
  const [loading, setLoading] = useState(true)
  const [starting, setStarting] = useState<number | null>(null)
  const [stopping, setStopping] = useState(false)
  const [message, setMessage] = useState('')

  useEffect(() => {
    Promise.all([zoneApi.list(), reputationApi.all()]).then(([zoneRes, repRes]) => {
      setLocalZones(zoneRes.data.zones)
      setZones(zoneRes.data.zones)
      const repMap: Record<number, ZoneReputation> = {}
      for (const rep of (repRes.data.reputations ?? [])) {
        repMap[rep.zone_id] = rep
      }
      setReputations(repMap)
    }).finally(() => setLoading(false))
  }, [])

  const handleStopExploration = async () => {
    setStopping(true)
    setMessage('')
    try {
      await explorationApi.stop()
      setMessage('Exploration arrêtée. Vos héros rentrent au camp.')
      setExploring(false)
      setLocalZones((prev) => prev.map((z) => ({ ...z, is_current: false })))
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur')
    } finally {
      setStopping(false)
    }
  }

  const handleStartExploration = async (zone: Zone) => {
    setStarting(zone.id)
    setMessage('')
    try {
      const { data } = await explorationApi.start(zone.id)
      setMessage(data.message)
      setExploring(true, zone.name)
      setLocalZones((prev) => prev.map((z) => ({ ...z, is_current: z.id === zone.id })))
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur')
    } finally {
      setStarting(null)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement de la carte…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🗺️ Carte du Monde</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Choisissez votre prochaine zone d'exploration
        </p>
      </div>

      {message && <NarratorBubble comment={message} />}

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(290px, 1fr))', gap: 16 }}>
        {zones.map((zone) => {
          const rep = reputations[zone.id]
          const elemIcon = ELEMENT_ICON[zone.dominant_element] ?? '⚔️'
          const elemColor = ELEMENT_COLOR[zone.dominant_element] ?? '#9ca3af'
          const repMax = 200

          return (
            <div
              key={zone.id}
              className={`zone-card ${zone.is_current ? 'zone-active' : ''} ${!zone.is_unlocked ? 'zone-locked' : ''}`}
              style={{ minHeight: 200 }}
            >
              {/* Thematic background */}
              <ZoneBackground element={zone.dominant_element} isActive={zone.is_current} isLocked={!zone.is_unlocked} imagePath={zone.background_image_path} />

              {/* Zone content */}
              <div className="zone-content" style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>

                {/* Header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    {/* Big element icon */}
                    <div style={{
                      width: 44, height: 44, borderRadius: 8,
                      background: `rgba(${zone.dominant_element === 'feu' ? '239,68,68' : zone.dominant_element === 'glace' ? '147,197,253' : '124,58,237'}, 0.15)`,
                      border: `1px solid ${elemColor}44`,
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: 22, flexShrink: 0,
                    }}>
                      {elemIcon}
                    </div>
                    <div>
                      <div className="game-title" style={{ fontSize: 14, color: zone.is_unlocked ? '#f9fafb' : '#4b5563', display: 'flex', alignItems: 'center', gap: 6 }}>
                        {zone.is_current && <span style={{ color: '#22c55e', fontSize: 10 }}>▶</span>}
                        {zone.name}
                      </div>
                      <div style={{ display: 'flex', gap: 6, marginTop: 4, flexWrap: 'wrap' }}>
                        <span style={{ background: '#0d1117aa', color: '#9ca3af', padding: '1px 6px', borderRadius: 3, fontSize: 10, border: '1px solid #1f2937' }}>
                          Niv.{zone.level_min}–{zone.level_max}
                        </span>
                        <span style={{ background: `${elemColor}22`, color: elemColor, padding: '1px 6px', borderRadius: 3, fontSize: 10, border: `1px solid ${elemColor}44` }}>
                          {zone.dominant_element}
                        </span>
                        {zone.is_magical && (
                          <span style={{ background: '#1e1b4b88', color: '#a5b4fc', padding: '1px 6px', borderRadius: 3, fontSize: 10, border: '1px solid #312e81' }}>
                            ✨ Magique
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                  {zone.boss_defeated && (
                    <span style={{ fontSize: 20 }} title="Boss vaincu">⭐</span>
                  )}
                </div>

                {/* Description */}
                <p style={{ color: '#6b7280', fontSize: 12, margin: 0, lineHeight: 1.5 }}>
                  {zone.description}
                </p>

                {/* Reputation */}
                {rep && (
                  <div>
                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                      <span style={{ fontSize: 11, color: REPUTATION_TIER_COLORS[rep.tier] ?? '#9ca3af' }}>
                        ⭐ Réputation : {rep.tier}
                      </span>
                      <span style={{ fontSize: 10, color: '#6b7280' }}>{rep.reputation}/{repMax}</span>
                    </div>
                    <StatBar value={rep.reputation} max={repMax} variant="custom" color={REPUTATION_TIER_COLORS[rep.tier] ?? '#9ca3af'} height={5} />
                    {rep.loot_bonus > 0 && (
                      <div style={{ fontSize: 10, color: '#a78bfa', marginTop: 3 }}>
                        +{rep.loot_bonus}% loot dans cette zone
                      </div>
                    )}
                  </div>
                )}

                {zone.total_victories > 0 && (
                  <div style={{ fontSize: 11, color: '#4b5563' }}>
                    🏆 {zone.total_victories} victoires
                  </div>
                )}

                {/* Action */}
                <div style={{ marginTop: 4 }}>
                  {zone.is_current ? (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6, color: '#22c55e', fontSize: 13, fontWeight: 600 }}>
                        <span className="anim-explore-pulse">●</span>
                        Exploration en cours
                      </div>
                      <GameButton
                        variant="danger"
                        size="sm"
                        icon="🛑"
                        onClick={handleStopExploration}
                        loading={stopping}
                        style={{ width: '100%' }}
                      >
                        Arrêter l'exploration
                      </GameButton>
                    </div>
                  ) : zone.is_unlocked ? (
                    <GameButton
                      variant="primary"
                      size="sm"
                      icon={elemIcon}
                      onClick={() => handleStartExploration(zone)}
                      loading={starting === zone.id}
                      style={{ width: '100%' }}
                    >
                      Explorer cette zone
                    </GameButton>
                  ) : (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, color: '#4b5563', fontSize: 12 }}>
                      <span style={{ fontSize: 14 }}>🔒</span>
                      Vaincre le boss de la zone précédente
                    </div>
                  )}
                </div>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
