import { useEffect, useState } from 'react'
import { inventoryApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { RarityBadge } from '../components/hero/RarityBadge'
import type { Item } from '../types'

const SLOT_LABELS: Record<string, string> = {
  arme: '⚔️ Arme', armure: '🛡️ Armure', casque: '🪖 Casque',
  bottes: '👢 Bottes', accessoire: '💍 Accessoire', truc_bizarre: '❓ Truc Bizarre',
}

const STAT_LABELS: Array<[keyof Item, string, string]> = [
  ['atq', 'ATQ', '#ef4444'], ['def', 'DEF', '#3b82f6'],
  ['hp', 'HP', '#22c55e'], ['vit', 'VIT', '#86efac'],
  ['cha', 'CHA', '#fbbf24'], ['int', 'INT', '#8b5cf6'],
]

export function InventoryPage() {
  const { updateUser } = useAuthStore()
  const { setInventory, setGold } = useGameStore()
  const [equipped, setEquipped] = useState<Item[]>([])
  const [unequipped, setUnequipped] = useState<Item[]>([])
  const [loading, setLoading] = useState(true)
  const [selling, setSelling] = useState<number | null>(null)
  const [message, setMessage] = useState('')

  useEffect(() => {
    inventoryApi.list().then(({ data }) => {
      setEquipped(data.equipped)
      setUnequipped(data.unequipped)
      setInventory(data.equipped, data.unequipped)
    }).finally(() => setLoading(false))
  }, [])

  const handleSell = async (item: Item) => {
    setSelling(item.id)
    setMessage('')
    try {
      const { data } = await inventoryApi.sell(item.id)
      setUnequipped((prev) => prev.filter((i) => i.id !== item.id))
      setGold(data.new_gold_total)
      updateUser({ gold: data.new_gold_total })
      setMessage(data.message)
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur')
    } finally {
      setSelling(null)
    }
  }

  if (loading) return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>

  const ItemCard = ({ item, canSell }: { item: Item; canSell: boolean }) => (
    <div style={{
      background: '#111827', border: '1px solid #1f2937', borderRadius: 8, padding: 12,
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 6 }}>
        <div>
          <div style={{ color: '#f9fafb', fontSize: 14, fontWeight: 'bold' }}>{item.name}</div>
          <div style={{ display: 'flex', gap: 6, marginTop: 4 }}>
            <RarityBadge rarity={item.rarity} />
            <span style={{ background: '#1f2937', color: '#9ca3af', padding: '2px 6px', borderRadius: 4, fontSize: 11 }}>
              {SLOT_LABELS[item.slot] ?? item.slot}
            </span>
            <span style={{ background: '#1f2937', color: '#9ca3af', padding: '2px 6px', borderRadius: 4, fontSize: 11 }}>
              Niv.{item.item_level}
            </span>
          </div>
        </div>
      </div>

      {item.description && (
        <p style={{ color: '#6b7280', fontSize: 12, margin: '6px 0', fontStyle: 'italic' }}>{item.description}</p>
      )}

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 4, margin: '8px 0' }}>
        {STAT_LABELS.map(([key, label, color]) => {
          const val = item[key] as number
          if (!val) return null
          return (
            <div key={key} style={{ textAlign: 'center', background: '#0f172a', borderRadius: 4, padding: '4px 0' }}>
              <div style={{ color, fontWeight: 'bold', fontSize: 14 }}>+{val}</div>
              <div style={{ color: '#4b5563', fontSize: 10 }}>{label}</div>
            </div>
          )
        })}
      </div>

      {canSell && (
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
          <span style={{ color: '#fbbf24', fontSize: 12 }}>💰 {item.sell_value} or</span>
          <button
            onClick={() => handleSell(item)}
            disabled={selling === item.id}
            style={{
              background: '#374151', color: '#d1d5db', border: 'none',
              borderRadius: 4, padding: '4px 12px', cursor: 'pointer', fontSize: 12,
            }}
          >
            {selling === item.id ? '...' : 'Vendre'}
          </button>
        </div>
      )}
    </div>
  )

  return (
    <div>
      <h1 style={{ color: '#f9fafb', marginBottom: 8 }}>🎒 Inventaire</h1>

      {message && (
        <div style={{ background: '#0f2d1a', border: '1px solid #22c55e', borderRadius: 6, padding: '8px 12px', marginBottom: 16, color: '#86efac', fontSize: 13 }}>
          {message}
        </div>
      )}

      {equipped.length > 0 && (
        <div style={{ marginBottom: 32 }}>
          <h2 style={{ color: '#9ca3af', fontSize: 14, textTransform: 'uppercase', letterSpacing: 1, marginBottom: 12 }}>
            Équipés ({equipped.length})
          </h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))', gap: 12 }}>
            {equipped.map((item) => <ItemCard key={item.id} item={item} canSell={false} />)}
          </div>
        </div>
      )}

      <div>
        <h2 style={{ color: '#9ca3af', fontSize: 14, textTransform: 'uppercase', letterSpacing: 1, marginBottom: 12 }}>
          Non équipés ({unequipped.length})
        </h2>
        {unequipped.length === 0 ? (
          <div style={{ textAlign: 'center', padding: 40, color: '#4b5563', border: '1px dashed #1f2937', borderRadius: 8 }}>
            Inventaire vide. Allez explorer !
          </div>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))', gap: 12 }}>
            {unequipped.map((item) => <ItemCard key={item.id} item={item} canSell={true} />)}
          </div>
        )}
      </div>
    </div>
  )
}
