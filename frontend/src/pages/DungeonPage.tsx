import { useEffect, useState } from 'react'
import { dungeonApi, zoneApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { MonsterPortrait } from '../components/ui/MonsterPortrait'
import { HeroPortrait } from '../components/ui/HeroPortrait'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import { RarityBadge } from '../components/hero/RarityBadge'
import type { Zone } from '../types'

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
  room_types?: { type: string; is_completed: boolean }[]
  gold_gained?: number; loot_count?: number; started_at?: string
}

type LastReward = {
  gold: number
  xp_per_hero: number
  loot_items: { item_id: number; name: string; rarity: string }[]
  outcome: 'victory' | 'found' | 'triggered' | 'healed'
  damage_percent?: number
  heal_percent?: number
}

const ROOM_CONFIG: Record<string, { icon: string; color: string; label: string; bg: string; accentBg: string }> = {
  combat:   { icon: '⚔️', color: '#ef4444', label: 'Combat',     bg: 'rgba(26,5,5,0.8)',    accentBg: 'rgba(127,29,29,0.3)' },
  treasure: { icon: '💰', color: '#fbbf24', label: 'Trésor',     bg: 'rgba(26,13,0,0.8)',   accentBg: 'rgba(120,53,15,0.3)' },
  trap:     { icon: '🪤', color: '#f97316', label: 'Piège',      bg: 'rgba(26,8,0,0.8)',    accentBg: 'rgba(124,45,18,0.3)' },
  rest:     { icon: '🛏️', color: '#22c55e', label: 'Repos',      bg: 'rgba(5,26,10,0.8)',   accentBg: 'rgba(20,83,45,0.3)' },
  boss:     { icon: '💀', color: '#a855f7', label: 'Boss Final', bg: 'rgba(17,8,32,0.8)',   accentBg: 'rgba(88,28,135,0.3)' },
}

const ROOM_FLAVOR: Record<string, string[]> = {
  combat:   ['⚔️', '🗡️', '🛡️'],
  treasure: ['💎', '📦', '🔮'],
  trap:     ['💥', '🕸️', '⚠️'],
  rest:     ['🔥', '🍺', '💤'],
  boss:     ['💀', '🐲', '👁️'],
}

function HpBar({ current, max, color = '#22c55e' }: { current: number; max: number; color?: string }) {
  const pct = max > 0 ? Math.max(0, Math.min(100, Math.round((current / max) * 100))) : 0
  const barColor = pct <= 25 ? '#ef4444' : pct <= 50 ? '#f97316' : color
  return (
    <div style={{ width: '100%' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 10, color: '#6b7280', marginBottom: 2 }}>
        <span style={{ color: barColor, fontWeight: 600 }}>{current}</span>
        <span>{max} PV</span>
      </div>
      <div style={{ height: 6, borderRadius: 3, background: '#1f2937', overflow: 'hidden' }}>
        <div style={{
          height: '100%', borderRadius: 3, width: `${pct}%`,
          background: barColor,
          boxShadow: `0 0 6px ${barColor}66`,
          transition: 'width 0.4s ease',
        }} />
      </div>
    </div>
  )
}

