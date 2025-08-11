<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-6 lg:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mon Profil</h1>
            <p class="mt-1 sm:mt-2 text-gray-600">Gérez vos informations personnelles et votre compte</p>
          </div>
          <div class="flex gap-2">
            <NuxtLink 
              to="/" 
              class="btn-secondary text-sm sm:text-base"
            >
              <HeroIcon name="arrow-left" size="sm" class="mr-2" />
              Retour
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Message de succès -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex">
          <HeroIcon name="check-circle" size="sm" class="h-4 w-4 sm:h-5 sm:w-5 text-green-400 flex-shrink-0 mt-0.5" />
          <div class="ml-2 sm:ml-3">
            <p class="text-sm text-green-800">{{ successMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Message d'erreur -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex">
          <HeroIcon name="warning-circle" size="sm" class="h-4 w-4 sm:h-5 sm:w-5 text-red-400 flex-shrink-0 mt-0.5" />
          <div class="ml-2 sm:ml-3">
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-8 sm:py-12">
        <div class="flex items-center space-x-2">
          <HeroIcon name="spinner" class="animate-spin h-5 w-5 sm:h-6 sm:w-6 text-blue-600" />
          <span class="text-sm sm:text-base text-gray-600">Chargement...</span>
        </div>
      </div>

      <!-- Contenu principal -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations actuelles -->
        <div class="lg:col-span-1">
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations actuelles</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ user?.firstName || 'Non renseigné' }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ user?.lastName || 'Non renseigné' }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ user?.email || 'Non renseigné' }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rôles</label>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="role in userRoles" 
                    :key="role"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ formatRole(role) }}
                  </span>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Membre depuis</label>
                <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                  {{ formatDate(user?.createdAt) }}
                </p>
              </div>
              
              <div v-if="user?.updatedAt">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dernière modification</label>
                <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                  {{ formatDate(user.updatedAt) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="lg:col-span-2">
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Modifier mon profil</h3>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
                    Prénom <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="firstName"
                    v-model="form.firstName"
                    type="text"
                    required
                    class="input-field w-full"
                    :class="{ 'border-red-300': errors.firstName }"
                    placeholder="Votre prénom"
                  />
                  <p v-if="errors.firstName" class="mt-1 text-sm text-red-600">{{ errors.firstName }}</p>
                </div>
                
                <div>
                  <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="lastName"
                    v-model="form.lastName"
                    type="text"
                    required
                    class="input-field w-full"
                    :class="{ 'border-red-300': errors.lastName }"
                    placeholder="Votre nom"
                  />
                  <p v-if="errors.lastName" class="mt-1 text-sm text-red-600">{{ errors.lastName }}</p>
                </div>
              </div>
              
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="input-field w-full"
                  :class="{ 'border-red-300': errors.email }"
                  placeholder="votre.email@exemple.com"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>
              
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                  Nouveau mot de passe
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  class="input-field w-full"
                  :class="{ 'border-red-300': errors.password }"
                  placeholder="Laissez vide pour ne pas changer"
                  minlength="6"
                />
                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                <p class="mt-1 text-xs text-gray-500">Minimum 6 caractères</p>
              </div>
              
              <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">
                  Confirmer le mot de passe
                </label>
                <input
                  id="confirmPassword"
                  v-model="form.confirmPassword"
                  type="password"
                  class="input-field w-full"
                  :class="{ 'border-red-300': errors.confirmPassword }"
                  placeholder="Confirmez le nouveau mot de passe"
                />
                <p v-if="errors.confirmPassword" class="mt-1 text-sm text-red-600">{{ errors.confirmPassword }}</p>
              </div>
              
              <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button
                  type="submit"
                  :disabled="updating"
                  class="btn-primary flex-1"
                >
                  <HeroIcon 
                    v-if="updating" 
                    name="spinner" 
                    class="animate-spin h-4 w-4 mr-2" 
                  />
                  <HeroIcon 
                    v-else 
                    name="check" 
                    class="h-4 w-4 mr-2" 
                  />
                  {{ updating ? 'Mise à jour...' : 'Mettre à jour le profil' }}
                </button>
                
                <button
                  type="button"
                  @click="resetForm"
                  class="btn-secondary flex-1"
                >
                  <HeroIcon name="refresh" class="h-4 w-4 mr-2" />
                  Réinitialiser
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useNotificationStore } from '~/stores/notification'

definePageMeta({
  middleware: 'auth'
})

const authStore = useAuthStore()
const { show: showNotification } = useNotificationStore()

const loading = ref(false)
const updating = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  confirmPassword: ''
})

