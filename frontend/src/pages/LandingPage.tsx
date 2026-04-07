import { Link } from 'react-router-dom'

const FEATURES = [
  {
    icon: '⚔️',
    title: 'Héros incompétents',
    desc: 'Recrutez jusqu\'à 5 héros avec des traits négatifs uniques. Le Couard fuit, le Narcoleptique dort en plein combat, le Pyromane met le feu à tout — y compris ses alliés.',
  },
  {
    icon: '🏰',
    title: 'Exploration idle',
    desc: 'Vos héros explorent pendant que vous dormez. Ils reviennent avec du loot, des cicatrices, et des anecdotes que vous préférerez ne pas connaître.',
  },
  {
    icon: '📜',
    title: 'Quêtes narratives',
    desc: 'Des choix moralement douteux à chaque étape. "Récupérer le fromage volé par un rat" ou "négocier diplomatiquement avec ledit rat" — les deux finissent mal.',
  },
  {
    icon: '⚗️',
    title: 'Forge de Gérard',
    desc: 'Craftez, fusionnez, enchantez. Gérard le forgeron aura un commentaire sarcastique sur chaque création. Il a tort. Enfin, pas toujours.',
  },
  {
    icon: '✨',
    title: 'Synergies cachées',
    desc: 'Certaines combinaisons classe + trait débloquent des synergies secrètes. Le Barbare Pyromane est terrifying. Le Barde Narcoleptique endort tout le monde, ennemis et alliés.',
  },
  {
    icon: '🌍',
    title: 'Boss Mondial',
    desc: 'Un boss partagé entre tous les joueurs. Attaquez, contribuez, récoltez. Le boss se moque de vos dégâts. Puis il tombe quand même.',
  },
  {
    icon: '🤖',
    title: 'Narrateur IA',
    desc: 'Un narrateur sarcastique alimenté par Gemini commente chaque action. Il vous déteste, mais de façon constructive.',
  },
  {
    icon: '🍺',
    title: 'Taverne',
    desc: 'Recrutez des héros — parfois des légendes (avec un passé douteux). Soignez vos debuffs. Écoutez une musique d\'ambiance contextuelle. Commandiez une bière.',
  },
]

const CLASSES = [
  { name: 'Guerrier', icon: '🗡️', role: 'Tank', desc: 'Frappe fort, encaisse plus fort.' },
  { name: 'Mage', icon: '🔮', role: 'DPS magique', desc: 'Puissant. Fragile. Arrogant.' },
  { name: 'Voleur', icon: '🗝️', role: 'DPS physique', desc: 'Vol de loot inclus.' },
  { name: 'Barde', icon: '🎵', role: 'Support', desc: 'Chante. Parfois ça aide.' },
  { name: 'Barbare', icon: '🪓', role: 'DPS brut', desc: 'Rage. Toujours en rage.' },
  { name: 'Prêtre', icon: '✝️', role: 'Soin', desc: 'Soigne quand il ne fuit pas.' },
  { name: 'Ranger', icon: '🏹', role: 'DPS distance', desc: 'Tire de loin. Rate de près.' },
  { name: 'Nécromancien', icon: '💀', role: 'Invocateur', desc: 'Ses alliés sont morts. C\'est voulu.' },
]

const TESTIMONIALS = [
  { text: '"Mon Barde s\'est endormi pendant le combat contre le dragon. On a quand même gagné. Je ne sais pas comment."', author: 'Aventurier_Anonyme' },
  { text: '"Le Narrateur m\'a dit que ma stratégie était \'audacieuse\'. Il voulait dire \'idiote\'."', author: 'Gruntak_le_Vaillant' },
  { text: '"Gérard a commenté mon enchantement avec un soupir. Juste un soupir."', author: 'Mage_Philosophe_42' },
]

