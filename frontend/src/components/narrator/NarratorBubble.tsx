import { useGameStore } from '../../store/gameStore'

export function NarratorBubble({ comment }: { comment?: string }) {
  const storeComment = useGameStore((s) => s.narratorComment)
  const text = comment ?? storeComment

  if (!text) return null

  return (
    <div style={{
      background: '#1a1a2e',
      border: '1px solid #7c3aed',
      borderRadius: 8,
      padding: '12px 16px',
      margin: '8px 0',
      fontStyle: 'italic',
      color: '#c4b5fd',
      fontSize: 14,
    }}>
      <span style={{ color: '#7c3aed', fontWeight: 'bold', marginRight: 8 }}>Le Narrateur :</span>
      {text}
    </div>
  )
}
