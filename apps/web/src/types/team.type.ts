import type { MatchSummary } from "./match.type";

export type TeamInput = {
  name: string;
  power: number;
};

export type TeamDetail = {
  id: number
  seasonId: number
  name: string
  power: number
  season: { id: number; name: string; currentWeek: number; isFinished: boolean } | null
}

export type TeamDetailResponse = {
  team: TeamDetail
  matches: MatchSummary[]
}