<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: '/settings/profile',
            },
        ],
    },
});
</script>

<template>
    <Head title="Security settings" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="TrustAI security"
            description="Security settings are managed through the phone-number authentication flow and do not use the legacy password or passkey system."
        />

        <p class="text-sm text-muted-foreground">
            This app currently signs users in with their trusted phone number, so traditional password and multi-factor settings are not enabled.
        </p>
    </div>
</template>
