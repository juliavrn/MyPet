<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Blog & Conseils</h1>
            <p class="text-lg text-gray-600">
              Découvrez nos conseils et articles pour prendre soin de vos animaux de compagnie
            </p>
          </div>
          <div v-if="authStore.getUser?.isAdmin || authStore.getUserRoles.includes('ROLE_BLOG_MODERATOR')">
            <NuxtLink 
              to="/blog/create" 
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
            >
              <span class="text-lg mr-2">➕</span>
              Créer un article
            </NuxtLink>
          </div>
        </div>
      </div>

      <div class="mb-8 flex flex-wrap gap-4">
        <button
          @click="selectedCategory = 'all'"
          :class="[
            selectedCategory === 'all'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
            'px-4 py-2 rounded-md text-sm font-medium border border-gray-300'
          ]"
        >
          Tous les articles
        </button>
        <button
          @click="selectedCategory = 'sante'"
          :class="[
            selectedCategory === 'sante'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
            'px-4 py-2 rounded-md text-sm font-medium border border-gray-300'
          ]"
        >
          Santé
        </button>
        <button
          @click="selectedCategory = 'alimentation'"
          :class="[
            selectedCategory === 'alimentation'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
            'px-4 py-2 rounded-md text-sm font-medium border border-gray-300'
          ]"
        >
          Alimentation
        </button>
        <button
          @click="selectedCategory = 'comportement'"
          :class="[
            selectedCategory === 'comportement'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
            'px-4 py-2 rounded-md text-sm font-medium border border-gray-300'
          ]"
        >
          Comportement
        </button>
      </div>

      <div class="mb-8">
        <div class="relative">
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Rechercher un article..."
            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-lg text-gray-400">🔍</span>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="filteredPosts.length === 0" class="text-center py-12">
        <span class="text-4xl text-gray-400">📰</span>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun article trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ searchTerm ? 'Aucun article ne correspond à votre recherche.' : 'Aucun article n\'est disponible pour le moment.' }}
        </p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <article 
          v-for="post in filteredPosts" 
          :key="post.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
        >
          <div class="aspect-w-16 aspect-h-9 bg-gray-200">
            <img 
              v-if="post.image"
              :src="post.image" 
              :alt="post.title"
              class="w-full h-48 object-cover"
            />
            <div v-else class="w-full h-48 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
              <span class="text-4xl text-gray-400">📰</span>
            </div>
          </div>

          <div class="p-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
              <span class="text-sm mr-1">👤</span>
              <span>{{ post.author?.firstName }} {{ post.author?.lastName }}</span>
              <span class="mx-2">•</span>
              <span class="text-sm mr-1">📅</span>
              <span>{{ formatDate(post.createdAt) }}</span>
            </div>

            <h3 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-3">
              {{ post.title }}
            </h3>

            <p class="text-gray-600 mb-4 line-clamp-4">
              {{ truncateContent(post.content) }}
            </p>

            <div class="flex items-center justify-between">
              <button
                @click="toggleLike(post)"
                class="flex items-center space-x-2 text-gray-500 hover:text-red-500 transition-colors"
                :class="{ 'text-red-500': post.isLiked }"
              >
                <span class="text-lg">{{ post.isLiked ? '❤️' : '🤍' }}</span>
                <span class="text-sm font-medium">{{ post.likes }}</span>
              </button>

              <button
                @click="openModal(post)"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium"
              >
                Lire la suite
                <span class="ml-1 text-sm">→</span>
              </button>
            </div>
          </div>
        </article>
      </div>

      <div v-if="totalPages > 1" class="mt-12 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            :class="[
              currentPage === 1
                ? 'text-gray-400 cursor-not-allowed'
                : 'text-gray-500 hover:text-gray-700',
              'px-3 py-2 rounded-md text-sm font-medium'
            ]"
          >
            Précédent
          </button>

          <button
            v-for="page in visiblePages"
            :key="page"
            @click="currentPage = page"
            :class="[
              page === currentPage
                ? 'bg-blue-600 text-white'
                : 'text-gray-500 hover:text-gray-700',
              'px-3 py-2 rounded-md text-sm font-medium'
            ]"
          >
            {{ page }}
          </button>

          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            :class="[
              currentPage === totalPages
                ? 'text-gray-400 cursor-not-allowed'
                : 'text-gray-500 hover:text-gray-700',
              'px-3 py-2 rounded-md text-sm font-medium'
            ]"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>
    
    <BlogPostModal 
      :is-open="isModalOpen" 
      :post="selectedPost"
      @close="closeModal"
      @toggle-like="toggleLike"
    />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const { $api } = useNuxtApp()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref('')
