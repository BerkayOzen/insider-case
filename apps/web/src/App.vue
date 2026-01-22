<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import FixtureList from './components/FixtureList.vue'
import LeagueInitForm from './components/LeagueInitForm.vue'
import MatchDetailModal from './components/MatchDetailModal.vue'
import TeamDetailModal from './components/TeamDetailModal.vue'
import LeagueService, { type InitLeaguePayload } from './services/league-service'

type Match = {
  id: number
  homeTeamId: number
  awayTeamId: number
  homeScore: number | null
  awayScore: number | null
  isPlayed: boolean
  homeTeam?: { id: number; name: string } | null
  awayTeam?: { id: number; name: string } | null
}

type Week = {
  id: number
  number: number
  isPlayed: boolean
  byeTeams: { id: number; name: string }[]
  matches: Match[]
}

type Standing = {
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

type LeagueState = {
  season: { id: number; name: string; currentWeek: number; isFinished: boolean }
  currentWeek: number
  weeks: Week[]
  standings: Standing[]
  prediction: Record<string, number> | null
}

const state = ref<LeagueState | null>(null)
const fixtures = ref<Week[]>([])
const isTeamModalOpen = ref(false)
const selectedTeamId = ref<number | null>(null)
const isMatchModalOpen = ref(false)
const selectedMatchId = ref<number | null>(null)
const loading = ref(false)
const error = ref('')
const stateNotFound = ref(false)

const leagueService = new LeagueService();

const latestPlayedWeek = computed(() => {
  if (!state.value) return null
  const playedWeeks = state.value.weeks.filter((week) => week.isPlayed)
  if (playedWeeks.length === 0) return state.value.weeks[0] ?? null
  return playedWeeks[playedWeeks.length - 1]
})

const loadState = async () => {
  loading.value = true
  error.value = ''
  stateNotFound.value = false
  try {
    const response = await leagueService.state();
    state.value = response.data
    const fixturesResponse = await leagueService.fixtures();
    fixtures.value = fixturesResponse.data?.weeks ?? []
  } catch (err: any) {
    state.value = null
    fixtures.value = []
    if (err?.response?.status === 404) {
      stateNotFound.value = true
      error.value = 'No league state found, create a new league.'
    } else {
      error.value = err?.message ?? 'Failed to load league state.'
    }
  } finally {
    loading.value = false
  }
}

const handleCreateLeague = async (payload: InitLeaguePayload) => {
  loading.value = true
  error.value = ''
  try {
    const response = await leagueService.init(payload)
    state.value = response.data
    stateNotFound.value = false
    const fixturesResponse = await leagueService.fixtures();
    fixtures.value = fixturesResponse.data?.weeks ?? []
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to initialize league.'
  } finally {
    loading.value = false
  }
}

const openTeamModal = (teamId: number) => {
  selectedTeamId.value = teamId
  isTeamModalOpen.value = true
}

const closeTeamModal = () => {
  isTeamModalOpen.value = false
}

const openMatchModal = (matchId: number) => {
  selectedMatchId.value = matchId
  isMatchModalOpen.value = true
}

const closeMatchModal = () => {
  isMatchModalOpen.value = false
}

const refreshLeagueData = async () => {
  const response = await leagueService.state()
  state.value = response.data
  const fixturesResponse = await leagueService.fixtures()
  fixtures.value = fixturesResponse.data?.weeks ?? []
}

const handlePlayWeek = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await leagueService.playWeek();
    state.value = response.data
    const fixturesResponse = await leagueService.fixtures();
    fixtures.value = fixturesResponse.data?.weeks ?? []
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to play week.'
  } finally {
    loading.value = false
  }
}

const handlePlayAll = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await leagueService.playAll()
    state.value = response.data
    const fixturesResponse = await leagueService.fixtures();
    fixtures.value = fixturesResponse.data?.weeks ?? []
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to play all weeks.'
  } finally {
    loading.value = false
  }
}

const handleReset = async () => {
  loading.value = true
  error.value = ''
  try {
    await leagueService.reset()
    await loadState()
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to reset league.'
  } finally {
    loading.value = false
  }
}

onMounted(loadState)
</script>

<template>
  <div class="page">
    <header class="hero">
      <div>
        <p class="eyebrow">League Simulation</p>
        <h1>Four teams, six weeks, one title.</h1>
        <p class="subhead">Kick off the season and watch the table evolve week by week.</p>
      </div>
      <div class="actions">
        <button v-if="state" class="ghost" :disabled="loading" @click="handleReset">Reset</button>
        <button class="ghost" :disabled="loading || !state" @click="handlePlayWeek">Play Week</button>
        <button class="ghost" :disabled="loading || !state" @click="handlePlayAll">Play All</button>
      </div>
    </header>

    <p v-if="error" class="error">{{ error }}</p>

    <LeagueInitForm v-if="stateNotFound && !state" @create="handleCreateLeague" />

    <FixtureList :fixtures="fixtures" @select-team="openTeamModal" @select-match="openMatchModal" />

    <TeamDetailModal
      :open="isTeamModalOpen"
      :team-id="selectedTeamId"
      @close="closeTeamModal"
      @updated="refreshLeagueData"
    />

    <MatchDetailModal
      :open="isMatchModalOpen"
      :match-id="selectedMatchId"
      @close="closeMatchModal"
      @updated="refreshLeagueData"
    />

    <section v-if="state" class="grid">
      <div class="panel">
        <div class="panel-header">
          <h2>Standings</h2>
          <span class="badge">Week {{ state.currentWeek }}</span>
        </div>
        <table>
          <thead>
            <tr>
              <th>Team</th>
              <th>W</th>
              <th>D</th>
              <th>L</th>
              <th>GD</th>
              <th>Pts</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="team in state.standings" :key="team.id">
              <td>
                <button class="link" type="button" @click="openTeamModal(team.teamId)">
                  {{ team.teamName }}
                </button>
              </td>
              <td>{{ team.won }}</td>
              <td>{{ team.drawn }}</td>
              <td>{{ team.lost }}</td>
              <td>{{ team.gd }}</td>
              <td>{{ team.points }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h2>Latest Results</h2>
          <span class="badge">Week {{ latestPlayedWeek?.number ?? '-' }}</span>
        </div>
        <div v-if="latestPlayedWeek" class="matches">
          <div v-for="match in latestPlayedWeek.matches" :key="match.id" class="match">
            <button class="match-id" type="button" @click="openMatchModal(match.id)">#{{ match.id }}</button>
            <button class="link" type="button" @click="openTeamModal(match.homeTeam?.id ?? match.homeTeamId)">
              {{ match.homeTeam?.name ?? match.homeTeamId }}
            </button>
            <strong>{{ match.homeScore ?? '-' }}</strong>
            <span class="divider">:</span>
            <strong>{{ match.awayScore ?? '-' }}</strong>
            <button class="link" type="button" @click="openTeamModal(match.awayTeam?.id ?? match.awayTeamId)">
              {{ match.awayTeam?.name ?? match.awayTeamId }}
            </button>
          </div>
        </div>
        <p v-else class="muted">No matches yet.</p>
      </div>

      <div class="panel" v-if="state.prediction">
        <div class="panel-header">
          <h2>Title Odds</h2>
          <span class="badge">Last 3 weeks</span>
        </div>
        <div class="prediction">
          <div v-for="standing in state.standings" :key="standing.teamId" class="prediction-row">
            <span>{{ standing.teamName }}</span>
            <strong>{{ state.prediction[standing.teamId] ?? 0 }}%</strong>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
<style scoped> 

</style>
