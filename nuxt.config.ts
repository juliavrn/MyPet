export default defineNuxtConfig({
  devtools: { enabled: false },
  
  // Ajout de la date de compatibilité recommandée
  compatibilityDate: '2025-08-11',
  
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/icon'
  ],

  css: [
    '~/assets/css/main.css'
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE || 'http://127.0.0.1:8000'
    }
  },

  app: {
    head: {
      title: 'MyPet - Gestion d\'animaux de compagnie',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Application de gestion d\'animaux de compagnie' }
      ]
    }
  },

  vite: {
    server: {
      fs: {
        strict: false
      }
    }
  }

  // Suppression de la configuration Nitro problématique
})
