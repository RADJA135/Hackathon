<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const trustCheckId = page.props.trustCheckId // passed in from AuthController redirect

const signals = ref([
  { key: 'sim', label: 'SIM Swap Check', done: false, error: false },
  { key: 'device', label: 'Device Status', done: false, error: false },
  { key: 'location', label: 'Location Verification', done: false, error: false },
])

const collected = ref({})
const status = ref('checking') // checking | deciding | done | failed

async function callSignal(key, url) {
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ trust_check_id: trustCheckId }),
    })
    const data = await res.json()
    collected.value[key] = data
    const sig = signals.value.find((s) => s.key === key)
    sig.done = true
    return data
  } catch (e) {
    const sig = signals.value.find((s) => s.key === key)
    sig.error = true
    throw e
  }
}

async function runChecks() {
  try {
    // Run all 3 in parallel — they're independent Nokia API calls
    await Promise.all([
      callSignal('sim', '/api/nokia/sim-swap'),
      callSignal('device', '/api/nokia/device-status'),
      callSignal('location', '/api/nokia/location'),
    ])

    status.value = 'deciding'

    // All 3 signals collected — ask the AI agent for the final decision
    const res = await fetch('/api/decision', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ trust_check_id: trustCheckId }),
    })
    const decision = await res.json()

    status.value = 'done'
    router.visit('/dashboard')
  } catch (e) {
    status.value = 'failed'
  }
}

onMounted(runChecks)
</script>

<template>
  <div class="min-h-screen bg-[#0B1230] flex items-center justify-center p-6">
    <div class="w-[460px] bg-[#101F3D] rounded-2xl p-8">
      <div class="flex justify-between items-baseline mb-1">
        <span class="font-bold text-lg text-white">AI Agent Analysis</span>
      </div>
      <p class="text-[#8AA0BE] text-xs mb-6">
        Orchestrating Nokia CAMARA APIs in real time
      </p>

      <div
        v-for="sig in signals"
        :key="sig.key"
        class="flex items-center gap-3.5 py-3"
        :class="{ 'opacity-40': !sig.done && !sig.error }"
      >
        <div
          class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
          :class="sig.error ? 'bg-red-500/20' : 'bg-[#16294C]'"
        >
          <span v-if="sig.done" class="text-[#2DD9C0]">✓</span>
          <span v-else-if="sig.error" class="text-red-400">✕</span>
          <span v-else class="text-[#8AA0BE] animate-pulse">…</span>
        </div>
        <span class="text-sm text-white">{{ sig.label }}</span>
      </div>

      <p v-if="status === 'deciding'" class="text-center text-[#2DD9C0] text-xs mt-4 animate-pulse">
        AI agent calculating trust score…
      </p>
      <p v-if="status === 'failed'" class="text-center text-red-400 text-xs mt-4">
        Something went wrong. Please try signing in again.
      </p>
    </div>
  </div>
</template>
