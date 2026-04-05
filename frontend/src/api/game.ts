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
  start: (questId: number) => apiClient.post<any>(`/quests/${questId}/start`),
  choose: (userQuestId: number, choiceId: string, heroId?: number) =>
    apiClient.post<any>(`/user-quests/${userQuestId}/choose`, { choice_id: choiceId, hero_id: heroId }),
}

export const craftingApi = {
  get: () => apiClient.get<{ materials: any[]; recipes: any[] }>('/crafting'),
  fuse: (itemIds: number[]) => apiClient.post<any>('/crafting/fuse', { item_ids: itemIds }),
  dismantle: (itemId: number) => apiClient.post<any>('/crafting/dismantle', { item_id: itemId }),
  craft: (recipeId: number) => apiClient.post<any>('/crafting/craft', { recipe_id: recipeId }),
}

export const tavernApi = {
  get: () => apiClient.get<any>('/tavern'),
  hire: (recruitId: number) => apiClient.post<any>(`/tavern/hire/${recruitId}`),
  removeDebuff: (heroId: number, buffId: number) =>
    apiClient.post<any>('/tavern/remove-debuff', { hero_id: heroId, buff_id: buffId }),
}
