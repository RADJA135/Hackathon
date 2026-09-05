<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Forgot password',
        description: 'TrustAI sign-in does not use password reset emails.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot password" />

    <div class="space-y-6 text-center">
        <p class="text-sm text-muted-foreground">
            Password reset links are not part of the TrustAI phone-number login flow.
        </p>

        <TextLink :href="login()" class="inline-flex">
            Back to sign in
        </TextLink>
    </div>
</template>
