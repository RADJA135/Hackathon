<script setup>
defineProps({
  title: String,
  description: String,
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md">
      <slot />
    </div>
  </div>
</template>