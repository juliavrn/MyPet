export default defineNuxtRouteMiddleware(async (to, from) => {
  // Si on est côté serveur, on laisse passer
  if (process.server) {
    return
  }
  
  // Si on est côté client
  if (process.client) {
    try {
      const authStore = useAuthStore()
      
      // Routes protégées qui nécessitent une authentification
      const protectedRoutes = ['/pets', '/calendar', '/health', '/checklist', '/admin', '/blog']
      
      if (protectedRoutes.some(route => to.path.startsWith(route))) {
        // Attendre que l'authentification soit initialisée
        if (!authStore.initialized) {
          // Initialiser l'authentification si ce n'est pas déjà fait
          await authStore.initAuth()
        }
        
        // Vérifier si l'utilisateur est connecté après initialisation
        if (!authStore.isLoggedIn) {
          return navigateTo('/login')
        }
      }
    } catch (error) {
      
      // En cas d'erreur, rediriger vers la page de connexion
      return navigateTo('/login')
    }
  }
}) 