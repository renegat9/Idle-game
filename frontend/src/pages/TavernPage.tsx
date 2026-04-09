import { useEffect, useRef, useState } from 'react'
import { tavernApi, musicApi } from '../api/game'
import { useGameStore } from '../store/gameStore'
import { NarratorBubble } from '../components/narrator/NarratorBubble'
import { HeroPortrait } from '../components/ui/HeroPortrait'
import { GameButton } from '../components/ui/GameButton'
import { GamePanel } from '../components/ui/GamePanel'

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

const ROLE_COLORS: Record<string, string> = {
  tank: '#3b82f6', dps: '#ef4444', support: '#22c55e', hybrid: '#f59e0b', summoner: '#a855f7'
}

const STYLE_LABELS: Record<string, string> = {
  taverne: '🍺 Taverne', victoire_epique: '🏆 Victoire', defaite: '😢 Défaite',
  exploration: '🗺️ Exploration', boss: '⚔️ Boss', repos: '😴 Repos',
}

export function TavernPage() {
  useGameStore()
  const [recruits, setRecruits] = useState<Recruit[]>([])
  const [heroDebuffs, setHeroDebuffs] = useState<HeroDebuffs[]>([])
  const [narratorComment, setNarratorComment] = useState('')
  const [loading, setLoading] = useState(true)
  const [acting, setActing] = useState(false)
  const [message, setMessage] = useState<{ text: string; ok: boolean } | null>(null)
  const [currentTrack, setCurrentTrack] = useState<{ style: string; context: string; file_path: string } | null>(null)
  const [selectedStyle, setSelectedStyle] = useState<string>('taverne')
  const [playing, setPlaying] = useState(false)
  const audioRef = useRef<HTMLAudioElement>(null)

  useEffect(() => {
    loadTavern()
    musicApi.current().then(({ data }) => setCurrentTrack({ style: data.style, context: data.context, file_path: data.file_path })).catch(() => {})
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

  if (loading) {
    return (
      <div className="game-loading">
        <div className="game-loading-spinner" />
        <div className="game-loading-text">La taverne s'anime…</div>
      </div>
    )
  }

  return (
    <div>
      <div style={{ marginBottom: 24 }}>
        <h1 className="game-title" style={{ fontSize: 26, margin: '0 0 4px' }}>🍺 La Taverne</h1>
        <p style={{ color: '#6b7280', fontSize: 13, margin: 0 }}>
          Recruter de nouveaux héros ou purifier les afflictions de votre équipe.
        </p>
      </div>

      {narratorComment && <NarratorBubble comment={narratorComment} />}

      {/* Music player */}
      <GamePanel icon="🎵" title="Ambiance musicale" variant="magic" style={{ marginBottom: 20 }}>
        <div style={{ marginBottom: 12, display: 'flex', gap: 6, flexWrap: 'wrap' }}>
          {Object.entries(STYLE_LABELS).map(([style, label]) => (
            <button
              key={style}
              onClick={async () => {
                setSelectedStyle(style)
                const { data } = await tavernApi.music(style)
                const track = { style: data.style, context: style, file_path: data.file_path }
                setCurrentTrack(track)
                if (audioRef.current) {
                  audioRef.current.src = `/${data.file_path}`
                  audioRef.current.load()
                  audioRef.current.play().then(() => setPlaying(true)).catch(() => {})
                }
              }}
              className={`game-tab ${selectedStyle === style ? 'active' : ''}`}
              style={{ fontSize: 12 }}
            >
              {label}
            </button>
          ))}
        </div>
        <div style={{ background: '#0d1117', borderRadius: 8, padding: '10px 14px', display: 'flex', alignItems: 'center', gap: 12 }}>
          <button
            onClick={() => {
              if (!audioRef.current) return
              if (playing) {
                audioRef.current.pause()
                setPlaying(false)
              } else {
                if (!audioRef.current.src && currentTrack) {
                  audioRef.current.src = `/${currentTrack.file_path}`
                  audioRef.current.load()
                }
                audioRef.current.play().then(() => setPlaying(true)).catch(() => {})
              }
            }}
            style={{ background: '#4c1d95', color: 'white', border: 'none', borderRadius: 6, width: 36, height: 36, fontSize: 16, cursor: 'pointer', flexShrink: 0 }}
          >
            {playing ? '⏸' : '▶'}
          </button>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{ color: '#c4b5fd', fontSize: 13, fontFamily: 'var(--font-title)' }}>
              {STYLE_LABELS[selectedStyle]}
            </div>
            <div style={{ color: '#4b5563', fontSize: 11, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
              {currentTrack ? currentTrack.file_path : `music/fallback/${selectedStyle}.mp3`}
            </div>
          </div>
          <audio ref={audioRef} loop onEnded={() => setPlaying(false)} onPause={() => setPlaying(false)} onPlay={() => setPlaying(true)} style={{ display: 'none' }} />
        </div>
      </GamePanel>

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

      {/* Recruits */}
      <div style={{ marginBottom: 8, display: 'flex', alignItems: 'center', gap: 10 }}>
        <h2 className="game-title" style={{ fontSize: 18, margin: 0 }}>Héros disponibles</h2>
        <span style={{ color: '#4b5563', fontSize: 13 }}>({recruits.length})</span>
      </div>

      {recruits.length === 0 ? (
        <GamePanel variant="default" style={{ textAlign: 'center', padding: '40px 20px', marginBottom: 24 }}>
          <div style={{ fontSize: 40, marginBottom: 10 }}>😴</div>
          <p style={{ color: '#6b7280', fontStyle: 'italic', margin: 0 }}>Aucun héros disponible pour le moment.</p>
        </GamePanel>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: 16, marginBottom: 32 }}>
          {recruits.map(r => (
            <div
              key={r.id}
              className={`game-panel ${r.is_legendary ? 'game-panel-gold' : ''}`}
              style={{ overflow: 'hidden' }}
            >
              {r.is_legendary && (
                <div style={{ background: 'linear-gradient(90deg, #78350f, #b45309)', padding: '4px 12px', textAlign: 'center' }}>
                  <span style={{ color: '#fde68a', fontSize: 11, fontFamily: 'var(--font-title)', letterSpacing: '0.1em' }}>
                    ⭐ HÉROS LÉGENDAIRE ⭐
                  </span>
                </div>
              )}
              <div style={{ padding: 18 }}>
                {/* Portrait + info */}
                <div style={{ display: 'flex', gap: 14, marginBottom: 14 }}>
                  <HeroPortrait
                    classSlug={r.class.slug}
                    imagePath={r.image_path ?? undefined}
                    name={r.name}
                    size={80}
                    animClass={r.is_legendary ? 'anim-breathe' : undefined}
                  />
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 }}>
                      <h3 className="game-title" style={{ margin: 0, fontSize: 15, color: r.is_legendary ? '#fcd34d' : '#f9fafb' }}>
                        {r.name}
                      </h3>
                      <span style={{ color: '#fbbf24', fontSize: 14, fontWeight: 700, whiteSpace: 'nowrap', marginLeft: 8 }}>
                        {r.hire_cost.toLocaleString('fr-FR')} 💰
                      </span>
                    </div>
                    {r.is_legendary && r.legendary_epithet && (
                      <div style={{ color: '#d97706', fontSize: 12, marginBottom: 6, fontStyle: 'italic' }}>
                        « {r.legendary_epithet} »
                      </div>
                    )}
                    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                      <span style={{ background: '#0d1117', border: '1px solid #2d3748', borderRadius: 4, padding: '2px 7px', fontSize: 11, color: '#9ca3af' }}>
                        {r.race.name}
                      </span>
                      <span style={{ background: '#0d1117', border: `1px solid ${ROLE_COLORS[r.class.role] ?? '#6b7280'}`, borderRadius: 4, padding: '2px 7px', fontSize: 11, color: ROLE_COLORS[r.class.role] ?? '#6b7280' }}>
                        {r.class.name}
                      </span>
                    </div>
                  </div>
                </div>

                {r.is_legendary && r.legendary_backstory && (
                  <div className="narrator-bubble" style={{ marginBottom: 12, padding: '8px 12px' }}>
                    <p className="narrator-text" style={{ margin: 0, fontSize: 12 }}>« {r.legendary_backstory} »</p>
                  </div>
                )}

                {/* Trait */}
                <div style={{ background: '#0d1117', border: '1px solid #2d3748', borderRadius: 6, padding: '8px 10px', marginBottom: 14 }}>
                  <div style={{ color: '#a78bfa', fontSize: 12, fontWeight: 600, marginBottom: 2 }}>
                    ⚠ Trait : {r.trait.name}
                  </div>
                  <p style={{ color: '#4b5563', fontSize: 11, margin: 0, fontStyle: 'italic' }}>{r.trait.description}</p>
                </div>

                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <span style={{ color: '#4b5563', fontSize: 11 }}>
                    Expire {new Date(r.expires_at).toLocaleDateString('fr-FR')}
                  </span>
                  <GameButton
                    variant={r.is_legendary ? 'gold' : 'primary'}
                    size="sm"
                    onClick={() => hire(r.id)}
                    loading={acting}
                    icon={r.is_legendary ? '⭐' : '🍺'}
                  >
                    Recruter
                  </GameButton>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Debuff removal */}
      {heroDebuffs.length > 0 && (
        <div>
          <h2 className="game-title" style={{ fontSize: 18, marginBottom: 14 }}>🧪 Purification des afflictions</h2>
          <div style={{ display: 'grid', gap: 12 }}>
            {heroDebuffs.map(hd => (
              <GamePanel key={hd.hero_id} icon="🧪" title={hd.hero_name} variant="danger">
                <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 10 }}>
                  <span style={{ color: '#9ca3af', fontSize: 12 }}>Coût : {hd.removal_cost} 💰 / debuff</span>
                </div>
                <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                  {hd.debuffs.map(b => (
                    <div key={b.id} style={{ background: '#0a0202', border: '1px solid #7f1d1d', borderRadius: 8, padding: '8px 12px', display: 'flex', alignItems: 'center', gap: 8 }}>
                      <div>
                        <div style={{ color: '#fca5a5', fontSize: 12 }}>{b.source.replace('quest_debuff_', '')}</div>
                        <div style={{ color: '#ef4444', fontSize: 11 }}>{b.modifier_percent}% {b.stat_affected}</div>
                        <div style={{ color: '#4b5563', fontSize: 11 }}>{b.remaining_combats} combats</div>
                      </div>
                      <GameButton size="sm" variant="primary" onClick={() => removeDebuff(hd.hero_id, b.id)} disabled={acting}>
                        Purifier
                      </GameButton>
                    </div>
                  ))}
                </div>
              </GamePanel>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
