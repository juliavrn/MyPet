import { defineStore } from 'pinia'

export const useCalendarStore = defineStore('calendar', () => {
  const events = ref([])
  const loading = ref(false)
  const error = ref('')

  const fetchEvents = async (params = {}) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      
      let url = '/api/calendar'
      if (Object.keys(params).length > 0) {
        const queryParams = new URLSearchParams()
        if (params.start) queryParams.append('start', params.start)
        if (params.end) queryParams.append('end', params.end)
        if (params.pet) queryParams.append('pet', params.pet)
        if (params.type) queryParams.append('type', params.type)
        url += `?${queryParams.toString()}`
      }
      
      const response = await $api.get(url)
      
      events.value = response['hydra:member'] || response || []
    } catch (error) {
      error.value = 'Erreur lors de la récupération des événements'
      events.value = []
    } finally {
      loading.value = false
    }
  }

  const createEvent = async (eventData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/calendar', eventData)
      
      if (response.id) {
        await fetchEvents()
        return { success: true, event: response }
      } else {
        return { success: false, error: 'Erreur création event' }
      }
    } catch (error) {
      return { success: false, error: 'Erreur création event' }
    } finally {
      loading.value = false
    }
  }

  const updateEvent = async (id, eventData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.put(`/api/calendar/${id}`, eventData)
      
      if (response.id) {
        await fetchEvents()
        return { success: true, event: response }
      } else {
        return { success: false, error: 'Erreur mise à jour event' }
      }
    } catch (error) {
      return { success: false, error: 'Erreur mise à jour event' }
    } finally {
      loading.value = false
    }
  }

  const deleteEvent = async (id) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      await $api.delete(`/api/calendar/${id}`)
      await fetchEvents()
      return { success: true }
    } catch (error) {
      return { success: false, error: 'Erreur suppression event' }
    } finally {
      loading.value = false
    }
  }

  const getEventsForDate = (date) => {
    const targetDate = new Date(date)
    targetDate.setHours(0, 0, 0, 0)
    
    return events.value.filter(event => {
      if (!event.startDate) return false
      
      const eventDate = new Date(event.startDate)
      if (isNaN(eventDate.getTime())) return false
      
      eventDate.setHours(0, 0, 0, 0)
      return eventDate.getTime() === targetDate.getTime()
    })
  }

  return {
    events,
    loading,
    error,
    fetchEvents,
    createEvent,
    updateEvent,
    deleteEvent,
    getEventsForDate
  }
}) 