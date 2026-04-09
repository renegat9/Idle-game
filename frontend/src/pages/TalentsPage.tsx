import { useEffect, useState } from 'react'
import { heroApi, talentApi } from '../api/game'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'

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

const BRANCH_BG = {
  offensive: '#1a0505',
  defensive: '#050a1a',
  defaut:    '#1a0f00',
}

const TYPE_BADGES: Record<string, { label: string; color: string }> = {
  passif:  { label: 'Passif',   color: '#9ca3af' },
  actif:   { label: 'Actif',    color: '#22c55e' },
  reactif: { label: 'Réactif',  color: '#f59e0b' },
}

export function TalentsPage() {
  const [heroes, setHeroes] = useState<Hero[]>([])
  const [selectedHeroId, setSelectedHeroId] = useState<number | null>(null)
  const [tree, setTree] = useState<TalentTree | null>(null)
  const [loading, setLoading] = useState(true)
  const [acting, setActing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)

  useEffect(() => { loadHeroes() }, [])
  useEffect(() => { if (selectedHeroId) loadTree(selectedHeroId) }, [selectedHeroId])

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
      <div style={{ flex: 1, minWidth: 220 }}>
        <div style={{
          background: BRANCH_BG[branchKey], border: `1px solid ${BRANCH_COLORS[branchKey]}44`,
          borderRadius: '6px 6px 0 0', padding: '10px 14px', marginBottom: 0,
        }}>
          <h3 className="game-title" style={{ color: BRANCH_COLORS[branchKey], margin: 0, fontSize: 14 }}>
            {BRANCH_LABELS[branchKey]}
          </h3>
        </div>
        <div style={{ border: `1px solid ${BRANCH_COLORS[branchKey]}33`, borderTop: 'none', borderRadius: '0 0 6px 6px', padding: 12 }}>
          {Object.entries(tierGroups).sort(([a], [b]) => Number(a) - Number(b)).map(([tier, group]) => (
            <div key={tier} style={{ marginBottom: 12 }}>
              <div style={{ color: '#4b5563', fontSize: 10, fontFamily: 'var(--font-title)', textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: 6 }}>
                Rang {tier}
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                {group.sort((a, b) => a.position - b.position).map(talent => (
                  <div
                    key={talent.id}
                    onClick={() => talent.can_unlock && !talent.is_unlocked && allocate(talent.id)}
                    style={{
                      background: talent.is_unlocked ? '#051a05' : talent.can_unlock ? '#0d1117' : '#080d0d',
                      border: `1px solid ${talent.is_unlocked ? '#16a34a' : talent.can_unlock ? BRANCH_COLORS[branchKey] + '88' : '#1f2937'}`,
                      borderRadius: 6, padding: '10px 12px',
                      opacity: talent.can_unlock || talent.is_unlocked ? 1 : 0.45,
                      cursor: talent.can_unlock && !talent.is_unlocked && !acting ? 'pointer' : 'default',
                      transition: 'border-color 0.2s, background 0.2s',
                    }}
                  >
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 }}>
                      <span style={{ color: talent.is_unlocked ? '#4ade80' : '#e2e8f0', fontWeight: 700, fontSize: 13, fontFamily: 'var(--font-title)' }}>
                        {talent.is_unlocked ? '✓ ' : ''}{talent.name}
                      </span>
                      <div style={{ display: 'flex', gap: 4, alignItems: 'center', flexShrink: 0, marginLeft: 6 }}>
                        <span style={{
                          color: TYPE_BADGES[talent.talent_type]?.color ?? '#9ca3af',
                          fontSize: 10, background: '#0d1117', padding: '1px 5px', borderRadius: 3,
                        }}>
                          {TYPE_BADGES[talent.talent_type]?.label}
                        </span>
                        {!talent.is_unlocked && (
                          <span style={{ color: '#f59e0b', fontSize: 11, fontWeight: 700 }}>{talent.cost}pt</span>
                        )}
                      </div>
                    </div>
                    <p style={{ color: '#6b7280', margin: 0, fontSize: 11, lineHeight: 1.5 }}>{talent.description}</p>
                    {talent.required_points_in_branch > 0 && !talent.is_unlocked && (
                      <p style={{ color: '#374151', margin: '4px 0 0', fontSize: 10 }}>
                        Requis : {talent.required_points_in_branch} pts dans cette branche
                      </p>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    )
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement des talents…</div>
      </div>
    )
  }

  if (heroes.length === 0) {
    return (
      <div>
        <h1 className="game-title" style={{ fontSize: 26, marginBottom: 12 }}>🌟 Arbres de talents</h1>
        <GamePanel variant="default" style={{ textAlign: 'center', padding: '60px 20px' }}>
          <div style={{ fontSize: 48, marginBottom: 12 }}>🌿</div>
          <p style={{ color: '#6b7280', fontStyle: 'italic', margin: 0 }}>
            Recrutez des héros dans la taverne pour débloquer les arbres de talents.
          </p>
        </GamePanel>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🌟 Arbres de talents</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          1 point tous les 5 niveaux, 20 points max. La Branche du Défaut… est inévitable.
        </p>
      </div>

      {/* Hero selector */}
      <div className="game-tabs" style={{ marginBottom: 20 }}>
        {heroes.map(h => (
          <button
            key={h.id}
            className={`game-tab ${selectedHeroId === h.id ? 'active' : ''}`}
            onClick={() => setSelectedHeroId(h.id)}
          >
            {h.name}
            <span style={{ opacity: 0.6, marginLeft: 6, fontSize: 11 }}>Niv. {h.level}</span>
            {h.talent_points_available > 0 && (
              <span style={{
                background: '#f59e0b', color: '#000', borderRadius: '50%',
                width: 16, height: 16, fontSize: 10, fontWeight: 700,
                display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginLeft: 6,
              }}>
                {h.talent_points_available}
              </span>
            )}
          </button>
        ))}
      </div>

      {message && (
        <div
          className="narrator-bubble anim-slide-in"
          style={{ marginBottom: 16, borderLeftColor: message.ok ? '#22c55e' : '#ef4444', background: message.ok ? '#020f08' : '#0a0202' }}
        >
          <p className="narrator-text" style={{ margin: 0, color: message.ok ? '#86efac' : '#fca5a5' }}>
            « {message.text} »
          </p>
        </div>
      )}

      {tree && (
        <>
          {/* Points header */}
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#0d1117', border: '1px solid #2d3748', borderRadius: 8, padding: '12px 16px', marginBottom: 20 }}>
            <div>
              <span style={{ color: '#f59e0b', fontWeight: 700, fontSize: 16, fontFamily: 'var(--font-title)' }}>
                {tree.points_available}
              </span>
              <span style={{ color: '#9ca3af', fontSize: 13, marginLeft: 6 }}>
                point{tree.points_available !== 1 ? 's' : ''} disponible{tree.points_available !== 1 ? 's' : ''}
              </span>
            </div>
            <GameButton
              variant="ghost"
              size="sm"
              onClick={reset}
              disabled={acting}
            >
              🔄 Réinitialiser ({tree.reset_cost.toLocaleString('fr-FR')} 💰)
            </GameButton>
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
