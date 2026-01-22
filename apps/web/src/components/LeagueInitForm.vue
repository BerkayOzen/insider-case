<script setup lang="ts">
import { onMounted, ref } from 'vue'
import LeagueService from '../services/league-service'
import TeamService from '@/services/team-service'
import type { TeamInput } from '@/types/team.type'
import type { InitLeaguePayload } from '@/types/league.type'

const teams = ref<TeamInput[]>([])
const loading = ref(false)
const error = ref('')

const leagueService = new LeagueService();
const teamService = new TeamService();

const emit = defineEmits<{
  (event: 'create', payload: InitLeaguePayload): void
}>()

const loadDefaults = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await teamService.getDefaultTeams()
    teams.value = response.data.map((team: TeamInput) => ({
      name: team.name,
      power: team.power,
    }))
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to load default teams.'
  } finally {
    loading.value = false
  }
}

const handleSubmit = () => {
  emit('create', { teams: teams.value })
}

onMounted(loadDefaults)
</script>

<template>
  <section class="panel form-panel">
    <div class="panel-header">
      <h2>Create a new league</h2>
      <span class="badge">Defaults</span>
    </div>
    <p class="muted">Adjust the teams before creating the fixture list.</p>
    <p v-if="error" class="error">{{ error }}</p>
    <form class="form-grid" @submit.prevent="handleSubmit">
      <div v-for="(team, index) in teams" :key="index" class="team-card">
        <label class="field">
          <span>Name</span>
          <input v-model="team.name" type="text" class="input" required />
        </label>
        <label class="field">
          <span>Power</span>
          <input v-model.number="team.power" type="number" min="1" max="100" class="input" required />
        </label>
      </div>
      <button class="primary" type="submit" :disabled="loading || teams.length === 0">
        Create Fixture
      </button>
    </form>
  </section>
</template>
