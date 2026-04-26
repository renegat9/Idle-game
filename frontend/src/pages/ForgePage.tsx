import { useEffect, useState } from 'react'
import { craftingApi, inventoryApi } from '../api/game'
import { useAuthStore } from '../store/authStore'
import { useGameStore } from '../store/gameStore'
import { RarityBadge } from '../components/hero/RarityBadge'
import { ItemImage } from '../components/ui/ItemImage'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import type { Enchantment, Item } from '../types'

type Material = { name: string; slug: string; description: string; quantity: number }
type Recipe = {
  id: number; name: string; description: string; gold_cost: number
  ingredients: Array<{ slug: string; qty: number }>
  result_name: string; result_rarity: string; result_slot: string | null; result_type: string
}

const RARITY_COLORS: Record<string, string> = {
  commun: '#9ca3af', peu_commun: '#4ade80', rare: '#60a5fa',
  epique: '#a78bfa', legendaire: '#fbbf24', wtf: '#f472b6',
}

const MATERIAL_ICONS: Record<string, string> = {
  ferraille:      '⚙️',
  essence_magique:'✨',
  bout_de_ficelle:'🧵',
}

const TIER_CONFIG: Record<string, { label: string; color: string; bg: string }> = {
  base:       { label: 'Base',       color: '#94a3b8', bg: '#1e293b' },
  avance:     { label: 'Avancé',     color: '#c4b5fd', bg: '#1e1b4b' },
  elementaire:{ label: 'Élémentaire',color: '#fbbf24', bg: '#1a0d00' },
}

