<template>
  <div class="min-h-screen bg-gray-50">
    <NotificationContainer />
    
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <NuxtLink to="/" class="flex items-center space-x-2">
                <span class="text-2xl text-blue-600">🐾</span>
                <span class="text-xl font-bold text-gray-900">MyPet</span>
              </NuxtLink>
            </div>
            
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <NuxtLink 
                to="/" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Accueil
              </NuxtLink>
              <NuxtLink 
                to="/pets" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Mes Animaux
              </NuxtLink>
              <NuxtLink 
                to="/calendar" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Calendrier
              </NuxtLink>
              <NuxtLink 
                to="/health" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Santé
              </NuxtLink>
              <NuxtLink 
                to="/checklist" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Tâches
              </NuxtLink>
              <NuxtLink 
                to="/blog" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Blog
              </NuxtLink>
              <NuxtLink 
                v-if="authStore.getUser?.isAdmin"
                to="/admin" 
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                active-class="border-blue-500 text-gray-900"
              >
                Administration
              </NuxtLink>

            </div>
          </div>

          <div class="hidden sm:ml-6 sm:flex sm:items-center">
            <div v-if="authStore.isLoggedIn" class="flex items-center space-x-4">
              <span class="text-sm text-gray-700">
                Bonjour, {{ authStore.getUser?.firstName }}
              </span>
              <NuxtLink to="/profile" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200">
                Mon Profil
              </NuxtLink>
              <button 
                @click="authStore.logout()"
                class="btn-secondary text-sm"
              >
                Déconnexion
              </button>
            </div>
            <div v-else class="flex items-center space-x-4">
              <NuxtLink to="/login" class="btn-secondary text-sm">
                Connexion
              </NuxtLink>
              <NuxtLink to="/register" class="btn-primary text-sm">
                Inscription
              </NuxtLink>
            </div>
          </div>

          <div class="flex items-center sm:hidden">
            <button 
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
            >
              <span class="text-xl">
                {{ mobileMenuOpen ? '❌' : '☰' }}
              </span>
            </button>
          </div>
        </div>
      </div>

      <div v-show="mobileMenuOpen" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <NuxtLink 
            to="/" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Accueil
          </NuxtLink>
          <NuxtLink 
            to="/pets" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Mes Animaux
          </NuxtLink>
          <NuxtLink 
            to="/calendar" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Calendrier
          </NuxtLink>
          <NuxtLink 
            to="/health" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Santé
          </NuxtLink>
          <NuxtLink 
            to="/checklist" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Tâches
          </NuxtLink>
          <NuxtLink 
            to="/blog" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Blog
          </NuxtLink>
          <NuxtLink 
            v-if="authStore.getUser?.isAdmin"
            to="/admin" 
            class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
            active-class="bg-blue-50 border-blue-500 text-blue-700"
            @click="mobileMenuOpen = false"
          >
            Administration
          </NuxtLink>

        </div>
        
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div v-if="authStore.isLoggedIn" class="flex items-center px-4">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                <span class="text-white font-medium">
                  {{ authStore.getUser?.firstName?.charAt(0) }}
                </span>
              </div>
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">
                {{ authStore.getUser?.firstName }} {{ authStore.getUser?.lastName }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ authStore.getUser?.email }}
              </div>
            </div>
          </div>
          <div class="mt-3 space-y-1">
            <NuxtLink 
              to="/profile" 
              class="block w-full text-left px-4 py-2 text-base font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50"
              @click="mobileMenuOpen = false"
            >
              Mon Profil
            </NuxtLink>
            <button 
              @click="authStore.logout(); mobileMenuOpen = false"
              class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
            >
              Déconnexion
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <slot />
      </div>
        </main>
    
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
          © 2025 MyPet. Tous droits réservés.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import NotificationContainer from '~/components/NotificationContainer.vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

// L'initialisation de l'authentification est gérée par le plugin auth.client.js
// Pas besoin de l'initialiser ici pour éviter les conflits
</script> 