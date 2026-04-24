import { Tooltip } from '../ui/Tooltip'

const RARITY_CONFIG: Record<string, { bg: string; color: string; border: string; label: string; icon: string; desc: string }> = {
  commun:     { bg: '#1f2937', color: '#9ca3af', border: '#374151',  label: 'Commun',      icon: '◆', desc: 'Objet ordinaire. Se trouve partout, souvent dans les poches des gens.' },
  peu_commun: { bg: '#052e16', color: '#86efac', border: '#166534',  label: 'Peu Commun',  icon: '◆', desc: 'Objet de qualité correcte. Pas exceptionnel, mais ça fait le travail.' },
  rare:       { bg: '#0c1a33', color: '#93c5fd', border: '#1d4ed8',  label: 'Rare',        icon: '◆', desc: 'Objet de qualité supérieure aux effets notables. Difficile à trouver.' },
  epique:     { bg: '#1a0733', color: '#d8b4fe', border: '#6d28d9',  label: 'Épique',      icon: '◆', desc: 'Objet puissant forgé par des artisans de légende. Extrêmement rare.' },
  legendaire: { bg: '#1a0d00', color: '#fdba74', border: '#b45309',  label: 'Légendaire',  icon: '◆', desc: 'Objet exceptionnel dont les récits traversent les âges. Presque mythique.' },
  wtf:        { bg: '#1a0520', color: '#f0abfc', border: '#86198f',  label: '??? WTF ???', icon: '✦', desc: 'Un objet dont l\'existence défie toute logique connue. Le Narrateur refuse de commenter.' },
}

export function RarityBadge({ rarity }: { rarity: string }) {
  const cfg = RARITY_CONFIG[rarity] ?? RARITY_CONFIG.commun
  const isWtf = rarity === 'wtf'
  return (
    <Tooltip content={cfg.desc}>
      <span
        className={isWtf ? 'anim-wtf' : ''}
        style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: 3,
          background: cfg.bg,
          color: cfg.color,
          border: `1px solid ${cfg.border}`,
          padding: '2px 7px',
          borderRadius: 4,
          fontSize: 11,
          fontWeight: 700,
          letterSpacing: '0.04em',
          boxShadow: isWtf ? '0 0 6px rgba(244,114,182,0.3)' : 'none',
        }}
      >
        <span style={{ fontSize: 8 }}>{cfg.icon}</span>
        {cfg.label}
      </span>
    </Tooltip>
  )
}
