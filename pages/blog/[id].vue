<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Bouton retour -->
      <div class="mb-6">
        <NuxtLink 
          to="/blog" 
          class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium"
        >
          <span class="mr-2">←</span>
          Retour au blog
        </NuxtLink>
      </div>

      <!-- Article -->
      <article v-if="post" class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Image de couverture -->
        <div class="aspect-w-16 aspect-h-9 bg-gray-200">
          <img 
            v-if="post.image"
            :src="post.image" 
            :alt="post.title"
            class="w-full h-64 object-cover"
          />
          <div v-else class="w-full h-64 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
            <span class="text-6xl text-gray-400">📰</span>
          </div>
        </div>

        <!-- Contenu de l'article -->
        <div class="p-8">
          <!-- En-tête -->
          <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-3">
              <span class="mr-1">👤</span>
              <span>{{ post.author }}</span>
              <span class="mx-2">•</span>
              <span class="mr-1">📅</span>
              <span>{{ formatDate(post.createdAt) }}</span>
              <span class="mx-2">•</span>
              <span class="mr-1">🏷️</span>
              <span class="capitalize">{{ post.category }}</span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">
              {{ post.title }}
            </h1>

            <!-- Actions (like, partage, etc.) -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
              <div class="flex items-center space-x-6">
                <!-- Bouton de like -->
                <button
                  @click="toggleLike"
                  class="flex items-center space-x-2 text-gray-500 hover:text-red-500 transition-colors"
                  :class="{ 'text-red-500': post.isLiked || false }"
                >
                  <span class="text-2xl">{{ post.isLiked ? '❤️' : '🤍' }}</span>
                  <span class="font-medium">{{ post.likes }} j'aime</span>
                </button>

                <!-- Bouton de partage -->
                <button
                  @click="shareArticle"
                  class="flex items-center space-x-2 text-gray-500 hover:text-blue-500 transition-colors"
                >
                  <span class="text-xl">📤</span>
                  <span>Partager</span>
                </button>

                <!-- Bouton d'impression -->
                <button
                  @click="printArticle"
                  class="flex items-center space-x-2 text-gray-500 hover:text-green-500 transition-colors"
                >
                  <span class="text-xl">🖨️</span>
                  <span>Imprimer</span>
                </button>
              </div>

              <!-- Actions d'édition (admin seulement) -->
              <div v-if="authStore.getUser?.isAdmin || authStore.getUserRoles.includes('ROLE_BLOG_MODERATOR')" class="flex items-center space-x-3">
                <NuxtLink
                  :to="`/blog/${post.id}/edit`"
                  class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors"
                >
                  <span class="text-xl">✏️</span>
                  <span>Modifier</span>
                </NuxtLink>
                <button
                  @click="deleteArticle"
                  class="flex items-center space-x-2 text-red-600 hover:text-red-800 transition-colors"
                >
                  <span class="text-xl">🗑️</span>
                  <span>Supprimer</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Contenu -->
          <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed mb-6">
              {{ post.content }}
            </p>
          </div>

          <!-- Tags -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Tags</h3>
            <div class="flex flex-wrap gap-2">
              <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                {{ post.category }}
              </span>
              <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                conseils
              </span>
              <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                soins
              </span>
            </div>
          </div>

          <!-- Auteur -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-2xl">👤</span>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ post.author }}
                </h3>
                <p class="text-gray-600">
                  Expert en soins animaliers avec plus de 10 ans d'expérience
                </p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <!-- Article non trouvé -->
      <div v-else class="text-center py-12">
        <span class="text-6xl text-gray-400 mb-4">📰</span>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Article non trouvé</h3>
        <p class="text-gray-500 mb-6">
          L'article que vous recherchez n'existe pas ou a été supprimé.
        </p>
        <NuxtLink 
          to="/blog" 
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
          Retour au blog
        </NuxtLink>
      </div>

      <!-- Articles similaires -->
      <div v-if="similarPosts.length > 0" class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Articles similaires</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article 
            v-for="similarPost in similarPosts" 
            :key="similarPost.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
          >
            <div class="aspect-w-16 aspect-h-9 bg-gray-200">
              <img 
                v-if="similarPost.image"
                :src="similarPost.image" 
                :alt="similarPost.title"
                class="w-full h-32 object-cover"
              />
              <div v-else class="w-full h-32 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
                <span class="text-3xl text-gray-400">📰</span>
              </div>
            </div>
            <div class="p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                {{ similarPost.title }}
              </h3>
              <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ truncateContent(similarPost.content) }}
              </p>
              <NuxtLink
                :to="`/blog/${similarPost.id}`"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm"
              >
                Lire la suite
                <span class="ml-1">→</span>
              </NuxtLink>
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const route = useRoute()
const { $api } = useNuxtApp()
const authStore = useAuthStore()

// État réactif
const post = ref(null)

// Récupérer l'ID de l'article depuis l'URL
const postId = parseInt(route.params.id)

