<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Modifier {{ pet?.name }}</h1>
            <p class="mt-2 text-gray-600">Modifiez les informations de votre animal</p>
          </div>
          <NuxtLink to="/pets" class="btn-secondary">
            <Icon name="ph:arrow-left" class="h-4 w-4 mr-2" />
            Retour aux animaux
          </NuxtLink>
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

      <!-- Formulaire -->
      <div v-else-if="pet" class="card">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Photo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Photo de l'animal</label>
            <ImageUpload v-model="form.photo" :alt="pet?.name || 'Animal'" />
          </div>

          <!-- Nom et Espèce -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="input-field"
                placeholder="Nom de l'animal"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Espèce *</label>
              <select v-model="form.species" required class="input-field">
                <option value="">Sélectionner une espèce</option>
                <option value="chien">Chien</option>
                <option value="chat">Chat</option>
                <option value="oiseau">Oiseau</option>
                <option value="poisson">Poisson</option>
                <option value="rongeur">Rongeur</option>
                <option value="reptile">Reptile</option>
                <option value="autre">Autre</option>
              </select>
            </div>
          </div>

          <!-- Race et Date de naissance -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Race</label>
              <input
                v-model="form.breed"
                type="text"
                class="input-field"
                placeholder="Race de l'animal"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance *</label>
              <input
                v-model="form.birthDate"
                type="date"
                required
                class="input-field"
              />
            </div>
          </div>

          <!-- Genre et Couleur -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Genre *</label>
              <select v-model="form.gender" required class="input-field">
                <option value="">Sélectionner le genre</option>
                <option value="male">Mâle</option>
                <option value="female">Femelle</option>
                <option value="unknown">Inconnu</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
              <input
                v-model="form.color"
                type="text"
                class="input-field"
                placeholder="Couleur de l'animal"
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="input-field"
              placeholder="Description de l'animal (caractère, habitudes, etc.)"
            ></textarea>
          </div>

          <!-- Boutons -->
          <div class="flex justify-end space-x-3">
            <NuxtLink to="/pets" class="btn-secondary">
              Annuler
            </NuxtLink>
            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary"
            >
              <span v-if="submitting" class="flex items-center">
                <Icon name="ph:spinner" class="animate-spin h-4 w-4 mr-2" />
                Modification...
              </span>
              <span v-else>Modifier l'animal</span>
            </button>
          </div>
        </form>
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
const router = useRouter()
const petId = route.params.id

// State
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const pet = ref(null)

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

// Charger l'animal
const loadPet = async () => {
  try {
    loading.value = true
    error.value = ''
    
    console.log('Chargement de l\'animal avec l\'ID:', petId)
    const result = await petsStore.fetchPet(petId)
    console.log('Résultat fetchPet:', result)
    
    if (result.success) {
      pet.value = result.data
      console.log('Animal chargé:', pet.value)
      
      // Remplir le formulaire
      form.value = {
        name: pet.value.name || '',
        species: pet.value.species || '',
        breed: pet.value.breed || '',
        birthDate: pet.value.birthDate ? (typeof pet.value.birthDate === 'string' ? pet.value.birthDate.split('T')[0] : pet.value.birthDate) : '',
        gender: pet.value.gender || '',
        color: pet.value.color || '',
        description: pet.value.description || '',
        photo: pet.value.photo || ''
      }
      console.log('Formulaire rempli:', form.value)
    } else {
      error.value = result.error || 'Erreur lors du chargement de l\'animal'
      console.error('Erreur lors du chargement:', result.error)
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement de l\'animal'
    console.error('Erreur loadPet:', err)
  } finally {
    loading.value = false
  }
}

// Soumettre le formulaire
const handleSubmit = async () => {
  try {
    submitting.value = true
    error.value = ''
    
    // Validation
    if (!form.value.name || !form.value.species || !form.value.birthDate || !form.value.gender) {
      error.value = 'Veuillez remplir tous les champs obligatoires'
      return
    }
    
    const result = await petsStore.updatePet(petId, {
      name: form.value.name,
      species: form.value.species,
      breed: form.value.breed,
      birthDate: form.value.birthDate,
      gender: form.value.gender,
      color: form.value.color,
      description: form.value.description,
      photo: form.value.photo
    })
    
    if (result.success) {
      // Rediriger vers la liste des animaux
      await router.push('/pets')
    } else {
      error.value = result.error || 'Erreur lors de la modification de l\'animal'
    }
  } catch (err) {
    error.value = 'Erreur lors de la modification de l\'animal'
    console.error('Erreur handleSubmit:', err)
  } finally {
    submitting.value = false
  }
}

// Charger l'animal au montage
onMounted(() => {
  loadPet()
})
</script> 