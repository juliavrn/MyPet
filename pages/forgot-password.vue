<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <NuxtLink to="/" class="flex items-center justify-center space-x-2 mb-6">
          <span class="text-4xl text-blue-600">🐾</span>
          <span class="text-3xl font-bold text-gray-900">MyPet</span>
        </NuxtLink>
        <h2 class="text-3xl font-bold text-gray-900">Mot de passe oublié</h2>
        <p class="mt-2 text-sm text-gray-600">
          Entrez votre adresse email pour recevoir un lien de réinitialisation
        </p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form @submit.prevent="handleForgotPassword" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Adresse email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              placeholder="votre@email.com"
            />
          </div>

          <div v-if="message" :class="messageType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'" class="border rounded-lg p-4">
            <div class="flex">
              <HeroIcon :name="messageType === 'error' ? 'warning-circle' : 'check-circle'" :class="messageType === 'error' ? 'text-red-400' : 'text-green-400'" class="h-5 w-5" />
              <div class="ml-3">
                <p class="text-sm">{{ message }}</p>
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <HeroIcon name="spinner" class="animate-spin h-5 w-5 mr-2" />
              Envoi en cours...
            </span>
            <span v-else>Envoyer le lien de réinitialisation</span>
          </button>
        </form>

        <div class="mt-6 text-center space-y-2">
          <p class="text-sm text-gray-600">
            <NuxtLink to="/login" class="font-medium text-blue-600 hover:text-blue-500">
              ← Retour à la connexion
            </NuxtLink>
          </p>
          <p class="text-sm text-gray-600">
            Pas encore de compte ?
            <NuxtLink to="/register" class="font-medium text-blue-600 hover:text-blue-500">
              Créer un compte
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
if (authStore.isLoggedIn) {
  navigateTo('/')
}

const form = ref({ email: '' })
const message = ref('')
const messageType = ref('')
const loading = ref(false)

const handleForgotPassword = async () => {
  if (!form.value.email) {
    showMessage('Veuillez entrer votre adresse email', 'error')
    return
  }

  loading.value = true
  message.value = ''
  
  try {
    const { $fetch } = useNuxtApp()
    const config = useRuntimeConfig()
    
    await $fetch(`${config.public.apiBase}/api/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: { email: form.value.email }
    })

    showMessage('Un email de réinitialisation a été envoyé à votre adresse email.', 'success')
    form.value.email = ''
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