export function ForgePage() {
  const { updateUser } = useAuthStore()
  const { setGold } = useGameStore()
  const [materials, setMaterials] = useState<Material[]>([])
  const [recipes, setRecipes] = useState<Recipe[]>([])
  const [items, setItems] = useState<Item[]>([])
  const [equippedItems, setEquippedItems] = useState<Item[]>([])
  const [enchantments, setEnchantments] = useState<Enchantment[]>([])
  const [loading, setLoading] = useState(true)
  const [tab, setTab] = useState<'fusion' | 'dismantle' | 'recipes' | 'enchant' | 'repair'>('fusion')
  const [selectedFusion, setSelectedFusion] = useState<number[]>([])
  const [enchantTarget, setEnchantTarget] = useState<number | null>(null)
  const [result, setResult] = useState<any>(null)
  const [acting, setActing] = useState(false)
  const [repairing, setRepairing] = useState<number | 'all' | null>(null)

  useEffect(() => { loadAll() }, [])

  async function loadAll() {
    try {
      const [craftData, invData, enchData] = await Promise.all([
        craftingApi.get(), inventoryApi.list(), craftingApi.enchantments()
      ])
      setMaterials(craftData.data.materials)
      setRecipes(craftData.data.recipes)
      setItems(invData.data.unequipped)
      setEquippedItems(invData.data.equipped)
      setEnchantments(enchData.data.enchantments)
    } catch { /* ok */ }
    setLoading(false)
  }

  async function doFuse() {
    if (selectedFusion.length !== 3 || acting) return
    setActing(true); setResult(null)
    try {
      const { data } = await craftingApi.fuse(selectedFusion)
      setResult(data); setSelectedFusion([])
      await loadAll()
    } catch (e: any) { alert(e.response?.data?.message ?? 'Erreur') }
    setActing(false)
  }

  async function doDismantle(itemId: number) {
    if (acting) return
    if (!confirm('Démonter cet objet ? Il sera détruit.')) return
    setActing(true); setResult(null)
    try {
      const { data } = await craftingApi.dismantle(itemId)
      setResult(data); await loadAll()
    } catch (e: any) { alert(e.response?.data?.message ?? 'Erreur') }
    setActing(false)
  }

  async function doCraft(recipeId: number) {
    if (acting) return
    setActing(true); setResult(null)
    try {
      const { data } = await craftingApi.craft(recipeId)
      setResult(data); await loadAll()
    } catch (e: any) { alert(e.response?.data?.message ?? 'Erreur') }
    setActing(false)
  }

  async function doEnchant(enchantSlug: string) {
    if (!enchantTarget || acting) return
    setActing(true); setResult(null)
    try {
      const { data } = await craftingApi.enchant(enchantTarget, enchantSlug)
      setResult({ ...data, success: true })
      setEnchantTarget(null)
      await loadAll()
    } catch (e: any) {
      setResult({ success: false, message: e.response?.data?.message ?? 'Erreur' })
    }
    setActing(false)
  }

  function toggleFusion(itemId: number) {
    setSelectedFusion(prev =>
      prev.includes(itemId) ? prev.filter(i => i !== itemId)
        : prev.length < 3 ? [...prev, itemId] : prev
    )
  }

  async function doRepair(itemId: number) {
    setRepairing(itemId)
    try {
      const { data } = await inventoryApi.repair(itemId)
      const patch = (prev: Item[]) => prev.map(i =>
        i.id === itemId ? { ...i, durability_current: data.durability_current } : i
      )
      setItems(patch)
      setEquippedItems(patch)
      setGold(data.new_gold)
      updateUser({ gold: data.new_gold })
      setResult({ success: true, message: data.message })
    } catch (e: any) {
      setResult({ success: false, message: e.response?.data?.message ?? 'Erreur lors de la réparation' })
    }
    setRepairing(null)
  }

  async function doRepairAll() {
    setRepairing('all')
    try {
      const { data } = await inventoryApi.repairAll()
      setGold(data.new_gold)
      updateUser({ gold: data.new_gold })
      setResult({ success: true, message: data.message })
      await loadAll()
    } catch (e: any) {
      setResult({ success: false, message: e.response?.data?.message ?? 'Erreur lors de la réparation' })
    }
    setRepairing(null)
  }

  const enchantableItems = items.filter(i => ['rare', 'epique', 'legendaire', 'wtf'].includes(i.rarity))
  const damagedItems = [...equippedItems, ...items].filter(
    i => (i.durability_max ?? 0) > 0 && (i.durability_max ?? 0) < 999 && (i.durability_current ?? 0) < (i.durability_max ?? 0)
  )

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Gérard allume sa forge…</div>
      </div>
    )
  }

  return (
    <div className="page-bg-forge">
      {/* Header */}
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>⚒️ Forge de Gérard</h1>
        <p className="flavor-text" style={{ margin: 0 }}>
          « Bienvenue dans ma forge ! Je fais de mon mieux. C'est déjà quelque chose. »
        </p>
      </div>

      {/* Craft Result */}
      {result && (
        <GamePanel
          icon={result.success ? '✅' : '❌'}
          title={result.success ? (result.is_critical ? '💥 Fusion critique !' : 'Réussi !') : 'Raté...'}
          variant={result.success ? 'success' : 'danger'}
          style={{ marginBottom: 20 }}
          className="anim-slide-in"
        >
          {(result.gerard_comment || result.message) && (
            <p className="flavor-text" style={{ marginBottom: 10 }}>
              Gérard : « {result.gerard_comment ?? result.message} »
            </p>
          )}
          {result.result_item && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, background: '#0d1117', borderRadius: 8, padding: '10px 12px', marginBottom: 8 }}>
              <ItemImage slot={result.result_item.slot ?? 'arme'} rarity={result.result_item.rarity} imageUrl={result.result_item.image_url} size={48} />
              <div>
                <div style={{ color: RARITY_COLORS[result.result_item.rarity] ?? '#f9fafb', fontWeight: 700, fontSize: 14 }}>
                  {result.result_item.name}
                </div>
                <RarityBadge rarity={result.result_item.rarity} />
              </div>
              {result.gold_spent && (
                <span style={{ marginLeft: 'auto', color: '#fbbf24', fontSize: 13 }}>-{result.gold_spent} 💰</span>
              )}
            </div>
          )}
          {result.enchantment && (
            <div style={{ color: '#c084fc', fontSize: 13 }}>✨ Enchantement appliqué : <strong>{result.enchantment}</strong></div>
          )}
          {result.materials && (
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginTop: 8 }}>
              {result.materials.map((m: any, i: number) => (
                <span key={i} style={{ background: '#1a0733', border: '1px solid #4c1d95', borderRadius: 6, padding: '2px 8px', fontSize: 12, color: '#a78bfa' }}>
                  {MATERIAL_ICONS[m.slug] ?? '🔩'} +{m.qty} {m.slug.replace(/_/g, ' ')}
                </span>
              ))}
            </div>
          )}
          <div style={{ marginTop: 10 }}>
            <GameButton variant="ghost" size="sm" onClick={() => setResult(null)}>Fermer ✕</GameButton>
          </div>
        </GamePanel>
      )}

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 220px', gap: 20 }}>
        {/* Main crafting area */}
        <div>
          {/* Tabs */}
          <div className="game-tabs">
            {(['fusion', 'dismantle', 'recipes', 'enchant', 'repair'] as const).map(t => (
              <button
                key={t}
                className={`game-tab${tab === t ? ' active' : ''}${t === 'repair' && damagedItems.length > 0 ? ' game-tab-alert' : ''}`}
                onClick={() => setTab(t)}
              >
                {t === 'fusion' ? '🔥 Fusion' : t === 'dismantle' ? '🔨 Démontage' : t === 'recipes' ? '📖 Recettes' : t === 'enchant' ? '✨ Enchantement' : `🔧 Réparation${damagedItems.length > 0 ? ` (${damagedItems.length})` : ''}`}
              </button>
            ))}
          </div>

          {/* Fusion tab */}
          {tab === 'fusion' && (
            <div>
              {/* Visual slot area */}
              <div style={{ display: 'flex', alignItems: 'center', gap: 16, background: '#0d1117', border: '1px solid #1f2937', borderRadius: 10, padding: 16, marginBottom: 16 }}>
                {[0, 1, 2].map(i => {
                  const itemId = selectedFusion[i]
                  const item = itemId ? items.find(it => it.id === itemId) : null
                  return (
                    <div key={i}>
                      {item ? (
                        <div style={{ position: 'relative', cursor: 'pointer' }} onClick={() => toggleFusion(itemId!)}>
                          <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={56} name={item.name} />
                          <div style={{ fontSize: 10, color: RARITY_COLORS[item.rarity], textAlign: 'center', marginTop: 3, maxWidth: 56, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            {item.name}
                          </div>
                          <div style={{ position: 'absolute', top: -4, right: -4, background: '#7f1d1d', borderRadius: '50%', width: 16, height: 16, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 10, color: 'white', cursor: 'pointer' }}>
                            ×
                          </div>
                        </div>
                      ) : (
                        <div style={{ width: 56, height: 56, border: '2px dashed #2d3748', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#4b5563', fontSize: 22 }}>
                          +
                        </div>
                      )}
                    </div>
                  )
                })}
                <div style={{ color: '#6b7280', fontSize: 24 }}>→</div>
                <div style={{ width: 56, height: 56, border: '2px dashed #4c1d95', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 24, opacity: selectedFusion.length === 3 ? 1 : 0.3 }}>
                  {selectedFusion.length === 3 ? '⭐' : '?'}
                </div>
                <GameButton
                  variant="primary"
                  icon="🔥"
                  onClick={doFuse}
                  disabled={selectedFusion.length !== 3}
                  loading={acting}
                >
                  Fusionner
                </GameButton>
              </div>

              <p style={{ color: '#6b7280', fontSize: 12, marginBottom: 12, fontStyle: 'italic' }}>
                Sélectionnez 3 objets de même rareté. Résultat : rareté supérieure (85% de réussite).
              </p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                {items.length === 0 && <div style={{ color: '#4b5563', fontStyle: 'italic', textAlign: 'center', padding: 20 }}>Aucun objet disponible.</div>}
                {items.map(item => (
                  <div
                    key={item.id}
                    onClick={() => toggleFusion(item.id)}
                    style={{
                      background: selectedFusion.includes(item.id) ? '#12122a' : '#0d1117',
                      border: `1px solid ${selectedFusion.includes(item.id) ? '#7c3aed' : '#1f2937'}`,
                      borderRadius: 8, padding: '8px 12px', cursor: 'pointer',
                      display: 'flex', alignItems: 'center', gap: 10,
                    }}
                  >
                    <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={36} name={item.name} />
                    <div style={{ flex: 1 }}>
                      <div style={{ color: RARITY_COLORS[item.rarity], fontWeight: 600, fontSize: 13 }}>{item.name}</div>
                      <div style={{ color: '#6b7280', fontSize: 11 }}>Niv.{item.item_level} · {item.slot}</div>
                    </div>
                    <RarityBadge rarity={item.rarity} />
                    {selectedFusion.includes(item.id) && <span style={{ color: '#7c3aed', fontSize: 16 }}>✓</span>}
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Dismantle tab */}
          {tab === 'dismantle' && (
            <div>
              <p style={{ color: '#6b7280', fontSize: 12, marginBottom: 12, fontStyle: 'italic' }}>
                Démontez un objet pour récupérer des matériaux. L'objet sera détruit définitivement.
              </p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                {items.length === 0 && <div style={{ color: '#4b5563', fontStyle: 'italic', textAlign: 'center', padding: 20 }}>Aucun objet disponible.</div>}
                {items.map(item => (
                  <div key={item.id} style={{
                    background: '#0d1117', border: '1px solid #1f2937',
                    borderRadius: 8, padding: '8px 12px',
                    display: 'flex', alignItems: 'center', gap: 10,
                  }}>
                    <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={36} name={item.name} />
                    <div style={{ flex: 1 }}>
                      <div style={{ color: RARITY_COLORS[item.rarity], fontWeight: 600, fontSize: 13 }}>{item.name}</div>
                      <div style={{ color: '#6b7280', fontSize: 11 }}>Niv.{item.item_level} · {item.slot}</div>
                    </div>
                    <RarityBadge rarity={item.rarity} />
                    <GameButton variant="danger" size="sm" onClick={() => doDismantle(item.id)} loading={acting}>
                      🔨
                    </GameButton>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Recipes tab */}
          {tab === 'recipes' && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              {recipes.length === 0 && (
                <div style={{ color: '#4b5563', fontStyle: 'italic', textAlign: 'center', padding: 32 }}>
                  <div style={{ fontSize: 40, marginBottom: 8 }}>📖</div>
                  <p style={{ margin: 0 }}>Aucune recette connue. Continuez à crafter.</p>
                </div>
              )}
              {recipes.map(recipe => (
                <GamePanel key={recipe.id} variant="magic" noPadding>
                  <div style={{ padding: '12px 14px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 6 }}>
                      <h3 className="game-title" style={{ margin: 0, fontSize: 14, color: '#f9fafb' }}>{recipe.name}</h3>
                      <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        {recipe.gold_cost > 0 && <span style={{ color: '#fbbf24', fontSize: 12 }}>💰 {recipe.gold_cost}</span>}
                        <RarityBadge rarity={recipe.result_rarity} />
                      </div>
                    </div>
                    <p style={{ color: '#9ca3af', fontSize: 12, margin: '0 0 8px', fontStyle: 'italic' }}>{recipe.description}</p>
                    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 10 }}>
                      {recipe.ingredients.map((ing, i) => (
                        <span key={i} style={{ background: '#0d1117', border: '1px solid #4c1d95', borderRadius: 4, padding: '2px 7px', fontSize: 11, color: '#a78bfa' }}>
                          {MATERIAL_ICONS[ing.slug] ?? '🔩'} {ing.qty}× {ing.slug.replace(/_/g, ' ')}
                        </span>
                      ))}
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ color: '#6b7280', fontSize: 12 }}>→ {recipe.result_name}</span>
                      <GameButton variant="gold" size="sm" icon="⚒️" onClick={() => doCraft(recipe.id)} loading={acting}>
                        Fabriquer
                      </GameButton>
                    </div>
                  </div>
                </GamePanel>
              ))}
            </div>
          )}

          {/* Repair tab */}
          {tab === 'repair' && (
            <div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                <p style={{ color: '#6b7280', fontSize: 12, margin: 0, fontStyle: 'italic' }}>
                  Coût : (max − actuel) × niveau × 2 or. Gérard fait des prix d'ami.
                </p>
                {damagedItems.length > 1 && (
                  <GameButton variant="secondary" size="sm" icon="🔧" onClick={doRepairAll} loading={repairing === 'all'}>
                    Tout réparer
                  </GameButton>
                )}
              </div>

              {damagedItems.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '40px 0', color: '#4b5563' }}>
                  <div style={{ fontSize: 40, marginBottom: 8 }}>✅</div>
                  <p style={{ margin: 0, fontSize: 13, fontStyle: 'italic' }}>Tous vos objets sont en parfait état.</p>
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {damagedItems.map(item => {
                    const cur = item.durability_current ?? 0
                    const max = item.durability_max ?? 0
                    const pct = Math.round((cur / max) * 100)
                    const barColor = pct <= 20 ? '#ef4444' : pct <= 50 ? '#f97316' : '#22c55e'
                    const repairCost = (max - cur) * item.item_level * 2
                    const isEquipped = item.equipped_by_hero_id !== null
                    return (
                      <div key={item.id} style={{
                        background: '#0d1117', border: '1px solid #1f2937',
                        borderRadius: 8, padding: '10px 12px',
                        display: 'flex', alignItems: 'center', gap: 12,
                      }}>
                        <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={40} name={item.name} />
                        <div style={{ flex: 1, minWidth: 0 }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4 }}>
                            <span style={{ color: RARITY_COLORS[item.rarity], fontWeight: 600, fontSize: 13 }}>{item.name}</span>
                            {isEquipped && (
                              <span style={{ background: '#1e3a5f', color: '#60a5fa', fontSize: 10, padding: '1px 5px', borderRadius: 3, border: '1px solid #1e40af' }}>
                                Équipé
                              </span>
                            )}
                          </div>
                          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 3 }}>
                            <span style={{ color: '#6b7280', fontSize: 11 }}>Niv.{item.item_level} · Durabilité</span>
                            <span style={{ color: barColor, fontSize: 11, fontWeight: 700 }}>{cur} / {max}</span>
                          </div>
                          <div style={{ height: 6, background: '#1f2937', borderRadius: 3, overflow: 'hidden', border: '1px solid #374151' }}>
                            <div style={{ width: `${pct}%`, height: '100%', background: barColor, borderRadius: 3, transition: 'width 0.3s' }} />
                          </div>
                        </div>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 4, flexShrink: 0 }}>
                          <span style={{ color: '#fbbf24', fontSize: 12, fontWeight: 600 }}>💰 {repairCost}</span>
                          <GameButton
                            variant="secondary"
                            size="sm"
                            onClick={() => doRepair(item.id)}
                            loading={repairing === item.id}
                          >
                            🔧 Réparer
                          </GameButton>
                        </div>
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          )}

          {/* Enchantment tab */}
          {tab === 'enchant' && (
            <div>
              <p style={{ color: '#6b7280', fontSize: 12, marginBottom: 16, fontStyle: 'italic' }}>
                Enchantez un objet <strong>Rare ou supérieur</strong>. Max 2 enchantements par objet.
              </p>

              <div style={{ marginBottom: 20 }}>
                <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 8, textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'var(--font-title)' }}>
                  1. Choisir l'objet
                </div>
                {enchantableItems.length === 0 && <div style={{ color: '#4b5563', fontSize: 13, fontStyle: 'italic' }}>Aucun objet Rare+ en inventaire.</div>}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 5 }}>
                  {enchantableItems.map(item => (
                    <div
                      key={item.id}
                      onClick={() => setEnchantTarget(item.id === enchantTarget ? null : item.id)}
                      style={{
                        background: enchantTarget === item.id ? '#12122a' : '#0d1117',
                        border: `1px solid ${enchantTarget === item.id ? '#7c3aed' : '#1f2937'}`,
                        borderRadius: 8, padding: '8px 12px', cursor: 'pointer',
                        display: 'flex', alignItems: 'center', gap: 10,
                      }}
                    >
                      <ItemImage slot={item.slot} rarity={item.rarity} imageUrl={item.image_url} size={36} name={item.name} />
                      <div style={{ flex: 1 }}>
                        <div style={{ color: RARITY_COLORS[item.rarity], fontWeight: 600, fontSize: 13 }}>{item.name}</div>
                        <div style={{ color: '#6b7280', fontSize: 11 }}>Niv.{item.item_level} · {item.slot}</div>
                      </div>
                      <RarityBadge rarity={item.rarity} />
                      {(item as any).enchant_count > 0 && (
                        <span style={{ color: '#a78bfa', fontSize: 11 }}>✨×{(item as any).enchant_count}</span>
                      )}
                    </div>
                  ))}
                </div>
              </div>

              {enchantTarget && (
                <div>
                  <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 10, textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'var(--font-title)' }}>
                    2. Choisir l'enchantement
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {enchantments.map(ench => {
                      const matMap = Object.fromEntries(materials.map(m => [m.slug, m.quantity]))
                      const canAfford = ench.materials.every(m => (matMap[m.slug] ?? 0) >= m.qty)
                      const tier = TIER_CONFIG[ench.tier] ?? TIER_CONFIG.base
                      return (
                        <GamePanel key={ench.slug} variant="magic" noPadding style={{ opacity: canAfford ? 1 : 0.5 }}>
                          <div style={{ padding: '10px 14px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 5 }}>
                              <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span style={{ color: '#f9fafb', fontWeight: 700, fontSize: 13 }}>{ench.name}</span>
                                <span style={{ background: tier.bg, color: tier.color, fontSize: 10, padding: '1px 6px', borderRadius: 4, border: '1px solid currentColor' }}>
                                  {tier.label}
                                </span>
                              </div>
                              <span style={{ color: '#fbbf24', fontSize: 12 }}>💰 {ench.gold_cost.toLocaleString()}</span>
                            </div>
                            <p style={{ color: '#9ca3af', fontSize: 12, margin: '0 0 7px', fontStyle: 'italic' }}>{ench.description}</p>
                            <div style={{ display: 'flex', gap: 5, flexWrap: 'wrap', marginBottom: 7 }}>
                              {ench.materials.map((m, i) => {
                                const have = matMap[m.slug] ?? 0
                                return (
                                  <span key={i} style={{ background: '#0d1117', border: `1px solid ${have >= m.qty ? '#4c1d95' : '#7f1d1d'}`, borderRadius: 4, padding: '2px 6px', fontSize: 11, color: have >= m.qty ? '#a78bfa' : '#fca5a5' }}>
                                    {m.qty}× {m.slug.replace(/_/g, ' ')} ({have})
                                  </span>
                                )
                              })}
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                              <span style={{ color: '#6b7280', fontSize: 11, fontStyle: 'italic' }}>{ench.gerard_comment}</span>
                              <GameButton variant="primary" size="sm" icon="✨" onClick={() => doEnchant(ench.slug)} disabled={!canAfford} loading={acting}>
                                Enchanter
                              </GameButton>
                            </div>
                          </div>
                        </GamePanel>
                      )
                    })}
                  </div>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Materials sidebar */}
        <div>
          <GamePanel icon="🔩" title="Matériaux" variant="default" noPadding>
            <div style={{ padding: '12px 12px' }}>
              {materials.length === 0 ? (
                <div style={{ color: '#4b5563', fontSize: 12, fontStyle: 'italic', textAlign: 'center', padding: '12px 0' }}>
                  Démontez des objets pour obtenir des matériaux.
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 5 }}>
                  {materials.map(m => (
                    <div key={m.slug} style={{
                      background: '#0d1117', borderRadius: 6, padding: '7px 10px',
                      display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                      border: '1px solid #1a1f2e',
                    }}>
                      <span style={{ color: '#d1d5db', fontSize: 12, display: 'flex', alignItems: 'center', gap: 6 }}>
                        {MATERIAL_ICONS[m.slug] ?? '🔩'} {m.name}
                      </span>
                      <span style={{ color: '#a78bfa', fontWeight: 700, fontSize: 14 }}>{m.quantity}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </GamePanel>
        </div>
      </div>
    </div>
  )
}
