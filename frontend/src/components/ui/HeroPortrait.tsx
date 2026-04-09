interface HeroPortraitProps {
  classSlug: string
  imagePath?: string | null
  name?: string
  size?: number
  animClass?: string
  hpPercent?: number
}

// SVG silhouette per class — stylized fantasy art
const GUERRIER = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Body */}
    <ellipse cx="30" cy="52" rx="13" ry="18" fill="#1e3a5f" stroke="#3b82f6" strokeWidth="1.2"/>
    {/* Pauldrons */}
    <ellipse cx="18" cy="38" rx="7" ry="5" fill="#2563eb" stroke="#60a5fa" strokeWidth="1"/>
    <ellipse cx="42" cy="38" rx="7" ry="5" fill="#2563eb" stroke="#60a5fa" strokeWidth="1"/>
    {/* Head */}
    <circle cx="30" cy="22" r="9" fill="#1e3a5f" stroke="#3b82f6" strokeWidth="1.5"/>
    {/* Helmet plume */}
    <path d="M26 14 Q30 6 34 14" stroke="#60a5fa" strokeWidth="2" fill="none" strokeLinecap="round"/>
    {/* Visor */}
    <path d="M24 22 Q30 26 36 22" stroke="#93c5fd" strokeWidth="1.2" fill="none"/>
    {/* Sword */}
    <line x1="48" y1="18" x2="48" y2="60" stroke="#94a3b8" strokeWidth="2.5" strokeLinecap="round"/>
    <line x1="43" y1="35" x2="53" y2="35" stroke="#cbd5e1" strokeWidth="1.5" strokeLinecap="round"/>
    <circle cx="48" cy="34" r="2" fill="#fbbf24"/>
    {/* Shield */}
    <path d="M10 32 Q10 52 17 58 Q17 42 10 32Z" fill="#1d4ed8" stroke="#3b82f6" strokeWidth="1.2"/>
    {/* Legs */}
    <rect x="22" y="66" width="7" height="10" rx="2" fill="#1e3a5f" stroke="#2563eb" strokeWidth="1"/>
    <rect x="31" y="66" width="7" height="10" rx="2" fill="#1e3a5f" stroke="#2563eb" strokeWidth="1"/>
  </svg>
)

const BARBARE = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Body — massive */}
    <ellipse cx="30" cy="52" rx="16" ry="18" fill="#3d1f1f" stroke="#ef4444" strokeWidth="1.5"/>
    {/* Fur collar */}
    <path d="M14 42 Q14 36 30 36 Q46 36 46 42" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
    {/* Head */}
    <circle cx="30" cy="22" r="10" fill="#3d1f1f" stroke="#ef4444" strokeWidth="1.5"/>
    {/* Horned helmet */}
    <path d="M20 16 L16 6 L22 14" fill="#dc2626" stroke="#ef4444" strokeWidth="1"/>
    <path d="M40 16 L44 6 L38 14" fill="#dc2626" stroke="#ef4444" strokeWidth="1"/>
    {/* Face */}
    <circle cx="26" cy="21" r="2" fill="#fca5a5"/>
    <circle cx="34" cy="21" r="2" fill="#fca5a5"/>
    <path d="M25 27 Q30 29 35 27" stroke="#fca5a5" strokeWidth="1.5" fill="none" strokeLinecap="round"/>
    {/* Great Axe */}
    <line x1="50" y1="10" x2="48" y2="65" stroke="#78716c" strokeWidth="3" strokeLinecap="round"/>
    <path d="M44 12 Q55 18 52 28 Q48 18 44 12Z" fill="#9ca3af" stroke="#d1d5db" strokeWidth="1"/>
    {/* Bracers */}
    <rect x="13" y="45" width="5" height="8" rx="2" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
    <rect x="42" y="45" width="5" height="8" rx="2" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
    {/* Legs */}
    <rect x="20" y="66" width="8" height="10" rx="2" fill="#44403c" stroke="#78716c" strokeWidth="1"/>
    <rect x="32" y="66" width="8" height="10" rx="2" fill="#44403c" stroke="#78716c" strokeWidth="1"/>
  </svg>
)

