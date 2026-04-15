interface StatBarProps {
  value: number
  max: number
  variant?: 'hp' | 'xp' | 'custom'
  color?: string
  height?: number
  showText?: boolean
  label?: string
}

export function StatBar({ value, max, variant = 'hp', color, height = 8, showText = false, label }: StatBarProps) {
  const pct = max > 0 ? Math.min(100, Math.round((value / max) * 100)) : 0

  let barClass = 'stat-bar-fill'
  if (variant === 'hp') {
    barClass += pct > 50 ? ' stat-bar-hp-high' : pct > 25 ? ' stat-bar-hp-mid' : ' stat-bar-hp-low'
  } else if (variant === 'xp') {
    barClass += ' stat-bar-xp'
  }

  const inlineColor = variant === 'custom' && color ? { background: color } : {}

  return (
    <div>
      {(showText || label) && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 3 }}>
          {label && <span style={{ fontSize: 11, color: '#6b7280' }}>{label}</span>}
          {showText && <span style={{ fontSize: 11, color: '#9ca3af' }}>{value} / {max}</span>}
        </div>
      )}
      <div className="stat-bar-track" style={{ height }}>
        <div className={barClass} style={{ width: `${pct}%`, ...inlineColor }} />
      </div>
    </div>
  )
}
