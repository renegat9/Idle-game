import { useEffect, useState } from 'react'
import { heroApi } from '../api/game'
import apiClient from '../api/client'
import { useGameStore } from '../store/gameStore'
import { HeroCard } from '../components/hero/HeroCard'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import type { Hero, Race, GameClass, Trait } from '../types'

export function TeamPage() {
  const { setHeroes } = useGameStore()
  const [heroes, setLocalHeroes] = useState<Hero[]>([])
  const [races, setRaces] = useState<Race[]>([])
  const [classes, setClasses] = useState<GameClass[]>([])
  const [traits, setTraits] = useState<Trait[]>([])
  const [loading, setLoading] = useState(true)
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [form, setForm] = useState({ name: '', race_id: 0, class_id: 0, trait_id: 0 })
  const [narratorComment, setNarratorComment] = useState('')
  const [creating, setCreating] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    Promise.all([
      heroApi.list(),
      apiClient.get('/reference/races'),
      apiClient.get('/reference/classes'),
      apiClient.get('/reference/traits'),
    ]).then(([heroRes, raceRes, classRes, traitRes]) => {
      setLocalHeroes(heroRes.data.heroes)
      setHeroes(heroRes.data.heroes)
      setRaces(raceRes.data.races ?? [])
      setClasses(classRes.data.classes ?? [])
      setTraits(traitRes.data.traits ?? [])
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

  const selectStyle = { width: '100%', background: '#1f2937', border: '1px solid #374151', borderRadius: 6, padding: '8px 12px', color: '#f9fafb', fontSize: 14 }
  const inputStyle = { ...selectStyle }

  if (loading) return <div style={{ color: '#6b7280', textAlign: 'center', paddingTop: 80 }}>Chargement...</div>

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
        <h1 style={{ color: '#f9fafb', margin: 0 }}>⚔️ Mon Équipe</h1>
        <button
          onClick={() => setShowCreateForm(!showCreateForm)}
          style={{ background: '#7c3aed', color: 'white', border: 'none', borderRadius: 6, padding: '8px 16px', cursor: 'pointer' }}
        >
          + Recruter un Héros
        </button>
      </div>

      {narratorComment && <NarratorBubble comment={narratorComment} />}

      {showCreateForm && (
        <div style={{ background: '#111827', border: '1px solid #374151', borderRadius: 8, padding: 24, marginBottom: 24 }}>
          <h3 style={{ color: '#f9fafb', marginTop: 0 }}>Créer un Héros</h3>
          {error && <div style={{ color: '#fca5a5', background: '#450a0a', padding: '8px 12px', borderRadius: 6, marginBottom: 12, fontSize: 13 }}>{error}</div>}
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
          {heroes.map((hero) => <HeroCard key={hero.id} hero={hero} />)}
        </div>
      )}
    </div>
  )
}
