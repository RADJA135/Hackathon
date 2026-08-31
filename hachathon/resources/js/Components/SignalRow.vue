<script setup>
defineProps({
  label: { type: String, required: true },
  verified: { type: Boolean, default: false },
})
</script>

<template>
  <div class="flex items-center justify-between py-2.5 border-b border-[#8AA0BE]/10 last:border-0">
    <span class="text-sm text-[#DCE6F0]">{{ label }}</span>
    <span
      class="flex items-center gap-1.5 text-xs font-semibold"
      :class="verified ? 'text-[#2DD9C0]' : 'text-[#8AA0BE]'"
    >
      <span>{{ verified ? '✓' : '…' }}</span>
      {{ verified ? 'Verified' : 'Pending' }}
    </span>
  </div>
</template>