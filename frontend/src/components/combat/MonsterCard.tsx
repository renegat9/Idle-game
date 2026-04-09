interface MonsterCardProps {
  name: string
  hp: number
  maxHp: number
  element?: string
  level?: number
  isBoss?: boolean
}

const ELEMENT_STYLE: Record<string, { emoji: string; color: string; anim: string }> = {
  feu:      { emoji: '🔥', color: '#f97316', anim: 'anim-shake'   },
  glace:    { emoji: '❄️', color: '#67e8f9', anim: 'anim-breathe' },
  foudre:   { emoji: '⚡', color: '#facc15', anim: 'anim-shake'   },
  poison:   { emoji: '☠️', color: '#86efac', anim: 'anim-float'   },
  ombre:    { emoji: '🌑', color: '#a78bfa', anim: 'anim-ghost'   },
  sacre:    { emoji: '✨', color: '#fde68a', anim: 'anim-breathe' },
  physique: { emoji: '💢', color: '#f87171', anim: 'anim-shake'   },
}

export function MonsterCard({ name, hp, maxHp, element = 'physique', level, isBoss = false }: MonsterCardProps) {
  const hpPercent = maxHp > 0 ? Math.round((hp / maxHp) * 100) : 0
  const el = ELEMENT_STYLE[element] ?? ELEMENT_STYLE['physique']

  const glowClass = isBoss
    ? 'glow-hp-danger'
    : hpPercent <= 25
      ? 'glow-hp-danger'
      : hpPercent <= 50
        ? 'glow-hp-warning'
        : ''

  return (
    <div
      className={glowClass}
      style={{
        background: isBoss ? '#1a0a0a' : '#111827',
        border: `2px solid ${isBoss ? '#dc2626' : '#374151'}`,
        borderRadius: 8,
        padding: 16,
        textAlign: 'center',
      }}
    >
      {/* Avatar animé */}
      <div
        className={isBoss ? 'anim-shake' : el.anim}
        style={{ fontSize: isBoss ? 52 : 40, display: 'inline-block', marginBottom: 8 }}
      >
        {isBoss ? '👹' : el.emoji}
      </div>

      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: 8, marginBottom: 4 }}>
        <span style={{ color: '#f9fafb', fontWeight: 'bold', fontSize: isBoss ? 16 : 14 }}>{name}</span>
        {level !== undefined && (
          <span style={{ background: '#374151', color: '#9ca3af', padding: '1px 6px', borderRadius: 4, fontSize: 11 }}>
            Niv. {level}
          </span>
        )}
        {isBoss && (
          <span style={{ background: '#7f1d1d', color: '#fca5a5', padding: '1px 6px', borderRadius: 4, fontSize: 11, fontWeight: 'bold' }}>
            BOSS
          </span>
        )}
      </div>

      <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4 }}>
        <span style={{ color: el.color, fontSize: 12 }}>{element}</span>
      </div>

      {/* Barre HP */}
      <div style={{ fontSize: 11, color: '#6b7280', marginBottom: 4 }}>
        {hp} / {maxHp} PV
      </div>
      <div style={{ background: '#374151', borderRadius: 4, height: 8 }}>
        <div style={{
          background: hpPercent > 50 ? '#22c55e' : hpPercent > 25 ? '#f59e0b' : '#ef4444',
          width: `${hpPercent}%`,
          height: '100%',
          borderRadius: 4,
          transition: 'width 0.5s',
        }} />
      </div>
    </div>
  )
}