const MAGE = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Robe */}
    <path d="M15 38 Q14 70 22 76 Q30 78 38 76 Q46 70 45 38 Q40 42 30 42 Q20 42 15 38Z" fill="#2d1b69" stroke="#7c3aed" strokeWidth="1.5"/>
    {/* Body */}
    <rect x="21" y="30" width="18" height="14" rx="3" fill="#1e1b4b" stroke="#6d28d9" strokeWidth="1"/>
    {/* Head */}
    <circle cx="30" cy="20" r="9" fill="#312e81" stroke="#6d28d9" strokeWidth="1.5"/>
    {/* Wizard hat */}
    <path d="M21 16 L30 2 L39 16Z" fill="#4c1d95" stroke="#7c3aed" strokeWidth="1.5"/>
    <ellipse cx="30" cy="16" rx="10" ry="3" fill="#3b0764" stroke="#6d28d9" strokeWidth="1"/>
    {/* Star on hat */}
    <circle cx="30" cy="8" r="2" fill="#fbbf24"/>
    {/* Eyes */}
    <circle cx="26" cy="20" r="2" fill="#a78bfa"/>
    <circle cx="34" cy="20" r="2" fill="#a78bfa"/>
    <circle cx="26" cy="20" r="1" fill="white"/>
    <circle cx="34" cy="20" r="1" fill="white"/>
    {/* Staff */}
    <line x1="48" y1="12" x2="46" y2="68" stroke="#6b7280" strokeWidth="2.5" strokeLinecap="round"/>
    <circle cx="48" cy="12" r="5" fill="#1e1b4b" stroke="#7c3aed" strokeWidth="1.5"/>
    <circle cx="48" cy="12" r="3" fill="#a78bfa" opacity="0.8"/>
    {/* Magic sparks */}
    <circle cx="44" cy="8" r="1.5" fill="#c4b5fd" opacity="0.7"/>
    <circle cx="52" cy="10" r="1" fill="#ddd6fe" opacity="0.6"/>
    {/* Robe bottom */}
    <path d="M22 76 Q16 78 14 76" stroke="#7c3aed" strokeWidth="1" fill="none"/>
    <path d="M38 76 Q44 78 46 76" stroke="#7c3aed" strokeWidth="1" fill="none"/>
  </svg>
)

const NECROMANCIEN = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Dark robe */}
    <path d="M13 38 Q12 72 20 76 Q30 79 40 76 Q48 72 47 38 Q41 43 30 43 Q19 43 13 38Z" fill="#0f0a1a" stroke="#6d28d9" strokeWidth="1.2"/>
    {/* Body */}
    <rect x="20" y="30" width="20" height="14" rx="3" fill="#0f0a1a" stroke="#4c1d95" strokeWidth="1"/>
    {/* Bone/skull accents on robe */}
    <ellipse cx="30" cy="50" rx="3" ry="4" fill="#1c1c2e" stroke="#4c1d95" strokeWidth="1"/>
    {/* Hood */}
    <path d="M19 22 Q18 10 30 8 Q42 10 41 22 Q41 34 30 36 Q19 34 19 22Z" fill="#1a1030" stroke="#4c1d95" strokeWidth="1.5"/>
    {/* Skull face in hood */}
    <circle cx="30" cy="20" r="7" fill="#e5e7eb" opacity="0.9"/>
    {/* Skull eye sockets */}
    <ellipse cx="27" cy="18" rx="2" ry="2.5" fill="#0f0a1a"/>
    <ellipse cx="33" cy="18" rx="2" ry="2.5" fill="#0f0a1a"/>
    {/* Skull nose */}
    <path d="M29 22 L30 24 L31 22" fill="#0f0a1a"/>
    {/* Skull teeth */}
    <line x1="27" y1="25" x2="27" y2="27" stroke="#0f0a1a" strokeWidth="1.2"/>
    <line x1="30" y1="26" x2="30" y2="28" stroke="#0f0a1a" strokeWidth="1.2"/>
    <line x1="33" y1="25" x2="33" y2="27" stroke="#0f0a1a" strokeWidth="1.2"/>
    {/* Staff with skull topper */}
    <line x1="47" y1="18" x2="45" y2="68" stroke="#374151" strokeWidth="2.5" strokeLinecap="round"/>
    <circle cx="47" cy="13" r="5" fill="#e5e7eb" stroke="#6d28d9" strokeWidth="1"/>
    <ellipse cx="45" cy="11" rx="1.5" ry="2" fill="#0f0a1a"/>
    <ellipse cx="49" cy="11" rx="1.5" ry="2" fill="#0f0a1a"/>
    {/* Purple glow on staff */}
    <circle cx="47" cy="13" r="7" fill="#7c3aed" opacity="0.15"/>
    {/* Bony hands */}
    <path d="M14 52 Q10 48 12 44 Q14 48 16 46" stroke="#9ca3af" strokeWidth="1.5" fill="none" strokeLinecap="round"/>
  </svg>
)

