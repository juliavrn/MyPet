<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Modifier l'article</h1>
            <p class="mt-2 text-gray-600">Modifiez et mettez à jour votre article</p>
          </div>
          <NuxtLink to="/blog" class="btn-secondary">
            <Icon name="ph:arrow-left" class="h-5 w-5 mr-2" />
            Retour au blog
          </NuxtLink>
        </div>
      </div>

      <!-- Chargement -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Erreur -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <Icon name="ph:warning-circle" class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Formulaire -->
      <div v-else class="card">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Titre -->
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

          <!-- Image -->
          <div>
            <label class="form-label">Image de l'article</label>
            <ImageUpload v-model="form.image" :alt="form.title || 'Article'" />
            <p class="mt-1 text-sm text-gray-500">
              Ajoutez une image pour illustrer votre article (optionnel)
            </p>
          </div>

          <!-- Contenu -->
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

          <!-- Statut de publication -->
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
                <span class="text-sm text-gray-700">Publié</span>
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Choisissez si vous voulez publier l'article ou le garder comme brouillon
            </p>
          </div>

          <!-- Message d'erreur -->
          <div v-if="submitError" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:warning-circle" class="h-5 w-5 text-red-400" />
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ submitError }}</p>
              </div>
            </div>
          </div>

          <!-- Message de succès -->
          <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
              <Icon name="ph:check-circle" class="h-5 w-5 text-green-400" />
              <div class="ml-3">
                <p class="text-sm text-green-800">{{ success }}</p>
              </div>
            </div>
          </div>

          <!-- Boutons -->
          <div class="flex justify-end space-x-4">
            <NuxtLink to="/blog" class="btn-secondary">
              Annuler
            </NuxtLink>
            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary"
            >
              <span v-if="submitting" class="flex items-center">
                <Icon name="ph:spinner" class="animate-spin h-5 w-5 mr-2" />
                {{ form.isPublished ? 'Publication...' : 'Sauvegarde...' }}
              </span>
              <span v-else>{{ form.isPublished ? 'Publier les modifications' : 'Sauvegarder les modifications' }}</span>
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
const route = useRoute()

if (!authStore.isLoggedIn) {
  navigateTo('/login')
}

// Vérifier si l'utilisateur a les droits pour éditer des articles
if (!authStore.getUser?.isAdmin && !authStore.getUser?.canPublish) {
  navigateTo('/blog')
}

const postId = route.params.id
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const submitError = ref('')
const success = ref('')

const form = ref({
  title: '',
  content: '',
  image: '',
  isPublished: false
})

// Charger l'article
const loadPost = async () => {
  try {
    loading.value = true
    const response = await $api.get(`/blog_posts/${postId}`)
    const post = response.data
    
    // Vérifier que l'utilisateur peut éditer cet article
    if (!authStore.getUser?.isAdmin && post.author?.id !== authStore.getUser?.id) {
      error.value = 'Vous n\'avez pas les droits pour éditer cet article'
      return
    }
    
    form.value = {
      title: post.title || '',
      content: post.content || '',
      image: post.image || '',
      isPublished: post.isPublished || false
    }
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    error.value = 'Impossible de charger l\'article'
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  submitError.value = ''
  success.value = ''
  
  // Validation
  if (!form.value.title || !form.value.content) {
    submitError.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }
  
  if (form.value.title.length < 5) {
    submitError.value = 'Le titre doit contenir au moins 5 caractères'
    return
  }
  
  if (form.value.content.length < 50) {
    submitError.value = 'Le contenu doit contenir au moins 50 caractères'
    return
  }
  
  submitting.value = true
  
  try {
    const response = await $api.put(`/blog_posts/${postId}`, {
      title: form.value.title,
      content: form.value.content,
      image: form.value.image || null,
      isPublished: form.value.isPublished
    })
    
    success.value = form.value.isPublished 
      ? 'Article publié avec succès !' 
      : 'Article sauvegardé avec succès !'
    
    // Rediriger vers le blog après 2 secondes
    setTimeout(() => {
      navigateTo('/blog')
    }, 2000)
    
  } catch (err) {
    console.error('Erreur lors de la modification:', err)
    submitError.value = err.response?.data?.message || 'Une erreur est survenue. Veuillez réessayer.'
  } finally {
    submitting.value = false
  }
}

// Chargement initial
onMounted(() => {
  loadPost()
})
</script> 