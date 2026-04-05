import type { Hero } from '../../types'

interface HeroCardProps {
  hero: Hero
  onClick?: () => void
  selected?: boolean
}

export function HeroCard({ hero, onClick, selected }: HeroCardProps) {
  const stats = hero.computed_stats
  const hpPercent = stats.max_hp > 0 ? Math.round((stats.current_hp / stats.max_hp) * 100) : 0

  return (
    <div
      onClick={onClick}
      style={{
        background: selected ? '#1e1b4b' : '#111827',
        border: `2px solid ${selected ? '#7c3aed' : '#374151'}`,
        borderRadius: 8,
        padding: 16,
        cursor: onClick ? 'pointer' : 'default',
        transition: 'border-color 0.2s',
      }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
        <h3 style={{ margin: 0, color: '#f9fafb', fontSize: 16 }}>{hero.name}</h3>
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

      {/* Stats principales */}
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
