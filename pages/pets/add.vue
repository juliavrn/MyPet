<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Ajouter un animal</h1>
            <p class="mt-2 text-gray-600">Créez le profil de votre nouvel animal de compagnie</p>
          </div>
          <NuxtLink to="/pets" class="btn-secondary">
            <Icon name="ph:arrow-left" class="h-5 w-5 mr-2" />
            Retour
          </NuxtLink>
        </div>
      </div>

      <div class="card">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="name" class="form-label">Nom de l'animal *</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              placeholder="Ex: Max, Luna, Rocky..."
            />
          </div>

          <div>
            <label for="species" class="form-label">Espèce *</label>
            <select
              id="species"
              v-model="form.species"
              required
              class="input-field"
            >
              <option value="">Sélectionnez une espèce</option>
              <option value="Chien">Chien</option>
              <option value="Chat">Chat</option>
              <option value="Oiseau">Oiseau</option>
              <option value="Poisson">Poisson</option>
              <option value="Rongeur">Rongeur</option>
              <option value="Reptile">Reptile</option>
              <option value="Autre">Autre</option>
            </select>
          </div>

          <div>
            <label for="breed" class="form-label">Race</label>
            <input
              id="breed"
              v-model="form.breed"
              type="text"
              class="input-field"
              placeholder="Ex: Golden Retriever, Persan..."
            />
          </div>

          <div>
            <label for="birthDate" class="form-label">Date de naissance *</label>
            <input
              id="birthDate"
              v-model="form.birthDate"
              type="date"
              required
              class="input-field"
            />
          </div>

          <div>
            <label for="gender" class="form-label">Genre *</label>
            <select
              id="gender"
              v-model="form.gender"
              required
              class="input-field"
            >
              <option value="">Sélectionnez un genre</option>
              <option value="Mâle">Mâle</option>
              <option value="Femelle">Femelle</option>
            </select>
          </div>

          <div>
            <label for="color" class="form-label">Couleur</label>
            <input
              id="color"
              v-model="form.color"
              type="text"
              class="input-field"
              placeholder="Ex: Marron, Noir, Blanc..."
            />
          </div>

          <div>
            <label for="description" class="form-label">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="input-field"
              placeholder="Décrivez votre animal, ses habitudes, son caractère..."
            ></textarea>
          </div>

          <div>
            <label class="form-label">Photo de l'animal</label>
            <ImageUpload v-model="form.photo" :alt="form.name || 'Animal'" />
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

          <div class="flex justify-end space-x-4">
            <NuxtLink to="/pets" class="btn-secondary">
              Annuler
            </NuxtLink>
            <button
              type="submit"
              :disabled="petsStore.loading"
              class="btn-primary"
            >
              <span v-if="petsStore.loading" class="flex items-center">
                <Icon name="ph:spinner" class="animate-spin h-5 w-5 mr-2" />
                Création en cours...
              </span>
              <span v-else>Créer l'animal</span>
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

if (!authStore.isLoggedIn) {
  navigateTo('/login')
}

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
      
      // Recharger les données pour s'assurer que tout est à jour
      await petsStore.fetchPets()
      
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
</script> 