<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import MatchService from '@/services/match-service';
import type { MatchDetail } from '@/types/match.type';

const props = defineProps<{
  open: boolean
  matchId: number | null
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'updated'): void
}>()

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const match = ref<MatchDetail | null>(null)
const homeScore = ref<number | null>(null)
const awayScore = ref<number | null>(null)
const editMode = ref(false)

const matchService = new MatchService();

const title = computed(() => {
  if (!match.value) return 'Match'
  return `${match.value.homeTeam?.name ?? 'Home'} vs ${match.value.awayTeam?.name ?? 'Away'}`
})

const loadMatch = async () => {
  if (!props.open || props.matchId === null) return
  loading.value = true
  error.value = ''
  try {
    const res = await matchService.getMatchById(props.matchId)
    match.value = res.data
    homeScore.value = res.data.homeScore
    awayScore.value = res.data.awayScore
    editMode.value = false
  } catch (err: any) {
    error.value = err?.message ?? 'Failed to load match details.'
  } finally {
    loading.value = false
  }
}

const handleSave = async () => {
  if (!match.value || homeScore.value === null || awayScore.value === null) return
  saving.value = true
  error.value = ''
  try {
    await matchService.updateMatchResult(match.value.id, homeScore.value, awayScore.value)
    editMode.value = false
    await loadMatch()
    emit('updated')
  } catch (err: any) {
    error.value = err?.response?.data?.message ?? err?.message ?? 'Failed to update match.'
  } finally {
    saving.value = false
  }
}

const close = () => {
  emit('close')
}

watch(() => [props.open, props.matchId], loadMatch, { immediate: true })
</script>

<template>
  <div v-if="open" class="modal-backdrop" @click.self="close">
    <div class="modal">
      <div class="modal-header">
        <div>
          <p class="eyebrow">Match Details</p>
          <h2>{{ title }}</h2>
          <p class="muted">Match #{{ match?.id ?? '-' }} • Week {{ match?.week?.number ?? '-' }}</p>
        </div>
        <button class="ghost" type="button" @click="close">Close</button>
      </div>

      <p v-if="error" class="error">{{ error }}</p>

      <div v-if="loading" class="muted">Loading...</div>

      <div v-else-if="match" class="modal-body">
        <div class="modal-grid">
          <label class="field">
            <span>Home Team</span>
            <input class="input" type="text" :value="match.homeTeam?.name ?? '-'" disabled />
          </label>
          <label class="field">
            <span>Away Team</span>
            <input class="input" type="text" :value="match.awayTeam?.name ?? '-'" disabled />
          </label>
          <label class="field">
            <span>Home Score</span>
            <input
              v-model.number="homeScore"
              class="input"
              type="number"
              min="0"
              max="10"
              :disabled="!editMode"
            />
          </label>
          <label class="field">
            <span>Away Score</span>
            <input
              v-model.number="awayScore"
              class="input"
              type="number"
              min="0"
              max="10"
              :disabled="!editMode"
            />
          </label>
        </div>

        <div class="modal-actions">
          <button class="ghost" type="button" @click="editMode = !editMode">
            {{ editMode ? 'Cancel' : 'Update' }}
          </button>
          <button class="primary" type="button" :disabled="!editMode || saving" @click="handleSave">
            Save Result
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
