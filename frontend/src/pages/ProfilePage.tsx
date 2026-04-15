import { useEffect, useState } from 'react'
import { profileApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import { StatBar } from '../components/ui/StatBar'

const NARRATOR_OPTIONS = [
  { value: 'never',    label: 'Jamais — Le Narrateur est muselé' },
  { value: 'rare',     label: 'Rare — Il intervient de temps en temps' },
  { value: 'normal',   label: 'Normal — Il commente régulièrement' },
  { value: 'annoying', label: 'Omniprésent — Il ne s\'arrête jamais' },
]

const selectStyle = {
  width: '100%', background: '#0d1117', border: '1px solid #2d3748',
  borderRadius: 6, padding: '9px 12px', color: '#f9fafb', fontSize: 13,
}

type EconomyEntry = {
  transaction_type: string
  source: string
  amount: number
  balance_after: number
  description: string | null
  created_at: string
}

type Stats = {
  total_kills: number
  total_defeats: number
  quests_done: number
  items_crafted: number
  dungeons_done: number
  gold_earned: number
  gold_spent: number
}

type ProfileData = {
  user: {
    id: number
    username: string
    email: string
    level: number
    xp: number
    xp_to_next_level: number
    gold: number
    narrator_frequency: string
    created_at: string
  }
  heroes: { id: number; name: string; level: number; race: string; class: string }[]
  stats: Stats
  economy_log: EconomyEntry[]
  ai_budget: { used: number; limit: number; percent: number }
}

export function ProfilePage() {
  const { updateUser } = useAuthStore()
  const [profile, setProfile]     = useState<ProfileData | null>(null)
  const [loading, setLoading]     = useState(true)
  const [saving, setSaving]       = useState(false)
  const [frequency, setFrequency] = useState<string>('normal')
  const [message, setMessage]     = useState<string | null>(null)
  const [activeTab, setActiveTab] = useState<'stats' | 'economy'>('stats')

  useEffect(() => {
    profileApi.get()
      .then(({ data }) => {
        setProfile(data)
        setFrequency(data.user.narrator_frequency)
      })
      .finally(() => setLoading(false))
  }, [])

  const handleSave = async () => {
    setSaving(true)
    setMessage(null)
    try {
      const { data } = await profileApi.update({ narrator_frequency: frequency })
      setMessage(data.message)
      updateUser(data.user)
    } catch {
      setMessage('Erreur lors de la sauvegarde.')
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement du profil…</div>
      </div>
    )
  }
  if (!profile) {
    return (
      <GamePanel variant="danger">
        <p style={{ color: '#fca5a5', margin: 0 }}>Erreur lors du chargement du profil.</p>
      </GamePanel>
    )
  }

  const { user, heroes, stats, economy_log, ai_budget } = profile

  return (
    <div style={{ maxWidth: 860, margin: '0 auto' }}>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>👤 Profil</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Votre légende en chiffres et préférences.
        </p>
      </div>

      {/* Header panel */}
      <div className="game-panel" style={{ padding: 24, marginBottom: 20, display: 'flex', alignItems: 'flex-start', gap: 20 }}>
        <div style={{
          background: 'linear-gradient(135deg, #4c1d95, #7c3aed)',
          borderRadius: '50%', width: 64, height: 64,
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 26, fontWeight: 700, color: 'white',
          fontFamily: 'var(--font-title)', flexShrink: 0,
          boxShadow: '0 0 20px rgba(124,58,237,0.4)',
        }}>
          {user.username.charAt(0).toUpperCase()}
        </div>
        <div style={{ flex: 1 }}>
          <h2 className="game-title" style={{ margin: '0 0 2px', fontSize: 22 }}>{user.username}</h2>
          <p style={{ color: '#6b7280', fontSize: 12, margin: '0 0 10px' }}>{user.email}</p>
          <div style={{ display: 'flex', gap: 16, marginBottom: 10 }}>
            <span style={{ color: '#f59e0b', fontFamily: 'var(--font-title)', fontSize: 14 }}>Niveau {user.level}</span>
            <span style={{ color: '#9ca3af', fontSize: 13 }}>{user.xp.toLocaleString('fr-FR')} / {user.xp_to_next_level.toLocaleString('fr-FR')} XP</span>
            <span style={{ color: '#fbbf24', fontSize: 13 }}>💰 {user.gold.toLocaleString('fr-FR')} or</span>
          </div>
          <StatBar value={user.xp} max={user.xp_to_next_level} variant="xp" height={6} />
          <p style={{ color: '#4b5563', fontSize: 11, margin: '6px 0 0' }}>
            Aventurier depuis le {new Date(user.created_at).toLocaleDateString('fr-FR')}
          </p>
        </div>
      </div>

      {/* Heroes */}
      {heroes.length > 0 && (
        <GamePanel icon="⚔️" title="Mon équipe" variant="default" style={{ marginBottom: 20 }}>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
            {heroes.map(h => (
              <div key={h.id} style={{ background: '#0d1117', border: '1px solid #2d3748', borderRadius: 6, padding: '6px 12px' }}>
                <span style={{ color: '#f9fafb', fontWeight: 600, fontSize: 13 }}>{h.name}</span>
                <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 6 }}>Niv.{h.level}</span>
                <span style={{ color: '#4b5563', fontSize: 11, marginLeft: 6 }}>{h.race} {h.class}</span>
              </div>
            ))}
          </div>
        </GamePanel>
      )}

      {/* Narrator preference */}
      <GamePanel icon="📖" title="Fréquence du Narrateur" variant="magic" style={{ marginBottom: 20 }}>
        <p style={{ color: '#6b7280', fontSize: 12, margin: '0 0 12px' }}>
          Le Narrateur peut commenter vos aventures. Ou pas, si vous êtes sensible aux critiques.
        </p>
        <select value={frequency} onChange={e => setFrequency(e.target.value)} style={selectStyle}>
          {NARRATOR_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value}>{opt.label}</option>
          ))}
        </select>
        <div style={{ marginTop: 12, display: 'flex', alignItems: 'center', gap: 12 }}>
          <GameButton variant="primary" size="sm" onClick={handleSave} loading={saving}>
            Sauvegarder
          </GameButton>
          {message && (
            <span style={{ color: '#4ade80', fontSize: 13, fontStyle: 'italic' }}>{message}</span>
          )}
        </div>
      </GamePanel>

      {/* Tabs */}
      <div className="game-panel" style={{ overflow: 'hidden' }}>
        <div className="game-tabs" style={{ borderRadius: 0, borderBottom: '1px solid #1f2937', marginBottom: 0 }}>
          {(['stats', 'economy'] as const).map(tab => (
            <button
              key={tab}
              className={`game-tab ${activeTab === tab ? 'active' : ''}`}
              onClick={() => setActiveTab(tab)}
            >
              {tab === 'stats' ? '📊 Statistiques' : '💰 Historique économique'}
            </button>
          ))}
        </div>

        {activeTab === 'stats' && (
          <div style={{ padding: 20 }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))', gap: 12, marginBottom: ai_budget.limit > 0 ? 16 : 0 }}>
              <StatCard label="Monstres vaincus"  value={stats.total_kills.toLocaleString('fr-FR')}    color="#4ade80" />
              <StatCard label="Défaites"           value={stats.total_defeats.toLocaleString('fr-FR')}  color="#f87171" />
              <StatCard label="Quêtes terminées"   value={stats.quests_done.toLocaleString('fr-FR')}    color="#60a5fa" />
              <StatCard label="Objets craftés"     value={stats.items_crafted.toLocaleString('fr-FR')}  color="#c084fc" />
              <StatCard label="Donjons complétés"  value={stats.dungeons_done.toLocaleString('fr-FR')}  color="#fb923c" />
              <StatCard label="Or total gagné"     value={`${stats.gold_earned.toLocaleString('fr-FR')} or`}  color="#fbbf24" />
              <StatCard label="Or total dépensé"   value={`${stats.gold_spent.toLocaleString('fr-FR')} or`}   color="#d97706" />
            </div>
            {ai_budget.limit > 0 && (
              <div style={{ background: '#0d1117', borderRadius: 6, padding: '12px 14px', border: '1px solid #2d3748' }}>
                <div style={{ color: '#9ca3af', fontSize: 11, marginBottom: 6, fontFamily: 'var(--font-title)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                  Budget IA journalier
                </div>
                <StatBar value={ai_budget.used} max={ai_budget.limit} variant="custom" color="#7c3aed" height={6} />
                <p style={{ color: '#4b5563', fontSize: 11, margin: '4px 0 0' }}>
                  {ai_budget.used} / {ai_budget.limit} unités utilisées aujourd'hui
                </p>
              </div>
            )}
          </div>
        )}

        {activeTab === 'economy' && (
          <div style={{ overflowY: 'auto', maxHeight: 320 }}>
            {economy_log.length === 0 ? (
              <p style={{ padding: 20, color: '#6b7280', fontStyle: 'italic', fontSize: 13 }}>
                Aucune transaction enregistrée.
              </p>
            ) : (
              <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                <thead>
                  <tr style={{ background: '#0d1117' }}>
                    <th style={{ padding: '8px 16px', textAlign: 'left', color: '#6b7280', fontFamily: 'var(--font-title)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Type</th>
                    <th style={{ padding: '8px 16px', textAlign: 'right', color: '#6b7280', fontFamily: 'var(--font-title)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Montant</th>
                    <th style={{ padding: '8px 16px', textAlign: 'right', color: '#6b7280', fontFamily: 'var(--font-title)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Solde après</th>
                    <th style={{ padding: '8px 16px', textAlign: 'left', color: '#6b7280', fontFamily: 'var(--font-title)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Date</th>
                  </tr>
                </thead>
                <tbody>
                  {economy_log.map((entry, i) => (
                    <tr key={i} style={{ borderTop: '1px solid #1f2937' }}>
                      <td style={{ padding: '8px 16px' }}>
                        <span style={{ color: entry.transaction_type === 'gain' ? '#4ade80' : '#f87171', fontWeight: 700 }}>
                          {entry.transaction_type === 'gain' ? '+' : '-'}{entry.amount.toLocaleString('fr-FR')} or
                        </span>
                        <span style={{ color: '#4b5563', fontSize: 11, marginLeft: 6 }}>{entry.source}</span>
                      </td>
                      <td style={{ padding: '8px 16px', textAlign: 'right', color: '#fbbf24' }}>
                        {entry.amount.toLocaleString('fr-FR')} or
                      </td>
                      <td style={{ padding: '8px 16px', textAlign: 'right', color: '#9ca3af' }}>
                        {entry.balance_after.toLocaleString('fr-FR')} or
                      </td>
                      <td style={{ padding: '8px 16px', color: '#4b5563' }}>
                        {new Date(entry.created_at).toLocaleDateString('fr-FR')}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </div>
    </div>
  )
}

function StatCard({ label, value, color }: { label: string; value: string; color: string }) {
  return (
    <div style={{ background: '#0d1117', border: '1px solid #1f2937', borderRadius: 6, padding: '12px', textAlign: 'center' }}>
      <p style={{ color, fontSize: 20, fontWeight: 700, margin: '0 0 4px', fontFamily: 'var(--font-title)' }}>{value}</p>
      <p style={{ color: '#6b7280', fontSize: 11, margin: 0 }}>{label}</p>
    </div>
  )
}
