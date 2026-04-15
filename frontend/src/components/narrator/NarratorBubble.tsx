import { useGameStore } from '../../store/gameStore'

export function NarratorBubble({ comment }: { comment?: string }) {
  const storeComment = useGameStore((s) => s.narratorComment)
  const text = comment ?? storeComment

  if (!text) return null

  return (
    <div className="narrator-bubble anim-slide-in" style={{ marginBottom: 16 }}>
      <div className="narrator-label">
        <span>📖</span>
        Le Narrateur
      </div>
      <p className="narrator-text" style={{ margin: 0 }}>
        « {text} »
      </p>
    </div>
  )
}
