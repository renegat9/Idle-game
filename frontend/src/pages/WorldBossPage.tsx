import { useEffect, useState } from 'react'
import { worldBossApi } from '../api/game'
import { Tooltip } from '../components/ui/Tooltip'

const MECHANIC_DESCRIPTIONS: Record<string, string> = {
  enrage:       'En dessous de 30% de HP, le boss entre en rage : ses dégâts sont doublés et il attaque plus fréquemment.',
  shield_phase: 'Périodiquement, le boss génère un bouclier magique qui absorbe tous les dégâts jusqu\'à ce qu\'il soit brisé par des attaques concentrées.',
}

type WorldBoss = {
  id: number
  name: string
  slug: string
  total_hp: number
  current_hp: number
  status: 'inactive' | 'active' | 'defeated'
  special_mechanic: string | null
  description: string | null
  image_path: string | null
  spawned_at: string | null
  defeated_at: string | null
}

type BossStatus = {
  active_boss: WorldBoss | null
  my_contribution: { damage_dealt: number; hits_count: number; reward_claimed: boolean } | null
  cooldown_seconds: number
  can_attack: boolean
}

export function WorldBossPage() {
  const [status, setStatus] = useState<BossStatus | null>(null)
  const [leaderboard, setLeaderboard] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [attacking, setAttacking] = useState(false)
  const [lastResult, setLastResult] = useState<any | null>(null)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)

  useEffect(() => {
    loadStatus()
    loadLeaderboard()
  }, [])

  async function loadStatus() {
    try {
      const { data } = await worldBossApi.status()
      setStatus(data)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function loadLeaderboard() {
    try {
      const { data } = await worldBossApi.leaderboard()
      setLeaderboard(data.leaderboard ?? [])
    } catch { /* ok */ }
  }

  async function attack() {
    if (attacking) return
    setAttacking(true)
    setMessage(null)
    try {
      const { data } = await worldBossApi.attack()
      setLastResult(data)
      if (data.boss_defeated) {
        setMessage({ text: 'Le boss est vaincu ! Gloire à vous !', ok: true })
      } else {
        setMessage({ text: `Vous infligez ${data.damage_dealt} dégâts !`, ok: true })
      }
      await loadStatus()
      await loadLeaderboard()
    } catch (e: any) {
      const msg = e.response?.data?.message ?? 'Erreur lors de l\'attaque.'
      const remaining = e.response?.data?.seconds_remaining
      setMessage({
        text: remaining
          ? `${msg} (${Math.ceil(remaining / 60)} min restantes)`
          : msg,
        ok: false,
      })
    }
    setAttacking(false)
  }

  function hpPercent(boss: WorldBoss): number {
    if (!boss.total_hp) return 0
    return Math.max(0, Math.min(100, Math.round((boss.current_hp / boss.total_hp) * 100)))
  }

  function hpColor(pct: number): string {
    if (pct > 60) return '#22c55e'
    if (pct > 30) return '#f59e0b'
    return '#ef4444'
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Le boss arrive…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🐉 Boss Mondial</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Événement serveur — tous les joueurs attaquent le même boss
        </p>
      </div>

      {message && (
        <div className={`narrator-bubble anim-slide-in`} style={{ marginBottom: 16, borderLeftColor: message.ok ? '#22c55e' : '#ef4444', background: message.ok ? '#020f08' : '#0a0202' }}>
          <p className="narrator-text" style={{ margin: 0, color: message.ok ? '#86efac' : '#fca5a5' }}>« {message.text} »</p>
        </div>
      )}

      {/* Boss panel */}
      {!status?.active_boss || status.active_boss!.status === 'inactive' ? (
        <div className="game-panel" style={{ padding: 40, textAlign: 'center', marginBottom: 24 }}>
          <div style={{ fontSize: 64, marginBottom: 12 }}>💤</div>
          <p style={{ color: '#6b7280', margin: 0, fontStyle: 'italic' }}>Aucun boss actif. Les rumeurs parlent d'une prochaine apparition…</p>
        </div>
      ) : (
        <div
          className={`game-panel ${status.active_boss!.status === 'defeated' ? '' : 'game-panel-danger'}`}
          style={{ marginBottom: 24, overflow: 'hidden' }}
        >
          {/* Boss header with portrait */}
          <div style={{ padding: '20px 20px 16px', background: 'linear-gradient(135deg, #1a0505, #0a0202)', borderBottom: '1px solid #7f1d1d', display: 'flex', gap: 16, alignItems: 'flex-start' }}>
            <div className="anim-breathe" style={{ flexShrink: 0, width: 96, height: 96, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              {status.active_boss!.image_path ? (
                <img
                  src={`/${status.active_boss!.image_path}`}
                  alt={status.active_boss!.name}
                  style={{ width: 96, height: 96, objectFit: 'contain', imageRendering: 'auto' }}
                  onError={(e) => { (e.currentTarget as HTMLImageElement).style.display = 'none'; (e.currentTarget.nextElementSibling as HTMLElement).style.display = 'block' }}
                />
              ) : null}
              <span style={{ fontSize: 64, lineHeight: 1, display: status.active_boss!.image_path ? 'none' : 'block' }}>🐉</span>
            </div>
            <div style={{ flex: 1 }}>
              <h2 className="game-title" style={{ margin: '0 0 6px', fontSize: 22, color: '#f9fafb' }}>
                {status.active_boss!.name}
              </h2>
              {status.active_boss!.description && (
                <p className="flavor-text" style={{ margin: '0 0 8px' }}>{status.active_boss!.description}</p>
              )}
              {status.active_boss!.special_mechanic && (() => {
                const mechanic = status.active_boss!.special_mechanic!
                const desc = MECHANIC_DESCRIPTIONS[mechanic]
                const tag = (
                  <span style={{ color: '#f59e0b', fontSize: 12, background: '#1a0d00', padding: '3px 10px', borderRadius: 4, border: '1px solid #b45309', cursor: desc ? 'help' : 'default' }}>
                    ⚡ {mechanic}
                  </span>
                )
                return desc ? <Tooltip content={desc}>{tag}</Tooltip> : tag
              })()}
            </div>
            <span style={{
              color: status.active_boss!.status === 'defeated' ? '#6b7280' : '#ef4444',
              background: '#0d1117', padding: '4px 12px', borderRadius: 6, fontSize: 12, fontWeight: 700,
              fontFamily: 'var(--font-title)', flexShrink: 0,
            }}>
              {status.active_boss!.status === 'defeated' ? '💀 Vaincu' : '⚔️ Actif'}
            </span>
          </div>

          <div style={{ padding: 20 }}>
            {/* HP bar */}
            {status.active_boss!.status !== 'defeated' && (
              <div style={{ marginBottom: 20 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
                  <span style={{ color: '#9ca3af', fontSize: 12, fontFamily: 'var(--font-title)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                    ❤️ Points de Vie
                  </span>
                  <span style={{ color: '#f9fafb', fontSize: 12 }}>
                    {status.active_boss!.current_hp.toLocaleString('fr-FR')} / {status.active_boss!.total_hp.toLocaleString('fr-FR')} ({hpPercent(status.active_boss!)}%)
                  </span>
                </div>
                <div className="stat-bar-track" style={{ height: 16 }}>
                  <div
                    className="stat-bar-fill"
                    style={{
                      width: `${hpPercent(status.active_boss!)}%`,
                      background: hpColor(hpPercent(status.active_boss!)),
                      height: '100%', borderRadius: 4, transition: 'width 0.5s ease',
                      boxShadow: `0 0 8px ${hpColor(hpPercent(status.active_boss!))}66`,
                    }}
                  />
                </div>
              </div>
            )}

            {/* Contribution */}
            {status.my_contribution && (
              <div style={{ background: '#0d1117', borderRadius: 8, padding: '10px 14px', marginBottom: 16, border: '1px solid #1f2937', display: 'flex', gap: 20 }}>
                <div><div style={{ color: '#6b7280', fontSize: 11, marginBottom: 2 }}>Vos dégâts</div><div style={{ color: '#ef4444', fontWeight: 700, fontSize: 16 }}>{status.my_contribution.damage_dealt.toLocaleString('fr-FR')}</div></div>
                <div><div style={{ color: '#6b7280', fontSize: 11, marginBottom: 2 }}>Attaques</div><div style={{ color: '#f9fafb', fontWeight: 700, fontSize: 16 }}>{status.my_contribution.hits_count}</div></div>
              </div>
            )}

            {/* Attack */}
            {status.active_boss!.status === 'active' && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <button
                  onClick={attack}
                  disabled={attacking || status.cooldown_seconds > 0}
                  className="game-btn game-btn-danger game-btn-lg"
                >
                  {attacking ? '⚔️ Attaque...' : '⚔️ Attaquer !'}
                </button>
                {status.cooldown_seconds > 0 && (
                  <span style={{ color: '#6b7280', fontSize: 13 }}>
                    ⏱ {Math.ceil(status.cooldown_seconds / 60)} min de recharge
                  </span>
                )}
              </div>
            )}

            {lastResult?.narration && (
              <div className="narrator-bubble" style={{ marginTop: 14 }}>
                <p className="narrator-text" style={{ margin: 0 }}>« {lastResult.narration} »</p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Leaderboard */}
      <div className="game-panel" style={{ overflow: 'hidden' }}>
        <div className="game-panel-header">
          <span className="panel-icon">🏆</span>
          <span className="panel-title">Classement des Contributeurs</span>
        </div>
        {leaderboard.length === 0 ? (
          <div style={{ padding: '32px 16px', textAlign: 'center', color: '#4b5563', fontStyle: 'italic' }}>
            Aucune contribution pour le moment.
          </div>
        ) : (
          <div>
            {leaderboard.map((entry, i) => (
              <div
                key={i}
                style={{
                  display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                  padding: '12px 16px',
                  borderBottom: i < leaderboard.length - 1 ? '1px solid #1f2937' : 'none',
                  background: i === 0 ? '#1a0d0022' : 'transparent',
                }}
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                  <span style={{ fontSize: 18, width: 28 }}>
                    {i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : <span style={{ color: '#4b5563', fontSize: 13 }}>#{i + 1}</span>}
                  </span>
                  <span style={{ color: '#e2e8f0', fontSize: 14 }}>{entry.username}</span>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <span style={{ color: '#ef4444', fontWeight: 700 }}>{entry.damage_dealt.toLocaleString('fr-FR')} dmg</span>
                  <span style={{ color: '#4b5563', fontSize: 12, marginLeft: 8 }}>({entry.hits_count} coups)</span>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
