<template>
  <div class="fade-in">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        📋 Listes de tâches
      </h1>
      <p class="text-gray-600">
        Gérez les tâches personnalisées pour vos animaux de compagnie
      </p>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 mb-6">
      <div class="flex-1">
        <select 
          v-model="selectedPet" 
          @change="filterByPet"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Tous les animaux</option>
                  <option 
          v-for="pet in (petsStore.getPets || [])" 
          :key="pet.id" 
          :value="pet.id"
        >
          {{ pet.name }}
        </option>
        </select>
      </div>
      
      <div class="flex gap-2">
        <NuxtLink 
          to="/health" 
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
        >
          <HeroIcon name="heart" size="sm" class="mr-2" />
          Suivi de santé
        </NuxtLink>
        <button 
          @click="showCompleted = !showCompleted"
          :class="[
            'px-4 py-2 rounded-lg border transition-colors flex items-center',
            showCompleted 
              ? 'bg-green-100 border-green-300 text-green-700 hover:bg-green-200' 
              : 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200'
          ]"
        >
          <HeroIcon 
            :name="showCompleted ? 'eye-slash' : 'eye'" 
            class="w-4 h-4 mr-2" 
          />
          {{ showCompleted ? 'Masquer terminées' : 'Afficher terminées' }}
        </button>
        
        <button 
          @click="openCreateModal"
          class="btn-primary px-4 py-2"
        >
          <HeroIcon name="plus" class="w-4 h-4 mr-2" />
          Nouvelle tâche
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Icon name="ph:list-checks" class="w-8 h-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-blue-600">Total des tâches</p>
            <p class="text-2xl font-bold text-blue-900">{{ (checklistStore.tasks || []).length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-yellow-50 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Icon name="ph:clock" class="w-8 h-8 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-yellow-600">En attente</p>
            <p class="text-2xl font-bold text-yellow-900">{{ (checklistStore.tasks || []).filter(t => !t.isCompleted).length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-red-50 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Icon name="ph:warning" class="w-8 h-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-red-600">En retard</p>
            <p class="text-2xl font-bold text-red-900">{{ (checklistStore.tasks || []).filter(t => !t.isCompleted && new Date(t.dueDate) < new Date()).length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-green-50 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Icon name="ph:check-circle" class="w-8 h-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-green-600">Terminées</p>
            <p class="text-2xl font-bold text-green-900">{{ (checklistStore.tasks || []).filter(t => t.isCompleted).length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tâches d'aujourd'hui -->
    <div v-if="(checklistStore.tasks || []).filter(t => isToday(new Date(t.dueDate))).length > 0" class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
        <HeroIcon name="calendar" class="w-5 h-5 mr-2 text-blue-600" />
        Tâches du jour
      </h3>
      <div class="grid gap-3">
        <div 
          v-for="checklist in checklistStore.getTodayChecklists" 
          :key="checklist.id"
          class="card border-l-4 border-blue-500 bg-blue-50"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <button 
                @click="toggleCompletion(checklist.id)"
                :class="[
                  'w-5 h-5 rounded border-2 mr-3 transition-colors',
                  checklist.isCompleted 
                    ? 'bg-green-500 border-green-500' 
                    : 'border-gray-300 hover:border-blue-500'
                ]"
              >
                <HeroIcon v-if="checklist.isCompleted" name="check" class="w-3 h-3 text-white" />
              </button>
              <div>
                <h4 class="font-medium text-gray-900">{{ checklist.title }}</h4>
                <p class="text-sm text-gray-600">{{ getPetName(checklist.pet) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Aujourd'hui</span>
              <button @click="editChecklist(checklist)" class="text-gray-400 hover:text-blue-600">
                <HeroIcon name="pencil" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="checklistStore.getOverdueChecklists.length > 0" class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
        <HeroIcon name="exclamation-triangle" class="w-5 h-5 mr-2 text-red-600" />
        Tâches en retard
      </h3>
      <div class="grid gap-3">
        <div 
          v-for="checklist in checklistStore.getOverdueChecklists" 
          :key="checklist.id"
          class="card border-l-4 border-red-500 bg-red-50"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <button 
                @click="toggleCompletion(checklist.id)"
                :class="[
                  'w-5 h-5 rounded border-2 mr-3 transition-colors',
                  checklist.isCompleted 
                    ? 'bg-green-500 border-green-500' 
                    : 'border-gray-300 hover:border-blue-500'
                ]"
              >
                <HeroIcon v-if="checklist.isCompleted" name="check" class="w-3 h-3 text-white" />
              </button>
              <div>
                <h4 class="font-medium text-gray-900">{{ checklist.title }}</h4>
                <p class="text-sm text-gray-600">{{ getPetName(checklist.pet) }}</p>
                <p class="text-xs text-red-600">En retard depuis {{ getDaysOverdue(checklist.dueDate) }} jour(s)</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">En retard</span>
              <button @click="editChecklist(checklist)" class="text-gray-400 hover:text-blue-600">
                <HeroIcon name="pencil" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="checklistStore.loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement des tâches...</p>
    </div>

          <div v-else-if="(filteredChecklists || []).length === 0" class="text-center py-12">
      <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <HeroIcon name="clipboard-document-list" class="w-12 h-12 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune tâche trouvée</h3>
      <p class="text-gray-600 mb-6">
        {{ selectedPet ? 'Aucune tâche pour cet animal.' : 'Commencez par créer votre première tâche !' }}
      </p>
      <button @click="openCreateModal" class="btn-primary">
        <HeroIcon name="plus" class="w-4 h-4 mr-2" />
        Créer une tâche
      </button>
    </div>

    <div v-else class="space-y-3">
      <div 
        v-for="checklist in filteredChecklists" 
        :key="checklist.id"
        :class="[
          'card transition-all duration-200 hover:shadow-md',
          checklist.isCompleted ? 'opacity-75 bg-gray-50' : '',
          isOverdue(checklist.dueDate) ? 'border-l-4 border-red-500' : ''
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center flex-1">
            <button 
              @click="toggleCompletion(checklist.id)"
              :class="[
                'w-5 h-5 rounded border-2 mr-3 transition-colors',
                checklist.isCompleted 
                  ? 'bg-green-500 border-green-500' 
                  : 'border-gray-300 hover:border-blue-500'
              ]"
            >
              <HeroIcon v-if="checklist.isCompleted" name="check" class="w-3 h-3 text-white" />
            </button>
            
            <div class="flex-1">
              <h4 
                :class="[
                  'font-medium transition-all',
                  checklist.isCompleted ? 'line-through text-gray-500' : 'text-gray-900'
                ]"
              >
                {{ checklist.title }}
              </h4>
              
              <div class="flex items-center gap-4 mt-1">
                <p class="text-sm text-gray-600">{{ getPetName(checklist.pet) }}</p>
                
                <div v-if="checklist.description" class="text-sm text-gray-500">
                  {{ checklist.description }}
                </div>
                
                <div v-if="checklist.dueDate" class="flex items-center text-sm">
                  <HeroIcon name="calendar" class="w-4 h-4 mr-1 text-gray-400" />
                  <span :class="[
                    'font-medium',
                    isOverdue(checklist.dueDate) ? 'text-red-600' : 'text-gray-600'
                  ]">
                    {{ formatDate(checklist.dueDate) }}
                  </span>
                </div>
                
                <div v-if="checklist.priority" class="flex items-center">
                  <span :class="[
                    'text-xs px-2 py-1 rounded-full font-medium',
                    getPriorityClass(checklist.priority)
                  ]">
                    {{ getPriorityLabel(checklist.priority) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 ml-4">
            <button @click="editChecklist(checklist)" class="text-gray-400 hover:text-blue-600">
              <HeroIcon name="pencil" class="w-4 h-4" />
            </button>
            <button @click="deleteChecklist(checklist.id)" class="text-gray-400 hover:text-red-600">
              <HeroIcon name="trash" class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

          <div v-if="showCompleted && (checklistStore.tasks || []).filter(t => t.isCompleted).length > 0" class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
        <HeroIcon name="check-circle" class="w-5 h-5 mr-2 text-green-600" />
        Tâches terminées
      </h3>
      <div class="space-y-3">
        <div 
          v-for="checklist in (checklistStore.tasks || []).filter(t => t.isCompleted)" 
          :key="checklist.id"
          class="card bg-green-50 border-green-200 opacity-75"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
              <div class="w-5 h-5 rounded-full bg-green-500 border-2 border-green-500 mr-3 flex items-center justify-center">
                <HeroIcon name="check" class="w-3 h-3 text-white" />
              </div>
              
              <div class="flex-1">
                <h4 class="font-medium text-gray-900 line-through">{{ checklist.title }}</h4>
                
                <div class="flex items-center gap-4 mt-1">
                  <p class="text-sm text-gray-600">{{ getPetName(checklist.pet) }}</p>
                  
                  <div v-if="checklist.description" class="text-sm text-gray-500">
                    {{ checklist.description }}
                  </div>
                  
                  <div v-if="checklist.dueDate" class="flex items-center text-sm">
                    <HeroIcon name="calendar" class="w-4 h-4 mr-1 text-gray-400" />
                    <span class="font-medium text-gray-600">
                      {{ formatDate(checklist.dueDate) }}
                    </span>
                  </div>
                  
                  <div v-if="checklist.priority" class="flex items-center">
                    <span :class="[
                      'text-xs px-2 py-1 rounded-full font-medium',
                      getPriorityClass(checklist.priority)
                    ]">
                      {{ getPriorityLabel(checklist.priority) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex items-center gap-2 ml-4">
              <button @click="deleteChecklist(checklist.id)" class="text-gray-400 hover:text-red-600">
                <HeroIcon name="trash" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ChecklistModal 
      v-if="showModal"
      :checklist="editingChecklist"
      :pets="petsStore.pets || []"
      @close="closeModal"
      @save="saveChecklist"
    />

    <div v-if="checklistStore.error" class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      {{ checklistStore.error }}
      <button @click="checklistStore.clearError()" class="ml-2 text-red-500 hover:text-red-700">
        <HeroIcon name="x-mark" class="w-4 h-4" />
      </button>
    </div>

    <div v-if="notification.show" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
      <div class="p-3">
        <p class="text-sm font-medium" :class="notification.type === 'success' ? 'text-green-800' : 'text-red-800'">
          {{ notification.message }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ middleware: 'auth' })

const checklistStore = useChecklistStore()
const petsStore = usePetsStore()
const authStore = useAuthStore()

const selectedPet = ref('')
const showCompleted = ref(false)
const showModal = ref(false)
const editingChecklist = ref(null)
const notification = ref({ show: false, message: '', type: 'success' })

const filteredChecklists = computed(() => {
  // S'assurer que tasks est un tableau
  if (!checklistStore.tasks || !Array.isArray(checklistStore.tasks)) {
    return []
  }

  let filtered = [...checklistStore.tasks]

  // Filtrer par animal
  if (selectedPet.value) {
    filtered = filtered.filter(checklist => {
      // Si pet est un objet, comparer l'ID
      if (checklist.pet && typeof checklist.pet === 'object' && checklist.pet.id) {
        return checklist.pet.id === parseInt(selectedPet.value)
      }
      // Si pet est une URL string (fallback)
      if (checklist.pet && typeof checklist.pet === 'string') {
        return checklist.pet === `/api/pets/${selectedPet.value}`
      }
      return false
    })
  }

  // Filtrer par statut
  if (!showCompleted.value) {
    filtered = filtered.filter(checklist => !checklist.isCompleted)
  }

  // Trier par priorité et date
  return filtered.sort((a, b) => {
    // Priorité : high > medium > low
    const priorityOrder = { high: 3, medium: 2, low: 1 }
    const aPriority = priorityOrder[a.priority] || 2
    const bPriority = priorityOrder[b.priority] || 2
    
    if (aPriority !== bPriority) {
      return bPriority - aPriority
    }
    
    // Si même priorité, trier par date d'échéance
    if (a.dueDate && b.dueDate) {
      return new Date(a.dueDate) - new Date(b.dueDate)
    }
    
    // Si pas de date, garder l'ordre original
    return 0
  })
})

const filterByPet = () => {
  // Le filtrage se fait automatiquement via computed
}

const openCreateModal = () => {
  editingChecklist.value = null
  showModal.value = true
}

const editChecklist = (checklist) => {
  editingChecklist.value = checklist
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingChecklist.value = null
}

const saveChecklist = async (checklistData) => {
  try {
    let result
    if (editingChecklist.value) {
      result = await checklistStore.updateChecklist(editingChecklist.value.id, checklistData)
      if (result.success) {
        showNotification('Tâche mise à jour avec succès !', 'success')
      } else {
        showNotification(result.error || 'Erreur lors de la mise à jour', 'error')
      }
    } else {
      result = await checklistStore.createChecklist(checklistData)
      if (result.success) {
        showNotification('Tâche créée avec succès !', 'success')
      } else {
        showNotification(result.error || 'Erreur lors de la création', 'error')
      }
    }
    
    if (result.success) {
      closeModal()
    }
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    showNotification('Erreur lors de la sauvegarde', 'error')
  }
}

const showNotification = (message, type = 'success') => {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

const closeNotification = () => {
  notification.value.show = false
}

const toggleCompletion = async (id) => {
  try {
    const result = await checklistStore.toggleChecklistCompletion(id)
    if (result.success) {
      showNotification('Statut de la tâche mis à jour !', 'success')
    } else {
      showNotification(result.error || 'Erreur lors de la mise à jour du statut', 'error')
    }
  } catch (error) {
    console.error('Erreur lors du changement de statut:', error)
    showNotification('Erreur lors du changement de statut', 'error')
  }
}

const deleteChecklist = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
    try {
      const result = await checklistStore.deleteChecklist(id)
      if (result.success) {
        showNotification('Tâche supprimée avec succès !', 'success')
      } else {
        showNotification(result.error || 'Erreur lors de la suppression', 'error')
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
      showNotification('Erreur lors de la suppression', 'error')
    }
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const isOverdue = (dateString) => {
  if (!dateString) return false
  const today = new Date().toISOString().split('T')[0]
  return dateString < today
}

const getDaysOverdue = (dateString) => {
  if (!dateString) return 0
  const today = new Date()
  const dueDate = new Date(dateString)
  const diffTime = today - dueDate
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getPetName = (pet) => {
  if (!pet) return 'Aucun animal'
  
  // Si pet est un objet (relation directe)
  if (typeof pet === 'object' && pet.name) {
    return pet.name
  }
  
  // Si pet est une URL string (fallback)
  if (typeof pet === 'string') {
    const petId = pet.split('/').pop()
    const pets = petsStore.getPets || []
    const petObj = pets.find(p => p.id === parseInt(petId))
    return petObj ? petObj.name : 'Animal inconnu'
  }
  
  return 'Animal inconnu'
}

const isToday = (date) => {
  if (!date) return false
  const today = new Date()
  const checkDate = new Date(date)
  return today.toDateString() === checkDate.toDateString()
}

const getPriorityClass = (priority) => {
  const classes = {
    high: 'bg-red-100 text-red-800',
    medium: 'bg-yellow-100 text-yellow-800',
    low: 'bg-green-100 text-green-800'
  }
  return classes[priority] || classes.medium
}

const getPriorityLabel = (priority) => {
  const labels = {
    high: 'Haute',
    medium: 'Moyenne',
    low: 'Basse'
  }
  return labels[priority] || 'Moyenne'
}

onMounted(async () => {
  try {
    if (authStore.isLoggedIn) {
      console.log('Chargement des données checklist...')
      await Promise.all([
        checklistStore.fetchChecklists(),
        petsStore.fetchPets()
      ])
      console.log('Données chargées:', {
        checklists: checklistStore.tasks?.length || 0,
        pets: petsStore.pets?.length || 0
      })
    } else {
      console.log('Utilisateur non connecté')
    }
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
})
</script> 