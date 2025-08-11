<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <NuxtLink to="/" class="flex items-center justify-center space-x-2 mb-6">
          <span class="text-4xl text-blue-600">🐾</span>
          <span class="text-3xl font-bold text-gray-900">MyPet</span>
        </NuxtLink>
        <h2 class="text-3xl font-bold text-gray-900">Connexion</h2>
        <p class="mt-2 text-sm text-gray-600">
          Connectez-vous à votre compte pour accéder à vos animaux
        </p>
      </div>

      <div class="card">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="email" class="form-label">Adresse email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input-field"
              placeholder="votre@email.com"
            />
          </div>

          <div>
            <label for="password" class="form-label">Mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="input-field"
              placeholder="Votre mot de passe"
            />
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <HeroIcon name="warning-circle" size="sm" class="h-5 w-5 text-red-400" />
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full btn-primary py-3 text-base font-medium"
          >
            <span v-if="authStore.loading" class="flex items-center justify-center">
              <HeroIcon name="spinner" size="sm" class="animate-spin h-5 w-5 mr-2" />
              Connexion en cours...
            </span>
            <span v-else>Se connecter</span>
          </button>
        </form>

        <div class="mt-6 text-center space-y-2">
          <p class="text-sm text-gray-600">
            <NuxtLink to="/forgot-password" class="font-medium text-blue-600 hover:text-blue-500">
              Mot de passe oublié ?
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

const isReady = ref(false)

onMounted(async () => {
  if (!authStore.initialized) {
    await authStore.initAuth()
  }
  isReady.value = true
  
  if (authStore.isLoggedIn) {
    console.log('🔐 Utilisateur déjà connecté, redirection...')
    navigateTo('/')
  }
})

const form = ref({
  email: '',
  password: ''
})

const error = ref('')

const handleLogin = async () => {
  error.value = ''
  
  try {
    const result = await authStore.login({
      email: form.value.email,
      password: form.value.password
    })
    
    if (result.success) {
      console.log('✅ Connexion réussie, redirection...')
      await nextTick()
      
      navigateTo('/')
    } else {
      error.value = result.error
    }
  } catch (err) {
    console.error('Erreur lors de la connexion:', err)
    error.value = 'Une erreur est survenue. Veuillez réessayer.'
  }
}
</script> 