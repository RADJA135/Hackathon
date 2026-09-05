<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'TrustAI does not require a password reset flow.',
    },
});
</script>

<template>
    <Head title="Reset password" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            Password reset is not part of the TrustAI authentication flow.
        </p>

        <TextLink :href="login()" class="inline-flex">
            Return to sign in
        </TextLink>
    </div>
</template>
