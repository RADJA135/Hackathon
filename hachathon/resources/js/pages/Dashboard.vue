<script setup>
// STARTER for Semsoum — reads the latest TrustCheck passed in from DashboardController.
import { computed } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const check = page.props.trustCheck // { trust_score, decision, sim_swapped, device_known, location_consistent, agent_reasoning }

const decisionColor = {
  allow: '#2DD9C0',
  warn: '#F2A93B',
  block: '#E8596B',
}[check?.decision] || '#8AA0BE'

const decisionLabel = {
  allow: 'Login Allowed',
  warn: 'Additional Check Required',
  block: 'Login Blocked',
}[check?.decision] || 'Pending'
</script>

<template>
  <div class="min-h-screen bg-[#0B1230] p-6">
    <div class="w-[460px] mx-auto bg-[#101F3D] rounded-2xl p-8">
      <!-- TODO: pull in AuthenticatedLayout.vue nav bar (Dashboard/History tabs) -->

      <div class="flex justify-center mb-2">
        <!-- TODO: swap for real ScoreDial.vue component -->
        <div class="text-5xl font-mono font-bold text-white text-center">
          {{ check?.trust_score ?? '--' }}
        </div>
      </div>
      <p class="text-center text-xs text-[#8AA0BE] tracking-widest mb-6">TRUST SCORE</p>

      <div
        class="flex items-center justify-center gap-2 rounded-lg py-2.5 mb-5"
        :style="{ background: decisionColor + '1A', border: `1px solid ${decisionColor}55` }"
      >
        <span class="font-bold text-sm" :style="{ color: decisionColor }">
          {{ decisionLabel }}
        </span>
      </div>

      <div class="text-xs text-[#8AA0BE] text-center">
        {{ check?.agent_reasoning }}
      </div>
    </div>
  </div>
</template>

<!--
  TODO for Semsoum:
  - Build ScoreDial.vue (animated circular progress, see earlier prototype for reference)
  - Build SignalRow.vue for the 3-signal checklist (SIM/Device/Location ✓)
  - Wrap this page in AuthenticatedLayout.vue once it exists
-->