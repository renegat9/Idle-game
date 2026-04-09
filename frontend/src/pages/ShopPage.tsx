import { useEffect, useState } from 'react'
import { shopApi } from '../api/game'
import { ItemImage } from '../components/ui/ItemImage'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'

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
  image_url?: string | null
}

const RARITY_LABEL: Record<string, string> = {
  commun:      'Commun',
  peu_commun:  'Peu commun',
  rare:        'Rare',
  epique:      'Épique',
  legendaire:  'Légendaire',
  wtf:         'WTF',
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

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Le marchand arrive…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🛒 Boutique</h1>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
            {zoneName ? `Marchand de ${zoneName}` : 'Marchand ambulant'} — assortiment limité, qualité douteuse.
          </p>
          {expiresAt && (
            <span style={{ color: '#4b5563', fontSize: 12, fontFamily: 'var(--font-title)' }}>
              ⏱ Expire le {new Date(expiresAt).toLocaleString('fr-FR')}
            </span>
          )}
        </div>
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

      {items.length === 0 ? (
        <GamePanel variant="default" style={{ textAlign: 'center', padding: '60px 20px' }}>
          <div style={{ fontSize: 48, marginBottom: 12 }}>🛏️</div>
          <p style={{ color: '#6b7280', fontStyle: 'italic', margin: 0 }}>
            Le marchand est en pause déjeuner. Revenez dans un moment.
          </p>
        </GamePanel>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16 }}>
          {items.map(item => (
            <div
              key={item.id}
              className={`game-panel rarity-frame rarity-frame-${item.rarity}`}
              style={{ overflow: 'hidden' }}
            >
              {/* Item header */}
              <div style={{ padding: '14px 16px 0', display: 'flex', gap: 14, alignItems: 'flex-start' }}>
                <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={64} name={item.name} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 6 }}>
                    <h3 className="game-title" style={{ margin: 0, fontSize: 14, color: '#f9fafb', lineHeight: 1.3 }}>
                      {item.name}
                    </h3>
                    <span style={{ color: '#4b5563', fontSize: 11, background: '#0d1117', padding: '2px 6px', borderRadius: 4, whiteSpace: 'nowrap', marginLeft: 6 }}>
                      Niv. {item.item_level}
                    </span>
                  </div>
                  <div style={{ display: 'flex', gap: 6 }}>
                    <span style={{
                      fontSize: 10, fontFamily: 'var(--font-title)', textTransform: 'uppercase',
                      letterSpacing: '0.05em', padding: '2px 8px', borderRadius: 4,
                      background: '#0d1117',
                      color: {
                        commun: '#94a3b8', peu_commun: '#22c55e', rare: '#3b82f6',
                        epique: '#a855f7', legendaire: '#f59e0b', wtf: '#ec4899',
                      }[item.rarity] ?? '#94a3b8',
                    }}>
                      {RARITY_LABEL[item.rarity] ?? item.rarity}
                    </span>
                    <span style={{ fontSize: 10, color: '#4b5563', padding: '2px 8px', borderRadius: 4, background: '#0d1117', textTransform: 'capitalize' }}>
                      {item.slot.replace('_', ' ')}
                    </span>
                  </div>
                </div>
              </div>

              {/* Stats */}
              <div style={{ padding: '10px 16px', display: 'flex', flexWrap: 'wrap', gap: '4px 14px' }}>
                {item.atq > 0 && <span style={{ color: '#ef4444', fontSize: 12 }}>⚔️ +{item.atq}</span>}
                {item.def > 0 && <span style={{ color: '#3b82f6', fontSize: 12 }}>🛡️ +{item.def}</span>}
                {item.hp  > 0 && <span style={{ color: '#22c55e', fontSize: 12 }}>❤️ +{item.hp}</span>}
                {item.vit > 0 && <span style={{ color: '#06b6d4', fontSize: 12 }}>💨 +{item.vit}</span>}
                {item.cha > 0 && <span style={{ color: '#ec4899', fontSize: 12 }}>✨ +{item.cha}</span>}
                {item.int > 0 && <span style={{ color: '#a855f7', fontSize: 12 }}>📖 +{item.int}</span>}
              </div>

              {/* Footer */}
              <div style={{ padding: '10px 16px 14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid #1f2937' }}>
                <span style={{ color: '#4b5563', fontSize: 11 }}>
                  Revente : {item.sell_value.toLocaleString('fr-FR')} 💰
                </span>
                <GameButton
                  variant="gold"
                  size="sm"
                  onClick={() => buy(item.id)}
                  loading={buying === item.id}
                  disabled={buying !== null}
                >
                  {item.shop_price.toLocaleString('fr-FR')} 💰 Acheter
                </GameButton>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
