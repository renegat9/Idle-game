export interface User {
  id: number
  username: string
  email: string
  gold: number
  level: number
  xp: number
  xp_to_next_level: number
  narrator_frequency: string
}

export interface ComputedStats {
  hp: number
  atq: number
  def: number
  vit: number
  cha: number
  int: number
  max_hp: number
  current_hp: number
}

export interface HeroRace {
  id: number
  name: string
  slug: string
  passive_bonus_description: string
}

export interface HeroClass {
  id: number
  name: string
  slug: string
  role: string
  key_skill_name: string
}

export interface HeroTrait {
  id: number
  name: string
  slug: string
  description: string
  flavor_text: string
}

export interface Item {
  id: number
  name: string
  description: string | null
  rarity: 'commun' | 'peu_commun' | 'rare' | 'epique' | 'legendaire' | 'wtf'
  slot: 'arme' | 'armure' | 'casque' | 'bottes' | 'accessoire' | 'truc_bizarre'
  element: string
  item_level: number
  atq: number
  def: number
  hp: number
  vit: number
  cha: number
  int: number
  sell_value: number
  equipped_by_hero_id: number | null
  is_ai_generated: boolean
  effects: { key: string; description: string }[]
}

export interface Hero {
  id: number
  name: string
  level: number
  xp: number
  xp_to_next_level: number
  slot_index: number
  is_active: boolean
  deaths: number
  talent_points: number
  race: HeroRace
  class: HeroClass
  trait: HeroTrait | null
  computed_stats: ComputedStats
  equipped_items: Item[]
}

export interface Zone {
  id: number
  slug: string
  name: string
  description: string
  level_min: number
  level_max: number
  dominant_element: string
  is_magical: boolean
  order_index: number
  is_unlocked: boolean
  boss_defeated: boolean
  total_victories: number
  is_current: boolean
}

export interface Race {
  id: number
  slug: string
  name: string
  base_hp: number
  base_atq: number
  base_def: number
  base_vit: number
  base_cha: number
  base_int: number
  passive_bonus_description: string
}

export interface GameClass {
  id: number
  slug: string
  name: string
  role: string
  key_skill_name: string
  key_skill_description: string
}

export interface Trait {
  id: number
  slug: string
  name: string
  description: string
  flavor_text: string
}

export interface OfflineResult {
  had_exploration: boolean
  elapsed_seconds: number
  combats_simulated: number
  victories?: number
  defeats?: number
  xp_gained: number
  gold_gained: number
  items_gained: Item[]
  narrator_comment: string
}
