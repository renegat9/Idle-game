import { useEffect, useState } from 'react'
import { zoneApi, explorationApi, reputationApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import type { Zone, ZoneReputation } from '../types'

const ELEMENT_COLORS: Record<string, string> = {
  physique: '#9ca3af', feu: '#ef4444', glace: '#93c5fd',
  foudre: '#fbbf24', poison: '#4ade80', sacre: '#fef08a', ombre: '#a78bfa',
}

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

  const handleStartExploration = async (zone: Zone) => {
    setStarting(zone.id)
    setMessage('')
    try {
      const { data } = await explorationApi.start(zone.id)
      setMessage(data.message)
      setExploring(true, zone.name)
      // Mettre à jour la zone courante
      setLocalZones((prev) => prev.map((z) => ({ ...z, is_current: z.id === zone.id })))
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur')
    } finally {
      setStarting(null)
    }
  }

  if (loading) return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>

  return (
    <div>
      <h1 style={{ color: '#f9fafb', marginBottom: 8 }}>🗺️ Carte du Monde</h1>
      {message && <NarratorBubble comment={message} />}

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16, marginTop: 20 }}>
        {zones.map((zone) => (
          <div
            key={zone.id}
            style={{
              background: zone.is_unlocked ? '#111827' : '#0d1117',
              border: `2px solid ${zone.is_current ? '#7c3aed' : zone.is_unlocked ? '#1f2937' : '#111'}`,
              borderRadius: 8,
              padding: 16,
              opacity: zone.is_unlocked ? 1 : 0.5,
            }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
              <div>
                <h3 style={{ color: zone.is_unlocked ? '#f9fafb' : '#4b5563', margin: 0, fontSize: 15 }}>
                  {zone.is_current && <span style={{ marginRight: 6 }}>▶</span>}
                  {zone.name}
                </h3>
                <div style={{ display: 'flex', gap: 8, marginTop: 4 }}>
                  <span style={{ background: '#1f2937', color: '#9ca3af', padding: '2px 8px', borderRadius: 4, fontSize: 11 }}>
                    Niv. {zone.level_min}–{zone.level_max}
                  </span>
                  <span style={{
                    background: '#1f2937',
                    color: ELEMENT_COLORS[zone.dominant_element] ?? '#9ca3af',
                    padding: '2px 8px', borderRadius: 4, fontSize: 11,
                  }}>
                    {zone.dominant_element}
                  </span>
                  {zone.is_magical && (
                    <span style={{ background: '#312e81', color: '#a5b4fc', padding: '2px 8px', borderRadius: 4, fontSize: 11 }}>Magique</span>
                  )}
                </div>
              </div>
              {zone.boss_defeated && (
                <span style={{ color: '#fbbf24', fontSize: 18 }} title="Boss vaincu">⭐</span>
              )}
            </div>

            <p style={{ color: '#6b7280', fontSize: 12, margin: '0 0 10px' }}>{zone.description}</p>

            {/* Reputation badge */}
            {reputations[zone.id] && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                <span style={{ fontSize: 11, color: REPUTATION_TIER_COLORS[reputations[zone.id].tier] ?? '#9ca3af', background: '#0f172a', border: '1px solid #1e293b', borderRadius: 6, padding: '2px 8px' }}>
                  ⭐ {reputations[zone.id].tier} ({reputations[zone.id].reputation}/200)
                </span>
                {reputations[zone.id].loot_bonus > 0 && (
                  <span style={{ fontSize: 11, color: '#a78bfa', background: '#0f172a', border: '1px solid #1e293b', borderRadius: 6, padding: '2px 8px' }}>
                    +{reputations[zone.id].loot_bonus}% loot
                  </span>
                )}
              </div>
            )}

            {zone.total_victories > 0 && (
              <p style={{ color: '#4b5563', fontSize: 11, margin: '0 0 8px' }}>
                {zone.total_victories} victoires
              </p>
            )}

            {zone.is_unlocked && !zone.is_current && (
              <button
                onClick={() => handleStartExploration(zone)}
                disabled={starting === zone.id}
                style={{
                  width: '100%', background: '#7c3aed', color: 'white', border: 'none',
                  borderRadius: 6, padding: '8px', cursor: starting === zone.id ? 'not-allowed' : 'pointer',
                  opacity: starting === zone.id ? 0.7 : 1, fontSize: 13,
                }}
              >
                {starting === zone.id ? 'Démarrage...' : 'Explorer cette zone'}
              </button>
            )}

            {zone.is_current && (
              <div style={{ textAlign: 'center', color: '#22c55e', fontSize: 13, padding: '6px 0' }}>
                ● Exploration en cours
              </div>
            )}

            {!zone.is_unlocked && (
              <div style={{ textAlign: 'center', color: '#4b5563', fontSize: 12, padding: '6px 0' }}>
                🔒 Vaincre le boss de la zone précédente
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
