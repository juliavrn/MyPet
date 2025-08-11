<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-6 lg:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mes Animaux</h1>
            <p class="mt-1 sm:mt-2 text-gray-600">Gérez vos animaux de compagnie</p>
          </div>
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:space-x-3">
            <NuxtLink 
              to="/health" 
              class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base"
            >
              <HeroIcon name="heart" size="sm" class="w-4 h-4 mr-2" />
              Suivi de santé
            </NuxtLink>
            <NuxtLink to="/pets/add" class="btn-primary text-sm sm:text-base">
              <Icon name="ph:plus" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" />
              Ajouter un animal
            </NuxtLink>
          </div>
        </div>
      </div>

      <div class="card mb-4 sm:mb-6">
        <div class="flex items-center space-x-4">
          <div class="flex-1">
            <input
              v-model="search"
              type="text"
              placeholder="Rechercher un animal..."
              class="input-field"
            />
          </div>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex">
          <Icon name="ph:warning-circle" class="h-4 w-4 sm:h-5 sm:w-5 text-red-400 flex-shrink-0 mt-0.5" />
          <div class="ml-2 sm:ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex">
          <Icon name="ph:check-circle" class="h-4 w-4 sm:h-5 sm:w-5 text-green-400 flex-shrink-0 mt-0.5" />
          <div class="ml-2 sm:ml-3">
            <p class="text-sm text-green-800">{{ success }}</p>
          </div>
        </div>
      </div>

      <div v-if="petsStore.loading" class="flex justify-center py-8 sm:py-12">
        <div class="flex items-center space-x-2">
          <Icon name="ph:spinner" class="animate-spin h-5 w-5 sm:h-6 sm:w-6 text-blue-600" />
          <span class="text-sm sm:text-base text-gray-600">Chargement des animaux...</span>
        </div>
      </div>

              <div v-else-if="petsStore.pets && petsStore.pets.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div
                      v-for="pet in petsStore.pets"
          :key="pet.id"
          class="card hover:shadow-lg transition-shadow duration-200"
        >
          <div class="aspect-square bg-gray-200 rounded-lg mb-3 sm:mb-4 overflow-hidden">
            <img
              v-if="pet.photo"
              :src="pet.photo"
              :alt="pet.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <Icon name="ph:heart" class="h-12 w-12 sm:h-16 sm:w-16 text-gray-400" />
            </div>
          </div>

          <div class="space-y-2 sm:space-y-3">
            <div>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ pet.name }}</h3>
              <p class="text-sm sm:text-base text-gray-600">{{ pet.species }} {{ pet.breed ? `- ${pet.breed}` : '' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm">
              <div>
                <span class="font-medium text-gray-700">Genre:</span>
                <span class="text-gray-600 ml-1">{{ getGenderLabel(pet.gender) }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700">Couleur:</span>
                <span class="text-gray-600 ml-1">{{ pet.color || 'Non spécifiée' }}</span>
              </div>
              <div v-if="pet.birthDate" class="col-span-2">
                <span class="font-medium text-gray-700">Né(e) le:</span>
                <span class="text-gray-600 ml-1">{{ formatDate(pet.birthDate) }}</span>
              </div>
            </div>

            <div v-if="pet.description" class="text-sm text-gray-600">
              {{ pet.description }}
            </div>

            <div class="flex space-x-2 pt-4">
              <NuxtLink
                :to="`/pets/${pet.id}`"
                class="flex-1 btn-secondary text-sm text-center"
              >
                <Icon name="ph:eye" class="h-4 w-4 mr-1" />
                Voir
              </NuxtLink>
              <NuxtLink
                :to="`/pets/${pet.id}/edit`"
                class="flex-1 btn-primary text-sm text-center"
              >
                <Icon name="ph:pencil" class="h-4 w-4 mr-1" />
                Modifier
              </NuxtLink>
              <button
                @click="deletePet(pet.id)"
                class="btn-danger text-sm"
                :disabled="deletingPet === pet.id"
              >
                <Icon name="ph:trash" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon name="ph:heart" class="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun animal pour le moment</h3>
          <p class="text-gray-600 mb-6">
            Commencez par ajouter votre premier animal de compagnie pour profiter de toutes les fonctionnalités de MyPet.
          </p>
          <NuxtLink to="/pets/add" class="btn-primary">
            <Icon name="ph:plus" class="h-5 w-5 mr-2" />
            Ajouter mon premier animal
          </NuxtLink>
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

// State
const showAddModal = ref(false)
const editingPet = ref(null)
const deletingPet = ref(null)

// Form
const form = ref({
  name: '',
  species: '',
  breed: '',
  birthDate: '',
  gender: '',
  color: '',
  description: '',
  photo: ''
})

const error = ref('')
const success = ref('')

// Computed
const filteredPets = computed(() => {
  if (!petsStore.getPets) return []
  
  return petsStore.getPets.filter(pet => {
    const searchTerm = search.value.toLowerCase()
    return pet.name.toLowerCase().includes(searchTerm) ||
           pet.species.toLowerCase().includes(searchTerm) ||
           (pet.breed && pet.breed.toLowerCase().includes(searchTerm))
  })
})

const search = ref('')

// Methods
const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  // Validation
  if (!form.value.name || !form.value.species || !form.value.birthDate || !form.value.gender) {
    error.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }
  
  try {
    const result = await petsStore.createPet({
      name: form.value.name,
      species: form.value.species,
      breed: form.value.breed || null,
      birthDate: form.value.birthDate,
      gender: form.value.gender,
      color: form.value.color || null,
      description: form.value.description || null,
      photo: form.value.photo || null
    })
    
    if (result.success) {
      success.value = 'Animal créé avec succès !'
      
      // Vider le formulaire
      form.value = {
        name: '',
        species: '',
        breed: '',
        birthDate: '',
        gender: '',
        color: '',
        description: '',
        photo: ''
      }
      
      // Fermer le modal
      showAddModal.value = false
      
      // Rediriger vers la liste des animaux après 2 secondes
      setTimeout(() => {
        navigateTo('/pets')
      }, 2000)
    } else {
      error.value = result.error
    }
  } catch (err) {
    
    error.value = 'Une erreur est survenue. Veuillez réessayer.'
  }
}

const editPet = (pet) => {
  editingPet.value = pet
  form.value = {
    name: pet.name,
    species: pet.species,
    breed: pet.breed || '',
    birthDate: pet.birthDate.split('T')[0],
    gender: pet.gender,
    color: pet.color || '',
    description: pet.description || '',
    photo: pet.photo || ''
  }
  showAddModal.value = true
}

const updatePet = async () => {
  error.value = ''
  success.value = ''
  
  try {
    const result = await petsStore.updatePet(editingPet.value.id, form.value)
    
    if (result.success) {
      success.value = 'Animal mis à jour avec succès !'
      showAddModal.value = false
      editingPet.value = null
      
      // Vider le formulaire
      form.value = {
        name: '',
        species: '',
        breed: '',
        birthDate: '',
        gender: '',
        color: '',
        description: '',
        photo: ''
      }
    } else {
      error.value = result.error
    }
  } catch (err) {
    
    error.value = 'Une erreur est survenue. Veuillez réessayer.'
  }
}

const deletePet = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet animal ?')) {
    deletingPet.value = id
    try {
      const result = await petsStore.deletePet(id)
      if (result.success) {
        success.value = 'Animal supprimé avec succès !'
      } else {
        error.value = result.error
      }
    } catch (err) {
      
      error.value = 'Une erreur est survenue. Veuillez réessayer.'
    } finally {
      deletingPet.value = null
    }
  }
}

const closeModal = () => {
  showAddModal.value = false
  editingPet.value = null
  form.value = {
    name: '',
    species: '',
    breed: '',
    birthDate: '',
    gender: '',
    color: '',
    description: '',
    photo: ''
  }
  error.value = ''
  success.value = ''
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

const getGenderLabel = (gender) => {
  return gender === 'male' ? 'Mâle' : 'Femelle'
}



// Charger les données au montage
onMounted(async () => {
  // Forcer le chargement des animaux
  await petsStore.fetchPets()
})
</script> 