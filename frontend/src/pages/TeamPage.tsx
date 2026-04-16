import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { heroApi } from '../api/game'
import apiClient from '../api/client'
import { useGameStore } from '../store/gameStore'
import { HeroCard } from '../components/hero/HeroCard'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { HeroPortrait } from '../components/ui/HeroPortrait'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'
import type { Hero, Race, GameClass, Trait } from '../types'

type Synergy = { hero_name: string; slug: string; name: string; description: string }

const selectStyle = {
  width: '100%', background: '#0d1117', border: '1px solid #2d3748',
  borderRadius: 6, padding: '9px 12px', color: '#f9fafb', fontSize: 13,
}
const inputStyle = { ...selectStyle }
const labelStyle = { display: 'block' as const, color: '#9ca3af', marginBottom: 6, fontSize: 12, textTransform: 'uppercase' as const, letterSpacing: '0.08em', fontFamily: 'var(--font-title)' }

export function TeamPage() {
  const { setHeroes } = useGameStore()
  const navigate = useNavigate()
  const [heroes, setLocalHeroes] = useState<Hero[]>([])
  const [races, setRaces] = useState<Race[]>([])
  const [classes, setClasses] = useState<GameClass[]>([])
  const [traits, setTraits] = useState<Trait[]>([])
  const [loading, setLoading] = useState(true)
  const [synergies, setSynergies] = useState<Synergy[]>([])
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [form, setForm] = useState({ name: '', race_id: 0, class_id: 0, trait_id: 0 })
  const [narratorComment, setNarratorComment] = useState('')
  const [creating, setCreating] = useState(false)
  const [dismissing, setDismissing] = useState<number | null>(null)
  const [confirmDismiss, setConfirmDismiss] = useState<number | null>(null)
  const [error, setError] = useState('')

  // Preview: get class slug for portrait preview
  const previewClass = classes.find(c => c.id === form.class_id)

  useEffect(() => {
    Promise.all([
      heroApi.list(),
      apiClient.get('/reference/races'),
      apiClient.get('/reference/classes'),
      apiClient.get('/reference/traits'),
      apiClient.get('/heroes/synergies'),
    ]).then(([heroRes, raceRes, classRes, traitRes, synRes]) => {
      const loadedHeroes = heroRes.data.heroes ?? []
      setLocalHeroes(loadedHeroes)
      setHeroes(loadedHeroes)
      setRaces(raceRes.data.races ?? [])
      setClasses(classRes.data.classes ?? [])
      setTraits(traitRes.data.traits ?? [])
      setSynergies(synRes.data.active_synergies ?? [])
      if (loadedHeroes.length === 0) setShowCreateForm(true)
    }).finally(() => setLoading(false))
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setCreating(true)
    try {
      const { data } = await heroApi.create(form)
      setLocalHeroes((prev) => [...prev, data.hero])
      setHeroes([...heroes, data.hero])
      setNarratorComment(data.narrator_comment)
      setShowCreateForm(false)
      setForm({ name: '', race_id: 0, class_id: 0, trait_id: 0 })
    } catch (err: any) {
      const errors = err.response?.data?.errors
      setError(errors ? Object.values(errors).flat().join(', ') : err.response?.data?.message)
    } finally {
      setCreating(false)
    }
  }

  const handleDismiss = async (heroId: number) => {
    setDismissing(heroId)
    setError('')
    try {
      const { data } = await apiClient.delete(`/heroes/${heroId}`)
      const updated = heroes.filter((h) => h.id !== heroId)
      setLocalHeroes(updated)
      setHeroes(updated)
      setNarratorComment(data.narrator_comment)
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erreur lors du renvoi.')
    } finally {
      setDismissing(null)
      setConfirmDismiss(null)
    }
  }

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">Rassemblement de l'équipe…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
        <div>
          <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>⚔️ Mon Équipe</h1>
          <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>{heroes.length}/5 héros</p>
        </div>
        {heroes.length === 0 ? (
          <GameButton variant="primary" icon="+" onClick={() => setShowCreateForm(!showCreateForm)}>
            Créer mon premier héros
          </GameButton>
        ) : (
          <GameButton variant="ghost" icon="🍺" onClick={() => navigate('/tavern')}>
            Recruter à la Taverne
          </GameButton>
        )}
      </div>

      {narratorComment && <NarratorBubble comment={narratorComment} />}

      {error && (
        <div style={{ color: '#fca5a5', background: '#1a0505', border: '1px solid #7f1d1d', padding: '8px 12px', borderRadius: 6, marginBottom: 12, fontSize: 13 }}>
          ⚠ {error}
        </div>
      )}

      {/* Synergies */}
      {synergies.length > 0 && (
        <GamePanel icon="✨" title="Synergies Actives" variant="success" style={{ marginBottom: 20 }}>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {synergies.map((s) => (
              <div key={s.slug} style={{ background: '#0a1a0a', borderRadius: 6, padding: '8px 10px', border: '1px solid #14532d' }}>
                <div style={{ color: '#4ade80', fontSize: 12, fontWeight: 700 }}>{s.hero_name} — {s.name}</div>
                <div style={{ color: '#6b7280', fontSize: 11, marginTop: 2 }}>{s.description}</div>
              </div>
            ))}
          </div>
        </GamePanel>
      )}

      {/* Create Form */}
      {showCreateForm && (
        <GamePanel icon="⚔️" title="Créer un Héros" variant="magic" style={{ marginBottom: 24 }} className="anim-slide-in">
          <div style={{ display: 'flex', gap: 20 }}>
            {/* Portrait preview */}
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, flexShrink: 0 }}>
              <HeroPortrait
                classSlug={previewClass?.slug ?? 'guerrier'}
                size={90}
                animClass="anim-breathe"
              />
              <div style={{ color: '#6b7280', fontSize: 11, textAlign: 'center' }}>
                {form.name || 'Votre héros'}
              </div>
            </div>
            {/* Form fields */}
            <form onSubmit={handleCreate} style={{ flex: 1 }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14, marginBottom: 16 }}>
                <div>
                  <label style={labelStyle}>Nom du héros</label>
                  <input
                    type="text"
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                    required
                    style={inputStyle}
                    placeholder="Gruntak le Magnifique"
                  />
                </div>
                <div>
                  <label style={labelStyle}>Race</label>
                  <select value={form.race_id} onChange={(e) => setForm({ ...form, race_id: +e.target.value })} required style={selectStyle}>
                    <option value={0}>— Choisir une race —</option>
                    {races.map((r) => <option key={r.id} value={r.id}>{r.name} — {r.passive_bonus_description}</option>)}
                  </select>
                </div>
                <div>
                  <label style={labelStyle}>Classe</label>
                  <select value={form.class_id} onChange={(e) => setForm({ ...form, class_id: +e.target.value })} required style={selectStyle}>
                    <option value={0}>— Choisir une classe —</option>
                    {classes.map((c) => <option key={c.id} value={c.id}>{c.name} ({c.role})</option>)}
                  </select>
                </div>
                <div>
                  <label style={labelStyle}>Trait Négatif</label>
                  <select value={form.trait_id} onChange={(e) => setForm({ ...form, trait_id: +e.target.value })} required style={selectStyle}>
                    <option value={0}>— Choisir un trait —</option>
                    {traits.map((t) => <option key={t.id} value={t.id}>{t.name} — {t.description}</option>)}
                  </select>
                </div>
              </div>
              <div style={{ display: 'flex', gap: 10 }}>
                <GameButton type="submit" variant="primary" loading={creating} icon="⚔️">
                  Créer le héros
                </GameButton>
                <GameButton type="button" variant="ghost" onClick={() => setShowCreateForm(false)}>
                  Annuler
                </GameButton>
              </div>
            </form>
          </div>
        </GamePanel>
      )}

      {/* Heroes grid */}
      {heroes.length === 0 ? (
        <GamePanel variant="default" style={{ textAlign: 'center', padding: '60px 20px' }}>
          <div style={{ fontSize: 48, marginBottom: 12 }}>🏚️</div>
          <p style={{ color: '#6b7280', fontStyle: 'italic', margin: 0 }}>
            Aucun héros. Le Narrateur hausse les épaules.
          </p>
        </GamePanel>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16 }}>
          {heroes.map((hero) => (
            <div key={hero.id}>
              <HeroCard hero={hero} />
              <div style={{ marginTop: 6 }}>
                {confirmDismiss === hero.id ? (
                  <div style={{ background: '#0a0202', border: '1px solid #7f1d1d', borderRadius: 6, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 8, justifyContent: 'space-between' }}>
                    <span style={{ color: '#fca5a5', fontSize: 12 }}>Renvoyer {hero.name} définitivement ?</span>
                    <div style={{ display: 'flex', gap: 6 }}>
                      <GameButton size="sm" variant="danger" onClick={() => handleDismiss(hero.id)} loading={dismissing === hero.id}>
                        Oui
                      </GameButton>
                      <GameButton size="sm" variant="ghost" onClick={() => setConfirmDismiss(null)}>
                        Non
                      </GameButton>
                    </div>
                  </div>
                ) : (
                  <GameButton variant="ghost" size="sm" style={{ width: '100%', justifyContent: 'center' }} onClick={() => setConfirmDismiss(hero.id)}>
                    Renvoyer
                  </GameButton>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
