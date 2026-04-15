interface MonsterPortraitProps {
  name?: string
  imagePath?: string | null
  level?: number
  size?: number
}

// Generic monster SVG silhouette
const MonsterSVG = () => (
  <svg viewBox="0 0 60 70" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Body */}
    <ellipse cx="30" cy="45" rx="16" ry="18" fill="#1a0505" stroke="#dc2626" strokeWidth="1.5"/>
    {/* Claws */}
    <path d="M14 38 L8 32 M14 42 L6 40 M14 46 L8 50" stroke="#ef4444" strokeWidth="2" strokeLinecap="round"/>
    <path d="M46 38 L52 32 M46 42 L54 40 M46 46 L52 50" stroke="#ef4444" strokeWidth="2" strokeLinecap="round"/>
    {/* Head */}
    <ellipse cx="30" cy="22" rx="13" ry="14" fill="#1a0505" stroke="#dc2626" strokeWidth="1.5"/>
    {/* Horns */}
    <path d="M20 14 L16 4 L22 12" fill="#b91c1c" stroke="#ef4444" strokeWidth="1"/>
    <path d="M40 14 L44 4 L38 12" fill="#b91c1c" stroke="#ef4444" strokeWidth="1"/>
    {/* Eyes — glowing red */}
    <circle cx="24" cy="20" r="4" fill="#450a0a"/>
    <circle cx="36" cy="20" r="4" fill="#450a0a"/>
    <circle cx="24" cy="20" r="2.5" fill="#ef4444" opacity="0.9"/>
    <circle cx="36" cy="20" r="2.5" fill="#ef4444" opacity="0.9"/>
    {/* Eye glow */}
    <circle cx="24" cy="20" r="5" fill="#ef4444" opacity="0.15"/>
    <circle cx="36" cy="20" r="5" fill="#ef4444" opacity="0.15"/>
    {/* Teeth */}
    <path d="M22 28 L24 33 L26 28 L28 33 L30 28 L32 33 L34 28 L36 33 L38 28" stroke="#9ca3af" strokeWidth="1.5" fill="none" strokeLinejoin="round"/>
    {/* Tail */}
    <path d="M42 58 Q50 52 46 44 Q50 52 46 60 Q44 62 42 60Z" fill="#7f1d1d" stroke="#dc2626" strokeWidth="1"/>
    {/* Feet */}
    <path d="M20 60 L16 66 M24 62 L22 68" stroke="#dc2626" strokeWidth="2" strokeLinecap="round"/>
    <path d="M40 60 L44 66 M36 62 L38 68" stroke="#dc2626" strokeWidth="2" strokeLinecap="round"/>
  </svg>
)

export function MonsterPortrait({ name, imagePath, level, size = 80 }: MonsterPortraitProps) {
  return (
    <div style={{
      width: size,
      height: size,
      borderRadius: 10,
      border: '2px solid #7f1d1d',
      background: 'linear-gradient(135deg, #1a0505, #0a0505)',
      boxShadow: '0 0 12px rgba(239,68,68,0.25)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      overflow: 'hidden',
      position: 'relative',
      flexShrink: 0,
    }}>
      {imagePath ? (
        <img src={`/${imagePath}`} alt={name ?? 'monstre'} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
      ) : (
        <div className="anim-breathe" style={{ width: '90%', height: '90%' }}>
          <MonsterSVG />
        </div>
      )}
      {level !== undefined && (
        <div style={{
          position: 'absolute',
          bottom: 4,
          right: 4,
          background: '#7f1d1d',
          color: '#fca5a5',
          fontSize: 10,
          fontWeight: 700,
          padding: '1px 5px',
          borderRadius: 3,
          fontFamily: 'var(--font-title)',
        }}>
          Niv.{level}
        </div>
      )}
    </div>
  )
}
