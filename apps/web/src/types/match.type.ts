export type MatchSummary = {
  id: number
  week: number | null
  isHome: boolean
  opponent: { id: number; name: string } | null
  homeScore: number | null
  awayScore: number | null
  isPlayed: boolean
}

export type MatchDetail = {
  id: number
  seasonId: number
  week: { id: number; number: number; isPlayed: boolean } | null
  homeTeam: { id: number; name: string } | null
  awayTeam: { id: number; name: string } | null
  homeScore: number | null
  awayScore: number | null
  isPlayed: boolean
}