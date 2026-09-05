<script setup lang="ts">
import Heading from '@/components/Heading.vue';

export type Props = {
    canManagePasskeys?: boolean;
    passkeys?: Array<{ id: number; name: string }>;
};

withDefaults(defineProps<Props>(), {
    canManagePasskeys: false,
    passkeys: () => [],
});
</script>

<template>
    <div v-if="canManagePasskeys" class="space-y-6">
        <Heading
            variant="small"
            title="Passkeys"
            description="This app does not use legacy passkey-based sign-in."
        />

        <p class="text-sm text-muted-foreground">
            TrustAI authenticates using the user’s phone number instead of passkeys or password-based flows.
        </p>
    </div>
</template>
