interface ZoneBackgroundProps {
  element: string
  isActive?: boolean
  isLocked?: boolean
}

// Large decorative emoji per element type
export const ELEMENT_ICON: Record<string, string> = {
  physique: '⚔️',
  feu:      '🔥',
  glace:    '❄️',
  foudre:   '⚡',
  poison:   '☠️',
  sacre:    '✨',
  ombre:    '🌑',
}

export const ELEMENT_COLOR: Record<string, string> = {
  physique: '#9ca3af',
  feu:      '#ef4444',
  glace:    '#93c5fd',
  foudre:   '#fbbf24',
  poison:   '#4ade80',
  sacre:    '#fef08a',
  ombre:    '#a78bfa',
}

const ELEMENT_GRADIENT: Record<string, string> = {
  physique: 'linear-gradient(135deg, #1a1f2e 0%, #111827 100%)',
  feu:      'linear-gradient(135deg, #2d1008 0%, #1c0505 100%)',
  glace:    'linear-gradient(135deg, #0a1628 0%, #060e1a 100%)',
  foudre:   'linear-gradient(135deg, #1a1a08 0%, #12120a 100%)',
  poison:   'linear-gradient(135deg, #0a1a0a 0%, #060d06 100%)',
  sacre:    'linear-gradient(135deg, #1a180e 0%, #141208 100%)',
  ombre:    'linear-gradient(135deg, #120820 0%, #0a0514 100%)',
}

const ELEMENT_ACCENT: Record<string, string> = {
  physique: 'rgba(156,163,175,0.08)',
  feu:      'rgba(239,68,68,0.08)',
  glace:    'rgba(147,197,253,0.08)',
  foudre:   'rgba(251,191,36,0.08)',
  poison:   'rgba(74,222,128,0.08)',
  sacre:    'rgba(254,240,138,0.08)',
  ombre:    'rgba(167,139,250,0.08)',
}

export function ZoneBackground({ element, isActive, isLocked }: ZoneBackgroundProps) {
  const gradient = ELEMENT_GRADIENT[element] ?? ELEMENT_GRADIENT.physique
  const accent   = ELEMENT_ACCENT[element] ?? 'transparent'
  const icon     = ELEMENT_ICON[element] ?? '⚔️'

  return (
    <div style={{
      position: 'absolute',
      inset: 0,
      background: gradient,
      overflow: 'hidden',
      opacity: isLocked ? 0.5 : 1,
    }}>
      {/* Large decorative background icon */}
      <div style={{
        position: 'absolute',
        right: -10,
        top: -10,
        fontSize: 80,
        opacity: 0.06,
        lineHeight: 1,
        userSelect: 'none',
        filter: 'blur(2px)',
      }}>
        {icon}
      </div>
      {/* Accent radial glow */}
      <div style={{
        position: 'absolute',
        inset: 0,
        background: `radial-gradient(ellipse at 30% 50%, ${accent}, transparent 70%)`,
      }} />
      {/* Active pulse overlay */}
      {isActive && (
        <div style={{
          position: 'absolute',
          inset: 0,
          background: 'rgba(34,197,94,0.04)',
          animation: 'explore-pulse 2s ease-in-out infinite',
        }} />
      )}
    </div>
  )
}
