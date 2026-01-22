import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
})

export const initLeague = (payload?: { teams: { name: string; power: number }[] }) =>
  api.post('/league/init', payload ?? {})
export const playWeek = () => api.post('/league/play-week')
export const playAll = () => api.post('/league/play-all')
export const getLeagueState = () => api.get('/league/state')
export const getDefaultTeams = () => api.get('/teams/defaults')
export const getTeamById = (teamId: number) => api.get(`/teams/${teamId}`)
export const updateTeam = (teamId: number, payload: { power: number }) =>
  api.put(`/teams/${teamId}`, payload)
export const getMatchById = (matchId: number) => api.get(`/matches/${matchId}`)
export const updateMatchResult = (matchId: number, homeScore: number, awayScore: number) =>
  api.put(`/matches/${matchId}`, {
    home_score: homeScore,
    away_score: awayScore,
  })

export default api
