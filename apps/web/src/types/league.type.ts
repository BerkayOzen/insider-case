import type { TeamInput } from "./team.type"

export type Standing = {
  id: number
  teamId: number
  teamName: string
  played: number
  won: number
  drawn: number
  lost: number
  gf: number
  ga: number
  gd: number
  points: number
}

export type Match = {
  id: number
  homeTeamId: number
  awayTeamId: number
  homeScore: number | null
  awayScore: number | null
  isPlayed: boolean
  homeTeam?: { id: number; name: string } | null
  awayTeam?: { id: number; name: string } | null
}

export type Week = {
  id: number
  number: number
  isPlayed: boolean
  byeTeams: { id: number; name: string }[]
  matches: Match[]
}

export type LeagueState = {
  season: { id: number; name: string; currentWeek: number; isFinished: boolean }
  currentWeek: number
  weeks: Week[]
  standings: Standing[]
  prediction: Record<string, number> | null
}

export type InitLeaguePayload = {
  teams: TeamInput[];
};