<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Two-factor authentication',
        description: 'TrustAI does not use traditional multi-factor security for this sign-in flow.',
    },
});
</script>

<template>
    <Head title="Two-factor authentication" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            This app does not use legacy two-factor or recovery-code sign-in flows. Continue with your trusted phone number sign-in.
        </p>

        <TextLink :href="login()" class="inline-flex">
            Return to sign in
        </TextLink>
    </div>
</template>
