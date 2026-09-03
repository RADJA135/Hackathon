<script setup lang="ts">
import { Link , usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <div class="min-h-screen bg-[#0B1230] p-6">
        <header class="flex items-center justify-between px-2 py-4 max-w-[460px] mx-auto">
            <span class="text-2xl font-bold text-white">◉ TrustAI</span>

            <button class="text-slate-300 hover:text-white text-sm">Sign Out</button>
        </header>
        <div class="max-w-[460px] mx-auto px-2 mb-6">
            <div class="grid grid-cols-2 gap-1 rounded-2xl bg-[#182d55] p-1">
                <Link href="/dashboard"
                class="rounded-xl py-3 text-center font-semibold "
                :class="page.url === '/dashboard' ? 'bg-[#2dd4bf] text-[#071936]' : 'text-slate-400 hover:text-white'">
                    Dashboard
                </Link>
                <Link href="/history"
                 class="rounded-xl py-3 text-center font-semibold "
                 :class="page.url === '/history' ? 'bg-[#2dd4bf] text-[#071936]' : 'text-slate-400 hover:text-white'"
                >
                    History
                </Link>
            </div>
        </div>
        <slot />
    </div>
</template>
