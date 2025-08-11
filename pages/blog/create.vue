<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Créer un article</h1>
            <p class="mt-2 text-gray-600">Rédigez et publiez un nouvel article pour le blog</p>
          </div>
          <NuxtLink to="/blog" class="btn-secondary">
            <Icon name="ph:arrow-left" class="h-5 w-5 mr-2" />
            Retour au blog
          </NuxtLink>
        </div>
      </div>

      <div class="card">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="title" class="form-label">Titre de l'article *</label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="input-field"
              placeholder="Ex: Comment prendre soin de votre chien en été"
            />
          </div>

          <div>
            <label class="form-label">Image de l'article</label>
            <ImageUpload v-model="form.image" :alt="form.title || 'Article'" />
            <p class="mt-1 text-sm text-gray-500">
              Ajoutez une image pour illustrer votre article (optionnel)
            </p>
          </div>

          <div>
            <label for="content" class="form-label">Contenu de l'article *</label>
            <textarea
              id="content"
              v-model="form.content"
              rows="12"
              required
              class="input-field"
              placeholder="Rédigez le contenu de votre article ici..."
            ></textarea>
            <p class="mt-1 text-sm text-gray-500">
              Utilisez des paragraphes pour structurer votre contenu. Vous pouvez utiliser des listes à puces avec des tirets (-).
            </p>
          </div>

          <div>
            <label class="form-label">Statut de publication</label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input
                  v-model="form.isPublished"
                  type="radio"
                  :value="false"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">Brouillon (non publié)</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.isPublished"
                  type="radio"
                  :value="true"
                  class="mr-2"
                />
                <span class="text-sm text-gray-700">Publié immédiatement</span>
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Choisissez si vous voulez publier l'article immédiatement ou le sauvegarder comme brouillon
            </p>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:warning-circle" class="h-5 w-5 text-red-400" />
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:check-circle" class="h-5 w-5 text-green-400" />
              <div class="ml-3">
                <p class="text-sm text-green-800">{{ success }}</p>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-4">
            <NuxtLink to="/blog" class="btn-secondary">
              Annuler
            </NuxtLink>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary"
            >
              <span v-if="loading" class="flex items-center">
                <Icon name="ph:spinner" class="animate-spin h-5 w-5 mr-2" />
                {{ form.isPublished ? 'Publication...' : 'Sauvegarde...' }}
              </span>
              <span v-else>{{ form.isPublished ? 'Publier l\'article' : 'Sauvegarder le brouillon' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// Rediriger si pas connecté
const authStore = useAuthStore()
const { $api } = useNuxtApp()

if (!authStore.isLoggedIn) {
  navigateTo('/login')
}

// Vérifier si l'utilisateur a les droits pour créer des articles
// Les administrateurs peuvent toujours créer des articles
if (!authStore.getUser?.isAdmin && !authStore.getUser?.canPublish) {
  navigateTo('/blog')
}

const form = ref({
  title: '',
  content: '',
  image: '',
  isPublished: false
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  
  // Validation
  if (!form.value.title || !form.value.content) {
    error.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }
  
  if (form.value.title.length < 5) {
    error.value = 'Le titre doit contenir au moins 5 caractères'
    return
  }
  
  if (form.value.content.length < 50) {
    error.value = 'Le contenu doit contenir au moins 50 caractères'
    return
  }
  
  loading.value = true
  
  try {
    const response = await $api.post('/blog_posts', {
      title: form.value.title,
      content: form.value.content,
      image: form.value.image || null,
      isPublished: form.value.isPublished,
      author: `/api/users/${authStore.getUser.id}`
    })
    
    success.value = form.value.isPublished 
      ? 'Article publié avec succès !' 
      : 'Brouillon sauvegardé avec succès !'
    
    // Vider le formulaire
    form.value = {
      title: '',
      content: '',
      image: '',
      isPublished: false
    }
    
    // Rediriger vers le blog après 2 secondes
    setTimeout(() => {
      navigateTo('/blog')
    }, 2000)
    
  } catch (err) {
    
    error.value = err.response?.data?.message || 'Une erreur est survenue. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script> 