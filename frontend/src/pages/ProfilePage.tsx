import { useEffect, useState } from 'react'
import { profileApi } from '../api/game'
import { useAuthStore } from '../store/authStore'

const NARRATOR_OPTIONS = [
  { value: 'never',    label: 'Jamais — Le Narrateur est muselé' },
  { value: 'rare',     label: 'Rare — Il intervient de temps en temps' },
  { value: 'normal',   label: 'Normal — Il commente régulièrement' },
  { value: 'annoying', label: 'Omniprésent — Il ne s\'arrête jamais' },
]

type EconomyEntry = {
  transaction_type: string
  source: string
  amount: number
  balance_after: number
  description: string | null
  created_at: string
}

type Stats = {
  total_kills: number
  total_defeats: number
  quests_done: number
  items_crafted: number
  dungeons_done: number
  gold_earned: number
  gold_spent: number
}

type ProfileData = {
  user: {
    id: number
    username: string
    email: string
    level: number
    xp: number
    xp_to_next_level: number
    gold: number
    narrator_frequency: string
    created_at: string
  }
  heroes: { id: number; name: string; level: number; race: string; class: string }[]
  stats: Stats
  economy_log: EconomyEntry[]
  ai_budget: { used: number; limit: number; percent: number }
}

export function ProfilePage() {
  const { updateUser } = useAuthStore()
  const [profile, setProfile]           = useState<ProfileData | null>(null)
  const [loading, setLoading]           = useState(true)
  const [saving, setSaving]             = useState(false)
  const [frequency, setFrequency]       = useState<string>('normal')
  const [message, setMessage]           = useState<string | null>(null)
  const [activeTab, setActiveTab]       = useState<'stats' | 'economy'>('stats')

  useEffect(() => {
    profileApi.get()
      .then(({ data }) => {
        setProfile(data)
        setFrequency(data.user.narrator_frequency)
      })
      .finally(() => setLoading(false))
  }, [])

  const handleSave = async () => {
    setSaving(true)
    setMessage(null)
    try {
      const { data } = await profileApi.update({ narrator_frequency: frequency })
      setMessage(data.message)
      updateUser(data.user)
    } catch {
      setMessage('Erreur lors de la sauvegarde.')
    } finally {
      setSaving(false)
    }
  }

  if (loading) return <div className="p-8 text-center text-gray-400">Chargement du profil...</div>
  if (!profile) return <div className="p-8 text-center text-red-400">Erreur lors du chargement.</div>

  const { user, heroes, stats, economy_log, ai_budget } = profile

  return (
    <div className="max-w-4xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="bg-gray-800 rounded-xl p-6 flex items-start gap-6">
        <div className="bg-indigo-700 rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold text-white">
          {user.username.charAt(0).toUpperCase()}
        </div>
        <div className="flex-1">
          <h1 className="text-2xl font-bold text-white">{user.username}</h1>
          <p className="text-gray-400 text-sm">{user.email}</p>
          <div className="flex gap-4 mt-2 text-sm">
            <span className="text-yellow-400">Niveau {user.level}</span>
            <span className="text-gray-400">{user.xp} / {user.xp_to_next_level} XP</span>
            <span className="text-yellow-300">{user.gold.toLocaleString()} or</span>
          </div>
          <p className="text-xs text-gray-500 mt-1">
            Aventurier depuis le {new Date(user.created_at).toLocaleDateString('fr-FR')}
          </p>
        </div>
      </div>

      {/* Heroes */}
      {heroes.length > 0 && (
        <div className="bg-gray-800 rounded-xl p-4">
          <h2 className="text-lg font-semibold text-gray-200 mb-3">Mon équipe</h2>
          <div className="flex flex-wrap gap-2">
            {heroes.map(h => (
              <div key={h.id} className="bg-gray-700 rounded-lg px-3 py-2 text-sm">
                <span className="text-white font-medium">{h.name}</span>
                <span className="text-gray-400 ml-2">Niv.{h.level}</span>
                <span className="text-gray-500 ml-2">{h.race} {h.class}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Narrator preference */}
      <div className="bg-gray-800 rounded-xl p-4 space-y-3">
        <h2 className="text-lg font-semibold text-gray-200">Fréquence du Narrateur</h2>
        <p className="text-xs text-gray-400">
          Le Narrateur peut commenter vos aventures à votre guise. Ou pas du tout, si vous êtes sensible aux critiques.
        </p>
        <select
          value={frequency}
          onChange={e => setFrequency(e.target.value)}
          className="w-full bg-gray-700 border border-gray-600 text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          {NARRATOR_OPTIONS.map(opt => (
            <option key={opt.value} value={opt.value}>{opt.label}</option>
          ))}
        </select>
        <button
          onClick={handleSave}
          disabled={saving}
          className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-colors"
        >
          {saving ? 'Sauvegarde...' : 'Sauvegarder'}
        </button>
        {message && (
          <p className="text-sm text-green-400 italic">{message}</p>
        )}
      </div>

      {/* Tabs: stats / economy */}
      <div className="bg-gray-800 rounded-xl overflow-hidden">
        <div className="flex border-b border-gray-700">
          {(['stats', 'economy'] as const).map(tab => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`px-6 py-3 text-sm font-medium transition-colors ${
                activeTab === tab
                  ? 'text-white border-b-2 border-indigo-400'
                  : 'text-gray-400 hover:text-gray-200'
              }`}
            >
              {tab === 'stats' ? 'Statistiques' : 'Historique économique'}
            </button>
          ))}
        </div>

        {activeTab === 'stats' && (
          <div className="p-5 grid grid-cols-2 sm:grid-cols-3 gap-4">
            <StatCard label="Monstres vaincus"   value={stats.total_kills.toLocaleString()}   color="text-green-400" />
            <StatCard label="Défaites"            value={stats.total_defeats.toLocaleString()} color="text-red-400" />
            <StatCard label="Quêtes terminées"    value={stats.quests_done.toLocaleString()}   color="text-blue-400" />
            <StatCard label="Objets craftés"      value={stats.items_crafted.toLocaleString()} color="text-purple-400" />
            <StatCard label="Donjons complétés"   value={stats.dungeons_done.toLocaleString()} color="text-orange-400" />
            <StatCard label="Or total gagné"      value={`${stats.gold_earned.toLocaleString()} or`} color="text-yellow-400" />
            <StatCard label="Or total dépensé"    value={`${stats.gold_spent.toLocaleString()} or`}  color="text-yellow-600" />
            {ai_budget.limit > 0 && (
              <div className="col-span-2 sm:col-span-3">
                <p className="text-xs text-gray-400 mb-1">Budget IA journalier</p>
                <div className="w-full bg-gray-700 rounded-full h-2">
                  <div
                    className="bg-indigo-500 h-2 rounded-full transition-all"
                    style={{ width: `${Math.min(ai_budget.percent, 100)}%` }}
                  />
                </div>
                <p className="text-xs text-gray-500 mt-1">{ai_budget.used} / {ai_budget.limit} unités utilisées aujourd'hui</p>
              </div>
            )}
          </div>
        )}

        {activeTab === 'economy' && (
          <div className="overflow-auto max-h-80">
            {economy_log.length === 0 ? (
              <p className="p-5 text-gray-400 text-sm italic">Aucune transaction enregistrée.</p>
            ) : (
              <table className="w-full text-sm">
                <thead className="bg-gray-750 text-gray-400 text-xs uppercase">
                  <tr>
                    <th className="px-4 py-2 text-left">Type</th>
                    <th className="px-4 py-2 text-right">Montant</th>
                    <th className="px-4 py-2 text-right">Solde après</th>
                    <th className="px-4 py-2 text-left hidden sm:table-cell">Description</th>
                    <th className="px-4 py-2 text-right hidden sm:table-cell">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {economy_log.map((entry, i) => (
                    <tr key={i} className="border-t border-gray-700 hover:bg-gray-750">
                      <td className="px-4 py-2">
                        <span className={`font-medium ${entry.transaction_type === 'gain' ? 'text-green-400' : 'text-red-400'}`}>
                          {entry.transaction_type === 'gain' ? '+' : '-'}{entry.amount.toLocaleString()} or
                        </span>
                        <span className="text-xs text-gray-500 ml-2">{entry.source}</span>
                      </td>
                      <td className="px-4 py-2 text-right text-yellow-300">{entry.amount.toLocaleString()} or</td>
                      <td className="px-4 py-2 text-right text-gray-400">{entry.balance_after.toLocaleString()} or</td>
                      <td className="px-4 py-2 text-gray-400 hidden sm:table-cell text-xs">{entry.description ?? '—'}</td>
                      <td className="px-4 py-2 text-right text-gray-500 hidden sm:table-cell text-xs">
                        {new Date(entry.created_at).toLocaleDateString('fr-FR')}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </div>
    </div>
  )
}

function StatCard({ label, value, color }: { label: string; value: string; color: string }) {
  return (
    <div className="bg-gray-750 rounded-lg p-3 text-center">
      <p className={`text-xl font-bold ${color}`}>{value}</p>
      <p className="text-xs text-gray-400 mt-1">{label}</p>
    </div>
  )
}
