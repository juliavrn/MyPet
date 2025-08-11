<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Suivi de Santé</h1>
            <p class="mt-1 text-sm text-gray-500">Surveillez la santé et le bien-être de vos animaux</p>
          </div>
          <button
            @click="showAddModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <HeroIcon name="plus" size="sm" class="mr-2" />
            Nouveau Suivi
          </button>
        </div>
      </div>
    </div>

    <div v-if="successMessage" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <HeroIcon name="check-circle" class="h-5 w-5 text-green-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
          </div>
          <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
              <button
                @click="successMessage = ''"
                class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600"
              >
                <span class="sr-only">Fermer</span>
                <HeroIcon name="x-mark" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="errorMessage" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <HeroIcon name="x-circle" class="h-5 w-5 text-red-400" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
          </div>
          <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
              <button
                @click="errorMessage = ''"
                class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"
              >
                <span class="sr-only">Fermer</span>
                <HeroIcon name="x-mark" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Animal</label>
            <select
              v-model="selectedPetId"
              @change="filterRecords"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Tous les animaux</option>
              <option v-for="pet in petsStore.pets" :key="pet.id" :value="pet.id">
                {{ pet.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
            <select
              v-model="selectedPeriod"
              @change="filterRecords"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="all">Toutes les dates</option>
              <option value="today">Aujourd'hui</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select
              v-model="selectedStatus"
              @change="filterRecords"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Tous les statuts</option>
              <option value="health_issues">Problèmes de santé</option>
              <option value="recent">Récents</option>
            </select>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <HeroIcon name="heart" class="w-8 h-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Enregistrements</p>
              <p class="text-2xl font-semibold text-gray-900">{{ filteredRecords.length }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <HeroIcon name="exclamation-triangle" class="w-8 h-8 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Problèmes</p>
              <p class="text-2xl font-semibold text-gray-900">{{ healthIssuesCount }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <HeroIcon name="calendar" class="w-8 h-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Cette semaine</p>
              <p class="text-2xl font-semibold text-gray-900">{{ weeklyRecordsCount }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <HeroIcon name="check-circle" class="w-8 h-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Aujourd'hui</p>
              <p class="text-2xl font-semibold text-gray-900">{{ todayRecordsCount }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Enregistrements de santé</h3>
        </div>
        
        <div v-if="healthStore.isLoading" class="p-8 text-center">
          <HeroIcon name="arrow-path" class="w-8 h-8 text-gray-400 animate-spin mx-auto" />
          <p class="mt-2 text-gray-500">Chargement...</p>
        </div>

        <div v-else-if="filteredRecords.length === 0" class="p-8 text-center">
          <HeroIcon name="document-text" class="w-12 h-12 text-gray-400 mx-auto" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun enregistrement</h3>
          <p class="mt-1 text-sm text-gray-500">
            Commencez par créer votre premier suivi de santé.
          </p>
          <div class="mt-6">
            <button
              @click="showAddModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              <HeroIcon name="plus" class="w-4 h-4 mr-2" />
              Nouveau Suivi
            </button>
          </div>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div v-for="petGroup in groupedRecords" :key="petGroup.petId" class="border-b border-gray-200">
            <div class="bg-gray-50 px-6 py-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                    <img 
                      v-if="getPetById(petGroup.petId)?.photo" 
                      :src="getPetById(petGroup.petId).photo" 
                      :alt="getPetNameById(petGroup.petId)"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full bg-indigo-100 flex items-center justify-center">
                      <HeroIcon name="user" class="w-5 h-5 text-indigo-600" />
                    </div>
                  </div>
                  <div>
                    <h4 class="text-lg font-medium text-gray-900">
                      {{ getPetNameById(petGroup.petId) }}
                    </h4>
                    <span class="text-sm text-gray-500">({{ petGroup.records.length }} enregistrement{{ petGroup.records.length > 1 ? 's' : '' }})</span>
                  </div>
                </div>
                <button
                  @click="togglePetGroup(petGroup.petId)"
                  class="text-gray-500 hover:text-gray-700 transition-colors"
                >
                  <HeroIcon
                    :name="expandedPets.includes(petGroup.petId) ? 'chevron-up' : 'chevron-down'"
                    size="sm"
                  />
                </button>
              </div>
            </div>
            
            <div v-show="expandedPets.includes(petGroup.petId)" class="divide-y divide-gray-100">
              <div
                v-for="record in petGroup.records"
                :key="record.id"
                class="px-6 py-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <HeroIcon name="heart" class="w-4 h-4 text-green-600" />
                      </div>
                    </div>
                    <div>
                      <h5 class="text-sm font-medium text-gray-900">
                        {{ formatDate(record.date) }}
                      </h5>
                      <p class="text-sm text-gray-500">
                        {{ getHealthSummary(record) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button
                      @click="viewRecord(record)"
                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    >
                      Voir
                    </button>
                    <button
                      @click="editRecord(record)"
                      class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                    >
                      Modifier
                    </button>
                    <button
                      @click="deleteRecord(record.id)"
                      class="text-red-600 hover:text-red-900 text-sm font-medium"
                    >
                      Supprimer
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <HealthRecordModal
      v-if="showAddModal || showEditModal"
      :record="editingRecord"
      :pets="petsStore.pets"
      @close="closeModal"
      @save="saveRecord"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useHealthStore } from '~/stores/health'
import { usePetsStore } from '~/stores/pets'
import { useAuthStore } from '~/stores/auth'

// Stores
const healthStore = useHealthStore()
const petsStore = usePetsStore()
const authStore = useAuthStore()

// État local
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingRecord = ref(null)
const selectedPetId = ref('')
const selectedPeriod = ref('all')
const selectedStatus = ref('')
const successMessage = ref('')
const errorMessage = ref('')
const expandedPets = ref([])

// Computed properties
const filteredRecords = computed(() => {
  let records = healthStore.records || []

  // Filtre par animal
  if (selectedPetId.value) {
    records = records.filter(record => {
      const petId = getPetIdFromRecord(record)
      return petId === selectedPetId.value
    })
  }

  // Filtre par période
  if (selectedPeriod.value !== 'all') {
    const today = new Date()
    records = records.filter(record => {
      const recordDate = new Date(record.date)
      switch (selectedPeriod.value) {
        case 'today':
          return recordDate.toDateString() === today.toDateString()
        case 'week':
          const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
          return recordDate >= weekAgo
        case 'month':
          const monthAgo = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
          return recordDate >= monthAgo
        default:
          return true
      }
    })
  }

  // Filtre par statut
  if (selectedStatus.value === 'health_issues') {
    records = records.filter(record => 
      record.signsOfIllness || record.fever || record.vomiting || record.limping
    )
  } else if (selectedStatus.value === 'recent') {
    const weekAgo = new Date()
    weekAgo.setDate(weekAgo.getDate() - 7)
    records = records.filter(record => new Date(record.date) >= weekAgo)
  }

  return records.sort((a, b) => new Date(b.date) - new Date(a.date))
})

const groupedRecords = computed(() => {
  const groups = {}
  
  filteredRecords.value.forEach(record => {
    const petId = getPetIdFromRecord(record)
    if (!groups[petId]) {
      groups[petId] = {
        petId,
        records: []
      }
    }
    groups[petId].records.push(record)
  })
  
  // Trier les enregistrements par date dans chaque groupe
  Object.values(groups).forEach(group => {
    group.records.sort((a, b) => new Date(b.date) - new Date(a.date))
  })
  
  // Trier les groupes par nom d'animal
  return Object.values(groups).sort((a, b) => {
    const nameA = getPetNameById(a.petId)
    const nameB = getPetNameById(b.petId)
    return nameA.localeCompare(nameB)
  })
})

const healthIssuesCount = computed(() => {
  return (healthStore.records || []).filter(record => 
    record.signsOfIllness || record.fever || record.vomiting || record.limping
  ).length
})

const weeklyRecordsCount = computed(() => {
  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  return (healthStore.records || []).filter(record => 
    new Date(record.date) >= weekAgo
  ).length
})

const todayRecordsCount = computed(() => {
  const today = new Date()
  return (healthStore.records || []).filter(record => 
    new Date(record.date).toDateString() === today.toDateString()
  ).length
})

// Methods
const getPetName = (pet) => {
  if (typeof pet === 'string') {
    const petId = parseInt(pet.split('/').pop())
    const petObj = (petsStore.pets || []).find(p => p.id === petId)
    return petObj ? petObj.name : 'Animal inconnu'
  }
  return pet?.name || 'Animal inconnu'
}

const getPetNameById = (petId) => {
  const pet = (petsStore.pets || []).find(p => p.id === petId)
  return pet ? pet.name : 'Animal inconnu'
}

const getPetIdFromRecord = (record) => {
  if (record.pet && typeof record.pet === 'string') {
    return parseInt(record.pet.split('/').pop())
  }
  return record.petId || record.pet?.id
}

const getPetById = (petId) => {
  return (petsStore.pets || []).find(p => p.id === petId)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getHealthSummary = (record) => {
  const summary = []
  
  if (record.signsOfIllness) summary.push('Signes de maladie')
  if (record.fever) summary.push('Fièvre')
  if (record.vomiting) summary.push('Vomissements')
  if (record.limping) summary.push('Boitement')
  
  if (summary.length > 0) {
    return summary.join(', ')
  }
  
  return 'État normal'
}

const filterRecords = () => {
  // Les filtres sont appliqués automatiquement via computed
}

const togglePetGroup = (petId) => {
  const index = expandedPets.value.indexOf(petId)
  if (index > -1) {
    expandedPets.value.splice(index, 1)
  } else {
    expandedPets.value.push(petId)
  }
}

const viewRecord = (record) => {
  editingRecord.value = record
  showEditModal.value = true
}

const editRecord = (record) => {
  editingRecord.value = record
  showEditModal.value = true
}

const deleteRecord = async (recordId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')) {
    const result = await healthStore.deleteRecord(recordId)
    if (result.success) {
      successMessage.value = 'Enregistrement supprimé avec succès'
    } else {
      errorMessage.value = `Erreur: ${result.error}`
    }
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingRecord.value = null
}

const saveRecord = async (recordData) => {
  console.log('saveRecord called with:', recordData)
  
  let result
  try {
    if (editingRecord.value) {
      console.log('Updating existing record:', editingRecord.value.id)
      result = await healthStore.updateRecord(editingRecord.value.id, recordData)
    } else {
      console.log('Creating new record')
      result = await healthStore.createRecord(recordData)
    }

    console.log('Save operation result:', result)

    if (result.success) {
      successMessage.value = editingRecord.value ? 'Enregistrement mis à jour avec succès' : 'Enregistrement créé avec succès'
      closeModal()
      
      // Forcer le rafraîchissement des données pour s'assurer qu'elles apparaissent
      console.log('Refreshing health records...')
      await healthStore.fetchRecords()
      console.log('Health records refreshed, count:', healthStore.records.length)
    } else {
      console.error('Save operation failed:', result.error)
      errorMessage.value = `Erreur: ${result.error}`
    }
  } catch (error) {
    console.error('Unexpected error in saveRecord:', error)
    errorMessage.value = `Erreur inattendue: ${error.message || 'Erreur inconnue'}`
  }
}

// Lifecycle
onMounted(async () => {
  if (!authStore.isLoggedIn) {
    await navigateTo('/login')
    return
  }

  await Promise.all([
    healthStore.fetchRecords(),
    petsStore.fetchPets()
  ])
  
  // Initialiser tous les groupes d'animaux comme déroulés par défaut
  nextTick(() => {
    if (groupedRecords.value.length > 0) {
      expandedPets.value = groupedRecords.value.map(group => group.petId)
    }
  })
})
</script> 