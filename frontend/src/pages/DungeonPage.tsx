import { useEffect, useState } from 'react'
import { dungeonApi, zoneApi } from '../api/game'

type RoomPreview = {
  room_number: number
  type: 'combat' | 'treasure' | 'trap' | 'rest' | 'boss'
  monster_name?: string
  monster_level?: number
  monster_image_path?: string
  description?: string
}

type DungeonStatus = {
  active: boolean
  on_cooldown?: boolean
  available_at?: string | null
  dungeon_id?: number
  zone_id?: number
  status?: string
  current_room?: number
  total_rooms?: number
  room_preview?: RoomPreview | null
  gold_gained?: number
  loot_count?: number
  started_at?: string
}

const ROOM_ICONS: Record<string, string> = {
  combat:   '⚔️',
  treasure: '💰',
  trap:     '🪤',
  rest:     '🛏️',
  boss:     '💀',
}

const ROOM_COLORS: Record<string, string> = {
  combat:   '#ef4444',
  treasure: '#f59e0b',
  trap:     '#f97316',
  rest:     '#22c55e',
  boss:     '#a855f7',
}

export function DungeonPage() {
  const [status, setStatus] = useState<DungeonStatus | null>(null)
  const [currentZoneId, setCurrentZoneId] = useState<number | null>(null)
  const [loading, setLoading] = useState(true)
  const [advancing, setAdvancing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)

  useEffect(() => {
    loadStatus()
    zoneApi.list().then(r => {
      const zones = r.data.zones ?? []
      if (zones.length > 0) setCurrentZoneId(zones[0].id)
    }).catch(() => {})
  }, [])

  async function loadStatus() {
    setLoading(true)
    try {
      const { data } = await dungeonApi.status()
      setStatus(data)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function startDungeon() {
    setMessage(null)
    try {
      const { data } = await dungeonApi.start(currentZoneId ?? 1)
      setMessage({ text: data.narrator ?? 'Donjon commencé ! Bonne chance, vous en aurez besoin.', ok: true })
      await loadStatus()
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Impossible de démarrer le donjon.', ok: false })
    }
  }

  async function advance() {
    if (!status?.dungeon_id || advancing) return
    setAdvancing(true)
    setMessage(null)
    try {
      const { data } = await dungeonApi.enter(status.dungeon_id)
      const narration = data.room_result?.narration ?? data.narration ?? data.summary
      if (narration) {
        setMessage({ text: narration, ok: data.outcome !== 'failed' })
      }
      await loadStatus()
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur.', ok: false })
    }
    setAdvancing(false)
  }

  async function abandon() {
    if (!status?.dungeon_id) return
    setMessage(null)
    try {
      await dungeonApi.abandon(status.dungeon_id)
      await loadStatus()
      setMessage({ text: 'Donjon abandonné. Votre réputation en prend un coup.', ok: false })
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur.', ok: false })
    }
  }

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement du donjon...</div>

  const room = status?.room_preview
  // isFinished used implicitly by on_cooldown state display below
  void (status?.active === false && status?.on_cooldown === false && status?.dungeon_id !== undefined)

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>🏰 Donjon</h1>
      <p style={{ color: '#6b7280', marginBottom: 16, fontSize: 14 }}>
        Exploration procédurale — 5 à 8 salles, boss en fin de parcours.
      </p>

      {message && (
        <div style={{ background: message.ok ? '#052e16' : '#1c0505', border: `1px solid ${message.ok ? '#16a34a' : '#991b1b'}`, borderRadius: 8, padding: 12, marginBottom: 16 }}>
          <span style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>{message.text}</span>
        </div>
      )}

      {!status?.active ? (
        /* No active dungeon */
        <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 32, textAlign: 'center' }}>
          {status?.on_cooldown && status.available_at ? (
            <div>
              <p style={{ color: '#6b7280', margin: '0 0 8px' }}>Vos héros sont épuisés. Ils reprennent leurs forces.</p>
              <p style={{ color: '#f59e0b', margin: 0, fontSize: 14 }}>
                Prochain donjon disponible le {new Date(status.available_at).toLocaleString('fr-FR')}
              </p>
            </div>
          ) : (
            <div>
              <p style={{ color: '#94a3b8', margin: '0 0 16px' }}>Aucun donjon en cours. Vos héros s'ennuient profondément.</p>
              <button
                onClick={startDungeon}
                style={{ background: '#7c3aed', color: 'white', border: 'none', padding: '10px 24px', borderRadius: 8, cursor: 'pointer', fontSize: 15, fontWeight: 'bold' }}
              >
                🏰 Entrer dans le donjon
              </button>
            </div>
          )}
        </div>
      ) : (
        /* Active dungeon */
        <div>
          {/* Progress bar */}
          <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 16, marginBottom: 16 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
              <span style={{ color: '#94a3b8', fontSize: 13 }}>
                Salle {status.current_room} / {status.total_rooms}
              </span>
              <div style={{ display: 'flex', gap: 16 }}>
                <span style={{ color: '#f59e0b', fontSize: 13 }}>💰 {status.gold_gained ?? 0}</span>
                {(status.loot_count ?? 0) > 0 && (
                  <span style={{ color: '#22c55e', fontSize: 13 }}>🎁 {status.loot_count} objet(s)</span>
                )}
              </div>
            </div>

            {/* Progress dots */}
            <div style={{ display: 'flex', gap: 6 }}>
              {Array.from({ length: status.total_rooms ?? 0 }, (_, i) => {
                const roomNum = i + 1
                const done = roomNum < (status.current_room ?? 1)
                const current = roomNum === status.current_room
                return (
                  <div key={i} style={{
                    flex: 1,
                    height: 8,
                    borderRadius: 4,
                    background: done ? '#16a34a' : current ? '#7c3aed' : '#1e293b',
                    border: `1px solid ${done ? '#16a34a' : current ? '#7c3aed' : '#334155'}`,
                  }} />
                )
              })}
            </div>
          </div>

          {/* Current room */}
          {room && (
            <div style={{
              background: '#1e293b',
              border: `1px solid ${ROOM_COLORS[room.type] ?? '#334155'}`,
              borderRadius: 12,
              padding: 24,
              marginBottom: 16,
            }}>
              <h2 style={{ color: ROOM_COLORS[room.type] ?? '#f1f5f9', margin: '0 0 8px', fontSize: 18 }}>
                {ROOM_ICONS[room.type]} Salle {room.room_number} — {
                  room.type === 'combat' ? 'Combat' :
                  room.type === 'treasure' ? 'Trésor' :
                  room.type === 'trap' ? 'Piège' :
                  room.type === 'rest' ? 'Repos' : 'Boss Final'
                }
              </h2>
              {room.monster_name && (
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 16 }}>
                  {room.monster_image_path && (
                    <img
                      src={`/${room.monster_image_path}`}
                      alt={room.monster_name}
                      style={{ width: 56, height: 56, objectFit: 'contain', imageRendering: 'auto' }}
                    />
                  )}
                  <p style={{ color: '#94a3b8', margin: 0, fontSize: 14 }}>
                    Ennemi : <strong style={{ color: '#f1f5f9' }}>{room.monster_name}</strong>
                    {room.monster_level && <span> (Niv. {room.monster_level})</span>}
                  </p>
                </div>
              )}
              {room.description && (
                <p style={{ color: '#6b7280', margin: '0 0 16px', fontSize: 13, fontStyle: 'italic' }}>
                  {room.description}
                </p>
              )}
              <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
                <button
                  onClick={advance}
                  disabled={advancing}
                  style={{ background: advancing ? '#374151' : '#7c3aed', color: 'white', border: 'none', padding: '10px 20px', borderRadius: 8, cursor: advancing ? 'not-allowed' : 'pointer', fontSize: 14, fontWeight: 'bold', opacity: advancing ? 0.6 : 1 }}
                >
                  {advancing ? '...' : 'Avancer ▶️'}
                </button>
                <button
                  onClick={abandon}
                  style={{ background: 'transparent', color: '#6b7280', border: '1px solid #374151', padding: '10px 20px', borderRadius: 8, cursor: 'pointer', fontSize: 14 }}
                >
                  Fuir 🏃
                </button>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
