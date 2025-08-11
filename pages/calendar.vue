<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-6 lg:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          📅 Calendrier
        </h1>
        <p class="text-gray-600">
          Gérez les événements et rendez-vous de vos animaux de compagnie
        </p>
      </div>

      <div class="card mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="flex-1 min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Animal</label>
            <select v-model="selectedPet" class="input-field w-full">
              <option value="">Tous les animaux</option>
              <option v-for="pet in petsStore.pets" :key="pet.id" :value="pet.id">
                {{ pet.name }} ({{ pet.species }})
              </option>
            </select>
          </div>
          <div class="flex-1 min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="selectedType" class="input-field w-full">
              <option value="">Tous les types</option>
              <option value="appointment">Rendez-vous vétérinaire</option>
              <option value="medication">Médicament</option>
              <option value="food">Nourriture</option>
              <option value="activity">Activité</option>
              <option value="grooming">Toilettage</option>
              <option value="vaccination">Vaccination</option>
              <option value="other">Autre</option>
            </select>
          </div>
          <div class="flex-1 min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
            <select v-model="selectedPeriod" class="input-field w-full">
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
              <option value="all">Tout</option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="calendarStore.error" class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="flex">
          <HeroIcon name="warning-circle" size="sm" class="h-4 w-4 sm:h-5 sm:w-5 text-red-400 flex-shrink-0 mt-0.5" />
          <div class="ml-2 sm:ml-3">
            <p class="text-sm text-red-800">{{ calendarStore.error }}</p>
          </div>
        </div>
      </div>

      <div v-if="calendarStore.loading" class="flex justify-center py-8 sm:py-12">
        <div class="flex items-center space-x-2">
          <HeroIcon name="spinner" class="animate-spin h-5 w-5 sm:h-6 sm:w-6 text-blue-600" />
          <span class="text-sm sm:text-base text-gray-600">Chargement du calendrier...</span>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="lg:col-span-2">
          <div class="card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2 sm:gap-0">
              <h3 class="text-lg font-semibold text-gray-900">Vue mensuelle</h3>
              <div class="flex items-center space-x-2">
                <button @click="previousMonth" class="btn-secondary">
                  <HeroIcon name="caret-left" class="h-4 w-4" />
                </button>
                <span class="text-sm font-medium text-gray-700 px-2">{{ currentMonthYear }}</span>
                <button @click="nextMonth" class="btn-secondary">
                  <HeroIcon name="caret-right" class="h-4 w-4" />
                </button>
              </div>
            </div>
            
            <div class="grid grid-cols-7 gap-1">
              <div v-for="day in ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']" :key="day" 
                   class="p-2 text-center text-sm font-medium text-gray-500">
                {{ day }}
              </div>
              
              <div v-for="day in calendarDays" :key="day.date" 
                   :class="[
                     'p-2 min-h-[80px] border border-gray-200 cursor-pointer hover:bg-gray-50 relative',
                     day.isCurrentMonth ? 'bg-white' : 'bg-gray-100 text-gray-400',
                     day.isToday ? 'bg-blue-50 border-blue-300' : ''
                   ]"
                   @click="selectDate(day.date)">
                <div class="text-sm font-medium">{{ day.dayNumber }}</div>
                <div v-if="day.isToday" class="absolute top-1 right-1 w-3 h-3 bg-blue-500 rounded-full"></div>
                
                <div class="mt-1 space-y-1">
                  <div v-for="event in getEventsForDate(day.date)" :key="event.id"
                       :class="getEventColor(event.type)"
                       class="text-xs p-1 rounded truncate cursor-pointer"
                       @click.stop="viewEvent(event)">
                    {{ event.title }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Événements à venir</h3>
            
            <div v-if="upcomingEvents.length === 0" class="text-center py-8">
              <HeroIcon name="calendar" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600">Aucun événement à venir</p>
            </div>
            
            <div v-else class="space-y-3">
              <div v-for="event in upcomingEvents" :key="event.id"
                   class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                   @click="viewEvent(event)">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="font-medium text-gray-900">{{ event.title }}</h4>
                    <p class="text-sm text-gray-600">{{ event.pet.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(event.start) }}</p>
                  </div>
                  <div :class="getEventColor(event.type)" class="w-3 h-3 rounded-full"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter un événement</h3>
        
        <form @submit.prevent="handleAddEvent" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
            <input v-model="newEvent.title" type="text" required class="input-field" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Animal *</label>
            <select v-model="newEvent.petId" required class="input-field">
              <option value="">Sélectionner un animal</option>
              <option v-for="pet in petsStore.pets" :key="pet.id" :value="pet.id">
                {{ pet.name }} ({{ pet.species }})
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
            <select v-model="newEvent.type" required class="input-field">
              <option value="">Sélectionner un type</option>
              <option value="appointment">Rendez-vous vétérinaire</option>
              <option value="medication">Médicament</option>
              <option value="food">Nourriture</option>
              <option value="activity">Activité</option>
              <option value="grooming">Toilettage</option>
              <option value="vaccination">Vaccination</option>
              <option value="other">Autre</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date et heure *</label>
            <input v-model="newEvent.startDate" type="datetime-local" required class="input-field" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin (optionnel)</label>
            <input v-model="newEvent.endDate" type="datetime-local" class="input-field" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="newEvent.description" rows="3" class="input-field"></textarea>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" @click="showAddModal = false" class="btn-secondary">
              Annuler
            </button>
            <button type="submit" :disabled="calendarStore.loading" class="btn-primary">
              <span v-if="calendarStore.loading" class="flex items-center">
                <HeroIcon name="spinner" class="animate-spin h-4 w-4 mr-2" />
                Création...
              </span>
              <span v-else>Créer l'événement</span>
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
const calendarStore = useCalendarStore()
const notificationStore = useNotificationStore()

if (!authStore.isLoggedIn) {
  navigateTo('/login')
}

// État local
const showAddModal = ref(false)
const showEventModal = ref(false)
const selectedEvent = ref(null)
const selectedPet = ref('')
const selectedType = ref('')
const selectedPeriod = ref('week')
const currentDate = ref(new Date())

const newEvent = ref({
  title: '',
  petId: '',
  type: '',
  startDate: '',
  endDate: '',
  description: ''
})

// Charger les données au montage
onMounted(async () => {
  await petsStore.fetchPets()
  await loadEvents()
})

// Charger les événements
const loadEvents = async () => {
  const params = {}
  if (selectedPet.value) params.pet = selectedPet.value
  
  // Utiliser la date actuelle du calendrier au lieu de la date système
  const currentYear = currentDate.value.getFullYear()
  const currentMonth = currentDate.value.getMonth()
  
  if (selectedPeriod.value === 'week') {
    const start = new Date(currentDate.value)
    start.setDate(start.getDate() - start.getDay()) // Commencer au lundi
    const end = new Date(start)
    end.setDate(start.getDate() + 6) // Finir le dimanche
    
    // Utiliser toLocaleDateString pour éviter le décalage UTC
    params.start = start.toLocaleDateString('en-CA') + 'T00:00:00'
    params.end = end.toLocaleDateString('en-CA') + 'T23:59:59'
  } else if (selectedPeriod.value === 'month') {
    // Charger les événements pour le mois actuel du calendrier
    const start = new Date(currentYear, currentMonth, 1)
    const end = new Date(currentYear, currentMonth + 1, 0, 23, 59, 59)
    
    // Utiliser toLocaleDateString pour éviter le décalage UTC
    params.start = start.toLocaleDateString('en-CA') + 'T00:00:00'
    params.end = end.toLocaleDateString('en-CA') + 'T23:59:59'
  } else {
    // Pour "all", ne pas limiter par date
    // Charger tous les événements
  }
  
  await calendarStore.fetchEvents(params)
}

// Watchers pour les filtres
watch([selectedPet, selectedType, selectedPeriod], () => {
  loadEvents()
})

// Watcher pour le changement de mois
watch(currentDate, () => {
  loadEvents()
})

// Calculs pour le calendrier
const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('fr-FR', { 
    month: 'long', 
    year: 'numeric' 
  })
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay() + 1)
  
  const days = []
  const today = new Date()
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    // Utiliser toLocaleDateString pour éviter le décalage UTC
    const dateString = date.toLocaleDateString('en-CA') // Format YYYY-MM-DD en heure locale
    
    days.push({
      date: dateString,
      dayNumber: date.getDate(),
      isCurrentMonth: date.getMonth() === month,
      isToday: date.toDateString() === today.toDateString()
    })
  }
  
  return days
})

