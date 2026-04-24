import { useEffect, useState } from 'react'
import { questApi } from '../api/game'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { RarityBadge } from '../components/hero/RarityBadge'
import { GameButton } from '../components/ui/GameButton'
import { Tooltip } from '../components/ui/Tooltip'

const EFFECT_TOOLTIPS: Record<string, string> = {
  buff:        'Amélioration temporaire des statistiques d\'un ou plusieurs héros.',
  debuff:      'Réduction temporaire des statistiques d\'un ou plusieurs héros.',
  gold:        'Or gagné ou perdu suite à ce choix.',
  reputation:  'Points de réputation gagnés dans la zone actuelle.',
  loot:        'Objet récupéré en récompense.',
}
import { GamePanel } from '../components/ui/GamePanel'
import { StatBar } from '../components/ui/StatBar'
import type { DailyQuest } from '../types'

type Quest = {
  id: number; title: string; description: string; steps_count: number
  order_index: number; reward_xp: number; reward_gold: number
  reward_loot_rarity: string | null; status: string; current_step: number
  user_quest_id: number | null
}

type Step = {
  step_index: number; narration: string; narrator_comment: string; is_final: boolean
  choices: Array<{ id: string; text: string; test?: { stat: string; has_test: boolean; type: string } | null }>
}

type ActiveQuest = {
  user_quest_id: number; quest_id: number; quest_title: string
  current_step: number; total_steps: number; step: Step | null
}

const STAT_ICON: Record<string, string> = {
  atq: '⚔️', def: '🛡️', vit: '💨', cha: '✨', int: '📖',
}

const STATUS_CONFIG: Record<string, { color: string; label: string; icon: string }> = {
  available:   { color: '#22c55e', label: 'Disponible', icon: '▶' },
  in_progress: { color: '#f59e0b', label: 'En cours', icon: '⟳' },
  completed:   { color: '#6b7280', label: 'Terminée', icon: '✓' },
  failed:      { color: '#ef4444', label: 'Échouée', icon: '✕' },
}

