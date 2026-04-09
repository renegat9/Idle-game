const RARITY_COLORS: Record<string, { bg: string; color: string; label: string }> = {
  commun:     { bg: '#374151', color: '#9ca3af', label: 'Commun' },
  peu_commun: { bg: '#14532d', color: '#86efac', label: 'Peu Commun' },
  rare:       { bg: '#1e3a5f', color: '#93c5fd', label: 'Rare' },
  epique:     { bg: '#3b0764', color: '#d8b4fe', label: 'Épique' },
  legendaire: { bg: '#431407', color: '#fdba74', label: 'Légendaire' },
  wtf:        { bg: '#4a044e', color: '#f0abfc', label: '??? WTF ???' },
}

export function RarityBadge({ rarity }: { rarity: string }) {
  const style = RARITY_COLORS[rarity] ?? RARITY_COLORS.commun
  return (
    <span style={{
      background: style.bg,
      color: style.color,
      padding: '2px 8px',
      borderRadius: 4,
      fontSize: 11,
      fontWeight: 'bold',
    }}>
      {style.label}
    </span>
  )
}