const upcomingEvents = computed(() => {
  const today = new Date()
  const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)
  
  return (calendarStore.events || []).filter(event => {
    const eventDate = new Date(event.startDate)
    return eventDate >= today && eventDate <= nextWeek
  }).sort((a, b) => new Date(a.startDate) - new Date(b.startDate))
})

// Méthodes
const getEventsForDate = (date) => {
  // La date est maintenant en format YYYY-MM-DD en heure locale
  // Pas besoin de conversion complexe
  return calendarStore.getEventsForDate(date)
}

const getEventColor = (type) => {
  const colors = {
    appointment: 'bg-red-100 text-red-800',
    medication: 'bg-blue-100 text-blue-800',
    food: 'bg-green-100 text-green-800',
    activity: 'bg-purple-100 text-purple-800',
    grooming: 'bg-yellow-100 text-yellow-800',
    vaccination: 'bg-orange-100 text-orange-800',
    other: 'bg-gray-100 text-gray-800'
  }
  return colors[type] || colors.other
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  // Vérifier si la date est valide
  if (isNaN(date.getTime())) {
    console.error('Date invalide:', dateString)
    return 'Date invalide'
  }
  
  // La date est maintenant stockée en local, pas besoin de conversion
  return date.toLocaleString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const previousMonth = async () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() - 1)
  currentDate.value = newDate
  await loadEvents()
}

