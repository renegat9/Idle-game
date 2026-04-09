import type { Hero } from '../../types'

interface HeroCardProps {
  hero: Hero
  onClick?: () => void
  selected?: boolean
}

const CLASS_ANIM: Record<string, string> = {
  guerrier:      'anim-shake',
  barbare:       'anim-shake',
  mage:          'anim-float',
  necromancien:  'anim-ghost',
  barde:         'anim-sway',
  pretre:        'anim-breathe',
  voleur:        'anim-breathe',
  ranger:        'anim-breathe',
}

const RARITY_COLOR: Record<string, string> = {
  commun:      '#9ca3af',
  peu_commun:  '#4ade80',
  rare:        '#60a5fa',
  epique:      '#a78bfa',
  legendaire:  '#fbbf24',
  wtf:         '#f472b6',
}

const SLOT_EMOJI: Record<string, string> = {
  arme:         '⚔️',
  armure:       '🛡️',
  casque:       '⛑️',
  bottes:       '👢',
  accessoire:   '💍',
  truc_bizarre: '🎲',
}

const CLASS_EMOJI: Record<string, string> = {
  guerrier:     '🗡️',
  barbare:      '🪓',
  mage:         '🔮',
  necromancien: '💀',
  barde:        '🎵',
  pretre:       '✝️',
  voleur:       '🗝️',
  ranger:       '🏹',
}

export function HeroCard({ hero, onClick, selected }: HeroCardProps) {
  const stats      = hero.computed_stats
  const hpPercent  = stats.max_hp > 0 ? Math.round((stats.current_hp / stats.max_hp) * 100) : 0
  const animClass  = CLASS_ANIM[hero.class.slug] ?? 'anim-breathe'
  const classEmoji = CLASS_EMOJI[hero.class.slug] ?? '⚔️'

  const glowClass = selected
    ? 'glow-selected'
    : hpPercent <= 25
      ? 'glow-hp-danger'
      : hpPercent <= 50
        ? 'glow-hp-warning'
        : ''

  return (
    <div
      onClick={onClick}
      className={glowClass}
      style={{
        background: selected ? '#1e1b4b' : '#111827',
        border: `2px solid ${selected ? '#7c3aed' : '#374151'}`,
        borderRadius: 8,
        padding: 16,
        cursor: onClick ? 'pointer' : 'default',
        transition: 'border-color 0.2s',
      }}
    >
      {/* Avatar animé — image Gemini si disponible, sinon emoji */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          {hero.image_path ? (
            <img
              src={`/${hero.image_path}`}
              alt={hero.name}
              className={animClass}
              style={{ width: 40, height: 40, objectFit: 'contain', imageRendering: 'auto' }}
            />
          ) : (
            <span className={animClass} style={{ fontSize: 28, display: 'inline-block' }}>
              {classEmoji}
            </span>
          )}
          <h3 style={{ margin: 0, color: '#f9fafb', fontSize: 16 }}>{hero.name}</h3>
        </div>
        <span style={{ background: '#374151', color: '#d1d5db', padding: '2px 8px', borderRadius: 4, fontSize: 12 }}>
          Niv. {hero.level}
        </span>
      </div>

      <div style={{ color: '#9ca3af', fontSize: 13, marginBottom: 8 }}>
        {hero.race.name} • {hero.class.name}
        {hero.trait && (
          <span style={{ color: '#f87171', marginLeft: 8 }}>⚠ {hero.trait.name}</span>
        )}
      </div>

      {/* Barre HP */}
      <div style={{ marginBottom: 8 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 12, color: '#6b7280', marginBottom: 2 }}>
          <span>PV</span>
          <span>{stats.current_hp} / {stats.max_hp}</span>
        </div>
        <div style={{ background: '#374151', borderRadius: 4, height: 6 }}>
          <div style={{
            background: hpPercent > 50 ? '#22c55e' : hpPercent > 25 ? '#f59e0b' : '#ef4444',
            width: `${hpPercent}%`,
            height: '100%',
            borderRadius: 4,
            transition: 'width 0.3s',
          }} />
        </div>
      </div>

      {/* Stats principales (totales avec équipement) */}
      <div style={{ fontSize: 10, color: '#4b5563', marginBottom: 4 }}>Stats totales (avec équipement)</div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 4, fontSize: 12 }}>
        {[
          ['ATQ', stats.atq, '#ef4444'],
          ['DEF', stats.def, '#3b82f6'],
          ['VIT', stats.vit, '#22c55e'],
          ['CHA', stats.cha, '#f59e0b'],
          ['INT', stats.int, '#8b5cf6'],
        ].map(([label, value, color]) => (
          <div key={label as string} style={{ textAlign: 'center', background: '#1f2937', borderRadius: 4, padding: '4px 0' }}>
            <div style={{ color: color as string, fontWeight: 'bold' }}>{value}</div>
            <div style={{ color: '#6b7280', fontSize: 10 }}>{label}</div>
          </div>
        ))}
      </div>

      {/* Équipement */}
      {hero.equipped_items && hero.equipped_items.length > 0 && (
        <div style={{ marginTop: 10 }}>
          <div style={{ fontSize: 10, color: '#4b5563', marginBottom: 4 }}>Équipement</div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
            {hero.equipped_items.map((item) => {
              const bonuses = [
                item.atq > 0 && `+${item.atq} ATQ`,
                item.def > 0 && `+${item.def} DEF`,
                item.hp  > 0 && `+${item.hp} PV`,
                item.vit > 0 && `+${item.vit} VIT`,
                item.cha > 0 && `+${item.cha} CHA`,
                item.int > 0 && `+${item.int} INT`,
              ].filter(Boolean).join(' ')

              return (
                <div key={item.id} style={{ display: 'flex', alignItems: 'center', gap: 6, background: '#1f2937', borderRadius: 4, padding: '3px 6px' }}>
                  <span style={{ fontSize: 12 }}>{SLOT_EMOJI[item.slot] ?? '📦'}</span>
                  <span style={{ color: RARITY_COLOR[item.rarity] ?? '#9ca3af', fontSize: 11, fontWeight: 'bold', flex: 1, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {item.name}
                  </span>
                  {bonuses && (
                    <span style={{ color: '#6b7280', fontSize: 10, whiteSpace: 'nowrap' }}>{bonuses}</span>
                  )}
                </div>
              )
            })}
          </div>
        </div>
      )}

      {/* XP */}
      <div style={{ marginTop: 8 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, color: '#6b7280', marginBottom: 2 }}>
          <span>XP</span>
          <span>{hero.xp} / {hero.xp_to_next_level}</span>
        </div>
        <div style={{ background: '#374151', borderRadius: 4, height: 4 }}>
          <div style={{
            background: '#6366f1',
            width: `${Math.min(100, Math.round((hero.xp / hero.xp_to_next_level) * 100))}%`,
            height: '100%',
            borderRadius: 4,
          }} />
        </div>
      </div>
    </div>
  )
}
