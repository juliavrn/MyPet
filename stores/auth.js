import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref('')
  const ready = ref(false)
  const loading = ref(false)
  const initialized = ref(false)

  const isLoggedIn = computed(() => !!token.value)
  
  const getUser = computed(() => user.value)
  
  const getUserRoles = computed(() => {
    if (!user.value || !user.value.roles || !Array.isArray(user.value.roles)) {
      return []
    }
    return user.value.roles
  })

  const hasRole = computed(() => (role) => {
    const roles = getUserRoles.value
    return Array.isArray(roles) && roles.includes(role)
  })

  const isAdmin = computed(() => hasRole.value('ROLE_ADMIN'))
  
  const canPublish = computed(() => hasRole.value('ROLE_BLOG_AUTHOR'))

  const initAuth = async () => {
    if (initialized.value || !process.client) return
    
    try {
      const savedToken = localStorage.getItem('mypet_token')
      
      if (savedToken) {
        token.value = savedToken
        await getUserInfo()
      }
    } catch (error) {
      // En cas d'erreur, nettoyer l'état mais ne pas rediriger automatiquement
      logout()
    } finally {
      ready.value = true
      initialized.value = true
    }
  }

  const login = async (credentials) => {
    try {
      loading.value = true
      
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/login', credentials)

      if (response.token) {
        token.value = response.token
        if (process.client) {
          localStorage.setItem('mypet_token', response.token)
        }
        await getUserInfo()
        return { success: true }
      } else {
        return { success: false, error: 'Erreur de connexion' }
      }
    } catch (error) {
      return { success: false, error: 'Erreur de connexion' }
    } finally {
      loading.value = false
    }
  }

  const getUserInfo = async () => {
    try {
      const { $api } = useNuxtApp()
      const response = await $api.get('/api/user')
      user.value = response
    } catch (error) {
      throw error
    }
  }

  const logout = () => {
    user.value = null
    token.value = ''
    if (process.client) {
      localStorage.removeItem('mypet_token')
    }
  }

  const register = async (userData) => {
    try {
      loading.value = true
      
      const { $api } = useNuxtApp()
      const response = await $api.post('/api/register', userData)

      // Vérifier si la réponse contient un message de succès ou un utilisateur créé
      if (response.message === 'User registered successfully' || response.user) {
        return { success: true, message: response.message }
      } else {
        return { success: false, error: response.message || 'Erreur inscription' }
      }
    } catch (error) {
      // Gérer les différents types d'erreurs
      if (error.status === 409) {
        return { success: false, error: 'Un compte avec cet email existe déjà' }
      } else if (error.status === 400) {
        // Erreur de validation
        if (error.data && error.data.message) {
          return { success: false, error: error.data.message }
        }
        return { success: false, error: 'Données invalides. Vérifiez vos informations.' }
      } else if (error.status === 500) {
        // Erreur serveur
        if (error.data && error.data.message) {
          return { success: false, error: error.data.message }
        }
        return { success: false, error: 'Erreur serveur. Veuillez réessayer plus tard.' }
      } else {
        // Erreur générique
        if (error.data && error.data.message) {
          return { success: false, error: error.data.message }
        }
        return { success: false, error: 'Erreur lors de l\'inscription. Veuillez réessayer.' }
      }
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    ready,
    loading,
    initialized,
    isLoggedIn,
    getUser,
    getUserRoles,
    initAuth,
    login,
    logout,
    register
  }
}) 