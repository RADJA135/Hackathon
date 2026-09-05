<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();


const TRUST_THEMED_PAGES = [
    'auth/Login',
    'auth/ForgotPassword',
    'auth/ConfirmPassword',
];

const page = usePage();
const appName = computed(() => (page.props as { name?: string }).name ?? 'App');
const isTrustThemed = computed(() =>
    TRUST_THEMED_PAGES.includes(page.component),
);

const footnotes: Record<string, string> = {
    'auth/Login': "",
    'auth/ForgotPassword':
        'Enter your email and we will send a secure reset link right away.',
    'auth/ConfirmPassword':
        'This is a secure area of the app. We just need to confirm it\u2019s you.',
};
const footnote = computed(() => footnotes[page.component] ?? '');
</script>

<template>
    <Head v-if="isTrustThemed">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <!-- ============ TrustAI-styled auth card (Login / Confirm Password) ============ -->
    <div
        v-if="isTrustThemed"
        class="trustai-shell flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
    >
        <div class="w-full max-w-[380px]">
            <div class="trustai-card dark">
                <Link :href="home()" class="trustai-logo">
                    <span class="dot" aria-hidden="true" />
                    <span class="wordmark">{{ appName }}</span>
                </Link>

                <h1 v-if="title" class="trustai-title">{{ title }}</h1>
                <p v-if="description" class="trustai-hint">{{ description }}</p>

                <slot />
            </div>

            <p v-if="footnote" class="trustai-footnote">{{ footnote }}</p>
        </div>
    </div>

    <!-- ============ Default auth layout (Register / Forgot / Reset / Verify / 2FA) ============ -->
    <div
        v-else
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.trustai-shell {
    background: #0a0e13;
    font-family:
        'Inter',
        ui-sans-serif,
        system-ui,
        sans-serif;
}

.trustai-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 28px;
    width: fit-content;
}
.trustai-logo .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #5eead4;
    box-shadow: 0 0 10px #5eead4;
}
.trustai-logo .wordmark {
    font-family: 'Space Grotesk', ;
    font-weight: 600;
    font-size: 15px;
    letter-spacing: -0.01em;
    color: #edf2f7;
    text-transform: uppercase;
}

.trustai-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 22px;
    font-weight: 600;
    color: #edf2f7;
    margin-bottom: 6px;
}
.trustai-hint {
    color: #8b98a9;
    font-size: 13px;
    margin-bottom: 26px;
}

.trustai-footnote {
    color: #5a6675;
    font-size: 11.5px;
    text-align: center;
    margin-top: 18px;
    line-height: 1.5;
    font-family: 'IBM Plex Mono', monospace;
}

.trustai-card {
    --background: #0a0e13;
    --foreground: #edf2f7;
    --card: #131b24;
    --card-foreground: #edf2f7;
    --popover: #131b24;
    --popover-foreground: #edf2f7;
    --primary: #5eead4;
    --primary-foreground: #0a0e13;
    --secondary: #1b2632;
    --secondary-foreground: #edf2f7;
    --muted: #1b2632;
    --muted-foreground: #8b98a9;
    --accent: #1b2632;
    --accent-foreground: #edf2f7;
    --destructive: #f2545b;
    --destructive-foreground: #edf2f7;
    --border: #263140;
    --input: #263140;
    --ring: #5eead4;

    color: var(--foreground);
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px 32px;
    box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.55);
}

.trustai-card :deep(a) {
    color: #5eead4;
    text-decoration-color: rgba(94, 234, 212, 0.4);

}
.trustai-card :deep(a:hover) {
    text-decoration-color: #5eead4;
}
</style>
