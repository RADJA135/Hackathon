<script setup>
const props = defineProps({
  score: { type: Number, default: 0 },
  decision: { type: String, default: 'warn' }, // allow | warn | block
})

const colors = { allow: '#2DD9C0', warn: '#F2A93B', block: '#E8596B' }
const color = colors[props.decision] || '#8AA0BE'

const r = 70
const c = 2 * Math.PI * r
const pct = Math.min(props.score, 100) / 100
const offset = c - c * pct
</script>

<template>
  <svg width="180" height="180" viewBox="0 0 180 180">
    <circle cx="90" cy="90" :r="r" fill="none" stroke="rgba(138,160,190,0.15)" stroke-width="10" />
    <circle
      cx="90" cy="90" :r="r" fill="none" :stroke="color" stroke-width="10"
      :stroke-dasharray="c" :stroke-dashoffset="offset" stroke-linecap="round"
      transform="rotate(-90 90 90)"
      style="transition: stroke-dashoffset 900ms cubic-bezier(.4,0,.2,1), stroke 300ms"
    />
    <text x="90" y="86" text-anchor="middle" font-family="'JetBrains Mono', monospace" font-size="40" font-weight="700" fill="#fff">
      {{ Math.round(score) }}
    </text>
    <text x="90" y="110" text-anchor="middle" font-family="Inter, sans-serif" font-size="10" letter-spacing="2" fill="#8AA0BE">
      TRUST SCORE
    </text>
  </svg>
</template>
