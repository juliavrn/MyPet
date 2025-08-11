import { defineStore } from 'pinia'

export const usePetsStore = defineStore('pets', () => {
  const pets = ref([])
  const loading = ref(false)

  const fetchPets = async () => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.get('/api/pets')
      pets.value = response['hydra:member'] || response
    } catch (error) {
      
    } finally {
      loading.value = false
    }
  }

  const createPet = async (petData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/pets', petData)
      
      if (response.pet && response.pet.id) {
        await fetchPets()
        return { success: true, pet: response.pet }
      } else if (response.id) {
        await fetchPets()
        return { success: true, pet: response }
      } else {
        return { success: false, error: 'Erreur création pet' }
      }
    } catch (error) {
      
      return { success: false, error: 'Erreur création pet' }
    } finally {
      loading.value = false
    }
  }

  const updatePet = async (id, petData) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      const response = await $api.put(`/api/pets/${id}`, petData)
      
      if (response.pet && response.pet.id) {
        await fetchPets()
        return { success: true, pet: response.pet }
      } else if (response.id) {
        await fetchPets()
        return { success: true, pet: response }
      } else {
        return { success: false, error: 'Erreur mise à jour pet' }
      }
    } catch (error) {
      
      return { success: false, error: 'Erreur mise à jour pet' }
    } finally {
      loading.value = false
    }
  }

  const deletePet = async (id) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      await $api.delete(`/api/pets/${id}`)
      await fetchPets()
      return { success: true }
    } catch (error) {
      
      return { success: false, error: 'Erreur suppression pet' }
    } finally {
      loading.value = false
    }
  }

  const fetchPet = async (id) => {
    try {
      loading.value = true
      const { $api } = useNuxtApp()
      
      const response = await $api.get(`/api/pets/${id}`)
      
      return { success: true, data: response }
    } catch (error) {
      
      return { success: false, error: 'Erreur lors de la récupération de l\'animal' }
    } finally {
      loading.value = false
    }
  }

  return {
    pets,
    loading,
    fetchPets,
    fetchPet,
    createPet,
    updatePet,
    deletePet
  }
}) 