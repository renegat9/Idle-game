import { useEffect, useState } from 'react'
import { heroApi, talentApi } from '../api/game'

type Hero = {
  id: number
  name: string
  level: number
  talent_points_available: number
  talent_reset_count: number
}

type Talent = {
  id: number
  name: string
  description: string
  branch: 'offensive' | 'defensive' | 'defaut'
  tier: number
  position: number
  cost: number
  required_points_in_branch: number
  talent_type: 'passif' | 'actif' | 'reactif'
  is_unlocked: boolean
  can_unlock: boolean
  effect_data: Record<string, any>
}

type TalentTree = {
  hero: Hero
  points_available: number
  reset_cost: number
  branches: {
    offensive: Talent[]
    defensive: Talent[]
    defaut: Talent[]
  }
}

const BRANCH_COLORS = {
  offensive: '#ef4444',
  defensive: '#3b82f6',
  defaut:    '#f59e0b',
}

const BRANCH_LABELS = {
  offensive: '⚔️ Offensive',
  defensive: '🛡️ Défensive',
  defaut:    '🤪 Branche du Défaut',
}

const TYPE_BADGES = {
  passif:   { label: 'Passif',   color: '#94a3b8' },
  actif:    { label: 'Actif',    color: '#22c55e' },
  reactif:  { label: 'Réactif', color: '#f59e0b' },
}

