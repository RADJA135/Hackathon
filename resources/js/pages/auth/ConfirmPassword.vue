<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Confirm access',
        description: 'This app does not use password confirmation prompts during TrustAI sign-in.',
    },
});
</script>

<template>
    <Head title="Confirm access" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            Password confirmation is not part of the TrustAI mobile-style authentication flow.
        </p>

        <TextLink :href="login()" class="inline-flex">
            Back to sign in
        </TextLink>
    </div>
</template>