const BARDE = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Colorful outfit */}
    <ellipse cx="30" cy="52" rx="13" ry="18" fill="#1e3a5f" stroke="#3b82f6" strokeWidth="1"/>
    {/* Tabard with pattern */}
    <path d="M24 34 L24 66 Q30 68 36 66 L36 34 Q30 36 24 34Z" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
    <line x1="30" y1="34" x2="30" y2="66" stroke="#fbbf24" strokeWidth="1" strokeDasharray="3,3"/>
    {/* Head */}
    <circle cx="30" cy="21" r="9" fill="#1c1917" stroke="#78716c" strokeWidth="1.5"/>
    {/* Feathered hat */}
    <ellipse cx="30" cy="15" rx="11" ry="4" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
    <path d="M38 12 Q42 4 38 8" stroke="#22c55e" strokeWidth="2" fill="none" strokeLinecap="round"/>
    <path d="M40 13 Q46 6 42 10" stroke="#4ade80" strokeWidth="1.5" fill="none" strokeLinecap="round"/>
    {/* Face */}
    <circle cx="26" cy="21" r="1.5" fill="#fcd34d"/>
    <circle cx="34" cy="21" r="1.5" fill="#fcd34d"/>
    <path d="M25 25 Q30 28 35 25" stroke="#fcd34d" strokeWidth="1.5" fill="none" strokeLinecap="round"/>
    {/* Lute */}
    <ellipse cx="44" cy="48" rx="7" ry="10" fill="#92400e" stroke="#b45309" strokeWidth="1.5"/>
    <ellipse cx="44" cy="48" rx="5" ry="7" fill="#451a03" opacity="0.5"/>
    <circle cx="44" cy="48" r="2" fill="#78350f"/>
    <line x1="44" y1="38" x2="44" y2="28" stroke="#78350f" strokeWidth="2" strokeLinecap="round"/>
    {/* Lute strings */}
    <line x1="41" y1="40" x2="41" y2="56" stroke="#fbbf24" strokeWidth="0.8" opacity="0.8"/>
    <line x1="44" y1="40" x2="44" y2="56" stroke="#fbbf24" strokeWidth="0.8" opacity="0.8"/>
    <line x1="47" y1="40" x2="47" y2="56" stroke="#fbbf24" strokeWidth="0.8" opacity="0.8"/>
    {/* Musical notes */}
    <text x="10" y="30" fontSize="10" fill="#fbbf24" opacity="0.6">♪</text>
    <text x="8" y="44" fontSize="8" fill="#60a5fa" opacity="0.5">♩</text>
    {/* Legs */}
    <rect x="23" y="66" width="6" height="10" rx="2" fill="#1e3a5f" stroke="#2563eb" strokeWidth="1"/>
    <rect x="31" y="66" width="6" height="10" rx="2" fill="#7c2d12" stroke="#b45309" strokeWidth="1"/>
  </svg>
)