export function LandingPage() {
  return (
    <div style={{ minHeight: '100vh', background: '#0a0a0f', color: '#f9fafb', fontFamily: 'system-ui, sans-serif' }}>

      {/* Nav */}
      <nav style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 40px', borderBottom: '1px solid #1f2937', position: 'sticky', top: 0, background: '#0a0a0fee', backdropFilter: 'blur(8px)', zIndex: 10 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <span style={{ fontSize: 24 }}>🏰</span>
          <span style={{ color: '#7c3aed', fontWeight: 'bold', fontSize: 16 }}>Le Donjon des Incompétents</span>
        </div>
        <div style={{ display: 'flex', gap: 12 }}>
          <Link to="/login" style={{ color: '#9ca3af', textDecoration: 'none', padding: '8px 16px', borderRadius: 6, fontSize: 14 }}>
            Se connecter
          </Link>
          <Link to="/register" style={{ background: '#7c3aed', color: 'white', textDecoration: 'none', padding: '8px 20px', borderRadius: 6, fontSize: 14, fontWeight: 'bold' }}>
            Jouer gratuitement
          </Link>
        </div>
      </nav>

      {/* Hero */}
      <section style={{ textAlign: 'center', padding: '100px 20px 80px', maxWidth: 800, margin: '0 auto' }}>
        <div style={{ display: 'inline-block', background: '#1a0a2e', border: '1px solid #7c3aed', borderRadius: 20, padding: '6px 16px', fontSize: 13, color: '#a78bfa', marginBottom: 24 }}>
          ✨ Un idle RPG humoristique inspiré de Kaamelott & Naheulbeuk
        </div>
        <h1 style={{ fontSize: 'clamp(36px, 6vw, 64px)', fontWeight: 900, lineHeight: 1.1, margin: '0 0 24px' }}>
          Des héros{' '}
          <span style={{ color: '#7c3aed' }}>incompétents.</span>
          <br />
          Un loot{' '}
          <span style={{ color: '#d97706' }}>absurde.</span>
          <br />
          Un narrateur qui vous{' '}
          <span style={{ color: '#dc2626' }}>déteste.</span>
        </h1>
        <p style={{ color: '#9ca3af', fontSize: 18, lineHeight: 1.6, marginBottom: 40, maxWidth: 600, margin: '0 auto 40px' }}>
          Recrutez une équipe de héros ratés, envoyez-les explorer des donjons pendant que vous dormez,
          et revenez découvrir ce qu'ils ont cassé. Le Narrateur prend des notes.
        </p>
        <div style={{ display: 'flex', gap: 16, justifyContent: 'center', flexWrap: 'wrap' }}>
          <Link to="/register" style={{
            background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
            color: 'white', textDecoration: 'none',
            padding: '14px 32px', borderRadius: 8, fontSize: 16, fontWeight: 'bold',
            boxShadow: '0 0 30px rgba(124,58,237,0.4)',
          }}>
            Commencer l'aventure →
          </Link>
          <Link to="/login" style={{
            background: 'transparent', color: '#9ca3af', textDecoration: 'none',
            padding: '14px 32px', borderRadius: 8, fontSize: 16,
            border: '1px solid #374151',
          }}>
            J'ai déjà un compte
          </Link>
        </div>
        <p style={{ color: '#4b5563', fontSize: 13, marginTop: 16 }}>Gratuit. Sans pub. Le Narrateur est inclus.</p>
      </section>

      {/* Stats */}
      <section style={{ background: '#0f0f1a', borderTop: '1px solid #1f2937', borderBottom: '1px solid #1f2937', padding: '40px 20px' }}>
        <div style={{ display: 'flex', justifyContent: 'center', gap: 'clamp(20px, 8vw, 80px)', flexWrap: 'wrap', maxWidth: 900, margin: '0 auto' }}>
          {[
            { value: '8', label: 'Classes de héros' },
            { value: '10', label: 'Traits négatifs' },
            { value: '7', label: 'Synergies cachées' },
            { value: '8+', label: 'Zones à explorer' },
            { value: '168', label: 'Talents à débloquer' },
            { value: '∞', label: 'Sarcasmes du Narrateur' },
          ].map(({ value, label }) => (
            <div key={label} style={{ textAlign: 'center' }}>
              <div style={{ fontSize: 36, fontWeight: 900, color: '#7c3aed' }}>{value}</div>
              <div style={{ color: '#6b7280', fontSize: 13, marginTop: 4 }}>{label}</div>
            </div>
          ))}
        </div>
      </section>

      {/* Features */}
      <section style={{ padding: '80px 20px', maxWidth: 1100, margin: '0 auto' }}>
        <h2 style={{ textAlign: 'center', fontSize: 32, fontWeight: 800, marginBottom: 12 }}>
          Tout ce qu'il faut pour échouer <span style={{ color: '#7c3aed' }}>avec style</span>
        </h2>
        <p style={{ textAlign: 'center', color: '#6b7280', marginBottom: 60, fontSize: 15 }}>
          Un système complet d'incompétence organisée.
        </p>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 20 }}>
          {FEATURES.map(({ icon, title, desc }) => (
            <div key={title} style={{
              background: '#0f172a', border: '1px solid #1e293b',
              borderRadius: 12, padding: 24,
              transition: 'border-color 0.2s',
            }}
              onMouseEnter={e => (e.currentTarget.style.borderColor = '#7c3aed')}
              onMouseLeave={e => (e.currentTarget.style.borderColor = '#1e293b')}
            >
              <div style={{ fontSize: 32, marginBottom: 12 }}>{icon}</div>
              <h3 style={{ color: '#f1f5f9', fontSize: 16, fontWeight: 'bold', marginBottom: 8 }}>{title}</h3>
              <p style={{ color: '#64748b', fontSize: 13, lineHeight: 1.6, margin: 0 }}>{desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Classes */}
      <section style={{ background: '#0f0f1a', borderTop: '1px solid #1f2937', padding: '80px 20px' }}>
        <div style={{ maxWidth: 1000, margin: '0 auto' }}>
          <h2 style={{ textAlign: 'center', fontSize: 32, fontWeight: 800, marginBottom: 12 }}>
            8 classes, <span style={{ color: '#7c3aed' }}>autant de façons de rater</span>
          </h2>
          <p style={{ textAlign: 'center', color: '#6b7280', marginBottom: 48, fontSize: 15 }}>
            Combinez avec l'un des 10 traits négatifs pour un résultat unique (et imprévisible).
          </p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 12 }}>
            {CLASSES.map(({ name, icon, role, desc }) => (
              <div key={name} style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 10, padding: 16, textAlign: 'center' }}>
                <div style={{ fontSize: 28, marginBottom: 8 }}>{icon}</div>
                <div style={{ color: '#f9fafb', fontWeight: 'bold', fontSize: 14 }}>{name}</div>
                <div style={{ color: '#7c3aed', fontSize: 11, marginBottom: 6 }}>{role}</div>
                <div style={{ color: '#4b5563', fontSize: 12 }}>{desc}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Comment ça marche */}
      <section style={{ padding: '80px 20px', maxWidth: 800, margin: '0 auto' }}>
        <h2 style={{ textAlign: 'center', fontSize: 32, fontWeight: 800, marginBottom: 48 }}>
          Comment ça marche ?
        </h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
          {[
            { step: '1', title: 'Recrutez votre équipe', desc: 'Choisissez vos héros à la taverne : race, classe, et un trait négatif qui rendra votre vie intéressante. Les héros légendaires apparaissent rarement — ils valent le coup.', color: '#7c3aed' },
            { step: '2', title: 'Lancez l\'exploration', desc: 'Envoyez vos héros explorer une zone. Ils combattent, trouvent du loot, accomplissent des quêtes. Vous pouvez fermer le jeu — ils continuent sans vous (jusqu\'à 12h).', color: '#2563eb' },
            { step: '3', title: 'Revenez collecter', desc: 'Récupérez l\'XP, l\'or et le loot accumulés. Le Narrateur vous résumera les événements avec le niveau d\'enthousiasme habituel (faible).', color: '#059669' },
            { step: '4', title: 'Améliorez, craftez, recommencez', desc: 'Équipez, enchantez à la Forge, débloquez des talents, découvrez les synergies cachées. Progressez vers les zones difficiles et le Boss Mondial.', color: '#d97706' },
          ].map(({ step, title, desc, color }, i) => (
            <div key={step} style={{ display: 'flex', gap: 24, position: 'relative' }}>
              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                <div style={{
                  width: 44, height: 44, borderRadius: '50%', background: color,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontWeight: 900, fontSize: 18, flexShrink: 0,
                }}>{step}</div>
                {i < 3 && <div style={{ width: 2, flex: 1, background: '#1f2937', margin: '8px 0' }} />}
              </div>
              <div style={{ paddingBottom: i < 3 ? 32 : 0 }}>
                <h3 style={{ color: '#f9fafb', fontSize: 18, fontWeight: 'bold', marginBottom: 8, marginTop: 10 }}>{title}</h3>
                <p style={{ color: '#6b7280', fontSize: 14, lineHeight: 1.7, margin: 0 }}>{desc}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Témoignages */}
      <section style={{ background: '#0f0f1a', borderTop: '1px solid #1f2937', padding: '80px 20px' }}>
        <div style={{ maxWidth: 900, margin: '0 auto' }}>
          <h2 style={{ textAlign: 'center', fontSize: 28, fontWeight: 800, marginBottom: 48 }}>
            Ce que disent nos <span style={{ color: '#7c3aed' }}>survivants</span>
          </h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: 20 }}>
            {TESTIMONIALS.map(({ text, author }) => (
              <div key={author} style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 10, padding: 24 }}>
                <p style={{ color: '#9ca3af', fontSize: 14, lineHeight: 1.7, fontStyle: 'italic', margin: '0 0 16px' }}>{text}</p>
                <div style={{ color: '#4b5563', fontSize: 12 }}>— {author}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA final */}
      <section style={{ textAlign: 'center', padding: '100px 20px' }}>
        <div style={{ fontSize: 48, marginBottom: 20 }}>🏰</div>
        <h2 style={{ fontSize: 36, fontWeight: 900, marginBottom: 16 }}>
          Prêt à décevoir le Narrateur ?
        </h2>
        <p style={{ color: '#6b7280', fontSize: 16, marginBottom: 40, maxWidth: 500, margin: '0 auto 40px' }}>
          Rejoignez l'aventure. Vos héros incompétents vous attendent à la taverne.
          Le Narrateur aussi. Il soupire déjà.
        </p>
        <Link to="/register" style={{
          background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
          color: 'white', textDecoration: 'none',
          padding: '16px 40px', borderRadius: 8, fontSize: 18, fontWeight: 'bold',
          boxShadow: '0 0 40px rgba(124,58,237,0.5)',
          display: 'inline-block',
        }}>
          Commencer gratuitement
        </Link>
        <p style={{ color: '#374151', fontSize: 12, marginTop: 20 }}>
          Aucune carte bancaire. Aucun engagement. Juste de l'incompétence.
        </p>
      </section>

      {/* Footer */}
      <footer style={{ borderTop: '1px solid #1f2937', padding: '24px 40px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 12 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <span>🏰</span>
          <span style={{ color: '#4b5563', fontSize: 13 }}>Le Donjon des Incompétents</span>
        </div>
        <div style={{ display: 'flex', gap: 20 }}>
          <Link to="/login" style={{ color: '#4b5563', textDecoration: 'none', fontSize: 13 }}>Connexion</Link>
          <Link to="/register" style={{ color: '#4b5563', textDecoration: 'none', fontSize: 13 }}>Inscription</Link>
        </div>
        <div style={{ color: '#374151', fontSize: 12 }}>
          Le Narrateur décline toute responsabilité pour les pertes de héros.
        </div>
      </footer>

    </div>
  )
}
