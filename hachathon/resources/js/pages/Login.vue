
<script setup>
// STARTER for Maroua — feel free to restyle, this just makes the flow work.
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const phone = ref('')
const loading = ref(false)

function submit() {
  loading.value = true
  router.post('/login', { phone: phone.value }, {
    onFinish: () => (loading.value = false),
  })
}
</script>

<template>
  <div class="min-h-screen bg-[#0B1230] flex items-center justify-center">
    <div class="w-[380px] bg-[#101F3D] rounded-2xl p-9">
      <h1 class="text-2xl font-bold text-white mb-2">Sign in</h1>
      <p class="text-[#8AA0BE] text-sm mb-7">
        Every login is checked against live telecom signals before access is granted.
      </p>
      <label class="text-xs text-[#8AA0BE] block mb-1.5">Phone number</label>
      <input
        v-model="phone"
        placeholder="+213 6 55 12 34 56"
        class="w-full bg-[#16294C] text-white rounded-lg px-4 py-3 mb-5 font-mono outline-none"
      />
      <button
        @click="submit"
        :disabled="loading"
        class="w-full bg-[#2DD9C0] text-[#0B1230] font-bold rounded-lg py-3 disabled:opacity-50"
      >
        {{ loading ? 'Checking…' : 'Verify & Sign In' }}
      </button>
    </div>
  </div>
</template>

<!--
  TODO for Maroua:
  - AuthController@login should create a TrustCheck row (phone_number only,
    everything else null) and redirect to /scan with trustCheckId in props
  - Add basic phone format validation before submit
-->