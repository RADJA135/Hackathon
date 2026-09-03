<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'

defineOptions({
    layout: AuthLayout,
})

function formatDate(dateString: string): string {
    const date = new Date(dateString)
    const now = new Date()

    const isSameDay = (a: Date, b: Date) =>
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()

    const yesterday = new Date(now)
    yesterday.setDate(yesterday.getDate() - 1)

    const time = date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
    })

    if (isSameDay(date, now)){
        return `Today, ${time}`
    }

    if (isSameDay(date, yesterday)){
        return `Yesterday, ${time}`
    }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + `, ${time}`
}

function statusLevel(score: number): 'good' | 'warning' | 'bad' {
    if (score >= 80) {
        return 'good'
    }

    if (score >= 50) {
        return 'warning'
    }

        return 'bad'
}

interface TrustCheck {
    id: number
    device_label: string | null
    location_city: string | null
    trust_score: number
    decision: string
    created_at: string
}

const props = defineProps<{
    checks: TrustCheck[]
}>()
</script>

<template>
    <Head title="History" />
    <div class="w-[460px] mx-auto bg-[#101F3D] rounded-2xl p-8">
        <div class="flex items-center gap-2 mb-6 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 7 12 12 15 15" />
            </svg>
            <h1 class="text-lg font-semibold">Login History</h1>
        </div>
        <div
        v-if="props.checks.length === 0"
        class="text-slate-400 text-sm py-8 text-center">
            No login history available.
        </div>
        <div v-else
        class="divide-y divide-white/10">
            <div v-for="check in props.checks"
            :key="check.id"
            class="flex items-center justify-between py-4">
                        <div>
            <p class="text-white font-medium">{{ formatDate(check.created_at) }}</p>
            <p class="text-slate-400 text-sm">{{ check.device_label ?? 'Unknown device' }} · {{ check.location_city ?? 'Unknown location' }}</p>
        </div>
        <div class="flex items-center gap-2">
    <span
        class="font-semibold"
        :class="{
            'text-emerald-400': statusLevel(check.trust_score) === 'good',
            'text-amber-400': statusLevel(check.trust_score) === 'warning',
            'text-red-400': statusLevel(check.trust_score) === 'bad',
        }"
    >
        {{ check.trust_score ?? '—' }}
    </span>

    <svg v-if="statusLevel(check.trust_score) === 'good'" class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="9" />
        <path d="M8 12l3 3 5-6" />
    </svg>

    <svg v-else-if="statusLevel(check.trust_score) === 'warning'" class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 9v4M12 17h.01" />
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
    </svg>

    <svg v-else class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="9" />
        <path d="M15 9l-6 6M9 9l6 6" />
    </svg>
</div>
        </div>
        </div>
    </div>
</template>
