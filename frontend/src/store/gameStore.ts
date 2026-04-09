import { create } from 'zustand'
import type { Hero, Zone, Item, OfflineResult } from '../types'

interface GameState {
  heroes: Hero[]
  zones: Zone[]
  equippedItems: Item[]
  unequippedItems: Item[]
  narratorComment: string | null
  unreadEventsCount: number
  gold: number
  offlineResult: OfflineResult | null
  isExploring: boolean
  currentZoneName: string | null

  setHeroes: (heroes: Hero[]) => void
  setZones: (zones: Zone[]) => void
  setInventory: (equipped: Item[], unequipped: Item[]) => void
  setNarratorComment: (comment: string) => void
  setUnreadCount: (count: number) => void
  setGold: (gold: number) => void
  setOfflineResult: (result: OfflineResult | null) => void
  setExploring: (is: boolean, zoneName?: string) => void
  updateFromPoll: (data: { unread_events_count: number; latest_narrator: string | null; gold: number }) => void
}

export const useGameStore = create<GameState>((set) => ({
  heroes: [],
  zones: [],
  equippedItems: [],
  unequippedItems: [],
  narratorComment: null,
  unreadEventsCount: 0,
  gold: 0,
  offlineResult: null,
  isExploring: false,
  currentZoneName: null,

  setHeroes: (heroes) => set({ heroes }),
  setZones: (zones) => set({ zones }),
  setInventory: (equipped, unequipped) => set({ equippedItems: equipped, unequippedItems: unequipped }),
  setNarratorComment: (comment) => set({ narratorComment: comment }),
  setUnreadCount: (count) => set({ unreadEventsCount: count }),
  setGold: (gold) => set({ gold }),
  setOfflineResult: (result) => set({ offlineResult: result }),
  setExploring: (is, zoneName) => set({ isExploring: is, currentZoneName: zoneName ?? null }),
  updateFromPoll: (data) =>
    set({
      unreadEventsCount: data.unread_events_count,
      narratorComment: data.latest_narrator ?? undefined,
      gold: data.gold,
    }),
}))
