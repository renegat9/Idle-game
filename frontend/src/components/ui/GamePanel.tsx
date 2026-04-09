import type { ReactNode } from 'react'

interface GamePanelProps {
  title?: string
  icon?: string
  variant?: 'default' | 'gold' | 'magic' | 'danger' | 'success'
  children: ReactNode
  className?: string
  style?: React.CSSProperties
  noPadding?: boolean
}

const VARIANT_CLASS: Record<string, string> = {
  default: 'game-panel',
  gold:    'game-panel game-panel-gold',
  magic:   'game-panel game-panel-magic',
  danger:  'game-panel game-panel-danger',
  success: 'game-panel game-panel-success',
}

const TITLE_COLOR: Record<string, string> = {
  default: '#9ca3af',
  gold:    '#fbbf24',
  magic:   '#a78bfa',
  danger:  '#fca5a5',
  success: '#86efac',
}

export function GamePanel({ title, icon, variant = 'default', children, className = '', style, noPadding = false }: GamePanelProps) {
  return (
    <div className={`${VARIANT_CLASS[variant]} ${className}`} style={style}>
      {title && (
        <div className="game-panel-header">
          {icon && <span className="panel-icon">{icon}</span>}
          <span className="panel-title" style={{ color: TITLE_COLOR[variant] }}>{title}</span>
        </div>
      )}
      {noPadding ? children : (
        <div className="game-panel-body">{children}</div>
      )}
    </div>
  )
}
