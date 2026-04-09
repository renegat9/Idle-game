import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { heroApi } from '../api/game'
import apiClient from '../api/client'
import { useGameStore } from '../store/gameStore'
import { HeroCard } from '../components/hero/HeroCard'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import type { Hero, Race, GameClass, Trait } from '../types'

type Synergy = { hero_name: string; slug: string; name: string; description: string }

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

  useEffect(() => {
    Promise.all([
      heroApi.list(),
      apiClient.get('/reference/races'),
      apiClient.get('/reference/classes'),
      apiClient.get('/reference/traits'),
      apiClient.get('/heroes/synergies'),
    ]).then(([heroRes, raceRes, classRes, traitRes, synRes]) => {
      const loadedHeroes = heroRes.data.heroes
      setLocalHeroes(loadedHeroes)
      setHeroes(loadedHeroes)
      setRaces(raceRes.data.races ?? [])
      setClasses(classRes.data.classes ?? [])
      setTraits(traitRes.data.traits ?? [])
      setSynergies(synRes.data.active_synergies ?? [])
      // Ouvrir le formulaire automatiquement si aucun héros
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

  const selectStyle = { width: '100%', background: '#1f2937', border: '1px solid #374151', borderRadius: 6, padding: '8px 12px', color: '#f9fafb', fontSize: 14 }
  const inputStyle = { ...selectStyle }

  if (loading) return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
        <h1 style={{ color: '#f9fafb', margin: 0 }}>⚔️ Mon Équipe</h1>
        {heroes.length === 0 ? (
          <button
            onClick={() => setShowCreateForm(!showCreateForm)}
            style={{ background: '#7c3aed', color: 'white', border: 'none', borderRadius: 6, padding: '8px 16px', cursor: 'pointer' }}
          >
            + Créer mon premier héros
          </button>
        ) : (
          <button
            onClick={() => navigate('/tavern')}
            style={{ background: '#1f2937', color: '#d1d5db', border: '1px solid #374151', borderRadius: 6, padding: '8px 16px', cursor: 'pointer' }}
          >
            + Recruter à la Taverne
          </button>
        )}
      </div>

      {narratorComment && <NarratorBubble comment={narratorComment} />}
      {error && <div style={{ color: '#fca5a5', background: '#450a0a', padding: '8px 12px', borderRadius: 6, marginBottom: 12, fontSize: 13 }}>{error}</div>}

      {synergies.length > 0 && (
        <div style={{ background: '#0d1a0d', border: '1px solid #166534', borderRadius: 8, padding: '12px 16px', marginBottom: 20 }}>
          <div style={{ color: '#86efac', fontSize: 13, fontWeight: 'bold', marginBottom: 8 }}>✨ Synergies actives</div>
          {synergies.map((s) => (
            <div key={s.slug} style={{ marginBottom: 6 }}>
              <span style={{ color: '#4ade80', fontSize: 12, fontWeight: 'bold' }}>{s.hero_name} — {s.name}</span>
              <div style={{ color: '#6b7280', fontSize: 11, marginTop: 2 }}>{s.description}</div>
            </div>
          ))}
        </div>
      )}

      {showCreateForm && (
        <div style={{ background: '#111827', border: '1px solid #374151', borderRadius: 8, padding: 24, marginBottom: 24 }}>
          <h3 style={{ color: '#f9fafb', marginTop: 0 }}>Créer un Héros</h3>
          <form onSubmit={handleCreate}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, marginBottom: 16 }}>
              <div>
                <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Nom du héros</label>
                <input type="text" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required style={inputStyle} placeholder="Gruntak le Magnifique" />
              </div>
              <div>
                <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Race</label>
                <select value={form.race_id} onChange={(e) => setForm({ ...form, race_id: +e.target.value })} required style={selectStyle}>
                  <option value={0}>-- Choisir une race --</option>
                  {races.map((r) => <option key={r.id} value={r.id}>{r.name} — {r.passive_bonus_description}</option>)}
                </select>
              </div>
              <div>
                <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Classe</label>
                <select value={form.class_id} onChange={(e) => setForm({ ...form, class_id: +e.target.value })} required style={selectStyle}>
                  <option value={0}>-- Choisir une classe --</option>
                  {classes.map((c) => <option key={c.id} value={c.id}>{c.name} ({c.role})</option>)}
                </select>
              </div>
              <div>
                <label style={{ display: 'block', color: '#9ca3af', marginBottom: 6, fontSize: 13 }}>Trait Négatif</label>
                <select value={form.trait_id} onChange={(e) => setForm({ ...form, trait_id: +e.target.value })} required style={selectStyle}>
                  <option value={0}>-- Choisir un trait --</option>
                  {traits.map((t) => <option key={t.id} value={t.id}>{t.name} — {t.description}</option>)}
                </select>
              </div>
            </div>
            <div style={{ display: 'flex', gap: 12 }}>
              <button type="submit" disabled={creating} style={{ background: '#7c3aed', color: 'white', border: 'none', borderRadius: 6, padding: '8px 20px', cursor: 'pointer' }}>
                {creating ? 'Création...' : 'Créer'}
              </button>
              <button type="button" onClick={() => setShowCreateForm(false)} style={{ background: '#374151', color: '#d1d5db', border: 'none', borderRadius: 6, padding: '8px 16px', cursor: 'pointer' }}>
                Annuler
              </button>
            </div>
          </form>
        </div>
      )}

      {heroes.length === 0 ? (
        <div style={{ textAlign: 'center', padding: 60, color: '#6b7280' }}>
          Aucun héros. Le Narrateur hausse les épaules.
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16 }}>
          {heroes.map((hero) => (
            <div key={hero.id} style={{ position: 'relative' }}>
              <HeroCard hero={hero} />
              <div style={{ marginTop: 8 }}>
                {confirmDismiss === hero.id ? (
                  <div style={{ background: '#1f0a0a', border: '1px solid #7f1d1d', borderRadius: 6, padding: '10px 12px', display: 'flex', alignItems: 'center', gap: 8, justifyContent: 'space-between' }}>
                    <span style={{ color: '#fca5a5', fontSize: 12 }}>Renvoyer {hero.name} définitivement ?</span>
                    <div style={{ display: 'flex', gap: 6 }}>
                      <button
                        onClick={() => handleDismiss(hero.id)}
                        disabled={dismissing === hero.id}
                        style={{ background: '#dc2626', color: 'white', border: 'none', borderRadius: 4, padding: '4px 10px', cursor: 'pointer', fontSize: 12 }}
                      >
                        {dismissing === hero.id ? '...' : 'Oui'}
                      </button>
                      <button
                        onClick={() => setConfirmDismiss(null)}
                        style={{ background: '#374151', color: '#d1d5db', border: 'none', borderRadius: 4, padding: '4px 10px', cursor: 'pointer', fontSize: 12 }}
                      >
                        Non
                      </button>
                    </div>
                  </div>
                ) : (
                  <button
                    onClick={() => setConfirmDismiss(hero.id)}
                    style={{ width: '100%', background: 'transparent', color: '#6b7280', border: '1px solid #374151', borderRadius: 6, padding: '6px 0', cursor: 'pointer', fontSize: 12 }}
                  >
                    Renvoyer
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
