<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import TeamService from '@/services/team-service'
import type { TeamDetailResponse } from '@/types/team.type';

const props = defineProps<{
  open: boolean
  teamId: number | null
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'updated'): void
}>()

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const response = ref<TeamDetailResponse | null>(null)
const power = ref<number | null>(null)
const editMode = ref(false)

const team = computed(() => response.value?.team ?? null)

const teamService = new TeamService();

const loadTeam = async () => {
  if (!props.open || props.teamId === null) return
  loading.value = true
  error.value = ''
  try {
    const res = await teamService.getTeamById(props.teamId)
    response.value = res.data
    power.value = res.data.team.power
    editMode.value = false
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to load team details.'
  } finally {
    loading.value = false
  }
}

const handleSave = async () => {
  if (!team.value || power.value === null) return
  saving.value = true
  error.value = ''
  try {
    await teamService.updateTeam(team.value.id,  power.value)
    editMode.value = false
    await loadTeam()
    emit('updated')
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? err?.message ?? 'Failed to update team.'
  } finally {
    saving.value = false
  }
}

const close = () => {
  emit('close')
}

watch(() => [props.open, props.teamId], loadTeam, { immediate: true })
</script>

<template>
  <div v-if="open" class="modal-backdrop" @click.self="close">
    <div class="modal">
      <div class="modal-header">
        <div>
          <p class="eyebrow">Team Details</p>
          <h2>{{ team?.name ?? 'Team' }}</h2>
        </div>
        <button class="ghost" type="button" @click="close">Close</button>
      </div>

      <p v-if="error" class="error">{{ error }}</p>

      <div v-if="loading" class="muted">Loading...</div>

      <div v-else-if="team" class="modal-body">
        <div class="modal-grid">
          <label class="field">
            <span>ID</span>
            <input class="input" type="text" :value="team.id" disabled />
          </label>
          <label class="field">
            <span>Name</span>
            <input class="input" type="text" :value="team.name" disabled />
          </label>
          <label class="field">
            <span>Power</span>
            <input
              v-model.number="power"
              class="input"
              type="number"
              min="1"
              max="100"
              :disabled="!editMode"
            />
          </label>
        </div>

        <div class="modal-actions">
          <button class="ghost" type="button" @click="editMode = !editMode">
            {{ editMode ? 'Cancel' : 'Update' }}
          </button>
          <button class="primary" type="button" :disabled="!editMode || saving" @click="handleSave">
            Save Changes
          </button>
        </div>

        <div class="panel matches-panel">
          <div class="panel-header">
            <h3>Matches</h3>
            <span class="badge">History</span>
          </div>
          <div v-if="response?.matches.length" class="matches">
            <div v-for="match in response.matches" :key="match.id" class="match">
              <span>Week {{ match.week ?? '-' }}</span>
              <span>{{ match.isHome ? 'Home' : 'Away' }}</span>
              <span>{{ match.opponent?.name ?? match.opponent?.id ?? '-' }}</span>
              <strong>{{ match.homeScore ?? '-' }}</strong>
              <span class="divider">:</span>
              <strong>{{ match.awayScore ?? '-' }}</strong>
            </div>
          </div>
          <p v-else class="muted">No matches recorded yet.</p>
        </div>
      </div>
    </div>
  </div>
</template>
