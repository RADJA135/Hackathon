<script setup>
defineProps({
  title: String,
})
</script>

<template>
  <div class="min-h-screen bg-white">
    <slot />
  </div>
</template>