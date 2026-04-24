import { useEffect, useState } from 'react'
import { inventoryApi, heroApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { RarityBadge } from '../components/hero/RarityBadge'
import { ItemImage } from '../components/ui/ItemImage'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import type { Item, Hero } from '../types'

const SLOT_LABELS: Record<string, string> = {
  arme: 'Arme', armure: 'Armure', casque: 'Casque',
  bottes: 'Bottes', accessoire: 'Accessoire', truc_bizarre: 'Truc Bizarre',
}

const STAT_LABELS: Array<[keyof Item, string, string, string, string]> = [
  ['atq', 'ATQ', '#ef4444', '⚔️', 'Attaque'],
  ['def', 'DEF', '#3b82f6', '🛡️', 'Défense'],
  ['hp',  'HP',  '#22c55e', '❤️', 'Points de vie'],
  ['vit', 'VIT', '#86efac', '💨', 'Vitesse'],
  ['cha', 'CHA', '#fbbf24', '✨', 'Charisme'],
  ['int', 'INT', '#8b5cf6', '📖', 'Intelligence'],
]

export function InventoryPage() {
  const { updateUser } = useAuthStore()
  const { setInventory, setGold } = useGameStore()
  const [equipped, setEquipped] = useState<Item[]>([])
  const [unequipped, setUnequipped] = useState<Item[]>([])
  const [heroes, setHeroes] = useState<Hero[]>([])
  const [loading, setLoading] = useState(true)
  const [selling, setSelling] = useState<number | null>(null)
  const [equipping, setEquipping] = useState<number | null>(null)
  const [unequipping, setUnequipping] = useState<number | null>(null)
  const [selectedHero, setSelectedHero] = useState<Record<number, number>>({})
  const [message, setMessage] = useState('')

  useEffect(() => {
    Promise.all([inventoryApi.list(), heroApi.list()])
      .then(([invRes, heroRes]) => {
        setEquipped(invRes.data.equipped)
        setUnequipped(invRes.data.unequipped)
        setInventory(invRes.data.equipped, invRes.data.unequipped)
        setHeroes(heroRes.data.heroes)
      })
      .finally(() => setLoading(false))
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

  const handleUnequip = async (item: Item) => {
    setUnequipping(item.id)
    setMessage('')
    try {
      const { data } = await inventoryApi.unequip(item.id)
      setEquipped((prev) => prev.filter((i) => i.id !== item.id))
      setUnequipped((prev) => [{ ...item, equipped_by_hero_id: null }, ...prev])
      setMessage(data.message)
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur')
    } finally {
      setUnequipping(null)
    }
  }

  const handleEquip = async (item: Item) => {
    const heroId = selectedHero[item.id]
    if (!heroId) return
    setEquipping(item.id)
    setMessage('')
    try {
      const { data } = await heroApi.equip(heroId, item.id)
      setMessage(data.message)
      setUnequipped((prev) => prev.filter((i) => i.id !== item.id))
      setEquipped((prev) => [...prev, { ...item, equipped_by_hero_id: heroId }])
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Erreur lors de l\'équipement')
    } finally {
      setEquipping(null)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Chargement de l'inventaire…</div>
      </div>
    )
  }

  const ItemCard = ({ item, canSell, canUnequip }: { item: Item; canSell: boolean; canUnequip?: boolean }) => {
    const stats = STAT_LABELS.filter(([key]) => (item[key] as number) > 0) as typeof STAT_LABELS
    return (
      <div className={`item-card rarity-frame rarity-frame-${item.rarity}`} style={{ display: 'flex', flexDirection: 'column' }}>
        {/* Image zone */}
        <div className="item-image-slot" style={{ padding: 12, display: 'flex', justifyContent: 'center' }}>
          <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={64} name={item.name} />
        </div>

        {/* Info */}
        <div style={{ padding: '10px 12px', flex: 1, display: 'flex', flexDirection: 'column', gap: 6 }}>
          <div style={{ color: '#f9fafb', fontSize: 13, fontWeight: 700, lineHeight: 1.3 }}>{item.name}</div>
          <div style={{ display: 'flex', gap: 5, flexWrap: 'wrap' }}>
            <RarityBadge rarity={item.rarity} />
            <span style={{ background: '#0d1117', color: '#9ca3af', padding: '2px 6px', borderRadius: 4, fontSize: 10, border: '1px solid #1f2937' }}>
              {SLOT_LABELS[item.slot] ?? item.slot}
            </span>
            <span style={{ background: '#0d1117', color: '#6b7280', padding: '2px 6px', borderRadius: 4, fontSize: 10, border: '1px solid #1f2937' }}>
              Niv.{item.item_level}
            </span>
            {item.is_ai_generated && (
              <span style={{ background: '#1e1b4b', color: '#818cf8', padding: '2px 6px', borderRadius: 4, fontSize: 10, border: '1px solid #312e81' }}>
                ✨ IA
              </span>
            )}
          </div>

          {item.description && (
            <p style={{ color: '#6b7280', fontSize: 11, margin: 0, fontStyle: 'italic', lineHeight: 1.4 }}>
              {item.description}
            </p>
          )}

          {/* Stats */}
          {stats.length > 0 && (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 4 }}>
              {stats.map(([key, label, color, icon, tooltip]) => (
                <div key={key as string} title={tooltip} style={{
                  textAlign: 'center', background: '#0d1117', borderRadius: 4,
                  padding: '4px 2px', border: '1px solid #1a1f2e', cursor: 'help',
                }}>
                  <div style={{ fontSize: 11 }}>{icon}</div>
                  <div style={{ color, fontWeight: 700, fontSize: 13 }}>+{item[key] as number}</div>
                  <div style={{ color: '#4b5563', fontSize: 9 }}>{label}</div>
                </div>
              ))}
            </div>
          )}

          {item.effects && item.effects.length > 0 && (
            <div style={{ background: '#0f0a2e', border: '1px solid #4c1d95', borderRadius: 6, padding: '6px 10px', display: 'flex', flexDirection: 'column', gap: 4 }}>
              <div style={{ fontSize: 9, color: '#7c3aed', fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>
                ✦ Effet{item.effects.length > 1 ? 's' : ''} spécial{item.effects.length > 1 ? 'x' : ''}
              </div>
              {item.effects.map((e, i) => (
                <div key={i} style={{ color: '#c4b5fd', fontSize: 11, lineHeight: 1.4 }}>
                  {e.description}
                </div>
              ))}
            </div>
          )}

          {/* Actions */}
          {canUnequip && (
            <div style={{ marginTop: 'auto', paddingTop: 6 }}>
              <GameButton
                variant="ghost"
                size="sm"
                onClick={() => handleUnequip(item)}
                loading={unequipping === item.id}
                style={{ width: '100%' }}
              >
                Déséquiper
              </GameButton>
            </div>
          )}
          {canSell && (
            <div style={{ marginTop: 'auto', display: 'flex', flexDirection: 'column', gap: 6 }}>
              {heroes.length > 0 && (
                <div style={{ display: 'flex', gap: 5 }}>
                  <select
                    value={selectedHero[item.id] ?? ''}
                    onChange={(e) => setSelectedHero((prev) => ({ ...prev, [item.id]: +e.target.value }))}
                    style={{
                      flex: 1, background: '#1f2937', border: '1px solid #374151',
                      borderRadius: 5, color: '#d1d5db', fontSize: 11, padding: '5px 6px',
                    }}
                  >
                    <option value="">— Choisir un héros —</option>
                    {heroes.map((h) => (
                      <option key={h.id} value={h.id}>{h.name} Niv.{h.level}</option>
                    ))}
                  </select>
                  <GameButton
                    variant="primary"
                    size="sm"
                    onClick={() => handleEquip(item)}
                    disabled={!selectedHero[item.id]}
                    loading={equipping === item.id}
                  >
                    Équiper
                  </GameButton>
                </div>
              )}
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ color: '#fbbf24', fontSize: 12, fontWeight: 600 }}>
                  💰 {item.sell_value} or
                </span>
                <GameButton
                  variant="ghost"
                  size="sm"
                  onClick={() => handleSell(item)}
                  loading={selling === item.id}
                >
                  Vendre
                </GameButton>
              </div>
            </div>
          )}
        </div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🎒 Inventaire</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          {equipped.length + unequipped.length} objet(s) au total
        </p>
      </div>

      {message && (
        <div className="narrator-bubble anim-slide-in" style={{ marginBottom: 16 }}>
          <p className="narrator-text" style={{ margin: 0 }}>« {message} »</p>
        </div>
      )}

      {equipped.length > 0 && (
        <GamePanel icon="⚔️" title={`Équipés (${equipped.length})`} variant="success" style={{ marginBottom: 24 }} noPadding>
          <div style={{ padding: 16 }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 10 }}>
              {equipped.map((item) => <ItemCard key={item.id} item={item} canSell={false} canUnequip={true} />)}
            </div>
          </div>
        </GamePanel>
      )}

      <GamePanel icon="🎒" title={`Non équipés (${unequipped.length})`} variant="default" noPadding>
        <div style={{ padding: 16 }}>
          {unequipped.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '32px 0', color: '#4b5563' }}>
              <div style={{ fontSize: 40, marginBottom: 8 }}>📦</div>
              <p style={{ margin: 0, fontSize: 13, fontStyle: 'italic' }}>
                Aucun objet disponible. Allez explorer !
              </p>
            </div>
          ) : (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 10 }}>
              {unequipped.map((item) => <ItemCard key={item.id} item={item} canSell={true} />)}
            </div>
          )}
        </div>
      </GamePanel>
    </div>
  )
}
