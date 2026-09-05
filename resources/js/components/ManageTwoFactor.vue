<script setup lang="ts">
import Heading from '@/components/Heading.vue';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});
</script>

<template>
    <div v-if="canManageTwoFactor" class="space-y-6">
        <Heading
            variant="small"
            title="Two-factor authentication"
            description="This app does not use the legacy 2FA flow."
        />

        <p class="text-sm text-muted-foreground">
            TrustAI authenticates with the user’s phone number and does not rely on traditional TOTP or recovery code setup.
        </p>
    </div>
</template>
