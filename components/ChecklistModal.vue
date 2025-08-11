<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ checklist ? 'Modifier la tâche' : 'Nouvelle tâche' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <HeroIcon name="x-mark" size="lg" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
            Titre de la tâche *
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Ex: Donner les médicaments à Max"
          />
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Description (optionnel)
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Détails supplémentaires..."
          ></textarea>
        </div>

        <div>
          <label for="petId" class="block text-sm font-medium text-gray-700 mb-1">
            Animal *
          </label>
          <select
            id="petId"
            v-model="form.petId"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Sélectionner un animal</option>
            <option 
              v-for="pet in pets" 
              :key="pet.id" 
              :value="pet.id"
            >
              {{ pet.name }}
            </option>
          </select>
        </div>

        <div>
          <label for="dueDate" class="block text-sm font-medium text-gray-700 mb-1">
            Date d'échéance (optionnel)
          </label>
          <input
            id="dueDate"
            v-model="form.dueDate"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
            Priorité
          </label>
          <select
            id="priority"
            v-model="form.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="low">Basse</option>
            <option value="medium">Moyenne</option>
            <option value="high">Haute</option>
          </select>
        </div>

        <div v-if="checklist" class="flex items-center">
          <input
            id="isCompleted"
            v-model="form.isCompleted"
            type="checkbox"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          />
          <label for="isCompleted" class="ml-2 text-sm text-gray-700">
            Tâche terminée
          </label>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">Suggestions rapides</h4>
          <div class="grid grid-cols-1 gap-2">
            <button
              v-for="suggestion in quickSuggestions"
              :key="suggestion.title"
              type="button"
              @click="applySuggestion(suggestion)"
              class="text-left p-2 text-sm text-gray-600 hover:bg-gray-50 rounded border border-gray-200 hover:border-blue-300 transition-colors"
            >
              <div class="font-medium">{{ suggestion.title }}</div>
              <div class="text-xs text-gray-500">{{ suggestion.description }}</div>
            </button>
          </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ checklist ? 'Modifier' : 'Créer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useNotificationStore } from '~/stores/notification'

const props = defineProps({
  checklist: {
    type: Object,
    default: null
  },
  pets: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'save'])

const notificationStore = useNotificationStore()
const loading = ref(false)
const form = ref({
  title: '',
  description: '',
  petId: '',
  dueDate: '',
  priority: 'medium',
  isCompleted: false
})

const quickSuggestions = [
  {
    title: 'Donner les médicaments',
    description: 'Traitement médical quotidien',
    priority: 'high'
  },
  {
    title: 'Nourrir l\'animal',
    description: 'Repas principal',
    priority: 'high'
  },
  {
    title: 'Sortir le chien',
    description: 'Promenade quotidienne',
    priority: 'medium'
  },
  {
    title: 'Nettoyer la litière',
    description: 'Entretien de la litière',
    priority: 'medium'
  },
  {
    title: 'Brosser l\'animal',
    description: 'Soins du pelage',
    priority: 'low'
  },
  {
    title: 'Rendez-vous vétérinaire',
    description: 'Visite de contrôle',
    priority: 'high'
  },
  {
    title: 'Vaccination',
    description: 'Rappel de vaccin',
    priority: 'high'
  },
  {
    title: 'Jouer avec l\'animal',
    description: 'Temps de jeu et d\'attention',
    priority: 'low'
  }
]

const handleSubmit = async () => {
  if (!form.value.title || !form.value.petId) {
    notificationStore.warning('Veuillez remplir tous les champs obligatoires')
    return
  }

  loading.value = true
  
  try {
    
    
    const result = await emit('save', {
      title: form.value.title,
      description: form.value.description || null,
      petId: form.value.petId,
      dueDate: form.value.dueDate || null,
      priority: form.value.priority,
      isCompleted: form.value.isCompleted
    })
    
    
    
    if (result && result.success) {
      notificationStore.success('Tâche créée avec succès !')
    } else if (result && result.error) {
      notificationStore.error(result.error)
    }
  } catch (error) {
    
    notificationStore.error('Erreur lors de la sauvegarde: ' + (error.message || 'Erreur inconnue'))
  } finally {
    loading.value = false
  }
}

const applySuggestion = (suggestion) => {
  form.value.title = suggestion.title
  form.value.description = suggestion.description
  form.value.priority = suggestion.priority
  
  // Ajouter une date d'échéance basée sur le type de tâche
  if (suggestion.title.includes('médicaments') || suggestion.title.includes('nourrir')) {
    form.value.dueDate = getNextHour()
  } else if (suggestion.title.includes('sortir') || suggestion.title.includes('nettoyer')) {
    form.value.dueDate = getNextDay()
  } else if (suggestion.title.includes('vétérinaire') || suggestion.title.includes('vaccination')) {
    form.value.dueDate = getNextWeek()
  }
}

// Fonctions utilitaires pour les suggestions de dates
const getNextHour = () => {
  const now = new Date()
  now.setHours(now.getHours() + 1)
  return now.toISOString().split('T')[0]
}

const getNextMealTime = () => {
  const now = new Date()
  const hour = now.getHours()
  
  if (hour < 8) {
    now.setHours(8, 0, 0, 0)
  } else if (hour < 18) {
    now.setHours(18, 0, 0, 0)
  } else {
    now.setDate(now.getDate() + 1)
    now.setHours(8, 0, 0, 0)
  }
  
  return now.toISOString().split('T')[0]
}

const getNextDay = () => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
}

const getNextWeek = () => {
  const nextWeek = new Date()
  nextWeek.setDate(nextWeek.getDate() + 7)
  return nextWeek.toISOString().split('T')[0]
}

// Initialiser le formulaire quand la prop checklist change
watch(() => props.checklist, (newChecklist) => {
  if (newChecklist) {
    // Mode édition
    form.value = {
      title: newChecklist.title,
      description: newChecklist.description || '',
      petId: newChecklist.pet ? newChecklist.pet.split('/').pop() : '',
      dueDate: newChecklist.dueDate || '',
      priority: newChecklist.priority || 'medium',
      isCompleted: newChecklist.isCompleted || false
    }
  } else {
    // Mode création
    form.value = {
      title: '',
      description: '',
      petId: '',
      dueDate: '',
      priority: 'medium',
      isCompleted: false
    }
  }
}, { immediate: true })
</script> 