// Données d'exemple avec une vraie image
const allPosts = [
  {
    id: 1,
    title: "Comment prendre soin de la santé dentaire de votre chien",
    content: "La santé bucco-dentaire est essentielle pour le bien-être de votre chien. Découvrez nos conseils pour maintenir une hygiène dentaire optimale, prévenir les problèmes de gencives et assurer une haleine fraîche. Nous vous expliquons comment brosser les dents de votre compagnon et quels produits utiliser pour un soin efficace. La plaque dentaire peut s'accumuler rapidement chez les chiens, surtout s'ils ne mangent que des croquettes molles. Un brossage régulier avec une brosse à dents adaptée et un dentifrice spécial pour chiens est recommandé au moins 2-3 fois par semaine. Les jouets dentaires et les friandises spéciales peuvent également aider à maintenir une bonne hygiène buccale. N'oubliez pas de consulter régulièrement votre vétérinaire pour des contrôles dentaires professionnels.",
    image: "http://localhost:8000/uploads/images/68927633affa3_1754428979.JPG",
    author: "Administrateur MyPet",
    category: "sante",
    createdAt: "2025-08-10T14:33:03+00:00",
    likes: 0,
    commentsCount: 0
  },
  {
    id: 2,
    title: "Les meilleures activités pour stimuler votre chat",
    content: "Les chats ont besoin de stimulation mentale et physique pour rester en bonne santé et heureux. Découvrez comment créer un environnement enrichissant avec des jouets interactifs, des parcours d'obstacles et des moments de jeu quotidiens. Nous vous donnons des conseils pour adapter les activités à l'âge et au tempérament de votre chat.",
    image: "http://localhost:8000/uploads/images/68927647e3904_1754428999.jpeg",
    author: "Administrateur MyPet",
    category: "bien-etre",
    createdAt: "2025-08-10T14:30:00+00:00",
    likes: 0,
    commentsCount: 0
  },
  {
    id: 3,
    title: "Guide complet de l'alimentation du lapin",
    content: "L'alimentation du lapin est cruciale pour sa santé. Découvrez les bonnes proportions de foin, légumes frais et granulés, ainsi que les aliments à éviter absolument. Nous vous expliquons comment introduire de nouveaux aliments et gérer les transitions alimentaires en douceur.",
    image: "http://localhost:8000/uploads/images/6892763a12bd4_1754428986.jpeg",
    author: "Administrateur MyPet",
    category: "nutrition",
    createdAt: "2025-08-10T14:25:00+00:00",
    likes: 0,
    commentsCount: 0
  }
]

// Computed
// Articles similaires (même catégorie, excluant l'article actuel)
const similarPosts = computed(() => {
  if (!post.value?.category) return []
  
  return allPosts
    .filter(p => p.id !== postId && p.category === post.value.category)
    .slice(0, 3)
})

// Méthodes
const loadPost = async () => {
  try {
    post.value = allPosts.find(p => p.id === postId) || null
    
    // Charger le statut du like pour cet article
    await loadLikeStatus()
    
  } catch (error) {
    
    
    // En cas d'erreur, on peut afficher un message ou utiliser les données d'exemple
    if (error.status === 404) {
      post.value = null // Article non trouvé
    } else if (error.status === 401) {
      
      post.value = allPosts.find(p => p.id === postId) || null
    } else {
      // Fallback vers les données d'exemple en cas d'erreur API
      
      post.value = allPosts.find(p => p.id === postId) || null
    }
  }
}

// Charger le statut du like pour cet article
const loadLikeStatus = async () => {
  if (!post.value || !authStore.isLoggedIn) return
  
  try {
    const response = await $api.get(`/api/likes/status/${post.value.id}`)
    if (response.liked !== undefined) {
      post.value.isLiked = response.liked
      post.value.likes = response.likesCount
    }
  } catch (error) {
    
  }
}

const toggleLike = async () => {
  if (post.value) {
    try {
      const response = await $api.post(`/api/likes/toggle/${post.value.id}`)
      
      if (response.liked !== undefined) {
        post.value.isLiked = response.liked
        post.value.likes = response.likesCount
      }
    } catch (error) {
      
      // En cas d'erreur, on peut afficher un message à l'utilisateur
    }
  }
}

const shareArticle = () => {
  if (navigator.share) {
    navigator.share({
      title: post.value.title,
      text: post.value.content.substring(0, 100) + '...',
      url: window.location.href
    })
  } else {
    // Fallback : copier l'URL dans le presse-papiers
    navigator.clipboard.writeText(window.location.href)
    alert('Lien copié dans le presse-papiers !')
  }
}

const printArticle = () => {
  window.print()
}

const deleteArticle = async () => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
    try {
      await $api.delete(`/blog_posts/${postId}`)
      await navigateTo('/blog')
      
    } catch (error) {
      
      alert('Erreur lors de la suppression de l\'article')
    }
  }
}

const truncateContent = (content) => {
  if (!content) return ''
  return content.length > 100 ? content.substring(0, 100) + '...' : content
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Chargement initial
onMounted(() => {
  loadPost()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media print {
  .no-print {
    display: none !important;
  }
}
</style> 