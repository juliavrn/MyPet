<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-medium text-gray-900">
            {{ isEditing ? 'Modifier' : 'Ajouter' }} un animal
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            <HeroIcon name="x-mark" size="lg" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="name" class="form-label">Nom *</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              placeholder="Nom de l'animal"
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
              <option value="">Sélectionner une espèce</option>
              <option value="Chien">Chien</option>
              <option value="Chat">Chat</option>
              <option value="Oiseau">Oiseau</option>
              <option value="Poisson">Poisson</option>
              <option value="Hamster">Hamster</option>
              <option value="Lapin">Lapin</option>
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
              placeholder="Race de l'animal"
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
            <label for="gender" class="form-label">Sexe *</label>
            <select
              id="gender"
              v-model="form.gender"
              required
              class="input-field"
            >
              <option value="">Sélectionner le sexe</option>
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
              placeholder="Couleur de l'animal"
            />
          </div>

          <div>
            <label for="description" class="form-label">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="input-field"
              placeholder="Description de l'animal..."
            ></textarea>
          </div>

          <div>
            <label for="photo" class="form-label">URL de la photo</label>
            <input
              id="photo"
              v-model="form.photo"
              type="url"
              class="input-field"
              placeholder="https://example.com/photo.jpg"
            />
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="btn-secondary flex-1"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="petsStore.isLoading"
              class="btn-primary flex-1"
            >
              <span v-if="petsStore.isLoading" class="flex items-center justify-center">
                <HeroIcon name="arrow-path" size="sm" class="animate-spin mr-2" />
                Enregistrement...
              </span>
              <span v-else>{{ isEditing ? 'Modifier' : 'Ajouter' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  pet: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const petsStore = usePetsStore()
const error = ref('')

const isEditing = computed(() => !!props.pet)

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

// Initialiser le formulaire si on modifie un animal
onMounted(() => {
  if (props.pet) {
    form.value = {
      name: props.pet.name || '',
      species: props.pet.species || '',
      breed: props.pet.breed || '',
      birthDate: props.pet.birthDate ? props.pet.birthDate.split('T')[0] : '',
      gender: props.pet.gender || '',
      color: props.pet.color || '',
      description: props.pet.description || '',
      photo: props.pet.photo || ''
    }
  }
})

const handleSubmit = async () => {
  error.value = ''
  
  try {
    let result
    
    if (isEditing.value) {
      result = await petsStore.updatePet(props.pet.id, form.value)
    } else {
      result = await petsStore.createPet(form.value)
    }
    
    if (result.success) {
      emit('saved')
    } else {
      error.value = result.error
    }
  } catch (err) {
    error.value = 'Une erreur est survenue lors de l\'enregistrement'
  }
}
</script> 