const posts = ref([])
const totalPosts = ref(0)
const searchTerm = ref('')
const selectedCategory = ref('all')
const currentPage = ref(1)
const postsPerPage = 9

const isModalOpen = ref(false)
const selectedPost = ref(null)

// Supprimer les posts d'exemple car nous utilisons maintenant l'API

const filteredPosts = computed(() => {
  let filtered = posts.value.filter(post => {
    return post.isPublished !== false // Vérifier que le post est publié
  })
  
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter(post =>
      post.title.toLowerCase().includes(search) ||
      post.content.toLowerCase().includes(search) ||
      post.author?.firstName?.toLowerCase().includes(search) ||
      post.author?.lastName?.toLowerCase().includes(search)
    )
  }

  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(post => post.category === selectedCategory.value)
  }

  return filtered
})

const totalPages = computed(() => {
  return Math.ceil(totalPosts.value / postsPerPage)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const loadPosts = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const params = new URLSearchParams()
    if (searchTerm.value) params.append('search', searchTerm.value)
    // Ne pas envoyer le paramètre category si "all" est sélectionné
    if (selectedCategory.value && selectedCategory.value !== 'all') {
      params.append('category', selectedCategory.value)
    }
    if (currentPage.value > 1) params.append('page', currentPage.value.toString())
    
    // Utiliser la bonne route API
    const response = await $api.get(`/blog/posts?${params.toString()}`)
    
    console.log('Réponse API blog:', response)
    
    // Vérifier le format de la réponse
    if (response.posts && Array.isArray(response.posts)) {
      // Format avec wrapper 'posts'
      posts.value = response.posts.map(post => ({
        ...post,
        isLiked: false,
        likes: post.likes || 0
      }))
      totalPosts.value = response.total || response.posts.length
    } else if (Array.isArray(response)) {
      // Format direct (tableau)
      posts.value = response.map(post => ({
        ...post,
        isLiked: false,
        likes: post.likes || 0
      }))
      totalPosts.value = response.length
    } else {
      console.warn('Format de réponse inattendu:', response)
      posts.value = []
      totalPosts.value = 0
    }
    
    totalPages.value = Math.ceil(totalPosts.value / postsPerPage)
    
    console.log('Posts traités:', posts.value)
    
    // Charger le statut des likes après avoir chargé les posts
    if (authStore.isLoggedIn && posts.value.length > 0) {
      await loadLikesStatus()
    }
  } catch (err) {
    console.error('Erreur lors du chargement des posts:', err)
    error.value = 'Erreur lors du chargement des articles'
    posts.value = []
    totalPosts.value = 0
    totalPages.value = 0
  } finally {
    loading.value = false
  }
}

const truncateContent = (content) => {
  if (!content) return ''
  return content.length > 300 ? content.substring(0, 300) + '...' : content
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const loadLikesStatus = async () => {
  if (!authStore.isLoggedIn) return
  
  try {
    console.log('Chargement du statut des likes...')
    for (const post of posts.value) {
      try {
        const response = await $api.get(`/api/likes/status/${post.id}`)
        console.log(`Like status pour post ${post.id}:`, response)
        if (response.liked !== undefined) {
          post.isLiked = response.liked
          post.likes = response.likesCount
        }
      } catch (error) {
        console.error(`Erreur pour le post ${post.id}:`, error)
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement du statut des likes:', error)
  }
}

const toggleLike = async (post) => {
  if (!authStore.isLoggedIn) {
    console.log('Utilisateur non connecté, redirection vers login')
    navigateTo('/login')
    return
  }
  
  try {
    console.log(`Toggle like pour post ${post.id}...`)
    const response = await $api.post(`/api/likes/toggle/${post.id}`)
    console.log('Réponse toggle like:', response)
    
    if (response.liked !== undefined) {
      post.isLiked = response.liked
      post.likes = response.likesCount
      console.log(`Post ${post.id} - Liked: ${post.isLiked}, Count: ${post.likes}`)
    }
  } catch (error) {
    console.error('Erreur lors du like:', error)
    if (error.status === 401) {
      console.log('Token expiré, redirection vers login')
      navigateTo('/login')
    }
  }
}

const openModal = (post) => {
  selectedPost.value = post
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  selectedPost.value = null
}

onMounted(() => {
  loadPosts()
})

watch([searchTerm, selectedCategory], () => {
  currentPage.value = 1
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 