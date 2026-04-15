import type { ReactNode, ButtonHTMLAttributes } from 'react'

interface GameButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'gold' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  icon?: string
  loading?: boolean
  children: ReactNode
}

const SIZE_CLASS: Record<string, string> = {
  sm: 'game-btn-sm',
  md: '',
  lg: 'game-btn-lg',
}

const VARIANT_CLASS: Record<string, string> = {
  primary:   'game-btn-primary',
  secondary: 'game-btn-secondary',
  danger:    'game-btn-danger',
  gold:      'game-btn-gold',
  ghost:     'game-btn-ghost',
}

export function GameButton({
  variant = 'primary',
  size = 'md',
  icon,
  loading = false,
  children,
  disabled,
  className = '',
  ...props
}: GameButtonProps) {
  return (
    <button
      className={`game-btn ${VARIANT_CLASS[variant]} ${SIZE_CLASS[size]} ${className}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? (
        <>
          <span style={{ display: 'inline-block', animation: 'spin 0.8s linear infinite', fontSize: 12 }}>⟳</span>
          <span>Chargement…</span>
        </>
      ) : (
        <>
          {icon && <span>{icon}</span>}
          {children}
        </>
      )}
    </button>
  )
}
