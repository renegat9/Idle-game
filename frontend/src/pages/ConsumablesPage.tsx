import { useEffect, useState } from 'react'
import { consumableApi } from '../api/game'
import { NarratorBubble } from '../components/narrator/NarratorBubble'

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
      // Rafraîchir l'inventaire
      const fresh = await consumableApi.list()
      setConsumables(fresh.data.consumables)
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erreur')
    } finally {
      setUsing(null)
    }
  }

  if (loading) return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>

  return (
    <div>
      <h1 style={{ color: '#f9fafb', marginBottom: 8 }}>🧪 Consommables</h1>
      <p style={{ color: '#6b7280', fontSize: 13, marginBottom: 20 }}>
        Les effets s'appliquent à toute l'équipe active.
      </p>

      {narrator && <NarratorBubble comment={narrator} />}

      {message && (
        <div style={{ background: '#0f2d1a', border: '1px solid #22c55e', borderRadius: 6, padding: '8px 12px', marginBottom: 16, color: '#86efac', fontSize: 13 }}>
          {message}
        </div>
      )}
      {error && (
        <div style={{ background: '#1f0a0a', border: '1px solid #7f1d1d', borderRadius: 6, padding: '8px 12px', marginBottom: 16, color: '#fca5a5', fontSize: 13 }}>
          {error}
        </div>
      )}

      {consumables.length === 0 ? (
        <div style={{ textAlign: 'center', padding: 60, color: '#4b5563', border: '1px dashed #1f2937', borderRadius: 8 }}>
          Aucun consommable. Achetez-en à la boutique ou trouvez-en en exploration.
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 12 }}>
          {consumables.map((c) => (
            <div key={c.consumable_slug} style={{
              background: '#111827', border: '1px solid #1f2937', borderRadius: 8, padding: 16,
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 6 }}>
                <div>
                  <div style={{ color: '#f9fafb', fontWeight: 'bold', fontSize: 14 }}>
                    {EFFECT_ICON[c.effect_type] ?? '🧴'} {c.name}
                  </div>
                  <span style={{
                    display: 'inline-block', marginTop: 4,
                    color: RARITY_COLOR[c.rarity] ?? '#9ca3af',
                    background: '#1f2937', padding: '2px 8px', borderRadius: 4, fontSize: 11,
                  }}>
                    {c.rarity}
                  </span>
                </div>
                <div style={{
                  background: '#374151', color: '#f9fafb', fontWeight: 'bold',
                  borderRadius: 20, padding: '4px 12px', fontSize: 13, minWidth: 32, textAlign: 'center',
                }}>
                  ×{c.quantity}
                </div>
              </div>

              <p style={{ color: '#9ca3af', fontSize: 12, margin: '6px 0' }}>{c.description}</p>
              {c.flavor_text && (
                <p style={{ color: '#4b5563', fontSize: 11, fontStyle: 'italic', margin: '4px 0 10px' }}>{c.flavor_text}</p>
              )}

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ color: '#fbbf24', fontSize: 12 }}>💰 {c.sell_value} vente</span>
                <button
                  onClick={() => handleUse(c.consumable_slug)}
                  disabled={using === c.consumable_slug || c.quantity <= 0}
                  style={{
                    background: using === c.consumable_slug ? '#374151' : '#059669',
                    color: 'white', border: 'none', borderRadius: 6,
                    padding: '6px 16px', cursor: 'pointer', fontSize: 13,
                  }}
                >
                  {using === c.consumable_slug ? '...' : 'Utiliser'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
