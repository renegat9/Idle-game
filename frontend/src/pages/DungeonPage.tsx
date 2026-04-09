import { useEffect, useState } from 'react'
import { dungeonApi, zoneApi } from '../api/game'
import { MonsterPortrait } from '../components/ui/MonsterPortrait'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'

type RoomPreview = {
  room_number: number
  type: 'combat' | 'treasure' | 'trap' | 'rest' | 'boss'
  monster_name?: string
  monster_level?: number
  monster_image_path?: string
  description?: string
}

type DungeonStatus = {
  active: boolean; on_cooldown?: boolean; available_at?: string | null
  dungeon_id?: number; zone_id?: number; status?: string
  current_room?: number; total_rooms?: number; room_preview?: RoomPreview | null
  gold_gained?: number; loot_count?: number; started_at?: string
}

const ROOM_CONFIG: Record<string, { icon: string; color: string; label: string; bg: string }> = {
  combat:   { icon: '⚔️', color: '#ef4444', label: 'Combat',      bg: '#1a0505' },
  treasure: { icon: '💰', color: '#fbbf24', label: 'Trésor',      bg: '#1a0d00' },
  trap:     { icon: '🪤', color: '#f97316', label: 'Piège',       bg: '#1a0800' },
  rest:     { icon: '🛏️', color: '#22c55e', label: 'Repos',       bg: '#051a0a' },
  boss:     { icon: '💀', color: '#a855f7', label: 'Boss Final',  bg: '#110820' },
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
      if (narration) setMessage({ text: narration, ok: data.outcome !== 'failed' })
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

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement du donjon…</div>
      </div>
    )
  }

  const room = status?.room_preview
  const roomCfg = room ? (ROOM_CONFIG[room.type] ?? ROOM_CONFIG.combat) : null

  return (
    <div className="page-bg-dungeon">
      {/* Header */}
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🏚️ Le Donjon</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Exploration procédurale — salles mystérieuses, boss en fin de parcours
        </p>
      </div>

      {/* Message */}
      {message && (
        <div
          className={`narrator-bubble anim-slide-in`}
          style={{
            marginBottom: 16,
            borderLeftColor: message.ok ? '#22c55e' : '#ef4444',
            borderColor: message.ok ? '#166534' : '#7f1d1d',
            background: message.ok ? '#020f08' : '#0a0202',
          }}
        >
          <div className="narrator-label" style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>
            {message.ok ? '✅ Résultat' : '❌ Résultat'}
          </div>
          <p className="narrator-text" style={{ color: message.ok ? '#86efac' : '#fca5a5', margin: 0 }}>
            « {message.text} »
          </p>
        </div>
      )}

      {!status?.active ? (
        /* No active dungeon */
        <GamePanel variant="default" noPadding>
          <div style={{ padding: 40, textAlign: 'center' }}>
            {status?.on_cooldown && status.available_at ? (
              <div>
                <div style={{ fontSize: 48, marginBottom: 12 }}>😴</div>
                <p style={{ color: '#9ca3af', marginBottom: 6 }}>Vos héros sont épuisés. Ils récupèrent.</p>
                <p style={{ color: '#f59e0b', fontSize: 14, margin: 0 }}>
                  Prochain donjon disponible : {new Date(status.available_at).toLocaleString('fr-FR')}
                </p>
              </div>
            ) : (
              <div>
                <div style={{ fontSize: 48, marginBottom: 12 }}>🏚️</div>
                <p style={{ color: '#9ca3af', marginBottom: 20 }}>
                  Aucun donjon en cours. Vos héros s'ennuient profondément.
                </p>
                <GameButton variant="primary" size="lg" icon="🏚️" onClick={startDungeon}>
                  Entrer dans le Donjon
                </GameButton>
              </div>
            )}
          </div>
        </GamePanel>
      ) : (
        /* Active dungeon */
        <div>
          {/* Progress panel */}
          <GamePanel icon="🗺️" title="Progression" variant="default" style={{ marginBottom: 16 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
              <span style={{ color: '#9ca3af', fontSize: 13 }}>
                Salle <strong style={{ color: '#f9fafb' }}>{status.current_room}</strong> / {status.total_rooms}
              </span>
              <div style={{ display: 'flex', gap: 16 }}>
                <span style={{ color: '#fbbf24', fontSize: 13 }}>💰 {status.gold_gained ?? 0} or</span>
                {(status.loot_count ?? 0) > 0 && (
                  <span style={{ color: '#4ade80', fontSize: 13 }}>🎁 {status.loot_count} objet(s)</span>
                )}
              </div>
            </div>

            {/* Room progress dots */}
            <div style={{ display: 'flex', gap: 5 }}>
              {Array.from({ length: status.total_rooms ?? 0 }, (_, i) => {
                const roomNum = i + 1
                const done = roomNum < (status.current_room ?? 1)
                const current = roomNum === status.current_room
                const isBoss = roomNum === status.total_rooms
                return (
                  <div
                    key={i}
                    title={`Salle ${roomNum}${isBoss ? ' (Boss)' : ''}`}
                    style={{
                      flex: 1, height: 10, borderRadius: 4, cursor: 'default',
                      background: done ? '#16a34a' : current ? '#7c3aed' : '#1f2937',
                      border: `1px solid ${done ? '#22c55e' : current ? '#7c3aed' : '#374151'}`,
                      boxShadow: current ? '0 0 8px rgba(124,58,237,0.5)' : 'none',
                      fontSize: isBoss ? 8 : undefined,
                      display: isBoss ? 'flex' : undefined,
                      alignItems: isBoss ? 'center' : undefined,
                      justifyContent: isBoss ? 'center' : undefined,
                    }}
                  >
                    {isBoss && !done && <span style={{ color: current ? 'white' : '#6b7280' }}>💀</span>}
                  </div>
                )
              })}
            </div>
          </GamePanel>

          {/* Current room */}
          {room && roomCfg && (
            <div
              className="game-panel anim-slide-in"
              style={{
                marginBottom: 16,
                borderColor: roomCfg.color + '44',
                boxShadow: `0 0 20px ${roomCfg.color}15`,
                overflow: 'hidden',
              }}
            >
              {/* Room header */}
              <div style={{
                padding: '14px 16px',
                background: `linear-gradient(90deg, ${roomCfg.bg}, transparent)`,
                borderBottom: `1px solid ${roomCfg.color}33`,
                display: 'flex', alignItems: 'center', gap: 10,
              }}>
                <span style={{ fontSize: 24 }}>{roomCfg.icon}</span>
                <div>
                  <div className="game-title" style={{ fontSize: 15, color: roomCfg.color }}>
                    Salle {room.room_number} — {roomCfg.label}
                  </div>
                </div>
              </div>

              <div style={{ padding: 16 }}>
                {/* Monster */}
                {room.monster_name && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 14, background: '#0d1117', borderRadius: 8, padding: '10px 12px' }}>
                    <MonsterPortrait
                      name={room.monster_name}
                      imagePath={room.monster_image_path}
                      level={room.monster_level}
                      size={72}
                    />
                    <div>
                      <div className="game-title" style={{ fontSize: 15, color: '#f9fafb', marginBottom: 4 }}>
                        {room.monster_name}
                      </div>
                      {room.monster_level && (
                        <span style={{ background: '#7f1d1d', color: '#fca5a5', padding: '2px 8px', borderRadius: 4, fontSize: 12, fontFamily: 'var(--font-title)' }}>
                          Niveau {room.monster_level}
                        </span>
                      )}
                    </div>
                  </div>
                )}

                {/* Room description */}
                {room.description && (
                  <p style={{ color: '#9ca3af', fontSize: 13, fontStyle: 'italic', lineHeight: 1.6, marginBottom: 16 }}>
                    {room.description}
                  </p>
                )}

                {/* Actions */}
                <div style={{ display: 'flex', gap: 10 }}>
                  <GameButton
                    variant="primary"
                    icon={roomCfg.icon}
                    onClick={advance}
                    loading={advancing}
                    size="lg"
                  >
                    {room.type === 'combat' || room.type === 'boss' ? 'Combattre !' : room.type === 'treasure' ? 'Ramasser' : room.type === 'rest' ? 'Se reposer' : 'Avancer'}
                  </GameButton>
                  <GameButton variant="ghost" onClick={abandon}>
                    🏃 Fuir
                  </GameButton>
                </div>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
