<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ pet?.name }}</h1>
            <p class="mt-2 text-gray-600">Détails de votre animal</p>
          </div>
          <div class="flex space-x-3">
            <NuxtLink to="/pets" class="btn-secondary">
              <Icon name="ph:arrow-left" class="h-4 w-4 mr-2" />
              Retour aux animaux
            </NuxtLink>
            <NuxtLink
              v-if="pet"
              :to="`/pets/${pet.id}/edit`"
              class="btn-primary"
            >
              <Icon name="ph:pencil" class="h-4 w-4 mr-2" />
              Modifier
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="flex items-center space-x-2">
          <Icon name="ph:spinner" class="animate-spin h-6 w-6 text-blue-600" />
          <span class="text-gray-600">Chargement de l'animal...</span>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <Icon name="ph:warning-circle" class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Détails de l'animal -->
      <div v-else-if="pet" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Photo -->
        <div class="card">
          <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden">
            <img
              v-if="pet.photo"
              :src="pet.photo"
              :alt="pet.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <Icon name="ph:heart" class="h-32 w-32 text-gray-400" />
            </div>
          </div>
        </div>

        <!-- Informations -->
        <div class="card space-y-6">
          <div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Informations générales</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nom</label>
                <p class="mt-1 text-lg text-gray-900">{{ pet.name }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Espèce</label>
                <p class="mt-1 text-lg text-gray-900">{{ getSpeciesLabel(pet.species) }}</p>
              </div>
              
              <div v-if="pet.breed">
                <label class="block text-sm font-medium text-gray-700">Race</label>
                <p class="mt-1 text-lg text-gray-900">{{ pet.breed }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Genre</label>
                <p class="mt-1 text-lg text-gray-900">{{ getGenderLabel(pet.gender) }}</p>
              </div>
              
              <div v-if="pet.birthDate">
                <label class="block text-sm font-medium text-gray-700">Date de naissance</label>
                <p class="mt-1 text-lg text-gray-900">{{ formatDate(pet.birthDate) }}</p>
              </div>
              
              <div v-if="pet.color">
                <label class="block text-sm font-medium text-gray-700">Couleur</label>
                <p class="mt-1 text-lg text-gray-900">{{ pet.color }}</p>
              </div>
            </div>
          </div>

          <div v-if="pet.description">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <p class="text-gray-900">{{ pet.description }}</p>
          </div>

          <div v-if="pet.createdAt">
            <label class="block text-sm font-medium text-gray-700">Ajouté le</label>
            <p class="mt-1 text-gray-900">{{ formatDateTime(pet.createdAt) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Rediriger si pas connecté
const authStore = useAuthStore()
const petsStore = usePetsStore()

// Initialiser l'authentification
authStore.initAuth()

if (!authStore.isLoggedIn) {
  navigateTo('/login')
}

// Route params
const route = useRoute()
const petId = route.params.id

// State
const loading = ref(true)
const error = ref('')
const pet = ref(null)

// Charger l'animal
const loadPet = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const result = await petsStore.fetchPet(petId)
    if (result.success) {
      pet.value = result.data
    } else {
      error.value = result.error || 'Erreur lors du chargement de l\'animal'
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement de l\'animal'
    console.error('Erreur loadPet:', err)
  } finally {
    loading.value = false
  }
}

// Méthodes utilitaires
const getSpeciesLabel = (species) => {
  const speciesLabels = {
    'chien': 'Chien',
    'chat': 'Chat',
    'oiseau': 'Oiseau',
    'poisson': 'Poisson',
    'rongeur': 'Rongeur',
    'reptile': 'Reptile',
    'autre': 'Autre'
  }
  return speciesLabels[species] || species
}

const getGenderLabel = (gender) => {
  const genderLabels = {
    'male': 'Mâle',
    'female': 'Femelle',
    'unknown': 'Inconnu'
  }
  return genderLabels[gender] || gender
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Charger l'animal au montage
onMounted(() => {
  loadPet()
})
</script> 