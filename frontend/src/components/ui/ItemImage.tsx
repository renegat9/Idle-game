interface ItemImageProps {
  slot: string
  rarity: string
  imageUrl?: string | null
  size?: number
  name?: string
}

const RARITY_BORDER: Record<string, string> = {
  commun:      '#4b5563',
  peu_commun:  '#166534',
  rare:        '#1d4ed8',
  epique:      '#6d28d9',
  legendaire:  '#b45309',
  wtf:         '#be185d',
}

const RARITY_BG: Record<string, string> = {
  commun:      '#0d1117',
  peu_commun:  '#052e16',
  rare:        '#0c1a33',
  epique:      '#1a0733',
  legendaire:  '#1a0d00',
  wtf:         '#1a0520',
}

const RARITY_GLOW: Record<string, string> = {
  commun:      'none',
  peu_commun:  '0 0 8px rgba(74,222,128,0.2)',
  rare:        '0 0 10px rgba(96,165,250,0.25)',
  epique:      '0 0 12px rgba(167,139,250,0.3)',
  legendaire:  '0 0 16px rgba(251,191,36,0.35)',
  wtf:         '0 0 16px rgba(244,114,182,0.4)',
}

// SVG icons per slot — styled fantasy icons
const SwordSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <line x1="8" y1="32" x2="32" y2="8" stroke={color} strokeWidth="3" strokeLinecap="round"/>
    <line x1="14" y1="22" x2="22" y2="14" stroke="white" strokeWidth="1.5" strokeLinecap="round" opacity="0.4"/>
    <circle cx="8" cy="32" r="3" fill={color} opacity="0.9"/>
    <line x1="16" y1="24" x2="24" y2="16" stroke={color} strokeWidth="5" strokeLinecap="round" opacity="0.3"/>
    <polygon points="32,6 36,10 34,14 30,10" fill={color} opacity="0.8"/>
  </svg>
)

const ArmourSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <path d="M20 4 L32 10 L32 24 Q32 34 20 38 Q8 34 8 24 L8 10 Z" fill="none" stroke={color} strokeWidth="2"/>
    <path d="M20 8 L28 12 L28 23 Q28 30 20 33 Q12 30 12 23 L12 12 Z" fill={color} opacity="0.15"/>
    <line x1="20" y1="4" x2="20" y2="38" stroke={color} strokeWidth="1.5" opacity="0.5"/>
    <line x1="10" y1="16" x2="30" y2="16" stroke={color} strokeWidth="1.5" opacity="0.5"/>
    <circle cx="20" cy="12" r="2" fill={color} opacity="0.8"/>
  </svg>
)

const HelmSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <path d="M10 28 Q8 18 20 10 Q32 18 30 28" fill="none" stroke={color} strokeWidth="2.5"/>
    <path d="M8 28 L32 28" stroke={color} strokeWidth="2.5" strokeLinecap="round"/>
    <path d="M14 28 L14 34" stroke={color} strokeWidth="2" strokeLinecap="round"/>
    <path d="M26 28 L26 34" stroke={color} strokeWidth="2" strokeLinecap="round"/>
    <path d="M12 22 Q16 18 20 18 Q24 18 28 22" stroke={color} strokeWidth="2" fill="none"/>
    <path d="M14 24 Q16 20 20 20 Q24 20 26 24" fill={color} opacity="0.15"/>
    <circle cx="20" cy="13" r="2" fill={color} opacity="0.7"/>
  </svg>
)

const BootsSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <path d="M12 8 L12 26 Q12 34 22 36 L30 36 L30 30 L22 30 Q16 28 16 22 L16 8 Z" fill={color} opacity="0.2" stroke={color} strokeWidth="2"/>
    <line x1="12" y1="16" x2="16" y2="16" stroke={color} strokeWidth="1.5" opacity="0.7"/>
    <line x1="12" y1="20" x2="16" y2="20" stroke={color} strokeWidth="1.5" opacity="0.7"/>
    <line x1="12" y1="24" x2="16" y2="24" stroke={color} strokeWidth="1.5" opacity="0.7"/>
    <path d="M16 36 L30 36" stroke={color} strokeWidth="3" strokeLinecap="round"/>
  </svg>
)

const GemSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <polygon points="20,4 34,14 34,26 20,36 6,26 6,14" fill={color} opacity="0.25" stroke={color} strokeWidth="2"/>
    <polygon points="20,10 28,16 28,24 20,30 12,24 12,16" fill={color} opacity="0.3"/>
    <polygon points="20,8 26,14 20,12 14,14" fill="white" opacity="0.3"/>
    <line x1="20" y1="4" x2="20" y2="36" stroke={color} strokeWidth="0.8" opacity="0.4"/>
    <line x1="6" y1="14" x2="34" y2="14" stroke={color} strokeWidth="0.8" opacity="0.4"/>
  </svg>
)

const WtfSVG = ({ color }: { color: string }) => (
  <svg viewBox="0 0 40 40" fill="none">
    <circle cx="20" cy="20" r="14" fill={color} opacity="0.1" stroke={color} strokeWidth="2" strokeDasharray="3,2"/>
    <text x="20" y="26" textAnchor="middle" fontSize="18" fill={color} fontWeight="bold" opacity="0.9">?</text>
    <circle cx="12" cy="10" r="2" fill={color} opacity="0.5"/>
    <circle cx="30" cy="8" r="1.5" fill={color} opacity="0.4"/>
    <circle cx="10" cy="28" r="1" fill={color} opacity="0.6"/>
    <circle cx="32" cy="30" r="2" fill={color} opacity="0.3"/>
  </svg>
)

const SLOT_SVG: Record<string, (color: string) => React.ReactElement> = {
  arme:         (c) => <SwordSVG color={c} />,
  armure:       (c) => <ArmourSVG color={c} />,
  casque:       (c) => <HelmSVG color={c} />,
  bottes:       (c) => <BootsSVG color={c} />,
  accessoire:   (c) => <GemSVG color={c} />,
  truc_bizarre: (c) => <WtfSVG color={c} />,
}

const RARITY_SVG_COLOR: Record<string, string> = {
  commun:      '#9ca3af',
  peu_commun:  '#4ade80',
  rare:        '#60a5fa',
  epique:      '#a78bfa',
  legendaire:  '#fbbf24',
  wtf:         '#f472b6',
}

export function ItemImage({ slot, rarity, imageUrl, size = 64, name }: ItemImageProps) {
  const borderColor = RARITY_BORDER[rarity] ?? '#4b5563'
  const bgColor     = RARITY_BG[rarity] ?? '#0d1117'
  const glow        = RARITY_GLOW[rarity] ?? 'none'
  const svgColor    = RARITY_SVG_COLOR[rarity] ?? '#9ca3af'
  const svgFn       = SLOT_SVG[slot]
  const isWtf       = rarity === 'wtf'
  const isLegend    = rarity === 'legendaire'

  return (
    <div
      className={isWtf ? 'anim-wtf' : isLegend ? 'anim-glow-gold' : ''}
      style={{
        width: size,
        height: size,
        borderRadius: 8,
        border: `2px solid ${borderColor}`,
        background: bgColor,
        boxShadow: glow,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        overflow: 'hidden',
        flexShrink: 0,
        position: 'relative',
      }}
    >
      {imageUrl ? (
        <img
          src={imageUrl}
          alt={name ?? slot}
          style={{ width: '100%', height: '100%', objectFit: 'cover' }}
        />
      ) : svgFn ? (
        <div style={{ width: size - 12, height: size - 12 }}>
          {svgFn(svgColor)}
        </div>
      ) : (
        <span style={{ fontSize: size * 0.4 }}>📦</span>
      )}
      {/* Legendary shimmer overlay */}
      {isLegend && (
        <div className="item-legendaire-shimmer" style={{ position: 'absolute', inset: 0, borderRadius: 6 }} />
      )}
    </div>
  )
}
