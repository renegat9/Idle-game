import { useEffect, useState } from 'react'
import { tavernApi, musicApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'

type Recruit = {
  id: number
  name: string
  hire_cost: number
  expires_at: string
  is_legendary: boolean
  legendary_epithet: string | null
  legendary_backstory: string | null
  image_path: string | null
  race: { id: number; name: string; slug: string }
  class: { id: number; name: string; role: string; slug: string }
  trait: { id: number; name: string; description: string }
}

type HeroDebuffs = {
  hero_id: number
  hero_name: string
  removal_cost: number
  debuffs: Array<{ id: number; source: string; stat_affected: string; modifier_percent: number; remaining_combats: number }>
}

const CLASS_EMOJI: Record<string, string> = {
  guerrier: '🗡️', barbare: '🪓', mage: '🔮', necromancien: '💀',
  barde: '🎵', pretre: '✝️', voleur: '🗝️', ranger: '🏹',
}

const STYLE_LABELS: Record<string, string> = {
  taverne: '🍺 Ambiance Taverne', victoire_epique: '🏆 Victoire Épique',
  defaite: '😢 Mélodie de la Défaite', exploration: '🗺️ Exploration',
  boss: '⚔️ Combat de Boss', repos: '😴 Repos',
}

export function TavernPage() {
  useGameStore()
  const [recruits, setRecruits] = useState<Recruit[]>([])
  const [heroDebuffs, setHeroDebuffs] = useState<HeroDebuffs[]>([])
  const [narratorComment, setNarratorComment] = useState('')
  const [loading, setLoading] = useState(true)
  const [acting, setActing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)
  const [currentTrack, setCurrentTrack] = useState<{ style: string; context: string } | null>(null)
  const [selectedStyle, setSelectedStyle] = useState<string>('taverne')

  useEffect(() => {
    loadTavern()
    musicApi.current().then(({ data }) => setCurrentTrack(data)).catch(() => {})
  }, [])

  async function loadTavern() {
    try {
      const { data } = await tavernApi.get()
      setRecruits(data.recruits ?? [])
      setHeroDebuffs(data.hero_debuffs ?? [])
      setNarratorComment(data.narrator_comment ?? '')
    } catch { /* ok */ }
    setLoading(false)
  }

  async function hire(recruitId: number) {
    if (acting) return
    setActing(true)
    setMessage(null)
    try {
      const { data } = await tavernApi.hire(recruitId)
      setMessage({ text: data.message, ok: true })
      await loadTavern()
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur', ok: false })
    }
    setActing(false)
  }

  async function removeDebuff(heroId: number, buffId: number) {
    if (acting) return
    setActing(true)
    setMessage(null)
    try {
      const { data } = await tavernApi.removeDebuff(heroId, buffId)
      setMessage({ text: data.message, ok: true })
      await loadTavern()
    } catch (e: any) {
      setMessage({ text: e.response?.data?.message ?? 'Erreur', ok: false })
    }
    setActing(false)
  }

  const roleColor = (role: string) =>
    ({ tank: '#3b82f6', dps: '#ef4444', support: '#22c55e', hybrid: '#f59e0b', summoner: '#a855f7' }[role] ?? '#6b7280')

  if (loading) return <div style={{ color: '#94a3b8' }}>Chargement de la taverne...</div>

  return (
    <div>
      <h1 style={{ color: '#f1f5f9', marginBottom: 4, fontSize: 24 }}>🍺 La Taverne</h1>
      <p style={{ color: '#6b7280', marginBottom: 16, fontSize: 14 }}>Recruter de nouveaux héros ou purifier les afflictions de votre équipe.</p>

      {narratorComment && <NarratorBubble comment={narratorComment} />}

      {/* Music player */}
      <div style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 16, marginBottom: 20 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontSize: 20 }}>🎵</span>
            <div>
              <div style={{ color: '#f1f5f9', fontSize: 14, fontWeight: 'bold' }}>Ambiance musicale</div>
              {currentTrack && (
                <div style={{ color: '#94a3b8', fontSize: 12 }}>
                  Contexte : {STYLE_LABELS[currentTrack.context] ?? currentTrack.context}
                </div>
              )}
            </div>
          </div>
          <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
            {Object.keys(STYLE_LABELS).map(style => (
              <button
                key={style}
                onClick={async () => {
                  setSelectedStyle(style)
                  const { data } = await tavernApi.music(style)
                  setCurrentTrack({ style: data.style, context: style })
                }}
                style={{ background: selectedStyle === style ? '#4c1d95' : '#0f172a', color: selectedStyle === style ? '#c4b5fd' : '#6b7280', border: '1px solid #334155', padding: '3px 10px', borderRadius: 6, cursor: 'pointer', fontSize: 11 }}
              >
                {STYLE_LABELS[style]}
              </button>
            ))}
          </div>
        </div>
        <div style={{ background: '#0f172a', borderRadius: 8, padding: '10px 14px', display: 'flex', alignItems: 'center', gap: 10 }}>
          <span style={{ fontSize: 24 }}>▶</span>
          <div>
            <div style={{ color: '#c4b5fd', fontSize: 13 }}>{STYLE_LABELS[selectedStyle]}</div>
            <div style={{ color: '#475569', fontSize: 11 }}>
              Piste : music/fallback/{selectedStyle}.mp3
              <span style={{ marginLeft: 8, color: '#6b7280', fontStyle: 'italic' }}>— MusicFX non disponible publiquement, fallback statique activé</span>
            </div>
          </div>
        </div>
      </div>

      {message && (
        <div style={{ background: message.ok ? '#052e16' : '#1c0505', border: `1px solid ${message.ok ? '#16a34a' : '#991b1b'}`, borderRadius: 8, padding: 12, marginBottom: 16 }}>
          <span style={{ color: message.ok ? '#22c55e' : '#ef4444' }}>{message.text}</span>
        </div>
      )}

      {/* Recruits */}
      <h2 style={{ color: '#e2e8f0', fontSize: 18, marginBottom: 14 }}>Héros disponibles</h2>
      {recruits.length === 0 && (
        <div style={{ color: '#6b7280', background: '#1e293b', borderRadius: 10, padding: 20, marginBottom: 24, textAlign: 'center' }}>
          Aucun héros disponible pour le moment. Revenez plus tard.
        </div>
      )}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: 16, marginBottom: 32 }}>
        {recruits.map(r => (
          <div key={r.id} style={{
            background: r.is_legendary ? 'linear-gradient(135deg, #1e293b 0%, #1c1505 100%)' : '#1e293b',
            border: r.is_legendary ? '2px solid #d97706' : '1px solid #334155',
            borderRadius: 12, padding: 18,
            boxShadow: r.is_legendary ? '0 0 12px rgba(217,119,6,0.3)' : 'none',
          }}>
            {/* Avatar */}
            <div style={{ display: 'flex', justifyContent: 'center', marginBottom: 12 }}>
              {r.image_path ? (
                <img
                  src={`/${r.image_path}`}
                  alt={r.name}
                  style={{ width: 80, height: 80, objectFit: 'contain', borderRadius: 8, border: `2px solid ${r.is_legendary ? '#d97706' : '#334155'}` }}
                  onError={(e) => { (e.target as HTMLImageElement).style.display = 'none' }}
                />
              ) : (
                <div style={{ width: 80, height: 80, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#0f172a', borderRadius: 8, border: `2px solid ${r.is_legendary ? '#d97706' : '#334155'}`, fontSize: 36 }}>
                  {CLASS_EMOJI[r.class.slug] ?? '⚔️'}
                </div>
              )}
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
              <div>
                <h3 style={{ color: r.is_legendary ? '#fcd34d' : '#f1f5f9', margin: 0, fontSize: 16 }}>{r.name}</h3>
                {r.is_legendary && r.legendary_epithet && (
                  <div style={{ color: '#d97706', fontSize: 12, marginTop: 2, fontStyle: 'italic' }}>
                    ⭐ {r.legendary_epithet}
                  </div>
                )}
              </div>
              <div style={{ textAlign: 'right' }}>
                <span style={{ color: '#fbbf24', fontSize: 14, fontWeight: 'bold' }}>{r.hire_cost} 💰</span>
                {r.is_legendary && (
                  <div style={{ background: '#78350f', color: '#fde68a', fontSize: 10, borderRadius: 4, padding: '1px 6px', marginTop: 2 }}>
                    LÉGENDAIRE
                  </div>
                )}
              </div>
            </div>
            {r.is_legendary && r.legendary_backstory && (
              <p style={{ color: '#92400e', fontSize: 11, margin: '4px 0 8px', fontStyle: 'italic', background: '#1c1505', borderRadius: 4, padding: '4px 8px' }}>
                {r.legendary_backstory}
              </p>
            )}
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 12 }}>
              <span style={{ background: '#0f172a', border: '1px solid #334155', borderRadius: 6, padding: '2px 8px', fontSize: 12, color: '#94a3b8' }}>
                {r.race.name}
              </span>
              <span style={{ background: '#0f172a', border: `1px solid ${roleColor(r.class.role)}`, borderRadius: 6, padding: '2px 8px', fontSize: 12, color: roleColor(r.class.role) }}>
                {r.class.name}
              </span>
              <span style={{ background: '#0f172a', border: '1px solid #7c3aed', borderRadius: 6, padding: '2px 8px', fontSize: 12, color: '#a78bfa' }}>
                {r.trait.name}
              </span>
            </div>
            <p style={{ color: '#6b7280', fontSize: 12, margin: '0 0 12px', fontStyle: 'italic' }}>{r.trait.description}</p>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ color: '#475569', fontSize: 11 }}>
                Expire {new Date(r.expires_at).toLocaleDateString('fr-FR')}
              </span>
              <button
                onClick={() => hire(r.id)}
                disabled={acting}
                style={{
                  background: r.is_legendary ? '#d97706' : '#7c3aed',
                  color: 'white', border: 'none', padding: '8px 16px',
                  borderRadius: 8, cursor: acting ? 'not-allowed' : 'pointer',
                  fontSize: 13, opacity: acting ? 0.6 : 1,
                }}
              >
                {r.is_legendary ? '⭐ Recruter (Légendaire)' : 'Recruter'}
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Debuff removal */}
      {heroDebuffs.length > 0 && (
        <>
          <h2 style={{ color: '#e2e8f0', fontSize: 18, marginBottom: 14 }}>🧪 Purification des afflictions</h2>
          <div style={{ display: 'grid', gap: 12 }}>
            {heroDebuffs.map(hd => (
              <div key={hd.hero_id} style={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 12, padding: 16 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
                  <h3 style={{ color: '#f1f5f9', margin: 0, fontSize: 15 }}>{hd.hero_name}</h3>
                  <span style={{ color: '#94a3b8', fontSize: 12 }}>Coût de purification : {hd.removal_cost} 💰 / debuff</span>
                </div>
                <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                  {hd.debuffs.map(b => (
                    <div key={b.id} style={{ background: '#1c0505', border: '1px solid #7f1d1d', borderRadius: 8, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 8 }}>
                      <div>
                        <span style={{ color: '#fca5a5', fontSize: 12 }}>{b.source.replace('quest_debuff_', '')}</span>
                        <span style={{ color: '#ef4444', fontSize: 11, marginLeft: 6 }}>{b.modifier_percent}% {b.stat_affected}</span>
                        <span style={{ color: '#6b7280', fontSize: 11, marginLeft: 6 }}>{b.remaining_combats} combats</span>
                      </div>
                      <button
                        onClick={() => removeDebuff(hd.hero_id, b.id)}
                        disabled={acting}
                        style={{ background: '#7c3aed', color: 'white', border: 'none', padding: '4px 10px', borderRadius: 6, cursor: acting ? 'not-allowed' : 'pointer', fontSize: 12 }}
                      >
                        Purifier
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  )
}
