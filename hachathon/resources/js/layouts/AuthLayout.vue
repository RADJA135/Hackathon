<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const showLogoutConfirmation = ref(false)
</script>

<template>
    <div class="min-h-screen bg-[#0B1230] p-6">
        <header class="flex items-center justify-between px-2 py-4 max-w-[460px] mx-auto">
            <span class="text-2xl font-bold text-white">◉ TrustAI</span>

            <button
                @click="showLogoutConfirmation = true"
                class="text-slate-300 hover:text-white text-sm"
            >
                Sign Out
            </button>
        </header>
        <div class="max-w-[460px] mx-auto px-2 mb-6">
            <div class="grid grid-cols-2 gap-1 rounded-2xl bg-[#182d55] p-1">
                <Link
                    href="/dashboard"
                    class="rounded-xl py-3 text-center font-semibold "
                    :class="page.url === '/dashboard' ? 'bg-[#2dd4bf] text-[#071936]' : 'text-slate-400 hover:text-white'"
                >
                    Dashboard
                </Link>
                <Link
                    href="/history"
                    class="rounded-xl py-3 text-center font-semibold "
                    :class="page.url === '/history' ? 'bg-[#2dd4bf] text-[#071936]' : 'text-slate-400 hover:text-white'"
                >
                    History
                </Link>
            </div>
        </div>

        <slot />

        <div
            v-if="showLogoutConfirmation"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-[#0B1230]/50 backdrop-blur-md"></div>

            <div class="relative bg-[#101F3D] rounded-2xl p-6 w-[320px] mx-4">
                <h2 class="text-white font-semibold text-lg mb-2">Sign Out?</h2>
                <p class="text-slate-400 text-sm mb-6">You'll need to log in again to access your dashboard.</p>
                <div class="flex gap-3">
                    <button
                        @click="showLogoutConfirmation = false"
                        class="flex-1 py-2.5 rounded-xl font-semibold text-slate-300 bg-[#182d55] hover:text-white"
                    >
                        Cancel
                    </button>
                    <button
                        @click="router.post('/logout')"
                        class="flex-1 py-2.5 rounded-xl font-semibold bg-[#2dd4bf] text-[#071936]"
                    >
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
