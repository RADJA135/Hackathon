<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Email verification',
        description: 'TrustAI authentication does not require traditional email verification.',
    },
});
</script>

<template>
    <Head title="Email verification" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            Email verification is not part of the TrustAI phone-number sign-in flow.
        </p>

        <TextLink :href="login()" class="inline-flex">
            Return to sign in
        </TextLink>
    </div>
</template>