export function DungeonPage() {
  const heroes = useGameStore(s => s.heroes)
  const [status, setStatus] = useState<DungeonStatus | null>(null)
  const [currentZoneId, setCurrentZoneId] = useState<number | null>(null)
  const [currentZoneName, setCurrentZoneName] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [advancing, setAdvancing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)
  const [lastReward, setLastReward] = useState<LastReward | null>(null)

  useEffect(() => {
    loadStatus()
    zoneApi.list().then(r => {
      const zones: Zone[] = r.data.zones ?? []
      // Use the player's active exploration zone; fall back to the furthest unlocked zone
      const current = zones.find(z => z.is_current)
        ?? [...zones].filter(z => z.is_unlocked).sort((a, b) => b.order_index - a.order_index)[0]
        ?? zones[0]
      if (current) {
        setCurrentZoneId(current.id)
        setCurrentZoneName(current.name)
      }
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
    setLastReward(null)
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
    setLastReward(null)
    try {
      const { data } = await dungeonApi.enter(status.dungeon_id)
      // room_result.summary = texte du combat ; data.narrator = commentaire global donjon
      const roomText = data.room_result?.summary ?? data.room_result?.narration ?? ''
      const dungeonText = data.narrator ?? ''
      let text = roomText || dungeonText
      if (data.dungeon_over && data.outcome === 'completed' && data.unlocked_zone) {
        text = (text ? text + ' — ' : '') + `🔓 Nouvelle zone débloquée : ${data.unlocked_zone} !`
      }
      if (data.dungeon_over && data.outcome === 'boss_defeat') {
        text = (text ? text + ' — ' : '') + '💀 Le boss résiste. Revenez plus fort !'
      }
      if (text) setMessage({ text, ok: data.outcome !== 'failed' && data.outcome !== 'boss_defeat' })

      // Capture room result details for display (all room types)
      const rr = data.room_result
      if (rr?.outcome) {
        setLastReward({
          gold: rr.gold ?? 0,
          xp_per_hero: rr.xp_per_hero ?? 0,
          loot_items: rr.loot_items ?? [],
          outcome: rr.outcome,
          damage_percent: rr.damage_percent,
          heal_percent: rr.heal_percent,
        })
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
  const isCombatRoom = room?.type === 'combat' || room?.type === 'boss'
  const activeHeroes = heroes.filter(h => h.is_active)

  return (
    <div className="page-bg-dungeon">
      {/* Header */}
      <div style={{ marginBottom: 20 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🏚️ Le Donjon</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Exploration procédurale — salles mystérieuses, boss en fin de parcours
        </p>
      </div>

      {/* Narration message */}
      {message && (
        <div
          className="narrator-bubble anim-slide-in"
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

      {/* Room result panel */}
      {lastReward && (() => {
        const isTrap   = lastReward.outcome === 'triggered'
        const isHeal   = lastReward.outcome === 'healed'
        const bg    = isTrap ? 'linear-gradient(135deg, #1a0505, #0a0202)' : isHeal ? 'linear-gradient(135deg, #051a12, #020f08)' : 'linear-gradient(135deg, #051a05, #0a1505)'
        const border = isTrap ? '#7f1d1d55' : isHeal ? '#16534455' : '#16a34a55'
        const label  = isTrap ? '🪤 Piège déclenché' : isHeal ? '🛏️ Repos' : lastReward.outcome === 'found' ? '💰 Butin trouvé' : '⚔️ Gains du combat'
        const labelColor = isTrap ? '#f87171' : isHeal ? '#4ade80' : '#4ade80'
        const chipBg  = isTrap ? '#1a0505' : isHeal ? '#051a12' : '#0d1a07'
        const chipBorder = isTrap ? '#7f1d1d33' : isHeal ? '#16534433' : '#16a34a33'
        return (
          <div
            className="anim-slide-in"
            style={{ marginBottom: 16, background: bg, border: `1px solid ${border}`, borderRadius: 8, padding: '14px 16px', display: 'flex', gap: 12, alignItems: 'flex-start', flexWrap: 'wrap' }}
          >
            <div style={{ fontSize: 11, color: labelColor, fontFamily: 'var(--font-title)', textTransform: 'uppercase', letterSpacing: '0.08em', width: '100%', marginBottom: 4 }}>
              {label}
            </div>
            {/* Trap: damage taken */}
            {isTrap && lastReward.damage_percent != null && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 6, background: chipBg, border: `1px solid ${chipBorder}`, borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ fontSize: 16 }}>💥</span>
                <div>
                  <div style={{ color: '#f87171', fontWeight: 700, fontSize: 16, fontFamily: 'var(--font-title)' }}>−{lastReward.damage_percent}% PV</div>
                  <div style={{ color: '#6b7280', fontSize: 10 }}>de l'équipe</div>
                </div>
              </div>
            )}
            {/* Rest: HP healed */}
            {isHeal && lastReward.heal_percent != null && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 6, background: chipBg, border: `1px solid ${chipBorder}`, borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ fontSize: 16 }}>❤️</span>
                <div>
                  <div style={{ color: '#4ade80', fontWeight: 700, fontSize: 16, fontFamily: 'var(--font-title)' }}>+{lastReward.heal_percent}% PV</div>
                  <div style={{ color: '#6b7280', fontSize: 10 }}>récupérés</div>
                </div>
              </div>
            )}
            {/* Gold */}
            {lastReward.gold > 0 && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 6, background: chipBg, border: `1px solid ${chipBorder}`, borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ fontSize: 16 }}>💰</span>
                <div>
                  <div style={{ color: '#fbbf24', fontWeight: 700, fontSize: 16, fontFamily: 'var(--font-title)' }}>+{lastReward.gold}</div>
                  <div style={{ color: '#6b7280', fontSize: 10 }}>or</div>
                </div>
              </div>
            )}
            {/* XP */}
            {lastReward.xp_per_hero > 0 && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 6, background: chipBg, border: `1px solid ${chipBorder}`, borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ fontSize: 16 }}>⭐</span>
                <div>
                  <div style={{ color: '#818cf8', fontWeight: 700, fontSize: 16, fontFamily: 'var(--font-title)' }}>+{lastReward.xp_per_hero}</div>
                  <div style={{ color: '#6b7280', fontSize: 10 }}>XP / héros</div>
                </div>
              </div>
            )}
            {/* Items */}
            {lastReward.loot_items.map((item, i) => (
              <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 8, background: chipBg, border: `1px solid ${chipBorder}`, borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ fontSize: 16 }}>🎁</span>
                <div>
                  <div style={{ color: '#f9fafb', fontWeight: 600, fontSize: 13 }}>{item.name}</div>
                  <RarityBadge rarity={item.rarity} />
                </div>
              </div>
            ))}
          </div>
        )
      })()}

      {!status?.active ? (
        /* ── No active dungeon ── */
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, alignItems: 'start' }}>
          {/* Start panel */}
          <GamePanel variant="default" noPadding>
            <div style={{ padding: 40, textAlign: 'center' }}>
              {status?.on_cooldown && status.available_at ? (
                <>
                  <div style={{ fontSize: 56, marginBottom: 12 }}>😴</div>
                  <div className="game-title" style={{ fontSize: 16, marginBottom: 8, color: '#f97316' }}>Héros épuisés</div>
                  <p style={{ color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Vos héros récupèrent péniblement.</p>
                  <p style={{ color: '#f59e0b', fontSize: 13, margin: 0 }}>
                    Disponible : {new Date(status.available_at).toLocaleString('fr-FR')}
                  </p>
                </>
              ) : (
                <>
                  <div style={{ fontSize: 56, marginBottom: 12 }}>🏚️</div>
                  <div className="game-title" style={{ fontSize: 16, marginBottom: 8 }}>Aucun donjon en cours</div>
                  <p style={{ color: '#9ca3af', marginBottom: 8, fontSize: 13 }}>
                    Vos héros s'ennuient profondément.
                  </p>
                  {currentZoneName && (
                    <p style={{ color: '#6b7280', marginBottom: 20, fontSize: 12 }}>
                      Zone : <span style={{ color: '#c4b5fd' }}>{currentZoneName}</span>
                    </p>
                  )}
                  <GameButton variant="danger" size="lg" icon="🏚️" onClick={startDungeon}>
                    Entrer dans le Donjon
                  </GameButton>
                </>
              )}
            </div>
          </GamePanel>

          {/* Hero roster preview */}
          {activeHeroes.length > 0 && (
            <GamePanel icon="⚔️" title="Votre Équipe" variant="default">
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                {activeHeroes.map(hero => (
                  <div key={hero.id} style={{ display: 'flex', alignItems: 'center', gap: 12, background: '#0d1117', borderRadius: 8, padding: '8px 12px' }}>
                    <HeroPortrait
                      classSlug={hero.class?.slug ?? ''}
                      imagePath={hero.image_path}
                      name={hero.name}
                      size={48}
                      hpPercent={hero.computed_stats?.max_hp ? Math.round(((hero.computed_stats.current_hp ?? 0) / hero.computed_stats.max_hp) * 100) : 100}
                    />
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontFamily: 'var(--font-title)', fontSize: 13, color: '#f9fafb', marginBottom: 4 }}>{hero.name}</div>
                      <HpBar current={hero.computed_stats?.current_hp ?? 0} max={hero.computed_stats?.max_hp ?? 0} />
                    </div>
                    <div style={{ fontSize: 11, color: '#6b7280', whiteSpace: 'nowrap' }}>Niv.{hero.level}</div>
                  </div>
                ))}
              </div>
            </GamePanel>
          )}
        </div>
      ) : (
        /* ── Active dungeon ── */
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

          {/* Progress bar */}
          <GamePanel icon="🗺️" title={`Salle ${status.current_room} / ${status.total_rooms}`} variant="default">
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 16, marginBottom: 10 }}>
              <span style={{ color: '#fbbf24', fontSize: 13 }}>💰 {status.gold_gained ?? 0} or</span>
              {(status.loot_count ?? 0) > 0 && (
                <span style={{ color: '#4ade80', fontSize: 13 }}>🎁 {status.loot_count} objet(s)</span>
              )}
            </div>
            <div style={{ display: 'flex', gap: 5 }}>
              {Array.from({ length: status.total_rooms ?? 0 }, (_, i) => {
                const roomNum = i + 1
                const done = roomNum < (status.current_room ?? 1)
                const current = roomNum === status.current_room
                const roomInfo = status.room_types?.[i]
                const cfg = roomInfo ? (ROOM_CONFIG[roomInfo.type] ?? ROOM_CONFIG.combat) : ROOM_CONFIG.combat
                return (
                  <div
                    key={i}
                    title={`Salle ${roomNum} — ${cfg.label}`}
                    style={{
                      flex: 1, height: 16, borderRadius: 4,
                      background: done ? '#16a34a33' : current ? cfg.color + '33' : '#1f2937',
                      border: `1px solid ${done ? '#22c55e55' : current ? cfg.color : '#374151'}`,
                      boxShadow: current ? `0 0 10px ${cfg.color}66` : 'none',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: 9, transition: 'all 0.3s ease',
                    }}
                  >
                    {done
                      ? <span style={{ color: '#4ade80' }}>✓</span>
                      : <span style={{ opacity: current ? 1 : 0.5 }}>{cfg.icon}</span>
                    }
                  </div>
                )
              })}
            </div>
          </GamePanel>

          {/* Battle arena */}
          {room && roomCfg && (
            <div
              className="game-panel anim-slide-in"
              style={{
                borderColor: roomCfg.color + '55',
                boxShadow: `0 0 30px ${roomCfg.color}18, inset 0 0 40px rgba(0,0,0,0.4)`,
                overflow: 'hidden',
                padding: 0,
              }}
            >
              {/* Room header */}
              <div style={{
                padding: '12px 20px',
                background: `linear-gradient(90deg, ${roomCfg.bg}, transparent)`,
                borderBottom: `1px solid ${roomCfg.color}33`,
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <span style={{ fontSize: 22 }}>{roomCfg.icon}</span>
                  <div>
                    <div className="game-title" style={{ fontSize: 16, color: roomCfg.color }}>
                      Salle {room.room_number} — {roomCfg.label}
                    </div>
                  </div>
                </div>
                <div style={{ display: 'flex', gap: 6 }}>
                  {(ROOM_FLAVOR[room.type] ?? []).map((e, i) => (
                    <span key={i} style={{ fontSize: 14, opacity: 0.5 }}>{e}</span>
                  ))}
                </div>
              </div>

              {/* Arena: heroes vs monster (or room visual) */}
              <div style={{ padding: 20 }}>
                {isCombatRoom && room.monster_name ? (
                  /* ── Battle layout ── */
                  <div style={{ marginBottom: 20 }}>
                    <div style={{
                      display: 'grid',
                      gridTemplateColumns: '1fr auto 1fr',
                      gap: 16,
                      alignItems: 'center',
                      background: roomCfg.accentBg,
                      borderRadius: 12,
                      padding: '20px 16px',
                      border: `1px solid ${roomCfg.color}22`,
                    }}>
                      {/* Heroes side */}
                      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                        <div style={{ fontSize: 10, color: '#6b7280', fontFamily: 'var(--font-title)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 4 }}>
                          Votre équipe
                        </div>
                        {activeHeroes.length > 0 ? activeHeroes.map(hero => {
                          const maxHp = hero.computed_stats?.max_hp ?? 0
                          const curHp = hero.computed_stats?.current_hp ?? 0
                          const hpPct = maxHp > 0 ? Math.round((curHp / maxHp) * 100) : 100
                          return (
                            <div key={hero.id} style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                              <HeroPortrait
                                classSlug={hero.class?.slug ?? ''}
                                imagePath={hero.image_path}
                                name={hero.name}
                                size={56}
                                hpPercent={hpPct}
                                animClass={hpPct <= 25 ? 'anim-shake' : ''}
                              />
                              <div style={{ flex: 1, minWidth: 0 }}>
                                <div style={{ fontFamily: 'var(--font-title)', fontSize: 12, color: '#e5e7eb', marginBottom: 3, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                  {hero.name}
                                </div>
                                <HpBar current={curHp} max={maxHp} />
                              </div>
                            </div>
                          )
                        }) : (
                          <div style={{ color: '#6b7280', fontSize: 13, fontStyle: 'italic' }}>Aucun héros actif</div>
                        )}
                      </div>

                      {/* VS divider */}
                      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
                        <div style={{
                          width: 1, height: 24,
                          background: `linear-gradient(to bottom, transparent, ${roomCfg.color}66)`,
                        }} />
                        <div style={{
                          fontFamily: 'var(--font-title)',
                          fontSize: 18,
                          fontWeight: 700,
                          color: roomCfg.color,
                          textShadow: `0 0 16px ${roomCfg.color}`,
                          letterSpacing: '0.05em',
                          padding: '6px 10px',
                          border: `1px solid ${roomCfg.color}44`,
                          borderRadius: 6,
                          background: `${roomCfg.color}11`,
                        }}>
                          VS
                        </div>
                        <div style={{
                          width: 1, height: 24,
                          background: `linear-gradient(to top, transparent, ${roomCfg.color}66)`,
                        }} />
                      </div>

                      {/* Monster side */}
                      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 8 }}>
                        <div style={{ fontSize: 10, color: '#6b7280', fontFamily: 'var(--font-title)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 4 }}>
                          {room.type === 'boss' ? '⚠️ Boss' : 'Ennemi'}
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexDirection: 'row-reverse' }}>
                          <MonsterPortrait
                            name={room.monster_name}
                            imagePath={room.monster_image_path}
                            level={room.monster_level}
                            size={room.type === 'boss' ? 96 : 72}
                          />
                          <div style={{ textAlign: 'right' }}>
                            <div className="game-title" style={{ fontSize: 15, color: '#f9fafb', marginBottom: 6 }}>
                              {room.monster_name}
                            </div>
                            {room.monster_level && (
                              <span style={{
                                background: roomCfg.color + '22',
                                color: roomCfg.color,
                                border: `1px solid ${roomCfg.color}44`,
                                padding: '2px 8px',
                                borderRadius: 4,
                                fontSize: 11,
                                fontFamily: 'var(--font-title)',
                              }}>
                                Niveau {room.monster_level}
                              </span>
                            )}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ) : (
                  /* ── Non-combat room visual ── */
                  <div style={{
                    display: 'flex', alignItems: 'center', gap: 20, marginBottom: 20,
                    background: roomCfg.accentBg,
                    borderRadius: 12, padding: '20px 24px',
                    border: `1px solid ${roomCfg.color}22`,
                  }}>
                    {/* Big room icon */}
                    <div style={{
                      fontSize: 64, lineHeight: 1, flexShrink: 0,
                      filter: `drop-shadow(0 0 16px ${roomCfg.color}66)`,
                    }}>
                      {roomCfg.icon}
                    </div>
                    {/* Heroes compact row for non-combat */}
                    {activeHeroes.length > 0 && (
                      <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 10, color: '#6b7280', fontFamily: 'var(--font-title)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 8 }}>
                          Votre équipe
                        </div>
                        <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
                          {activeHeroes.map(hero => {
                            const maxHp = hero.computed_stats?.max_hp ?? 0
                            const curHp = hero.computed_stats?.current_hp ?? 0
                            const hpPct = maxHp > 0 ? Math.round((curHp / maxHp) * 100) : 100
                            return (
                              <div key={hero.id} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                                <HeroPortrait
                                  classSlug={hero.class?.slug ?? ''}
                                  imagePath={hero.image_path}
                                  name={hero.name}
                                  size={52}
                                  hpPercent={hpPct}
                                />
                                <div style={{ width: 52 }}>
                                  <HpBar current={curHp} max={maxHp} color={roomCfg.color} />
                                </div>
                                <div style={{ fontSize: 10, color: '#9ca3af', textAlign: 'center', maxWidth: 52, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                  {hero.name}
                                </div>
                              </div>
                            )
                          })}
                        </div>
                      </div>
                    )}
                  </div>
                )}

                {/* Room description */}
                {room.description && (
                  <p style={{ color: '#9ca3af', fontSize: 13, fontStyle: 'italic', lineHeight: 1.7, marginBottom: 18, paddingLeft: 2 }}>
                    {room.description}
                  </p>
                )}

                {/* Actions */}
                <div style={{ display: 'flex', gap: 10 }}>
                  <GameButton
                    variant={room.type === 'boss' ? 'danger' : 'primary'}
                    icon={roomCfg.icon}
                    onClick={advance}
                    loading={advancing}
                    size="lg"
                  >
                    {room.type === 'combat' ? 'Combattre !'
                      : room.type === 'boss' ? '⚔️ Affronter le Boss !'
                      : room.type === 'treasure' ? 'Ramasser le butin'
                      : room.type === 'rest' ? 'Se reposer'
                      : 'Avancer'}
                  </GameButton>
                  <GameButton variant="ghost" onClick={abandon}>
                    🏃 Fuir lâchement
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
