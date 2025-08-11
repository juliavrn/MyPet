import { defineStore } from 'pinia'

export const useChecklistStore = defineStore('checklist', () => {
  const tasks = ref([])
  const loading = ref(false)
  const error = ref('')

  const fetchChecklists = async () => {
    try {
      loading.value = true
      error.value = ''
      const { $api } = useNuxtApp()
      const response = await $api.get('/api/checklist')
      tasks.value = response['hydra:member'] || response || []
    } catch (error) {
      
      error.value = 'Erreur lors de la récupération des tâches'
      tasks.value = []
    } finally {
      loading.value = false
    }
  }

  const createChecklist = async (checklistData) => {
    try {
      loading.value = true
      error.value = ''
      
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/checklist', checklistData)
      
      if (response && response.id) {
        await fetchChecklists()
        return { success: true, checklist: response }
      } else {
        const errorMsg = response?.message || 'Erreur création checklist'
        return { success: false, error: errorMsg }
      }
    } catch (error) {
      
      const errorMsg = error.data?.message || error.message || 'Erreur création checklist'
      return { success: false, error: errorMsg }
    } finally {
      loading.value = false
    }
  }

  const updateChecklist = async (id, checklistData) => {
    try {
      loading.value = true
      error.value = ''
      const { $api } = useNuxtApp()
      const response = await $api.put(`/api/checklist/${id}`, checklistData)
      
      if (response.id) {
        await fetchChecklists()
        return { success: true, checklist: response }
      } else {
        return { success: false, error: 'Erreur mise à jour checklist' }
      }
    } catch (error) {
      
      return { success: false, error: 'Erreur mise à jour checklist' }
    } finally {
      loading.value = false
    }
  }

  const deleteChecklist = async (id) => {
    try {
      loading.value = true
      error.value = ''
      const { $api } = useNuxtApp()
      await $api.delete(`/api/checklist/${id}`)
      await fetchChecklists()
      return { success: true }
    } catch (error) {
      
      return { success: false, error: 'Erreur suppression checklist' }
    } finally {
      loading.value = false
    }
  }

  const toggleChecklistCompletion = async (id) => {
    try {
      loading.value = true
      error.value = ''
      const { $api } = useNuxtApp()
      const response = await $api.patch(`/api/checklist/${id}/toggle`)
      
      if (response.id) {
        await fetchChecklists()
        return { success: true, checklist: response }
      } else {
        return { success: false, error: 'Erreur toggle checklist' }
      }
    } catch (error) {
      
      return { success: false, error: 'Erreur toggle checklist' }
    } finally {
      loading.value = false
    }
  }

  // Computed properties
  const getTodayChecklists = computed(() => {
    if (!tasks.value || !Array.isArray(tasks.value)) return []
    const today = new Date().toISOString().split('T')[0]
    return tasks.value.filter(task => {
      if (!task.dueDate) return false
      const taskDate = new Date(task.dueDate).toISOString().split('T')[0]
      return taskDate === today
    })
  })

  const getOverdueChecklists = computed(() => {
    if (!tasks.value || !Array.isArray(tasks.value)) return []
    const today = new Date().toISOString().split('T')[0]
    return tasks.value.filter(task => {
      if (!task.dueDate || task.isCompleted) return false
      return task.dueDate < today
    })
  })

  const clearError = () => {
    error.value = ''
  }

  return {
    tasks,
    loading,
    error,
    fetchChecklists,
    createChecklist,
    updateChecklist,
    deleteChecklist,
    toggleChecklistCompletion,
    getTodayChecklists,
    getOverdueChecklists,
    clearError
  }
}) 