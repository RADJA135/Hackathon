<script setup lang="ts">
   defineProps<{
    label: string
    passed: boolean | undefined
   }>();
</script>

<template>
    <div class="flex items-center justify-between border-b border-[#243858] py-4">
    <span class="text-white min-w-0 truncate pr-2">{{ label }}</span>
        <span class="flex items-center gap-2 font-semibold text-[#2dd4bf]">
            <span>✓</span> {{ passed ? 'Verified' : 'Pending' }}
        </span>
    </div>
</template>
