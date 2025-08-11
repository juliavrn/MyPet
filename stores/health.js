import { defineStore } from 'pinia'

export const useHealthStore = defineStore('health', () => {
  const records = ref([])
  const loading = ref(false)

  const fetchRecords = async () => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      console.log('Fetching health records...')
      const response = await $api.get('/api/health-records')
      console.log('Health records response:', response)
      
      // Gérer les différents formats de réponse
      if (response && response['hydra:member']) {
        records.value = response['hydra:member']
      } else if (Array.isArray(response)) {
        records.value = response
      } else {
        records.value = []
        console.warn('Unexpected response format:', response)
      }
      
      console.log('Records loaded:', records.value.length)
    } catch (error) {
      console.error('Error fetching health records:', error)
      records.value = []
    } finally {
      loading.value = false
    }
  }

  const createRecord = async (recordData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      
      console.log('Creating health record with data:', recordData)
      
      // Nettoyer les données avant envoi
      const cleanData = { ...recordData }
      
      // Convertir les valeurs vides en null pour les champs texte
      Object.keys(cleanData).forEach(key => {
        if (typeof cleanData[key] === 'string' && cleanData[key].trim() === '') {
          cleanData[key] = null
        }
        // Convertir les chaînes vides en 0 pour walkingTime
        if (key === 'walkingTime' && cleanData[key] === '') {
          cleanData[key] = null
        }
      })
      
      console.log('Cleaned data for API:', cleanData)
      
      const response = await $api.post('/api/health-records', cleanData)
      console.log('Create response:', response)
      
      if (response.record && response.record.id) {
        await fetchRecords()
        return { success: true, record: response.record }
      } else if (response.id) {
        await fetchRecords()
        return { success: true, record: response }
      } else {
        console.error('Unexpected response format:', response)
        return { success: false, error: 'Format de réponse inattendu' }
      }
    } catch (error) {
      console.error('Error creating health record:', error)
      
      // Extraire le message d'erreur
      let errorMessage = 'Erreur lors de la création'
      if (error.response) {
        if (error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.response.status === 401) {
          errorMessage = 'Non autorisé - Veuillez vous reconnecter'
        } else if (error.response.status === 403) {
          errorMessage = 'Accès refusé'
        } else if (error.response.status === 422) {
          errorMessage = 'Données invalides'
        } else if (error.response.status >= 500) {
          errorMessage = 'Erreur serveur'
        }
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }

  const updateRecord = async (id, recordData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      
      console.log('Updating health record', id, 'with data:', recordData)
      
      // Nettoyer les données avant envoi
      const cleanData = { ...recordData }
      
      // Convertir les valeurs vides en null pour les champs texte
      Object.keys(cleanData).forEach(key => {
        if (typeof cleanData[key] === 'string' && cleanData[key].trim() === '') {
          cleanData[key] = null
        }
        // Convertir les chaînes vides en 0 pour walkingTime
        if (key === 'walkingTime' && cleanData[key] === '') {
          cleanData[key] = null
        }
      })
      
      const response = await $api.put(`/api/health-records/${id}`, cleanData)
      console.log('Update response:', response)
      
      if (response.id) {
        await fetchRecords()
        return { success: true, record: response }
      } else {
        console.error('Unexpected update response format:', response)
        return { success: false, error: 'Format de réponse inattendu' }
      }
    } catch (error) {
      console.error('Error updating health record:', error)
      
      // Extraire le message d'erreur
      let errorMessage = 'Erreur lors de la mise à jour'
      if (error.response) {
        if (error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.response.status === 401) {
          errorMessage = 'Non autorisé - Veuillez vous reconnecter'
        } else if (error.response.status === 403) {
          errorMessage = 'Accès refusé'
        } else if (error.response.status === 404) {
          errorMessage = 'Enregistrement non trouvé'
        } else if (error.response.status === 422) {
          errorMessage = 'Données invalides'
        } else if (error.response.status >= 500) {
          errorMessage = 'Erreur serveur'
        }
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }

  const deleteRecord = async (id) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      
      console.log('Deleting health record:', id)
      
      await $api.delete(`/api/health-records/${id}`)
      await fetchRecords()
      return { success: true }
    } catch (error) {
      console.error('Error deleting health record:', error)
      
      // Extraire le message d'erreur
      let errorMessage = 'Erreur lors de la suppression'
      if (error.response) {
        if (error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message
        } else if (error.response.status === 401) {
          errorMessage = 'Non autorisé - Veuillez vous reconnecter'
        } else if (error.response.status === 403) {
          errorMessage = 'Accès refusé'
        } else if (error.response.status === 404) {
          errorMessage = 'Enregistrement non trouvé'
        } else if (error.response.status >= 500) {
          errorMessage = 'Erreur serveur'
        }
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, error: errorMessage }
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