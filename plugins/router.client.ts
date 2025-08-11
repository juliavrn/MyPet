export default defineNuxtPlugin(() => {
  if (process.client) {
    // Attendre que l'application soit complètement initialisée
    nextTick(() => {
      try {
        // Initialiser l'authentification au démarrage
        const authStore = useAuthStore()
        authStore.initAuth()
        
        
      } catch (error) {
        
      }
    })
  }
}) 