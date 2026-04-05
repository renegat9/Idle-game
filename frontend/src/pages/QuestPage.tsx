import { useEffect, useState } from 'react'
import { questApi } from '../api/game'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { RarityBadge } from '../components/hero/RarityBadge'

type Quest = {
  id: number
  title: string
  description: string
  steps_count: number
  order_index: number
  reward_xp: number
  reward_gold: number
  reward_loot_rarity: string | null
  status: string
  current_step: number
  user_quest_id: number | null
}

type Step = {
  step_index: number
  narration: string
  narrator_comment: string
  is_final: boolean
  choices: Array<{
    id: string
    text: string
    test?: { stat: string; has_test: boolean; type: string } | null
  }>
}

type ActiveQuest = {
  user_quest_id: number
  quest_id: number
  quest_title: string
  current_step: number
  total_steps: number
  step: Step | null
}

export function QuestPage() {
  const [quests, setQuests] = useState<Quest[]>([])
  const [loading, setLoading] = useState(true)
  const [activeQuest, setActiveQuest] = useState<ActiveQuest | null>(null)
  const [result, setResult] = useState<any>(null)
  const [acting, setActing] = useState(false)

  useEffect(() => {
    loadQuests()
  }, [])

  async function loadQuests() {
    try {
      const { data } = await questApi.list()
      setQuests(data.quests)
    } catch { /* ok */ }
    setLoading(false)
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
        setActiveQuest(prev => prev ? { ...prev, current_step: data.next_step.step_index, step: data.next_step } : null)
      }
    } catch (e: any) {
      alert(e.response?.data?.message ?? 'Erreur')
    }
    setActing(false)
  }

  const statusColor = (s: string) => ({ available: '#22c55e', in_progress: '#f59e0b', completed: '#6b7280', failed: '#ef4444' }[s] ?? '#6b7280')
  const statusLabel = (s: string) => ({ available: 'Disponible', in_progress: 'En cours', completed: 'Terminée', failed: 'Échouée' }[s] ?? s)

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement des quêtes...</div>

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 24, fontSize: 24 }}>📜 Quêtes</h1>

      {/* Result panel */}
      {result && (
        <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 20, marginBottom: 24 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12 }}>
            <span style={{ fontSize: 20 }}>{result.success ? '✅' : '❌'}</span>
            <span style={{ color: result.success ? '#22c55e' : '#ef4444', fontWeight: 'bold' }}>
              {result.success ? 'Succès !' : 'Échec...'}
            </span>
            {result.roll && (
              <span style={{ color: '#94a3b8', fontSize: 13 }}>
                (jet: {result.roll.roll} + {result.roll.stat} = {result.roll.total} vs {result.roll.difficulty})
              </span>
            )}
          </div>
          <p style={{ color: '#e2e8f0', fontStyle: 'italic', marginBottom: 12 }}>"{result.narration}"</p>

          {result.effects_applied?.length > 0 && (
            <div style={{ marginBottom: 12 }}>
              {result.effects_applied.map((e: any, i: number) => (
                <span key={i} style={{ background: '#0f172a', border: '1px solid #334155', borderRadius: 6, padding: '2px 8px', fontSize: 12, marginRight: 6, color: '#a78bfa' }}>
                  {e.type === 'buff' && `🔺 Buff ${e.id}`}
                  {e.type === 'debuff' && `🔻 Debuff ${e.id}`}
                  {e.type === 'gold' && `💰 ${e.amount > 0 ? '+' : ''}${e.amount} or`}
                  {e.type === 'reputation' && `⭐ Réputation +${e.amount}`}
                  {e.type === 'loot' && `🎁 ${e.item_name} (${e.rarity})`}
                </span>
              ))}
            </div>
          )}

          {result.is_final && result.rewards && (
            <div style={{ background: '#0f172a', borderRadius: 8, padding: 12 }}>
              <div style={{ color: '#fbbf24', fontWeight: 'bold', marginBottom: 8 }}>🏆 Récompenses</div>
              <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap' }}>
                <span style={{ color: '#22c55e' }}>+{result.rewards.xp} XP {result.rewards.xp_bonus > 0 && <span style={{ color: '#86efac', fontSize: 12 }}>(+{result.rewards.xp_bonus} bonus)</span>}</span>
                <span style={{ color: '#fbbf24' }}>+{result.rewards.gold} 💰 {result.rewards.gold_bonus > 0 && <span style={{ color: '#fde68a', fontSize: 12 }}>(+{result.rewards.gold_bonus} bonus)</span>}</span>
                {result.rewards.loot && <span style={{ color: '#c084fc' }}>🎁 {result.rewards.loot.name}</span>}
                <span style={{ color: '#94a3b8', fontSize: 12 }}>Voie: {result.rewards.dominant_voice}</span>
              </div>
            </div>
          )}

          {result.narrator_comment && <NarratorBubble comment={result.narrator_comment} />}

          <button onClick={() => setResult(null)} style={{ marginTop: 12, background: 'transparent', border: '1px solid #475569', color: '#94a3b8', padding: '6px 14px', borderRadius: 6, cursor: 'pointer', fontSize: 13 }}>
            Fermer
          </button>
        </div>
      )}

      {/* Active quest step */}
      {activeQuest?.step && !result && (
        <div style={{ background: '#1e293b', border: '2px solid #7c3aed', borderRadius: 12, padding: 24, marginBottom: 24 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
            <h2 style={{ color: '#a78bfa', margin: 0 }}>{activeQuest.quest_title}</h2>
            <span style={{ color: '#6b7280', fontSize: 13 }}>Étape {activeQuest.current_step}/{activeQuest.total_steps}</span>
          </div>

          <div style={{ background: '#0f172a', borderRadius: 8, padding: 16, marginBottom: 16 }}>
            <p style={{ color: '#e2e8f0', margin: 0, lineHeight: 1.6 }}>{activeQuest.step.narration}</p>
          </div>

          {activeQuest.step.narrator_comment && (
            <NarratorBubble comment={activeQuest.step.narrator_comment} />
          )}

          <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 16 }}>
            {activeQuest.step.choices.map(choice => (
              <button
                key={choice.id}
                onClick={() => choose(choice.id)}
                disabled={acting}
                style={{
                  background: '#0f172a', border: '1px solid #334155', color: '#e2e8f0',
                  padding: '12px 16px', borderRadius: 8, cursor: acting ? 'not-allowed' : 'pointer',
                  textAlign: 'left', opacity: acting ? 0.6 : 1,
                  display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                }}
              >
                <span>
                  <span style={{ color: '#7c3aed', fontWeight: 'bold', marginRight: 8 }}>[{choice.id}]</span>
                  {choice.text}
                </span>
                {choice.test?.has_test && (
                  <span style={{ color: '#f59e0b', fontSize: 12 }}>Test {choice.test.stat?.toUpperCase()}</span>
                )}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Quest list */}
      {!activeQuest && (
        <div style={{ display: 'grid', gap: 16 }}>
          {quests.length === 0 && (
            <div style={{ color: '#6b7280', textAlign: 'center', padding: 40 }}>
              Aucune quête disponible. Explorez une zone pour en débloquer.
            </div>
          )}
          {quests.map(q => (
            <div key={q.id} style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 20 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 10 }}>
                <div>
                  <h3 style={{ color: '#f1f5f9', margin: '0 0 4px' }}>{q.title}</h3>
                  <span style={{ color: statusColor(q.status), fontSize: 12, fontWeight: 'bold' }}>
                    {statusLabel(q.status)}
                  </span>
                  {q.status === 'in_progress' && (
                    <span style={{ color: '#f59e0b', fontSize: 12, marginLeft: 8 }}>
                      (étape {q.current_step}/{q.steps_count})
                    </span>
                  )}
                </div>
                <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                  <span style={{ color: '#22c55e', fontSize: 13 }}>+{q.reward_xp} XP</span>
                  <span style={{ color: '#fbbf24', fontSize: 13 }}>+{q.reward_gold} 💰</span>
                  {q.reward_loot_rarity && <RarityBadge rarity={q.reward_loot_rarity} />}
                </div>
              </div>
              <p style={{ color: '#94a3b8', fontSize: 14, margin: '0 0 14px', lineHeight: 1.5 }}>{q.description}</p>
              {(q.status === 'available' || q.status === 'in_progress') && (
                <button
                  onClick={() => startQuest(q.id)}
                  style={{
                    background: '#7c3aed', color: 'white', border: 'none',
                    padding: '8px 18px', borderRadius: 8, cursor: 'pointer', fontSize: 14,
                  }}
                >
                  {q.status === 'in_progress' ? '▶ Reprendre' : '▶ Commencer'}
                </button>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
