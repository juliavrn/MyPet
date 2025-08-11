export default defineNuxtPlugin(async () => {
  if (process.client) {
    try {
      await nextTick()
      
      const auth = useAuthStore()
      
      if (!auth.ready) {
        await auth.initAuth()
      }
    } catch (error) {
      
    }
  }
}) 