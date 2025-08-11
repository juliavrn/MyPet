<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <NuxtLink to="/" class="flex items-center justify-center space-x-2 mb-6">
          <span class="text-4xl text-blue-600">🐾</span>
          <span class="text-3xl font-bold text-gray-900">MyPet</span>
        </NuxtLink>
        <h2 class="text-3xl font-bold text-gray-900">Créer un compte</h2>
        <p class="mt-2 text-sm text-gray-600">
          Rejoignez MyPet pour gérer vos animaux de compagnie
        </p>
      </div>

      <div class="card">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <div>
            <label for="firstName" class="form-label">Prénom</label>
            <input
              id="firstName"
              v-model="form.firstName"
              type="text"
              required
              class="input-field"
              placeholder="Votre prénom"
            />
          </div>

          <div>
            <label for="lastName" class="form-label">Nom</label>
            <input
              id="lastName"
              v-model="form.lastName"
              type="text"
              required
              class="input-field"
              placeholder="Votre nom"
            />
          </div>

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
              minlength="6"
            />
            <p class="mt-1 text-xs text-gray-500">
              Le mot de passe doit contenir au moins 6 caractères
            </p>
          </div>

          <div>
            <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              type="password"
              required
              class="input-field"
              placeholder="Confirmez votre mot de passe"
            />
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:warning-circle" class="h-5 w-5 text-red-400" />
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:check-circle" class="h-5 w-5 text-green-400" />
              <div class="ml-3">
                <p class="text-sm text-green-800">{{ success }}</p>
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="authStore.loading || !isFormValid"
            class="w-full btn-primary py-3 text-base font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="authStore.loading" class="flex items-center justify-center">
              <Icon name="ph:spinner" class="animate-spin h-5 w-5 mr-2" />
              Création en cours...
            </span>
            <span v-else>Créer mon compte</span>
          </button>


        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Déjà un compte ? 
            <NuxtLink to="/login" class="font-medium text-blue-600 hover:text-blue-500">
              Se connecter
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Rediriger si déjà connecté
const authStore = useAuthStore()

if (authStore.isLoggedIn) {
  navigateTo('/')
}

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  confirmPassword: ''
})

const error = ref('')
const success = ref('')

// Validation du formulaire
const isFormValid = computed(() => {
  return form.value.firstName && 
         form.value.lastName && 
         form.value.email && 
         form.value.password && 
         form.value.confirmPassword &&
         form.value.password === form.value.confirmPassword &&
         form.value.password.length >= 6
})

const handleRegister = async () => {
  error.value = ''
  success.value = ''
  
  // Validation côté client
  if (!form.value.firstName || !form.value.lastName || !form.value.email || !form.value.password || !form.value.confirmPassword) {
    error.value = 'Veuillez remplir tous les champs'
    return
  }
  
  if (form.value.password !== form.value.confirmPassword) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }
  
  if (form.value.password.length < 6) {
    error.value = 'Le mot de passe doit contenir au moins 6 caractères'
    return
  }
  
  try {
    const result = await authStore.register({
      firstName: form.value.firstName,
      lastName: form.value.lastName,
      email: form.value.email,
      password: form.value.password
    })
    
    if (result.success) {
      success.value = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.'
      
      // Vider le formulaire
      form.value = {
        firstName: '',
        lastName: '',
        email: '',
        password: '',
        confirmPassword: ''
      }
      
      // Rediriger vers la page de connexion après 2 secondes
      setTimeout(() => {
        if (process.client) {
          window.location.href = '/login'
        } else {
          navigateTo('/login')
        }
      }, 2000)
    } else {
      error.value = result.error
    }
  } catch (err) {
    error.value = 'Une erreur est survenue. Veuillez réessayer.'
  }
}

</script> 