export function TalentsPage() {
  const [heroes, setHeroes] = useState<Hero[]>([])
  const [selectedHeroId, setSelectedHeroId] = useState<number | null>(null)
  const [tree, setTree] = useState<TalentTree | null>(null)
  const [loading, setLoading] = useState(true)
  const [acting, setActing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)

  useEffect(() => {
    loadHeroes()
  }, [])

  useEffect(() => {
    if (selectedHeroId) loadTree(selectedHeroId)
  }, [selectedHeroId])

  async function loadHeroes() {
    try {
      const { data } = await heroApi.list()
      const h = data.heroes ?? []
      setHeroes(h)
      if (h.length > 0) setSelectedHeroId(h[0].id)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function loadTree(heroId: number) {
    try {
      const { data } = await talentApi.tree(heroId)
      setTree(data)
    } catch { /* ok */ }
  }

  async function allocate(talentId: number) {
    if (!selectedHeroId || acting) return
    setActing(true)
    setMessage(null)
    try {
      const { data } = await talentApi.allocate(selectedHeroId, talentId)
      setMessage({ text: data.message ?? 'Talent débloqué !', ok: true })
      await loadTree(selectedHeroId)
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur.', ok: false })
    }
    setActing(false)
  }

  async function reset() {
    if (!selectedHeroId || acting) return
    if (!confirm('Réinitialiser tous les talents ? Cela coûte de l\'or.')) return
    setActing(true)
    setMessage(null)
    try {
      const { data } = await talentApi.reset(selectedHeroId)
      setMessage({ text: data.message ?? 'Talents réinitialisés.', ok: true })
      await loadTree(selectedHeroId)
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur lors de la réinitialisation.', ok: false })
    }
    setActing(false)
  }

  function renderBranch(branchKey: keyof typeof BRANCH_LABELS, talents: Talent[]) {
    const tierGroups: Record<number, Talent[]> = {}
    for (const t of talents) {
      tierGroups[t.tier] = tierGroups[t.tier] ?? []
      tierGroups[t.tier].push(t)
    }

    return (
      <div style={{ flex: 1, minWidth: 240 }}>
        <h3 style={{ color: BRANCH_COLORS[branchKey], marginBottom: 12, fontSize: 15 }}>
          {BRANCH_LABELS[branchKey]}
        </h3>
        {Object.entries(tierGroups).sort(([a], [b]) => Number(a) - Number(b)).map(([tier, group]) => (
          <div key={tier} style={{ marginBottom: 12 }}>
            <div style={{ color: '#475569', fontSize: 11, marginBottom: 6 }}>Rang {tier}</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
              {group.sort((a, b) => a.position - b.position).map(talent => (
                <div
                  key={talent.id}
                  style={{
                    background: talent.is_unlocked ? '#0f2a0f' : talent.can_unlock ? '#1e293b' : '#0f172a',
                    border: `1px solid ${talent.is_unlocked ? '#16a34a' : talent.can_unlock ? BRANCH_COLORS[branchKey] : '#1e293b'}`,
                    borderRadius: 8,
                    padding: 12,
                    opacity: talent.can_unlock || talent.is_unlocked ? 1 : 0.5,
                    cursor: talent.can_unlock && !acting ? 'pointer' : 'default',
                    transition: 'border-color 0.2s',
                  }}
                  onClick={() => talent.can_unlock && !talent.is_unlocked && allocate(talent.id)}
                >
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 }}>
                    <span style={{ color: talent.is_unlocked ? '#22c55e' : '#e2e8f0', fontWeight: 'bold', fontSize: 13 }}>
                      {talent.is_unlocked ? '✓ ' : ''}{talent.name}
                    </span>
                    <div style={{ display: 'flex', gap: 4, alignItems: 'center' }}>
                      <span style={{ color: TYPE_BADGES[talent.talent_type].color, fontSize: 10, background: '#0f172a', padding: '1px 5px', borderRadius: 3 }}>
                        {TYPE_BADGES[talent.talent_type].label}
                      </span>
                      {!talent.is_unlocked && (
                        <span style={{ color: '#f59e0b', fontSize: 11 }}>{talent.cost} pt</span>
                      )}
                    </div>
                  </div>
                  <p style={{ color: '#6b7280', margin: 0, fontSize: 11, lineHeight: 1.4 }}>{talent.description}</p>
                  {talent.required_points_in_branch > 0 && !talent.is_unlocked && (
                    <p style={{ color: '#475569', margin: '4px 0 0', fontSize: 10 }}>
                      Requis : {talent.required_points_in_branch} pts dans cette branche
                    </p>
                  )}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    )
  }

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement des talents...</div>

  if (heroes.length === 0) {
    return (
      <div>
        <h1 style={{ color: '#f1f5f9', fontSize: 24 }}>🌟 Arbres de talents</h1>
        <p style={{ color: '#6b7280' }}>Recrutez des héros dans la taverne pour débloquer les arbres de talents.</p>
      </div>
    )
  }

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>🌟 Arbres de talents</h1>
      <p style={{ color: '#6b7280', marginBottom: 16, fontSize: 14 }}>1 point tous les 5 niveaux, 20 points max. Les talents de Branche du Défaut... sont inévitables.</p>

      {/* Hero selector */}
      <div style={{ display: 'flex', gap: 8, marginBottom: 20, flexWrap: 'wrap' }}>
        {heroes.map(h => (
          <button
            key={h.id}
            onClick={() => setSelectedHeroId(h.id)}
            style={{
              background: selectedHeroId === h.id ? '#7c3aed' : '#1e293b',
              color: selectedHeroId === h.id ? 'white' : '#94a3b8',
              border: `1px solid ${selectedHeroId === h.id ? '#7c3aed' : '#334155'}`,
              padding: '6px 14px',
              borderRadius: 8,
              cursor: 'pointer',
              fontSize: 13,
            }}
          >
            {h.name} <span style={{ opacity: 0.7 }}>Niv. {h.level}</span>
          </button>
        ))}
      </div>

      {message && (
        <div style={{ background: message.ok ? '#052e16' : '#1c0505', border: `1px solid ${message.ok ? '#16a34a' : '#991b1b'}`, borderRadius: 8, padding: 12, marginBottom: 16 }}>
          <span style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>{message.text}</span>
        </div>
      )}

      {tree && (
        <>
          {/* Points header */}
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#1e293b', border: '1px solid #334155', borderRadius: 10, padding: '12px 16px', marginBottom: 20 }}>
            <span style={{ color: '#f59e0b', fontWeight: 'bold' }}>
              {tree.points_available} point{tree.points_available !== 1 ? 's' : ''} disponible{tree.points_available !== 1 ? 's' : ''}
            </span>
            <button
              onClick={reset}
              disabled={acting}
              style={{ background: 'transparent', color: '#ef4444', border: '1px solid #7f1d1d', padding: '4px 12px', borderRadius: 6, cursor: acting ? 'not-allowed' : 'pointer', fontSize: 12 }}
            >
              Réinitialiser ({tree.reset_cost} 💰)
            </button>
          </div>

          {/* Three branches */}
          <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap', alignItems: 'flex-start' }}>
            {renderBranch('offensive', tree.branches.offensive)}
            {renderBranch('defensive', tree.branches.defensive)}
            {renderBranch('defaut', tree.branches.defaut)}
          </div>
        </>
      )}
    </div>
  )
}
