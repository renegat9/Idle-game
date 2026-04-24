import type { Hero } from '../../types'
import { HeroPortrait } from '../ui/HeroPortrait'
import { Tooltip } from '../ui/Tooltip'

interface HeroCardProps {
  hero: Hero
  onClick?: () => void
  selected?: boolean
}

const CLASS_ANIM: Record<string, string> = {
  guerrier:      'anim-breathe',
  barbare:       'anim-breathe',
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

const STAT_ICONS: Record<string, { icon: string; color: string }> = {
  ATQ: { icon: '⚔️', color: '#f87171' },
  DEF: { icon: '🛡️', color: '#60a5fa' },
  VIT: { icon: '💨', color: '#4ade80' },
  CHA: { icon: '✨', color: '#fbbf24' },
  INT: { icon: '📖', color: '#a78bfa' },
}

export function HeroCard({ hero, onClick, selected }: HeroCardProps) {
  const stats      = hero.computed_stats ?? { max_hp: 0, current_hp: 0, atq: 0, def: 0, vit: 0, cha: 0, int: 0 }
  const hpPercent  = stats.max_hp > 0 ? Math.round((stats.current_hp / stats.max_hp) * 100) : 0
  const xpPercent  = hero.xp_to_next_level > 0 ? Math.min(100, Math.round((hero.xp / hero.xp_to_next_level) * 100)) : 0
  const animClass  = CLASS_ANIM[hero.class.slug] ?? 'anim-breathe'

  const glowClass = selected
    ? 'glow-selected'
    : hpPercent <= 25
      ? 'glow-hp-danger'
      : hpPercent <= 50
        ? 'glow-hp-warning'
        : ''

  const hpBarClass = hpPercent > 50
    ? 'stat-bar-fill stat-bar-hp-high'
    : hpPercent > 25
      ? 'stat-bar-fill stat-bar-hp-mid'
      : 'stat-bar-fill stat-bar-hp-low'

  return (
    <div
      onClick={onClick}
      className={`game-panel ${glowClass}`}
      style={{
        cursor: onClick ? 'pointer' : 'default',
        transition: 'transform 0.15s, box-shadow 0.2s',
        borderColor: selected ? '#7c3aed' : undefined,
      }}
    >
      {/* ── Header: Portrait + Name ── */}
      <div style={{ display: 'flex', gap: 12, padding: '14px 14px 10px' }}>
        <HeroPortrait
          classSlug={hero.class.slug}
          imagePath={hero.image_path}
          name={hero.name}
          size={76}
          animClass={animClass}
          hpPercent={hpPercent}
        />
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 }}>
            <h3 className="game-title" style={{ margin: 0, fontSize: 15, color: '#f9fafb', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
              {hero.name}
            </h3>
            <span style={{
              background: selected ? '#4c1d95' : '#1f2937',
              color: selected ? '#c4b5fd' : '#9ca3af',
              padding: '2px 8px',
              borderRadius: 4,
              fontSize: 11,
              fontWeight: 700,
              flexShrink: 0,
              marginLeft: 6,
              fontFamily: 'var(--font-title)',
            }}>
              Niv.{hero.level}
            </span>
          </div>
          <div style={{ color: '#9ca3af', fontSize: 12, marginBottom: 8 }}>
            {hero.race.name} · {hero.class.name}
          </div>
          {hero.trait && (
            <Tooltip content={hero.trait.description} position="bottom">
              <div style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: 4,
                background: '#2d0a0a',
                border: '1px solid #7f1d1d',
                borderRadius: 4,
                padding: '2px 7px',
                fontSize: 11,
                color: '#fca5a5',
                cursor: 'help',
              }}>
                ⚠ {hero.trait.name}
              </div>
            </Tooltip>
          )}
        </div>
      </div>

      <div className="game-divider" style={{ margin: '0 14px' }} />

      {/* ── HP Bar ── */}
      <div style={{ padding: '8px 14px 6px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 5 }}>
          <span style={{ fontSize: 11, color: '#6b7280', display: 'flex', alignItems: 'center', gap: 4 }}>
            ❤️ <span style={{ color: hpPercent <= 25 ? '#ef4444' : hpPercent <= 50 ? '#f59e0b' : '#22c55e' }}>
              {stats.current_hp}
            </span>
            <span style={{ color: '#4b5563' }}>/ {stats.max_hp} PV</span>
          </span>
          <span style={{ fontSize: 11, color: hpPercent <= 25 ? '#ef4444' : hpPercent <= 50 ? '#f59e0b' : '#22c55e' }}>
            {hpPercent}%
          </span>
        </div>
        <div className="stat-bar-track" style={{ height: 10 }}>
          <div className={hpBarClass} style={{ width: `${hpPercent}%` }} />
        </div>
      </div>

      {/* ── Stats Grid ── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(5, 1fr)', gap: 4, padding: '6px 14px' }}>
        {([
          ['ATQ', stats.atq],
          ['DEF', stats.def],
          ['VIT', stats.vit],
          ['CHA', stats.cha],
          ['INT', stats.int],
        ] as [string, number][]).map(([label, value]) => {
          const s = STAT_ICONS[label]
          return (
            <div key={label} style={{
              textAlign: 'center',
              background: '#0d1117',
              borderRadius: 5,
              padding: '5px 2px',
              border: '1px solid #1f2937',
            }}>
              <div style={{ fontSize: 12, marginBottom: 1 }}>{s.icon}</div>
              <div style={{ color: s.color, fontWeight: 700, fontSize: 13 }}>{value}</div>
              <div style={{ color: '#4b5563', fontSize: 9, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{label}</div>
            </div>
          )
        })}
      </div>

      {/* ── Equipment ── */}
      {hero.equipped_items && hero.equipped_items.length > 0 && (
        <>
          <div className="game-divider" style={{ margin: '6px 14px 0' }} />
          <div style={{ padding: '6px 14px' }}>
            <div style={{ fontSize: 10, color: '#4b5563', marginBottom: 5, textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'var(--font-title)' }}>
              Équipement
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
              {hero.equipped_items.map((item) => {
                const bonuses = [
                  item.atq > 0 && `+${item.atq}⚔`,
                  item.def > 0 && `+${item.def}🛡`,
                  item.hp  > 0 && `+${item.hp}❤`,
                  item.vit > 0 && `+${item.vit}💨`,
                ].filter(Boolean).join(' ')
                return (
                  <div key={item.id} style={{
                    display: 'flex', alignItems: 'center', gap: 6,
                    background: '#0d1117', borderRadius: 4, padding: '3px 7px',
                    border: '1px solid #1a1f2e',
                  }}>
                    <span style={{ fontSize: 12, flexShrink: 0 }}>{SLOT_EMOJI[item.slot] ?? '📦'}</span>
                    <span style={{
                      color: RARITY_COLOR[item.rarity] ?? '#9ca3af',
                      fontSize: 11, fontWeight: 600, flex: 1,
                      overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                    }}>
                      {item.name}
                    </span>
                    {bonuses && (
                      <span style={{ color: '#6b7280', fontSize: 10, whiteSpace: 'nowrap', flexShrink: 0 }}>{bonuses}</span>
                    )}
                  </div>
                )
              })}
            </div>
          </div>
        </>
      )}

      {/* ── XP Bar ── */}
      <div style={{ padding: '6px 14px 14px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
          <span style={{ fontSize: 10, color: '#4b5563', textTransform: 'uppercase', letterSpacing: '0.08em', fontFamily: 'var(--font-title)' }}>
            XP
          </span>
          <span style={{ fontSize: 10, color: '#6366f1' }}>{hero.xp} / {hero.xp_to_next_level}</span>
        </div>
        <div className="stat-bar-track" style={{ height: 6 }}>
          <div className="stat-bar-fill stat-bar-xp" style={{ width: `${xpPercent}%` }} />
        </div>
      </div>
    </div>
  )
}