export function QuestPage() {
  const [quests, setQuests] = useState<Quest[]>([])
  const [dailyQuests, setDailyQuests] = useState<DailyQuest[]>([])
  const [dailyRefreshAt, setDailyRefreshAt] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [tab, setTab] = useState<'zone' | 'daily'>('zone')
  const [activeQuest, setActiveQuest] = useState<ActiveQuest | null>(null)
  const [result, setResult] = useState<any>(null)
  const [acting, setActing] = useState(false)

  useEffect(() => { loadAll() }, [])

  async function loadAll() {
    try {
      const [zoneRes, dailyRes] = await Promise.all([questApi.list(), questApi.daily()])
      setQuests(zoneRes.data.quests)
      setDailyQuests(dailyRes.data.quests)
      setDailyRefreshAt(dailyRes.data.refresh_at)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function loadQuests() {
    try {
      const { data } = await questApi.list()
      setQuests(data.quests)
    } catch { /* ok */ }
  }

  async function startQuest(questId: number) {
    setResult(null)
    try {
      const { data } = await questApi.start(questId)
      setActiveQuest(data)
    } catch (e: any) {
      alert(e.response?.data?.message ?? 'Erreur')
    }
  }

  async function choose(choiceId: string) {
    if (!activeQuest || acting) return
    setActing(true)
    try {
      const { data } = await questApi.choose(activeQuest.user_quest_id, choiceId)
      setResult(data)
      if (data.is_final) {
        setActiveQuest(null)
        await loadQuests()
      } else if (data.next_step) {
        setActiveQuest(prev => prev
          ? { ...prev, current_step: data.next_step.step_index, step: data.next_step }
          : null
        )
      }
    } catch (e: any) {
      alert(e.response?.data?.message ?? 'Erreur')
    }
    setActing(false)
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement des quêtes…</div>
      </div>
    )
  }

  const availableDaily = dailyQuests.filter(q => q.status === 'available').length

  return (
    <div>
      {/* Header */}
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>📜 Quêtes</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Journal de vos aventures incompétentes
        </p>
      </div>

      {/* Tabs */}
      <div className="game-tabs">
        <button className={`game-tab${tab === 'zone' ? ' active' : ''}`} onClick={() => setTab('zone')}>
          🗺️ Quêtes de Zone
        </button>
        <button className={`game-tab${tab === 'daily' ? ' active' : ''}`} onClick={() => setTab('daily')}>
          ☀️ Quotidiennes
          {availableDaily > 0 && (
            <span className="sidebar-nav-badge" style={{ marginLeft: 6 }}>{availableDaily}</span>
          )}
        </button>
      </div>

      {/* Choice Result */}
      {result && (
        <GamePanel
          icon={result.success ? '✅' : '❌'}
          title={result.success ? 'Succès !' : 'Échec...'}
          variant={result.success ? 'success' : 'danger'}
          style={{ marginBottom: 20 }}
          className="anim-slide-in"
        >
          {result.roll && (
            <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 8, fontStyle: 'italic' }}>
              Jet : {result.roll.roll} + {result.roll.stat} = {result.roll.total} (difficulté : {result.roll.difficulty})
            </div>
          )}
          {result.narration && (
            <p style={{ color: '#e2e8f0', fontStyle: 'italic', marginBottom: 12, lineHeight: 1.6, background: '#0d1117', padding: '10px 12px', borderRadius: 6 }}>
              « {result.narration} »
            </p>
          )}
          {result.effects_applied?.length > 0 && (
            <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 10 }}>
              {result.effects_applied.map((e: any, i: number) => (
                <Tooltip key={i} content={EFFECT_TOOLTIPS[e.type] ?? e.type}>
                  <span style={{ background: '#1a0733', border: '1px solid #4c1d95', borderRadius: 4, padding: '2px 7px', fontSize: 11, color: '#a78bfa', cursor: 'help' }}>
                    {e.type === 'buff' && `🔺 ${e.id}`}
                    {e.type === 'debuff' && `🔻 ${e.id}`}
                    {e.type === 'gold' && `💰 ${e.amount > 0 ? '+' : ''}${e.amount}`}
                    {e.type === 'reputation' && `⭐ +${e.amount}`}
                    {e.type === 'loot' && `🎁 ${e.item_name}`}
                  </span>
                </Tooltip>
              ))}
            </div>
          )}
          {result.is_final && result.rewards && (
            <div style={{ background: '#0d1117', borderRadius: 8, padding: '10px 14px', display: 'flex', gap: 16, flexWrap: 'wrap', marginTop: 8 }}>
              <span style={{ color: '#818cf8', fontSize: 13 }}>✨ +{result.rewards.xp} XP</span>
              <span style={{ color: '#fbbf24', fontSize: 13 }}>💰 +{result.rewards.gold}</span>
              {result.rewards.loot && <span style={{ color: '#c084fc', fontSize: 13 }}>🎁 {result.rewards.loot.name}</span>}
            </div>
          )}
          {result.narrator_comment && <div style={{ marginTop: 10 }}><NarratorBubble comment={result.narrator_comment} /></div>}
          <div style={{ marginTop: 12 }}>
            <GameButton variant="ghost" size="sm" onClick={() => setResult(null)}>Fermer ✕</GameButton>
          </div>
        </GamePanel>
      )}

      {/* Active quest step */}
      {activeQuest?.step && !result && (
        <div className="game-panel game-panel-magic anim-slide-in" style={{ marginBottom: 24, overflow: 'hidden' }}>
          {/* Quest header */}
          <div style={{ padding: '14px 16px', background: 'rgba(124,58,237,0.08)', borderBottom: '1px solid #4c1d95' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h2 className="game-title" style={{ margin: 0, fontSize: 16, color: '#c4b5fd' }}>
                📜 {activeQuest.quest_title}
              </h2>
              <span style={{ color: '#6b7280', fontSize: 12 }}>
                Étape {activeQuest.current_step} / {activeQuest.total_steps}
              </span>
            </div>
            <StatBar value={activeQuest.current_step} max={activeQuest.total_steps} variant="custom" color="#7c3aed" height={4} />
          </div>

          <div style={{ padding: 16 }}>
            {/* Narration */}
            <div style={{ background: '#0d1117', border: '1px solid #1f2937', borderRadius: 8, padding: '14px 16px', marginBottom: 14 }}>
              <p style={{ color: '#e2e8f0', margin: 0, lineHeight: 1.7, fontSize: 14 }}>
                {activeQuest.step.narration}
              </p>
            </div>

            {activeQuest.step.narrator_comment && (
              <NarratorBubble comment={activeQuest.step.narrator_comment} />
            )}

            {/* Choices */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 14 }}>
              {activeQuest.step.choices.map((choice, idx) => (
                <button
                  key={choice.id}
                  onClick={() => choose(choice.id)}
                  disabled={acting}
                  className="game-btn game-btn-secondary"
                  style={{
                    width: '100%', textAlign: 'left', justifyContent: 'flex-start',
                    padding: '12px 16px', fontSize: 13,
                    opacity: acting ? 0.6 : 1,
                    gap: 12,
                  }}
                >
                  <span style={{ color: '#7c3aed', fontWeight: 700, fontSize: 14, flexShrink: 0 }}>
                    {String.fromCharCode(65 + idx)}.
                  </span>
                  <span style={{ flex: 1 }}>{choice.text}</span>
                  {choice.test?.has_test && (
                    <span style={{ color: '#f59e0b', fontSize: 11, flexShrink: 0, background: '#1a1000', border: '1px solid #b45309', borderRadius: 3, padding: '1px 6px' }}>
                      {STAT_ICON[choice.test.stat] ?? '🎲'} Test {choice.test.stat?.toUpperCase()}
                    </span>
                  )}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Daily quests */}
      {!activeQuest && tab === 'daily' && (
        <div>
          {dailyRefreshAt && (
            <div style={{ color: '#6b7280', fontSize: 12, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 6 }}>
              ⏰ Renouvellement : {new Date(dailyRefreshAt).toLocaleString('fr-FR')}
            </div>
          )}
          {dailyQuests.length === 0 ? (
            <GamePanel variant="default" style={{ textAlign: 'center', padding: '40px 20px' }}>
              <div style={{ fontSize: 40, marginBottom: 8 }}>☀️</div>
              <p style={{ color: '#4b5563', margin: 0, fontStyle: 'italic' }}>Aucune quête quotidienne. Revenez demain.</p>
            </GamePanel>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              {dailyQuests.map(q => (
                <GamePanel
                  key={q.user_daily_id}
                  variant={q.status === 'completed' ? 'success' : q.status === 'in_progress' ? 'gold' : 'default'}
                  noPadding
                >
                  <div style={{ padding: '14px 16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                      <div>
                        <h3 className="game-title" style={{ margin: '0 0 6px', fontSize: 15 }}>{q.title}</h3>
                        <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                          <span style={{ background: '#1a0d00', color: '#f59e0b', padding: '1px 6px', borderRadius: 3, fontSize: 10, border: '1px solid #b45309' }}>
                            ☀️ Quotidienne
                          </span>
                          <span style={{ color: STATUS_CONFIG[q.status]?.color ?? '#9ca3af', fontSize: 12, fontWeight: 600 }}>
                            {STATUS_CONFIG[q.status]?.icon} {STATUS_CONFIG[q.status]?.label ?? q.status}
                          </span>
                        </div>
                      </div>
                      <div style={{ display: 'flex', gap: 10, flexShrink: 0 }}>
                        <span style={{ color: '#818cf8', fontSize: 13 }}>✨ {q.reward_xp}</span>
                        <span style={{ color: '#fbbf24', fontSize: 13 }}>💰 {q.reward_gold}</span>
                      </div>
                    </div>
                    <p style={{ color: '#9ca3af', fontSize: 13, margin: '0 0 12px', lineHeight: 1.5 }}>{q.description}</p>
                    {(q.status === 'available' || q.status === 'in_progress') && (
                      <GameButton variant={q.status === 'in_progress' ? 'primary' : 'gold'} size="sm" icon={q.status === 'in_progress' ? '▶' : '☀️'} onClick={() => startQuest(q.quest_id)}>
                        {q.status === 'in_progress' ? 'Reprendre' : 'Accepter'}
                      </GameButton>
                    )}
                  </div>
                </GamePanel>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Zone quests */}
      {!activeQuest && tab === 'zone' && (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {quests.length === 0 ? (
            <GamePanel variant="default" style={{ textAlign: 'center', padding: '40px 20px' }}>
              <div style={{ fontSize: 40, marginBottom: 8 }}>🗺️</div>
              <p style={{ color: '#4b5563', margin: 0, fontStyle: 'italic' }}>
                Aucune quête. Explorez une zone pour en débloquer.
              </p>
            </GamePanel>
          ) : (
            quests.map(q => {
              const cfg = STATUS_CONFIG[q.status] ?? STATUS_CONFIG.available
              const isActive = q.status === 'available' || q.status === 'in_progress'
              return (
                <GamePanel
                  key={q.id}
                  variant={q.status === 'completed' ? 'default' : q.status === 'in_progress' ? 'gold' : 'default'}
                  noPadding
                >
                  <div style={{ padding: '14px 16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <h3 className="game-title" style={{ margin: '0 0 6px', fontSize: 15, color: q.status === 'completed' ? '#6b7280' : '#f9fafb' }}>
                          {q.title}
                        </h3>
                        <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
                          <span style={{ color: cfg.color, fontSize: 12, fontWeight: 600 }}>
                            {cfg.icon} {cfg.label}
                          </span>
                          {q.status === 'in_progress' && (
                            <span style={{ color: '#6b7280', fontSize: 12 }}>
                              étape {q.current_step}/{q.steps_count}
                            </span>
                          )}
                        </div>
                      </div>
                      <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexShrink: 0, marginLeft: 10 }}>
                        <span style={{ color: '#818cf8', fontSize: 12 }}>+{q.reward_xp} XP</span>
                        <span style={{ color: '#fbbf24', fontSize: 12 }}>+{q.reward_gold} 💰</span>
                        {q.reward_loot_rarity && <RarityBadge rarity={q.reward_loot_rarity} />}
                      </div>
                    </div>
                    <p style={{ color: '#9ca3af', fontSize: 13, margin: '0 0 12px', lineHeight: 1.5 }}>{q.description}</p>
                    {q.status === 'in_progress' && (
                      <div style={{ marginBottom: 10 }}>
                        <StatBar value={q.current_step} max={q.steps_count} variant="custom" color="#f59e0b" height={4} />
                      </div>
                    )}
                    {isActive && (
                      <GameButton variant="primary" size="sm" icon={q.status === 'in_progress' ? '▶' : '▶'} onClick={() => startQuest(q.id)}>
                        {q.status === 'in_progress' ? 'Reprendre la quête' : 'Commencer'}
                      </GameButton>
                    )}
                  </div>
                </GamePanel>
              )
            })
          )}
        </div>
      )}
    </div>
  )
}