const user = computed(() => authStore.getUser)
const userRoles = computed(() => authStore.getUserRoles)

const formatRole = (role) => {
  const roleMap = {
    'ROLE_USER': 'Utilisateur',
    'ROLE_ADMIN': 'Administrateur',
    'ROLE_MODERATOR': 'Modérateur'
  }
  return roleMap[role] || role.replace('ROLE_', '')
}

const formatDate = (dateString) => {
  if (!dateString) return 'Non renseigné'
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return 'Date invalide'
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.value.firstName?.trim()) {
    errors.value.firstName = 'Le prénom est requis'
  }
  
  if (!form.value.lastName?.trim()) {
    errors.value.lastName = 'Le nom est requis'
  }
  
  if (!form.value.email?.trim()) {
    errors.value.email = 'L\'email est requis'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'L\'email n\'est pas valide'
  }
  
  if (form.value.password && form.value.password.length < 6) {
    errors.value.password = 'Le mot de passe doit contenir au moins 6 caractères'
  }
  
  if (form.value.password && form.value.password !== form.value.confirmPassword) {
    errors.value.confirmPassword = 'Les mots de passe ne correspondent pas'
  }
  
  return Object.keys(errors.value).length === 0
}

const updateProfile = async () => {
  if (!validateForm()) {
    return
  }
  
  updating.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    const { $api } = useNuxtApp()
    
    const updateData = {
      firstName: form.value.firstName.trim(),
      lastName: form.value.lastName.trim(),
      email: form.value.email.trim()
    }
    
    if (form.value.password) {
      updateData.password = form.value.password
    }
    
    const response = await $api.put('/api/user/profile', updateData)
    
    if (response.message === 'Profile updated successfully') {
      authStore.user = response.user
      
      if (process.client) {
        localStorage.setItem('mypet_user', JSON.stringify(response.user))
      }
      
      successMessage.value = 'Profil mis à jour avec succès !'
      showNotification('Profil mis à jour avec succès !', 'success')
      
      resetForm()
      
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    } else {
      throw new Error(response.message || 'Erreur lors de la mise à jour')
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil:', error)
    
    if (error.data?.errors) {
      errorMessage.value = 'Veuillez corriger les erreurs suivantes :'
      error.data.errors.forEach(err => {
        if (err.includes('firstName')) errors.value.firstName = err
        if (err.includes('lastName')) errors.value.lastName = err
        if (err.includes('email')) errors.value.email = err
        if (err.includes('password')) errors.value.password = err
      })
    } else if (error.data?.message) {
      errorMessage.value = error.data.message
    } else {
      errorMessage.value = 'Une erreur est survenue lors de la mise à jour du profil'
    }
    
    showNotification(errorMessage.value, 'error')
  } finally {
    updating.value = false
  }
}

const resetForm = () => {
  form.value = {
    firstName: user.value?.firstName || '',
    lastName: user.value?.lastName || '',
    email: user.value?.email || '',
    password: '',
    confirmPassword: ''
  }
  errors.value = {}
}

const loadUserData = async () => {
  if (!authStore.isLoggedIn) {
    await navigateTo('/login')
    return
  }
  
  loading.value = true
  
  try {
    if (!authStore.user) {
      const { $api } = useNuxtApp()
      const userResponse = await $api.get('/api/user')
      if (userResponse) {
        authStore.user = userResponse
      }
    }
    
    resetForm()
  } catch (error) {
    console.error('Erreur lors du chargement des données utilisateur:', error)
    errorMessage.value = 'Impossible de charger les données utilisateur'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadUserData()
})
</script>
