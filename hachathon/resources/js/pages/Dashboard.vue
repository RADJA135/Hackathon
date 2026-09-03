   <script setup lang="ts">
// STARTER for Semsoum — reads the latest TrustCheck passed in from DashboardController.
//import { computed } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import ScoreDial from '@/Components/ScoreDial.vue'
import SignalRow from '@/Components/SignalRow.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

interface TrustCheck {
    trust_score: number
    decision: 'allow' | 'warn' | 'block'
    sim_swapped: boolean
    device_known: boolean
    location_consistent: boolean
    agent_reasoning: string
}

defineOptions({
    layout: AuthLayout,
});

const page = usePage()
const check = page.props.trustCheck as TrustCheck | undefined// { trust_score, decision, sim_swapped, device_known, location_consistent, agent_reasoning }

// @ts-expect-error    because the decision is a string, but we know it will be one of the three values
const decisionColor = {
  allow: '#2DD9C0',
  warn: '#F2A93B',
  block: '#E8596B',
}[check?.decision as any] || '#8AA0BE'

// @ts-expect-error    because the decision is a string, but we know it will be one of the three values
const decisionLabel = {
  allow: 'Login Allowed',
  warn: 'Additional Check Required',
  block: 'Login Blocked',
}[check?.decision as any] || 'Pending'
</script>

<template>
  <Head title="Dashboard" />
    <div class="w-[460px] mx-auto bg-[#101F3D] rounded-2xl p-8">
      <!-- TODO: pull in AuthenticatedLayout.vue nav bar (Dashboard/History tabs) -->

      <div class="flex justify-center mb-2">
        <ScoreDial :score="check?.trust_score ?? 0" :decision="check?.decision ?? 'warn'"></ScoreDial>
      </div>

      <div
        class="flex items-center justify-center gap-2 rounded-lg py-2.5 mb-5"
        :style="{ background: decisionColor + '1A', border: `1px solid ${decisionColor}55` }"
      >
        <span class="font-bold text-sm" :style="{ color: decisionColor }">
          {{ decisionLabel }}
        </span>
      </div>
      <SignalRow label="SIM Swap Check" :passed="check?.sim_swapped === false"></SignalRow>
      <SignalRow label="Device Status" :passed="check?.device_known"></SignalRow>
      <SignalRow label="Location Verification" :passed="check?.location_consistent"></SignalRow>

      <div class="text-xs text-[#8AA0BE] text-center">
        {{ check?.agent_reasoning }}
      </div>
    </div>
</template>
