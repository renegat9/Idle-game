import { useEffect, useState } from 'react'
import { shopApi } from '../api/game'

type ShopItem = {
  id: number
  name: string
  rarity: string
  slot: string
  item_level: number
  atq: number
  def: number
  hp: number
  vit: number
  cha: number
  int: number
  sell_value: number
  shop_price: number
  expires_at: string
}

const RARITY_COLORS: Record<string, string> = {
  commun:      '#94a3b8',
  peu_commun:  '#22c55e',
  rare:        '#3b82f6',
  epique:      '#a855f7',
  legendaire:  '#f59e0b',
  wtf:         '#ec4899',
}

const SLOT_ICONS: Record<string, string> = {
  arme:         '⚔️',
  armure:       '🛡️',
  casque:       '⛑️',
  bottes:       '👢',
  accessoire:   '💍',
  truc_bizarre: '🤔',
}

export function ShopPage() {
  const [items, setItems] = useState<ShopItem[]>([])
  const [zoneName, setZoneName] = useState('')
  const [expiresAt, setExpiresAt] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)
  const [buying, setBuying] = useState<number | null>(null)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)

  useEffect(() => { loadShop() }, [])

  async function loadShop() {
    setLoading(true)
    try {
      const { data } = await shopApi.get()
      setItems(data.items ?? [])
      setZoneName(data.zone_name ?? '')
      setExpiresAt(data.expires_at ?? null)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function buy(itemId: number) {
    if (buying !== null) return
    setBuying(itemId)
    setMessage(null)
    try {
      const { data } = await shopApi.buy(itemId)
      setMessage({ text: data.message, ok: true })
      await loadShop()
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur lors de l\'achat.', ok: false })
    }
    setBuying(null)
  }

  function statRow(label: string, value: number) {
    if (!value) return null
    return (
      <span style={{ color: '#94a3b8', fontSize: 11, marginRight: 8 }}>
        {label} <span style={{ color: '#e2e8f0' }}>+{value}</span>
      </span>
    )
  }

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement de la boutique...</div>

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>🛒 Boutique</h1>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
        <p style={{ color: '#6b7280', fontSize: 14, margin: 0 }}>
          {zoneName ? `Marchand de ${zoneName}` : 'Marchand ambulant'} — assortiment limité, qualité douteuse.
        </p>
        {expiresAt && (
          <span style={{ color: '#475569', fontSize: 12 }}>
            Expire le {new Date(expiresAt).toLocaleString('fr-FR')}
          </span>
        )}
      </div>

      {message && (
        <div style={{ background: message.ok ? '#052e16' : '#1c0505', border: `1px solid ${message.ok ? '#16a34a' : '#991b1b'}`, borderRadius: 8, padding: 12, marginBottom: 16 }}>
          <span style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>{message.text}</span>
        </div>
      )}

      {items.length === 0 ? (
        <div style={{ color: '#6b7280', background: '#1e293b', borderRadius: 10, padding: 40, textAlign: 'center' }}>
          Le marchand est en pause déjeuner. Revenez dans un moment.
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16 }}>
          {items.map(item => (
            <div key={item.id} style={{
              background: '#1e293b',
              border: `1px solid ${RARITY_COLORS[item.rarity] ?? '#334155'}`,
              borderRadius: 12,
              padding: 18,
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                <div>
                  <span style={{ fontSize: 18, marginRight: 8 }}>{SLOT_ICONS[item.slot] ?? '📦'}</span>
                  <span style={{ color: RARITY_COLORS[item.rarity] ?? '#94a3b8', fontWeight: 'bold', fontSize: 14 }}>
                    {item.name}
                  </span>
                </div>
                <span style={{ color: '#94a3b8', fontSize: 11, background: '#0f172a', padding: '2px 6px', borderRadius: 4 }}>
                  Niv. {item.item_level}
                </span>
              </div>

              <div style={{ marginBottom: 10 }}>
                <span style={{ fontSize: 11, color: RARITY_COLORS[item.rarity], background: '#0f172a', padding: '2px 8px', borderRadius: 4, textTransform: 'capitalize' }}>
                  {item.rarity.replace('_', ' ')}
                </span>
                <span style={{ fontSize: 11, color: '#94a3b8', marginLeft: 8, textTransform: 'capitalize' }}>
                  {item.slot.replace('_', ' ')}
                </span>
              </div>

              <div style={{ marginBottom: 14, minHeight: 20 }}>
                {statRow('ATQ', item.atq)}
                {statRow('DEF', item.def)}
                {statRow('HP', item.hp)}
                {statRow('VIT', item.vit)}
                {statRow('CHA', item.cha)}
                {statRow('INT', item.int)}
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ color: '#94a3b8', fontSize: 11 }}>Revente : {item.sell_value} 💰</span>
                <button
                  onClick={() => buy(item.id)}
                  disabled={buying !== null}
                  style={{
                    background: buying === item.id ? '#374151' : '#7c3aed',
                    color: 'white',
                    border: 'none',
                    padding: '8px 16px',
                    borderRadius: 8,
                    cursor: buying !== null ? 'not-allowed' : 'pointer',
                    fontSize: 13,
                    fontWeight: 'bold',
                    opacity: buying !== null ? 0.6 : 1,
                  }}
                >
                  {buying === item.id ? '...' : `${item.shop_price} 💰`}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
