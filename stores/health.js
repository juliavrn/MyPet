import { defineStore } from 'pinia'

export const useHealthStore = defineStore('health', () => {
  const records = ref([])
  const loading = ref(false)

  const fetchRecords = async () => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.get('/api/health-records')
      records.value = response['hydra:member'] || response
    } catch (error) {
    } finally {
      loading.value = false
    }
  }

  const createRecord = async (recordData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/health-records', recordData)
      
      if (response.record && response.record.id) {
        await fetchRecords()
        return { success: true, record: response.record }
      } else if (response.id) {
        await fetchRecords()
        return { success: true, record: response }
      } else {
        return { success: false, error: 'Erreur création record' }
      }
    } catch (error) {
      return { success: false, error: 'Erreur création record' }
    } finally {
      loading.value = false
    }
  }

  const updateRecord = async (id, recordData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.put(`/api/health-records/${id}`, recordData)
      
      if (response.id) {
        await fetchRecords()
        return { success: true, record: response }
      } else {
        return { success: false, error: 'Erreur mise à jour record' }
      }
    } catch (error) {
      return { success: false, error: 'Erreur mise à jour record' }
    } finally {
      loading.value = false
    }
  }

  const deleteRecord = async (id) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      await $api.delete(`/api/health-records/${id}`)
      await fetchRecords()
      return { success: true }
    } catch (error) {
      return { success: false, error: 'Erreur suppression record' }
    } finally {
      loading.value = false
    }
  }

  return {
    records,
    loading,
    fetchRecords,
    createRecord,
    updateRecord,
    deleteRecord
  }
}) 