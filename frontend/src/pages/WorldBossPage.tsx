import { useEffect, useState } from 'react'
import { worldBossApi } from '../api/game'

type WorldBoss = {
  id: number
  name: string
  slug: string
  total_hp: number
  current_hp: number
  status: 'inactive' | 'active' | 'defeated'
  special_mechanic: string | null
  description: string | null
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

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement du boss mondial...</div>

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>💀 Boss Mondial</h1>
      <p style={{ color: '#6b7280', marginBottom: 16, fontSize: 14 }}>
        Un événement serveur — tous les joueurs attaquent le même boss.
      </p>

      {message && (
        <div style={{ background: message.ok ? '#052e16' : '#1c0505', border: `1px solid ${message.ok ? '#16a34a' : '#991b1b'}`, borderRadius: 8, padding: 12, marginBottom: 16 }}>
          <span style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>{message.text}</span>
        </div>
      )}

      {/* Boss panel */}
      {!status?.active_boss || status.active_boss!.status === 'inactive' ? (
        <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 40, textAlign: 'center', marginBottom: 24 }}>
          <p style={{ color: '#6b7280', margin: 0 }}>Aucun boss actif pour l'instant. Les rumeurs parlent d'une prochaine apparition...</p>
        </div>
      ) : (
        <div style={{ background: '#1e293b', border: `1px solid ${status.active_boss!.status === 'defeated' ? '#334155' : '#991b1b'}`, borderRadius: 12, padding: 24, marginBottom: 24 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 }}>
            <div>
              <h2 style={{ color: '#f1f5f9', margin: '0 0 4px', fontSize: 22 }}>{status.active_boss!.name}</h2>
              {status.active_boss!.description && (
                <p style={{ color: '#94a3b8', fontSize: 13, margin: '6px 0 4px', fontStyle: 'italic' }}>
                  {status.active_boss!.description}
                </p>
              )}
              {status.active_boss!.special_mechanic && (
                <span style={{ color: '#f59e0b', fontSize: 12, background: '#1c1005', padding: '2px 8px', borderRadius: 4 }}>
                  Mécanisme : {status.active_boss!.special_mechanic}
                </span>
              )}
            </div>
            <span style={{
              color: status.active_boss!.status === 'defeated' ? '#6b7280' : '#ef4444',
              background: '#0f172a',
              padding: '4px 12px',
              borderRadius: 6,
              fontSize: 13,
              fontWeight: 'bold',
            }}>
              {status.active_boss!.status === 'defeated' ? '💀 Vaincu' : '⚔️ Actif'}
            </span>
          </div>

          {/* HP bar */}
          {status.active_boss!.status !== 'defeated' && (
            <div style={{ marginBottom: 20 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                <span style={{ color: '#94a3b8', fontSize: 12 }}>Points de vie</span>
                <span style={{ color: '#e2e8f0', fontSize: 12 }}>
                  {status.active_boss!.current_hp.toLocaleString('fr-FR')} / {status.active_boss!.total_hp.toLocaleString('fr-FR')} ({hpPercent(status.active_boss!)}%)
                </span>
              </div>
              <div style={{ background: '#0f172a', borderRadius: 6, height: 14, overflow: 'hidden' }}>
                <div style={{
                  width: `${hpPercent(status.active_boss!)}%`,
                  height: '100%',
                  background: hpColor(hpPercent(status.active_boss!)),
                  transition: 'width 0.5s ease',
                  borderRadius: 6,
                }} />
              </div>
            </div>
          )}

          {/* My contribution */}
          {status.my_contribution && (
            <div style={{ background: '#0f172a', borderRadius: 8, padding: 12, marginBottom: 16 }}>
              <span style={{ color: '#94a3b8', fontSize: 12 }}>Votre contribution : </span>
              <span style={{ color: '#f59e0b', fontWeight: 'bold' }}>
                {status.my_contribution.damage_dealt.toLocaleString('fr-FR')} dégâts
              </span>
              <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 8 }}>
                ({status.my_contribution.hits_count} attaques)
              </span>
            </div>
          )}

          {/* Attack button */}
          {status.active_boss!.status === 'active' && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <button
                onClick={attack}
                disabled={attacking || (status.cooldown_seconds > 0)}
                style={{
                  background: attacking || status.cooldown_seconds > 0 ? '#374151' : '#dc2626',
                  color: 'white',
                  border: 'none',
                  padding: '10px 24px',
                  borderRadius: 8,
                  cursor: attacking || status.cooldown_seconds > 0 ? 'not-allowed' : 'pointer',
                  fontSize: 15,
                  fontWeight: 'bold',
                  opacity: attacking || status.cooldown_seconds > 0 ? 0.6 : 1,
                }}
              >
                {attacking ? '⚔️ Attaque...' : '⚔️ Attaquer'}
              </button>
              {status.cooldown_seconds > 0 && (
                <span style={{ color: '#6b7280', fontSize: 13 }}>
                  Prochain attaque dans {Math.ceil(status.cooldown_seconds / 60)} min
                </span>
              )}
            </div>
          )}

          {lastResult && (
            <div style={{ marginTop: 12, color: '#94a3b8', fontSize: 13, fontStyle: 'italic' }}>
              {lastResult.narration}
            </div>
          )}
        </div>
      )}

      {/* Leaderboard */}
      <div>
        <h2 style={{ color: '#e2e8f0', fontSize: 18, marginBottom: 12 }}>🏆 Classement des contributeurs</h2>
        {leaderboard.length === 0 ? (
          <p style={{ color: '#6b7280' }}>Aucune contribution enregistrée pour le moment.</p>
        ) : (
          <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, overflow: 'hidden' }}>
            {leaderboard.map((entry, i) => (
              <div
                key={i}
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: '12px 16px',
                  borderBottom: i < leaderboard.length - 1 ? '1px solid #1e293b' : 'none',
                  background: i === 0 ? '#1a1005' : i === 1 ? '#131a25' : 'transparent',
                }}
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                  <span style={{ color: i === 0 ? '#f59e0b' : i === 1 ? '#94a3b8' : '#6b7280', fontWeight: 'bold', width: 24, fontSize: 16 }}>
                    {i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}`}
                  </span>
                  <span style={{ color: '#e2e8f0', fontSize: 14 }}>{entry.username}</span>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <span style={{ color: '#ef4444', fontWeight: 'bold' }}>
                    {entry.damage_dealt.toLocaleString('fr-FR')} dmg
                  </span>
                  <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 8 }}>
                    ({entry.hits_count} coups)
                  </span>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
