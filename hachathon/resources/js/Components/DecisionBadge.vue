<script setup lang="ts">
const props = defineProps({
  decision: { type: String, default: 'warn' }, // allow | warn | block
  compact: { type: Boolean, default: false },
})

const config = {
  allow: { color: '#2DD9C0', label: 'Allowed', icon: '✓' },
  warn: { color: '#F2A93B', label: 'Warned', icon: '⚠' },
  block: { color: '#E8596B', label: 'Blocked', icon: '✕' },
}
// @ts-expect-error    because the decision is a string, but we know it will be one of the three values
const c = config[props.decision as any] || { color: '#8AA0BE', label: 'Pending', icon: '…' }
</script>

<template>
  <span
    v-if="compact"
    class="text-xs font-semibold px-2 py-0.5 rounded-full"
    :style="{ color: c.color, background: c.color + '1A' }"
  >
    {{ c.icon }} {{ c.label }}
  </span>
  <div
    v-else
    class="flex items-center justify-center gap-2 rounded-lg py-2.5"
    :style="{ background: c.color + '1A', border: `1px solid ${c.color}55` }"
  >
    <span class="font-bold text-sm" :style="{ color: c.color }">
      {{ c.icon }} {{ c.label }}
    </span>
  </div>
</template>
