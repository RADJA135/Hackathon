<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const loading = ref(false)
const error = ref('')

// Stores only the digits after +999
const digits = ref('')


const phoneDisplay = computed({
  get: () => '+999' + digits.value,
  set: (value) => {
    // Remove all non‑digits
    let cleaned = value.replace(/\D/g, '')
    // If the user pastes or types something starting with 999 (like +99999991001),
    // strip that prefix so we don't duplicate it.
    if (cleaned.startsWith('999')) {
      cleaned = cleaned.slice(3)
    }
    digits.value = cleaned
  }
})

function submit() {
  error.value = ''
  const clean = digits.value

  if (!clean) {
    error.value = 'Please enter your phone number.'
    return
  }

  if (clean.length < 8 || clean.length > 13) {
    error.value = 'Please enter a valid phone number (8‑13 digits).'
    return
  }

  const fullNumber = '+999' + clean
  loading.value = true

  router.post('/login', {
    phone: fullNumber,
  }, {
    onError: (errors) => {
      error.value = errors.phone || 'Something went wrong.'
    },
    onFinish: () => {
      loading.value = false
    },
  })
}

function handlePaste(event) {
  const pasted = event.clipboardData.getData('text')
  let cleaned = pasted.replace(/\D/g, '')
  if (cleaned.startsWith('999')) {
    cleaned = cleaned.slice(3)
  }
  digits.value = cleaned
  event.preventDefault()
}
</script>

<template>
  <div class="min-h-screen bg-[#0B1230] flex items-center justify-center">
    <div class="w-[380px] bg-[#101F3D] rounded-2xl p-9">

      <h1 class="text-2xl font-bold text-white mb-2">
        Sign in
      </h1>

      <p class="text-[#8AA0BE] text-sm mb-7">
        Every login is checked against live telecom signals
        before access is granted.
      </p>

      <label class="text-xs text-[#8AA0BE] block mb-1.5">
        Phone number
      </label>

      <!-- Single input with fixed +999 prefix – it never disappears -->
      <input
        v-model="phoneDisplay"
        type="text"
        inputmode="numeric"
        placeholder="6 55 12 34 56"
        @keyup.enter="submit"
        @paste="handlePaste"
        class="w-full bg-[#16294C] text-white rounded-lg px-4 py-3 mb-2 font-mono outline-none placeholder:text-[#4A6A8F]"
      />

      <p v-if="error" class="text-red-400 text-xs mb-4">
        {{ error }}
      </p>

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