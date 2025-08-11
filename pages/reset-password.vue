<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <NuxtLink to="/" class="flex items-center justify-center space-x-2 mb-6">
          <span class="text-4xl text-blue-600">🐾</span>
          <span class="text-3xl font-bold text-gray-900">MyPet</span>
        </NuxtLink>
        <h2 class="text-3xl font-bold text-gray-900">Nouveau mot de passe</h2>
        <p class="mt-2 text-sm text-gray-600">
          Créez votre nouveau mot de passe
        </p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form @submit.prevent="handleResetPassword" class="space-y-6">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Nouveau mot de passe
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                placeholder="Votre nouveau mot de passe"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <Icon :name="showPassword ? 'ph:eye-slash' : 'ph:eye'" class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
              Confirmer le mot de passe
            </label>
            <div class="relative">
              <input
                id="confirmPassword"
                v-model="form.confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                placeholder="Confirmez votre nouveau mot de passe"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <Icon :name="showConfirmPassword ? 'ph:eye-slash' : 'ph:eye'" class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div v-if="form.password" class="space-y-2">
            <p class="text-sm font-medium text-gray-700">Critères :</p>
            <div class="space-y-1">
              <div class="flex items-center text-sm" :class="passwordChecks.length ? 'text-green-600' : 'text-red-600'">
                <Icon :name="passwordChecks.length ? 'ph:check-circle' : 'ph:x-circle'" class="h-4 w-4 mr-2" />
                Au moins 6 caractères
              </div>
              <div class="flex items-center text-sm" :class="passwordChecks.confirm ? 'text-green-600' : 'text-red-600'">
                <Icon :name="passwordChecks.confirm ? 'ph:check-circle' : 'ph:x-circle'" class="h-4 w-4 mr-2" />
                Les mots de passe correspondent
              </div>
            </div>
          </div>

          <div v-if="message" :class="messageType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'" class="border rounded-lg p-4">
            <div class="flex">
              <Icon :name="messageType === 'error' ? 'ph:warning-circle' : 'ph:check-circle'" :class="messageType === 'error' ? 'text-red-400' : 'text-green-400'" class="h-5 w-5" />
              <div class="ml-3">
                <p class="text-sm">{{ message }}</p>
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading || !isFormValid"
            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <HeroIcon name="spinner" size="md" class="animate-spin mr-2" />
              Réinitialisation en cours...
            </span>
            <span v-else>Réinitialiser le mot de passe</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            <NuxtLink to="/login" class="font-medium text-blue-600 hover:text-blue-500">
              ← Retour à la connexion
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Récupérer le token depuis l'URL
const route = useRoute()
const token = route.query.token

// Rediriger si pas de token
if (!token) {
  navigateTo('/forgot-password')
}

const form = ref({
  password: '',
  confirmPassword: ''
})

const message = ref('')
const messageType = ref('')
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Validation
const passwordChecks = computed(() => ({
  length: form.value.password.length >= 6,
  confirm: form.value.password === form.value.confirmPassword && form.value.password !== ''
}))

const isFormValid = computed(() => {
  return form.value.password && 
         form.value.confirmPassword && 
         passwordChecks.value.length && 
         passwordChecks.value.confirm
})

const handleResetPassword = async () => {
  if (!isFormValid.value) {
    showMessage('Veuillez remplir tous les champs correctement', 'error')
    return
  }

  loading.value = true
  message.value = ''
  
  try {
    const { $fetch } = useNuxtApp()
    const config = useRuntimeConfig()
    
    await $fetch(`${config.public.apiBase}/api/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: { token, password: form.value.password }
    })

    showMessage('Votre mot de passe a été réinitialisé avec succès !', 'success')
    
    // Rediriger après 2 secondes
    setTimeout(() => navigateTo('/login'), 2000)
  } catch (err) {
    console.error('Erreur:', err)
    showMessage(err.data?.message || 'Une erreur est survenue. Veuillez réessayer.', 'error')
  } finally {
    loading.value = false
  }
}

const showMessage = (text, type) => {
  message.value = text
  messageType.value = type
}
</script> 