const PRETRE = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* White robe */}
    <path d="M14 38 Q14 72 22 76 Q30 78 38 76 Q46 72 46 38 Q40 42 30 42 Q20 42 14 38Z" fill="#f0f9ff" stroke="#e0f2fe" strokeWidth="1"/>
    {/* Body */}
    <rect x="21" y="30" width="18" height="14" rx="3" fill="#e0f2fe" stroke="#bae6fd" strokeWidth="1"/>
    {/* Gold cross on chest */}
    <line x1="30" y1="34" x2="30" y2="44" stroke="#fbbf24" strokeWidth="2.5"/>
    <line x1="25" y1="38" x2="35" y2="38" stroke="#fbbf24" strokeWidth="2.5"/>
    {/* Head */}
    <circle cx="30" cy="20" r="9" fill="#f0f9ff" stroke="#bae6fd" strokeWidth="1.5"/>
    {/* Halo */}
    <circle cx="30" cy="20" r="12" fill="none" stroke="#fbbf24" strokeWidth="1.5" opacity="0.5" strokeDasharray="2,2"/>
    {/* Face */}
    <circle cx="26" cy="19" r="1.5" fill="#0369a1"/>
    <circle cx="34" cy="19" r="1.5" fill="#0369a1"/>
    <path d="M26 25 Q30 27 34 25" stroke="#0369a1" strokeWidth="1.2" fill="none" strokeLinecap="round"/>
    {/* Holy staff */}
    <line x1="47" y1="15" x2="45" y2="68" stroke="#d4af37" strokeWidth="2.5" strokeLinecap="round"/>
    {/* Cross atop staff */}
    <line x1="47" y1="10" x2="47" y2="20" stroke="#fbbf24" strokeWidth="2"/>
    <line x1="43" y1="14" x2="51" y2="14" stroke="#fbbf24" strokeWidth="2"/>
    {/* Divine glow */}
    <circle cx="47" cy="14" r="7" fill="#fbbf24" opacity="0.12"/>
    {/* Floating light orbs */}
    <circle cx="14" cy="34" r="2" fill="#fef3c7" opacity="0.7"/>
    <circle cx="12" cy="26" r="1.5" fill="#fde68a" opacity="0.5"/>
    {/* Robe hem */}
    <path d="M14 72 Q10 74 12 76" stroke="#bae6fd" strokeWidth="1" fill="none"/>
    <path d="M46 72 Q50 74 48 76" stroke="#bae6fd" strokeWidth="1" fill="none"/>
  </svg>
)

const VOLEUR = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Leather outfit */}
    <ellipse cx="30" cy="52" rx="12" ry="18" fill="#1c1a10" stroke="#78716c" strokeWidth="1"/>
    {/* Leather straps */}
    <path d="M22 34 Q22 50 24 66" stroke="#78716c" strokeWidth="2" fill="none"/>
    <path d="M38 34 Q38 50 36 66" stroke="#78716c" strokeWidth="2" fill="none"/>
    <line x1="22" y1="44" x2="38" y2="44" stroke="#78716c" strokeWidth="1.5"/>
    {/* Head with hood */}
    <circle cx="30" cy="21" r="9" fill="#111827" stroke="#374151" strokeWidth="1.5"/>
    <path d="M21 16 Q22 8 30 7 Q38 8 39 16" fill="#111827" stroke="#374151" strokeWidth="1"/>
    <path d="M21 20 Q20 12 30 10 Q40 12 39 20" fill="#1f2937" stroke="#374151" strokeWidth="0.8" opacity="0.8"/>
    {/* Face — mostly hidden */}
    <circle cx="26" cy="22" r="1.5" fill="#10b981"/>
    <circle cx="34" cy="22" r="1.5" fill="#10b981"/>
    {/* Daggers */}
    <line x1="14" y1="32" x2="16" y2="54" stroke="#9ca3af" strokeWidth="2" strokeLinecap="round"/>
    <path d="M12 30 L18 34 L14 32Z" fill="#cbd5e1"/>
    <line x1="46" y1="32" x2="44" y2="54" stroke="#9ca3af" strokeWidth="2" strokeLinecap="round"/>
    <path d="M48 30 L42 34 L46 32Z" fill="#cbd5e1"/>
    {/* Throwing stars on belt */}
    <polygon points="30,42 32,46 30,50 28,46" fill="#6b7280" stroke="#9ca3af" strokeWidth="0.8"/>
    {/* Boots */}
    <rect x="22" y="66" width="7" height="10" rx="2" fill="#1c1a10" stroke="#44403c" strokeWidth="1"/>
    <rect x="31" y="66" width="7" height="10" rx="2" fill="#1c1a10" stroke="#44403c" strokeWidth="1"/>
  </svg>
)

