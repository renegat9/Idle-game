import { useEffect, useState } from 'react'
import { consumableApi } from '../api/game'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'

const RARITY_COLOR: Record<string, string> = {
  commun: '#9ca3af',
  peu_commun: '#22c55e',
  rare: '#3b82f6',
}

const EFFECT_ICON: Record<string, string> = {
  heal_hp: '❤️',
  restore_hp_pct: '💖',
  xp_boost: '✨',
  gold_boost: '💰',
  cure_debuff: '🧹',
}

export function ConsumablesPage() {
  const [consumables, setConsumables] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [using, setUsing] = useState<string | null>(null)
  const [narrator, setNarrator] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const load = () =>
    consumableApi.list().then(({ data }) => setConsumables(data.consumables)).finally(() => setLoading(false))

  useEffect(() => { load() }, [])

  const handleUse = async (slug: string) => {
    setUsing(slug)
    setMessage('')
    setError('')
    setNarrator('')
    try {
      const { data } = await consumableApi.use(slug)
      setMessage(data.message)
      setNarrator(data.narrator_comment)
      const fresh = await consumableApi.list()
      setConsumables(fresh.data.consumables)
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erreur')
    } finally {
      setUsing(null)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement des consommables…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🧪 Consommables</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Les effets s'appliquent à toute l'équipe active.
        </p>
      </div>

      {narrator && <NarratorBubble comment={narrator} />}

      {message && (
        <div
          className="narrator-bubble anim-slide-in"
          style={{ marginBottom: 16, borderLeftColor: '#22c55e', background: '#020f08' }}
        >
          <p className="narrator-text" style={{ margin: 0, color: '#86efac' }}>« {message} »</p>
        </div>
      )}
      {error && (
        <div
          className="narrator-bubble anim-slide-in"
          style={{ marginBottom: 16, borderLeftColor: '#ef4444', background: '#0a0202' }}
        >
          <p className="narrator-text" style={{ margin: 0, color: '#fca5a5' }}>« {error} »</p>
        </div>
      )}

      {consumables.length === 0 ? (
        <GamePanel variant="default" style={{ textAlign: 'center', padding: '60px 20px' }}>
          <div style={{ fontSize: 48, marginBottom: 12 }}>🧴</div>
          <p style={{ color: '#6b7280', fontStyle: 'italic', margin: 0 }}>
            Aucun consommable. Achetez-en à la boutique ou trouvez-en en exploration.
          </p>
        </GamePanel>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 12 }}>
          {consumables.map((c) => (
            <div
              key={c.consumable_slug}
              className={`game-panel rarity-frame rarity-frame-${c.rarity}`}
            >
              <div style={{ padding: '14px 16px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                  <div>
                    <div className="game-title" style={{ fontSize: 14, color: '#f9fafb', marginBottom: 4 }}>
                      {EFFECT_ICON[c.effect_type] ?? '🧴'} {c.name}
                    </div>
                    <span style={{
                      display: 'inline-block',
                      color: RARITY_COLOR[c.rarity] ?? '#9ca3af',
                      background: '#0d1117', padding: '2px 8px', borderRadius: 4, fontSize: 10,
                      fontFamily: 'var(--font-title)', textTransform: 'uppercase', letterSpacing: '0.05em',
                    }}>
                      {c.rarity}
                    </span>
                  </div>
                  <div style={{
                    background: '#1f2937', color: '#f9fafb', fontWeight: 700,
                    borderRadius: 20, padding: '4px 12px', fontSize: 14, minWidth: 32, textAlign: 'center',
                    fontFamily: 'var(--font-title)',
                  }}>
                    ×{c.quantity}
                  </div>
                </div>

                <p style={{ color: '#9ca3af', fontSize: 12, margin: '6px 0' }}>{c.description}</p>
                {c.flavor_text && (
                  <p style={{ color: '#4b5563', fontSize: 11, fontStyle: 'italic', margin: '4px 0 10px' }}>
                    « {c.flavor_text} »
                  </p>
                )}

                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid #1f2937', paddingTop: 10, marginTop: 4 }}>
                  <span style={{ color: '#fbbf24', fontSize: 12 }}>💰 {c.sell_value} vente</span>
                  <GameButton
                    variant="secondary"
                    size="sm"
                    onClick={() => handleUse(c.consumable_slug)}
                    loading={using === c.consumable_slug}
                    disabled={c.quantity <= 0}
                  >
                    Utiliser
                  </GameButton>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
