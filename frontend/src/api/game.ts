import apiClient from './client'
import type { Hero, Item, Zone, Race, GameClass, Trait, OfflineResult } from '../types'

export const heroApi = {
  list: () => apiClient.get<{ heroes: Hero[] }>('/heroes'),
  create: (data: { name: string; race_id: number; class_id: number; trait_id: number }) =>
    apiClient.post<{ hero: Hero; message: string; narrator_comment: string }>('/heroes', data),
  equip: (heroId: number, itemId: number) =>
    apiClient.post<{ hero: Hero; message: string }>(`/heroes/${heroId}/equip`, { item_id: itemId }),
}

export const explorationApi = {
  status: () => apiClient.get('/exploration/status'),
  start: (zoneId: number) => apiClient.post('/exploration/start', { zone_id: zoneId }),
  collect: () => apiClient.post<{
    result: OfflineResult
    user: { gold: number; level: number; xp: number; xp_to_next_level: number }
    heroes: Array<{ id: number; name: string; level: number; xp: number; xp_to_next_level: number; current_hp: number; max_hp: number }>
  }>('/exploration/collect'),
}

export const inventoryApi = {
  list: () => apiClient.get<{ equipped: Item[]; unequipped: Item[]; total_count: number }>('/inventory'),
  sell: (itemId: number) =>
    apiClient.post<{ message: string; gold_earned: number; new_gold_total: number }>('/inventory/sell', { item_id: itemId }),
}

export const zoneApi = {
  list: () => apiClient.get<{ zones: Zone[] }>('/zones'),
}

export const dashboardApi = {
  get: () => apiClient.get('/game/dashboard'),
  poll: () => apiClient.get('/game/poll'),
}

export const referenceApi = {
  races: () => apiClient.get<{ races: Race[] }>('/reference/races'),
  classes: () => apiClient.get<{ classes: GameClass[] }>('/reference/classes'),
  traits: () => apiClient.get<{ traits: Trait[] }>('/reference/traits'),
}

export const questApi = {
  list: () => apiClient.get<{ quests: any[] }>('/quests'),
  daily: () => apiClient.get<{ quests: any[]; date: string; refresh_at: string }>('/quests/daily'),
  start: (questId: number) => apiClient.post<any>(`/quests/${questId}/start`),
  choose: (userQuestId: number, choiceId: string, heroId?: number) =>
    apiClient.post<any>(`/user-quests/${userQuestId}/choose`, { choice_id: choiceId, hero_id: heroId }),
}

export const craftingApi = {
  get: () => apiClient.get<{ materials: any[]; recipes: any[] }>('/crafting'),
  fuse: (itemIds: number[]) => apiClient.post<any>('/crafting/fuse', { item_ids: itemIds }),
  dismantle: (itemId: number) => apiClient.post<any>('/crafting/dismantle', { item_id: itemId }),
  craft: (recipeId: number) => apiClient.post<any>('/crafting/craft', { recipe_id: recipeId }),
  enchantments: () => apiClient.get<{ enchantments: any[] }>('/crafting/enchantments'),
  enchant: (itemId: number, enchantmentSlug: string) =>
    apiClient.post<any>('/crafting/enchant', { item_id: itemId, enchantment: enchantmentSlug }),
}

export const tavernApi = {
  get: () => apiClient.get<any>('/tavern'),
  hire: (recruitId: number) => apiClient.post<any>(`/tavern/hire/${recruitId}`),
  removeDebuff: (heroId: number, buffId: number) =>
    apiClient.post<any>('/tavern/remove-debuff', { hero_id: heroId, buff_id: buffId }),
  music: (style?: string) =>
    apiClient.get<{ style: string; file_path: string; prompt: string }>('/tavern/music', { params: style ? { style } : {} }),
}

export const reputationApi = {
  all: () => apiClient.get<{ reputations: any[] }>('/reputation'),
  zone: (zoneId: number) => apiClient.get<any>(`/reputation/${zoneId}`),
}

export const eventsApi = {
  current: () => apiClient.get<{ active_events: any[]; modifiers: any; has_event: boolean }>('/events/current'),
  all: () => apiClient.get<{ events: any[] }>('/events'),
}

export const musicApi = {
  current: () => apiClient.get<{ style: string; file_path: string; context: string }>('/music/current'),
}

export const shopApi = {
  get: (zoneId?: number) =>
    apiClient.get<any>('/shop', { params: zoneId ? { zone_id: zoneId } : undefined }),
  buy: (itemId: number) => apiClient.post<any>('/shop/buy', { item_id: itemId }),
}

export const dungeonApi = {
  status: () => apiClient.get<any>('/dungeon'),
  start: (zoneId: number) => apiClient.post<any>('/dungeon/start', { zone_id: zoneId }),
  enter: (dungeonId: number) => apiClient.post<any>(`/dungeon/${dungeonId}/enter`),
  abandon: (dungeonId: number) => apiClient.post<any>(`/dungeon/${dungeonId}/abandon`),
}

export const worldBossApi = {
  status: () => apiClient.get<any>('/world-boss'),
  attack: () => apiClient.post<any>('/world-boss/attack'),
  leaderboard: () => apiClient.get<any>('/world-boss/leaderboard'),
}

export const talentApi = {
  tree: (heroId: number) => apiClient.get<any>(`/heroes/${heroId}/talents`),
  allocate: (heroId: number, talentId: number) =>
    apiClient.post<any>(`/heroes/${heroId}/talents/${talentId}/allocate`),
  reset: (heroId: number) => apiClient.post<any>(`/heroes/${heroId}/talents/reset`),
}
export const profileApi = {
  get: () => apiClient.get<any>('/profile'),
  update: (data: { narrator_frequency?: string; username?: string }) =>
    apiClient.patch<any>('/profile', data),
}

export const consumableApi = {
  list: () => apiClient.get<{ consumables: any[] }>('/consumables'),
  catalog: () => apiClient.get<{ catalog: any[] }>('/consumables/catalog'),
  use: (slug: string) => apiClient.post<{ message: string; narrator_comment: string; result: any }>(`/consumables/${slug}/use`),
}