const RANGER = () => (
  <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    {/* Forest outfit */}
    <ellipse cx="30" cy="52" rx="13" ry="18" fill="#14532d" stroke="#16a34a" strokeWidth="1"/>
    {/* Cloak */}
    <path d="M17 36 Q14 60 16 72 Q30 76 44 72 Q46 60 43 36 Q38 42 30 42 Q22 42 17 36Z" fill="#166534" stroke="#15803d" strokeWidth="1" opacity="0.8"/>
    {/* Head */}
    <circle cx="30" cy="21" r="9" fill="#1c1917" stroke="#292524" strokeWidth="1.5"/>
    {/* Ranger hood */}
    <path d="M21 18 Q22 10 30 8 Q38 10 39 18 Q38 22 30 22 Q22 22 21 18Z" fill="#14532d" stroke="#15803d" strokeWidth="1"/>
    {/* Face */}
    <circle cx="26" cy="21" r="1.5" fill="#4ade80"/>
    <circle cx="34" cy="21" r="1.5" fill="#4ade80"/>
    <path d="M26 25 Q30 27 34 25" stroke="#86efac" strokeWidth="1.2" fill="none" strokeLinecap="round"/>
    {/* Bow */}
    <path d="M48 12 Q54 30 48 68" stroke="#92400e" strokeWidth="2.5" fill="none" strokeLinecap="round"/>
    <line x1="48" y1="14" x2="48" y2="66" stroke="#fbbf24" strokeWidth="0.8" opacity="0.8"/>
    {/* Arrow on bow */}
    <line x1="46" y1="40" x2="20" y2="36" stroke="#d1fae5" strokeWidth="1.5" strokeLinecap="round"/>
    <polygon points="20,36 24,33 22,38" fill="#86efac"/>
    <polygon points="46,40 50,38 48,43" fill="#86efac"/>
    {/* Quiver */}
    <rect x="8" y="34" width="6" height="18" rx="3" fill="#92400e" stroke="#b45309" strokeWidth="1"/>
    <line x1="10" y1="34" x2="10" y2="30" stroke="#fbbf24" strokeWidth="1" strokeLinecap="round"/>
    <line x1="12" y1="34" x2="12" y2="28" stroke="#d1fae5" strokeWidth="1" strokeLinecap="round"/>
    <line x1="14" y1="34" x2="14" y2="31" stroke="#fbbf24" strokeWidth="1" strokeLinecap="round"/>
    {/* Boots */}
    <rect x="22" y="66" width="7" height="10" rx="2" fill="#14532d" stroke="#166534" strokeWidth="1"/>
    <rect x="31" y="66" width="7" height="10" rx="2" fill="#14532d" stroke="#166534" strokeWidth="1"/>
  </svg>
)

const HERO_SVG: Record<string, React.FC> = {
  guerrier:     GUERRIER,
  barbare:      BARBARE,
  mage:         MAGE,
  necromancien: NECROMANCIEN,
  barde:        BARDE,
  pretre:       PRETRE,
  voleur:       VOLEUR,
  ranger:       RANGER,
}

const CLASS_BG: Record<string, string> = {
  guerrier:     'linear-gradient(135deg, #0f1e3d, #0d1117)',
  barbare:      'linear-gradient(135deg, #1a0505, #0d0805)',
  mage:         'linear-gradient(135deg, #0f0a2e, #08051a)',
  necromancien: 'linear-gradient(135deg, #060310, #0a0514)',
  barde:        'linear-gradient(135deg, #0f1a2e, #0a0d1a)',
  pretre:       'linear-gradient(135deg, #071a2e, #041424)',
  voleur:       'linear-gradient(135deg, #0a0d0a, #060808)',
  ranger:       'linear-gradient(135deg, #051a0d, #040d07)',
}

export function HeroPortrait({ classSlug, imagePath, name, size = 80, animClass = '', hpPercent = 100 }: HeroPortraitProps) {
  const SvgComponent = HERO_SVG[classSlug]
  const bg = CLASS_BG[classSlug] ?? 'linear-gradient(135deg, #111827, #0d1117)'

  const frameClass = hpPercent <= 25
    ? 'hero-portrait-frame hero-portrait-frame-danger'
    : hpPercent <= 50
      ? 'hero-portrait-frame hero-portrait-frame-warning'
      : 'hero-portrait-frame'

  return (
    <div
      className="hero-portrait-wrapper"
      style={{ width: size, height: size }}
    >
      <div
        className={frameClass}
        style={{ width: size, height: size, background: bg }}
      >
        {imagePath ? (
          <img
            src={`/${imagePath}`}
            alt={name ?? classSlug}
            className={animClass}
            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
          />
        ) : SvgComponent ? (
          <div className={animClass} style={{ width: '100%', height: '100%', padding: 4 }}>
            <SvgComponent />
          </div>
        ) : (
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: '100%', fontSize: size * 0.5 }}>
            ⚔️
          </div>
        )}
      </div>
    </div>
  )
}
