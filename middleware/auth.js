export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useAuthStore()
  
  if (!auth.ready) {
    auth.initAuth()
  }
  
  if (!auth.ready) {
    return
  }
  
  if (!auth.isLoggedIn) {
    
    return navigateTo('/login')
  }
  
  
}) 