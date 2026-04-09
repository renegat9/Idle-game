import { useEffect, useState } from 'react'
import { craftingApi, inventoryApi } from '../api/game'
import { RarityBadge } from '../components/hero/RarityBadge'
import type { Enchantment, Item } from '../types'

type Material = { name: string; slug: string; description: string; quantity: number }
type Recipe = {
  id: number; name: string; description: string; gold_cost: number
  ingredients: Array<{ slug: string; qty: number }>
  result_name: string; result_rarity: string; result_slot: string | null; result_type: string
}

const rarityColors: Record<string, string> = {
  commun: '#6b7280', peu_commun: '#22c55e', rare: '#3b82f6',
  epique: '#a855f7', legendaire: '#f59e0b', wtf: '#ec4899'
}

const tierLabels: Record<string, string> = { base: 'Base', avance: 'Avancé', elementaire: 'Élémentaire' }
const tierColors: Record<string, string> = { base: '#64748b', avance: '#7c3aed', elementaire: '#f59e0b' }

export function ForgePage() {
  const [materials, setMaterials] = useState<Material[]>([])
  const [recipes, setRecipes] = useState<Recipe[]>([])
  const [items, setItems] = useState<Item[]>([])
  const [enchantments, setEnchantments] = useState<Enchantment[]>([])
  const [loading, setLoading] = useState(true)
  const [tab, setTab] = useState<'fusion' | 'dismantle' | 'recipes' | 'enchant'>('fusion')
  const [selectedFusion, setSelectedFusion] = useState<number[]>([])
  const [enchantTarget, setEnchantTarget] = useState<number | null>(null)
  const [result, setResult] = useState<any>(null)
  const [acting, setActing] = useState(false)

  useEffect(() => { loadAll() }, [])

  async function loadAll() {
    try {
      const [craftData, invData, enchData] = await Promise.all([
        craftingApi.get(), inventoryApi.list(), craftingApi.enchantments()
      ])
      setMaterials(craftData.data.materials)
      setRecipes(craftData.data.recipes)
      setItems(invData.data.unequipped)
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

  // Only Rare+ items can be enchanted
  const enchantableItems = items.filter(i => ['rare', 'epique', 'legendaire', 'wtf'].includes(i.rarity))

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement de la forge...</div>

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>⚒️ Forge de Gérard</h1>
      <p style={{ color: '#6b7280', marginBottom: 24, fontSize: 14 }}>
        "Bienvenue dans ma forge ! Je fais de mon mieux. C'est déjà quelque chose."
      </p>

      {/* Result */}
      {result && (
        <div style={{ background: result.success ? '#052e16' : '#1c0505', border: `1px solid ${result.success ? '#16a34a' : '#991b1b'}`, borderRadius: 12, padding: 16, marginBottom: 20 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
            <span>{result.success ? '✅' : '❌'}</span>
            <span style={{ color: result.success ? '#22c55e' : '#ef4444', fontWeight: 'bold' }}>
              {result.success ? (result.is_critical ? '💥 Fusion critique !' : result.message ?? 'Réussi !') : result.message ?? 'Raté...'}
            </span>
            {result.gold_spent && <span style={{ color: '#fbbf24', fontSize: 13 }}>-{result.gold_spent} 💰</span>}
          </div>
          {result.gerard_comment && <p style={{ color: '#94a3b8', fontStyle: 'italic', margin: '0 0 8px' }}>Gérard : "{result.gerard_comment}"</p>}
          {result.result_item && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <span style={{ color: '#f1f5f9' }}>Obtenu :</span>
              <span style={{ color: rarityColors[result.result_item.rarity] ?? '#f1f5f9', fontWeight: 'bold' }}>{result.result_item.name}</span>
              <RarityBadge rarity={result.result_item.rarity} />
            </div>
          )}
          {result.enchantment && (
            <div style={{ color: '#c084fc', fontSize: 13 }}>
              ✨ Enchantement appliqué : <strong>{result.enchantment}</strong>
            </div>
          )}
          {result.materials && (
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
              {result.materials.map((m: any, i: number) => (
                <span key={i} style={{ background: '#0f172a', border: '1px solid #334155', borderRadius: 6, padding: '2px 8px', fontSize: 12, color: '#a78bfa' }}>
                  +{m.qty} {m.slug}
                </span>
              ))}
            </div>
          )}
          <button onClick={() => setResult(null)} style={{ marginTop: 10, background: 'transparent', border: '1px solid #475569', color: '#6b7280', padding: '4px 12px', borderRadius: 6, cursor: 'pointer', fontSize: 12 }}>Fermer</button>
        </div>
      )}

      {/* Tab bar */}
      <div style={{ display: 'flex', gap: 4, marginBottom: 20 }}>
        {(['fusion', 'dismantle', 'recipes', 'enchant'] as const).map(t => (
          <button key={t} onClick={() => setTab(t)} style={{ background: tab === t ? '#7c3aed' : '#1e293b', color: tab === t ? 'white' : '#94a3b8', border: 'none', padding: '8px 18px', borderRadius: 8, cursor: 'pointer', fontSize: 14 }}>
            {t === 'fusion' ? '🔥 Fusion' : t === 'dismantle' ? '🔨 Démontage' : t === 'recipes' ? '📖 Recettes' : '✨ Enchantement'}
          </button>
        ))}
      </div>

      {/* Grid: content + materials */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 220px', gap: 20 }}>
        <div>
          {/* Fusion tab */}
          {tab === 'fusion' && (
            <div>
              <p style={{ color: '#94a3b8', fontSize: 14, marginBottom: 16 }}>
                Sélectionnez 3 objets <strong>de même rareté</strong> à fusionner. Résultat : 1 objet de rareté supérieure (85% de chance).
              </p>
              <div style={{ display: 'flex', gap: 8, marginBottom: 16, flexWrap: 'wrap' }}>
                {selectedFusion.map(id => {
                  const item = items.find(i => i.id === id)
                  return item ? (
                    <span key={id} style={{ background: '#7c3aed', color: 'white', padding: '4px 10px', borderRadius: 6, fontSize: 13 }}>
                      {item.name} <button onClick={() => toggleFusion(id)} style={{ background: 'none', border: 'none', color: 'white', cursor: 'pointer', padding: '0 0 0 4px' }}>×</button>
                    </span>
                  ) : null
                })}
                {selectedFusion.length < 3 && <span style={{ color: '#475569', fontSize: 13 }}>{3 - selectedFusion.length} objet(s) manquant(s)</span>}
              </div>
              <button
                onClick={doFuse}
                disabled={selectedFusion.length !== 3 || acting}
                style={{ background: '#7c3aed', color: 'white', border: 'none', padding: '10px 24px', borderRadius: 8, cursor: selectedFusion.length === 3 ? 'pointer' : 'not-allowed', opacity: selectedFusion.length === 3 ? 1 : 0.4, marginBottom: 20, fontSize: 14 }}
              >
                🔥 Fusionner
              </button>
              <div style={{ display: 'grid', gap: 8 }}>
                {items.length === 0 && <div style={{ color: '#6b7280' }}>Aucun objet en inventaire.</div>}
                {items.map(item => (
                  <div key={item.id} onClick={() => toggleFusion(item.id)} style={{ background: selectedFusion.includes(item.id) ? '#1e1b4b' : '#1e293b', border: `1px solid ${selectedFusion.includes(item.id) ? '#7c3aed' : '#334155'}`, borderRadius: 8, padding: '10px 14px', cursor: 'pointer', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div>
                      <span style={{ color: rarityColors[item.rarity] ?? '#f1f5f9', fontWeight: 'bold' }}>{item.name}</span>
                      <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 8 }}>Niv.{item.item_level} • {item.slot}</span>
                    </div>
                    <RarityBadge rarity={item.rarity} />
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Dismantle tab */}
          {tab === 'dismantle' && (
            <div>
              <p style={{ color: '#94a3b8', fontSize: 14, marginBottom: 16 }}>Démontez un objet pour récupérer des matériaux. L'objet sera détruit.</p>
              <div style={{ display: 'grid', gap: 8 }}>
                {items.length === 0 && <div style={{ color: '#6b7280' }}>Aucun objet en inventaire.</div>}
                {items.map(item => (
                  <div key={item.id} style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 8, padding: '10px 14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div>
                      <span style={{ color: rarityColors[item.rarity] ?? '#f1f5f9', fontWeight: 'bold' }}>{item.name}</span>
                      <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 8 }}>Niv.{item.item_level} • {item.slot}</span>
                    </div>
                    <button onClick={() => doDismantle(item.id)} style={{ background: '#7f1d1d', color: '#fca5a5', border: 'none', padding: '6px 12px', borderRadius: 6, cursor: 'pointer', fontSize: 12 }}>
                      Démonter
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Recipes tab */}
          {tab === 'recipes' && (
            <div style={{ display: 'grid', gap: 12 }}>
              {recipes.length === 0 && <div style={{ color: '#6b7280' }}>Aucune recette connue. Continuez à crafter pour en découvrir.</div>}
              {recipes.map(recipe => (
                <div key={recipe.id} style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 16 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                    <h3 style={{ color: '#f1f5f9', margin: 0 }}>{recipe.name}</h3>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                      {recipe.gold_cost > 0 && <span style={{ color: '#fbbf24', fontSize: 13 }}>{recipe.gold_cost} 💰</span>}
                      <RarityBadge rarity={recipe.result_rarity} />
                    </div>
                  </div>
                  <p style={{ color: '#94a3b8', fontSize: 13, margin: '0 0 10px' }}>{recipe.description}</p>
                  <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 10 }}>
                    {recipe.ingredients.map((ing, i) => (
                      <span key={i} style={{ background: '#0f172a', border: '1px solid #334155', borderRadius: 6, padding: '2px 8px', fontSize: 12, color: '#c084fc' }}>
                        {ing.qty}× {ing.slug.replace(/_/g, ' ')}
                      </span>
                    ))}
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <span style={{ color: '#6b7280', fontSize: 12 }}>→ {recipe.result_name}</span>
                    <button onClick={() => doCraft(recipe.id)} style={{ background: '#064e3b', color: '#6ee7b7', border: 'none', padding: '6px 14px', borderRadius: 6, cursor: 'pointer', fontSize: 13 }}>
                      ⚒️ Fabriquer
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Enchantment tab */}
          {tab === 'enchant' && (
            <div>
              <p style={{ color: '#94a3b8', fontSize: 14, marginBottom: 20 }}>
                Enchantez un objet <strong>Rare ou supérieur</strong>. Max 2 enchantements par objet (le dernier est remplacé si plein).
              </p>

              {/* Step 1: pick item */}
              <div style={{ marginBottom: 24 }}>
                <h3 style={{ color: '#f1f5f9', fontSize: 15, marginBottom: 12 }}>1. Choisir l'objet à enchanter</h3>
                {enchantableItems.length === 0 && (
                  <div style={{ color: '#6b7280', fontSize: 13 }}>Aucun objet Rare+ en inventaire.</div>
                )}
                <div style={{ display: 'grid', gap: 8 }}>
                  {enchantableItems.map(item => (
                    <div
                      key={item.id}
                      onClick={() => setEnchantTarget(item.id === enchantTarget ? null : item.id)}
                      style={{ background: enchantTarget === item.id ? '#1e1b4b' : '#1e293b', border: `1px solid ${enchantTarget === item.id ? '#7c3aed' : '#334155'}`, borderRadius: 8, padding: '10px 14px', cursor: 'pointer', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}
                    >
                      <div>
                        <span style={{ color: rarityColors[item.rarity], fontWeight: 'bold' }}>{item.name}</span>
                        <span style={{ color: '#6b7280', fontSize: 12, marginLeft: 8 }}>Niv.{item.item_level} • {item.slot}</span>
                        {(item as any).enchant_count > 0 && (
                          <span style={{ color: '#a78bfa', fontSize: 11, marginLeft: 8 }}>✨ ×{(item as any).enchant_count}</span>
                        )}
                      </div>
                      <RarityBadge rarity={item.rarity} />
                    </div>
                  ))}
                </div>
              </div>

              {/* Step 2: pick enchantment */}
              {enchantTarget && (
                <div>
                  <h3 style={{ color: '#f1f5f9', fontSize: 15, marginBottom: 12 }}>2. Choisir l'enchantement</h3>
                  <div style={{ display: 'grid', gap: 10 }}>
                    {enchantments.map(ench => {
                      const matMap = Object.fromEntries(materials.map(m => [m.slug, m.quantity]))
                      const canAfford = ench.materials.every(m => (matMap[m.slug] ?? 0) >= m.qty)
                      return (
                        <div key={ench.slug} style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 14, opacity: canAfford ? 1 : 0.5 }}>
                          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
                            <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                              <span style={{ color: '#f1f5f9', fontWeight: 'bold' }}>{ench.name}</span>
                              <span style={{ background: tierColors[ench.tier], color: 'white', fontSize: 10, padding: '1px 6px', borderRadius: 4 }}>
                                {tierLabels[ench.tier]}
                              </span>
                            </div>
                            <span style={{ color: '#fbbf24', fontSize: 13 }}>{ench.gold_cost.toLocaleString()} 💰</span>
                          </div>
                          <p style={{ color: '#94a3b8', fontSize: 13, margin: '0 0 8px' }}>{ench.description}</p>
                          <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 10 }}>
                            {ench.materials.map((m, i) => {
                              const have = matMap[m.slug] ?? 0
                              return (
                                <span key={i} style={{ background: '#0f172a', border: `1px solid ${have >= m.qty ? '#334155' : '#7f1d1d'}`, borderRadius: 6, padding: '2px 8px', fontSize: 11, color: have >= m.qty ? '#a78bfa' : '#fca5a5' }}>
                                  {m.qty}× {m.slug.replace(/_/g, ' ')} ({have} dispo)
                                </span>
                              )
                            })}
                          </div>
                          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <span style={{ color: '#6b7280', fontSize: 11, fontStyle: 'italic' }}>{ench.gerard_comment}</span>
                            <button
                              onClick={() => doEnchant(ench.slug)}
                              disabled={!canAfford || acting}
                              style={{ background: canAfford ? '#4c1d95' : '#1e293b', color: canAfford ? '#c4b5fd' : '#6b7280', border: 'none', padding: '6px 14px', borderRadius: 6, cursor: canAfford ? 'pointer' : 'not-allowed', fontSize: 13 }}
                            >
                              ✨ Enchanter
                            </button>
                          </div>
                        </div>
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
          <h3 style={{ color: '#94a3b8', fontSize: 14, marginBottom: 12 }}>Matériaux</h3>
          {materials.length === 0 && <div style={{ color: '#6b7280', fontSize: 13 }}>Aucun matériau. Démontez des objets.</div>}
          {materials.map(m => (
            <div key={m.slug} style={{ background: '#1e293b', borderRadius: 8, padding: '8px 12px', marginBottom: 6, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ color: '#e2e8f0', fontSize: 13 }}>{m.name}</span>
              <span style={{ color: '#a78bfa', fontWeight: 'bold', fontSize: 13 }}>{m.quantity}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
