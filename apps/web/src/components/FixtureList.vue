<script setup lang="ts">
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

defineProps<{
  fixtures: Week[]
}>()

const emit = defineEmits<{
  (event: 'select-team', teamId: number): void
  (event: 'select-match', matchId: number): void
}>()

const handleSelect = (teamId: number | null | undefined) => {
  if (!teamId) return
  emit('select-team', teamId)
}

const handleMatchSelect = (matchId: number) => {
  emit('select-match', matchId)
}
</script>

<template>
  <section v-if="fixtures.length" class="panel fixtures-panel">
    <div class="panel-header">
      <h2>Fixture List</h2>
      <span class="badge">All weeks</span>
    </div>
    <div class="fixtures-grid">
      <article v-for="week in fixtures" :key="week.id" class="fixture-card">
        <div class="fixture-card__header">
          <h3>Week {{ week.number }}</h3>
          <span class="badge badge--light">{{ week.isPlayed ? 'Played' : 'Upcoming' }}</span>
        </div>
        <p v-if="week.byeTeams.length" class="bye">
          Bye: {{ week.byeTeams.map((team) => team.name).join(', ') }}
        </p>
        <div class="matches">
            <div v-for="match in week.matches" :key="match.id" class="match">
              <button class="match-id" type="button" @click="handleMatchSelect(match.id)">
                #{{ match.id }}
              </button>
              <button class="link" type="button" @click="handleSelect(match.homeTeam?.id ?? match.homeTeamId)">
                {{ match.homeTeam?.name ?? match.homeTeamId }}
              </button>
              <strong>{{ match.homeScore ?? '-' }}</strong>
              <span class="divider">:</span>
              <strong>{{ match.awayScore ?? '-' }}</strong>
              <button class="link" type="button" @click="handleSelect(match.awayTeam?.id ?? match.awayTeamId)">
                {{ match.awayTeam?.name ?? match.awayTeamId }}
              </button>
            </div>
          </div>
      </article>
    </div>
  </section>
</template>
