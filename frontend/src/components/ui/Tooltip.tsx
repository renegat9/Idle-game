import { useRef, useState } from 'react'

interface TooltipProps {
  content: string
  children: React.ReactNode
  position?: 'top' | 'bottom'
}

export function Tooltip({ content, children, position = 'top' }: TooltipProps) {
  const [visible, setVisible] = useState(false)
  const [coords, setCoords] = useState({ x: 0, y: 0 })
  const ref = useRef<HTMLSpanElement>(null)

  function show() {
    if (!ref.current) return
    const rect = ref.current.getBoundingClientRect()
    setCoords({
      x: rect.left + rect.width / 2,
      y: position === 'top' ? rect.top - 8 : rect.bottom + 8,
    })
    setVisible(true)
  }

  return (
    <>
      <span
        ref={ref}
        onMouseEnter={show}
        onMouseLeave={() => setVisible(false)}
        style={{ display: 'inline-flex', cursor: 'help' }}
      >
        {children}
      </span>
      {visible && (
        <div
          style={{
            position: 'fixed',
            left: coords.x,
            top: position === 'top' ? coords.y : coords.y,
            transform: position === 'top' ? 'translate(-50%, -100%)' : 'translate(-50%, 0)',
            zIndex: 9999,
            maxWidth: 260,
            background: '#0d1117',
            border: '1px solid #374151',
            borderRadius: 6,
            padding: '7px 10px',
            fontSize: 12,
            color: '#d1d5db',
            lineHeight: 1.5,
            pointerEvents: 'none',
            boxShadow: '0 4px 16px rgba(0,0,0,0.6)',
            whiteSpace: 'normal',
            textAlign: 'left',
          }}
        >
          {content}
          <div style={{
            position: 'absolute',
            left: '50%',
            transform: 'translateX(-50%)',
            ...(position === 'top'
              ? { bottom: -5, borderTop: '5px solid #374151', borderLeft: '5px solid transparent', borderRight: '5px solid transparent' }
              : { top: -5, borderBottom: '5px solid #374151', borderLeft: '5px solid transparent', borderRight: '5px solid transparent' }
            ),
            width: 0, height: 0,
          }} />
        </div>
      )}
    </>
  )
}