const nextMonth = async () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() + 1)
  currentDate.value = newDate
  await loadEvents()
}

const selectDate = (date) => {
  // Ouvrir le modal avec la date présélectionnée
  // La date est maintenant en format YYYY-MM-DD en heure locale
  newEvent.value.startDate = date + 'T10:00'
  showAddModal.value = true
}

const viewEvent = (event) => {
  selectedEvent.value = event
  showEventModal.value = true
}

const handleAddEvent = async () => {
  if (!newEvent.value.title || !newEvent.value.startDate) {
    return
  }

  let formattedStartDate = newEvent.value.startDate
  let formattedEndDate = newEvent.value.endDate || null

  if (formattedStartDate) {
    try {
      const startDate = new Date(formattedStartDate)
      
              if (isNaN(startDate.getTime())) {
          notificationStore.error('Date de début invalide')
          return
        }

      // Utiliser toLocaleDateString et toLocaleTimeString pour éviter le décalage UTC
      const year = startDate.getFullYear()
      const month = String(startDate.getMonth() + 1).padStart(2, '0')
      const day = String(startDate.getDate()).padStart(2, '0')
      const hours = String(startDate.getHours()).padStart(2, '0')
      const minutes = String(startDate.getMinutes()).padStart(2, '0')
      formattedStartDate = `${year}-${month}-${day}T${hours}:${minutes}:00`
          } catch (error) {
        notificationStore.error('Erreur lors du formatage de la date de début')
        return
      }
  }

  if (formattedEndDate) {
    try {
      const endDate = new Date(formattedEndDate)
      const year = endDate.getFullYear()
      const month = String(endDate.getMonth() + 1).padStart(2, '0')
      const day = String(endDate.getDate()).padStart(2, '0')
      const hours = String(endDate.getHours()).padStart(2, '0')
      const minutes = String(endDate.getMinutes()).padStart(2, '0')
      formattedEndDate = `${year}-${month}-${day}T${hours}:${minutes}:00`
          } catch (error) {
        notificationStore.error('Erreur lors du formatage de la date de fin')
      }
  }

  const eventData = {
    title: newEvent.value.title,
    petId: newEvent.value.petId,
    type: newEvent.value.type,
    startDate: formattedStartDate,
    endDate: formattedEndDate,
    description: newEvent.value.description || null
  }

  const result = await calendarStore.createEvent(eventData)

  if (result.success) {
    notificationStore.success('Événement créé avec succès !')
    showAddModal.value = false
    
    resetNewEvent()
    await loadEvents()
  } else {
    notificationStore.error(`Erreur lors de la création : ${result.error}`)
  }
}

const resetNewEvent = () => {
  newEvent.value = {
    title: '',
    petId: '',
    type: '',
    startDate: '',
    endDate: '',
    description: ''
  }
}
</script> 