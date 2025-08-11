<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>
    
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative w-full max-w-4xl bg-white rounded-lg shadow-xl">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900">{{ post?.title }}</h2>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <span class="text-2xl">×</span>
          </button>
        </div>
        
        <div class="p-6 max-h-[70vh] overflow-y-auto">
          <div v-if="post?.image" class="mb-6">
            <img 
              :src="post.image" 
              :alt="post.title"
              class="w-full h-64 object-cover rounded-lg"
            />
          </div>
          
          <div class="flex items-center text-sm text-gray-500 mb-4">
            <span class="text-sm mr-1">👤</span>
            <span>{{ post?.author?.firstName }} {{ post?.author?.lastName }}</span>
            <span class="mx-2">•</span>
            <span class="text-sm mr-1">📅</span>
            <span>{{ formatDate(post?.createdAt) }}</span>
            <span class="mx-2">•</span>
            <span class="text-sm mr-1">🏷️</span>
            <span class="capitalize">{{ post?.category }}</span>
          </div>
          
          <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ post?.content }}</p>
          </div>
          
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💬 Commentaires ({{ comments.length }})</h3>
            
            <div v-if="isLoadingComments" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p class="mt-2 text-gray-600">Chargement des commentaires...</p>
            </div>
            
            <div v-else-if="comments.length > 0" class="space-y-4 mb-6">
              <div 
                v-for="comment in comments" 
                :key="comment.id"
                class="bg-gray-50 rounded-lg p-4"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center mb-2">
                      <span class="font-medium text-gray-900">{{ comment.author?.firstName }} {{ comment.author?.lastName }}</span>
                      <span class="mx-2 text-gray-400">•</span>
                      <span class="text-sm text-gray-500">{{ formatDate(comment.createdAt) }}</span>
                    </div>
                    
                    <div v-if="!comment.isEditing">
                      <p class="text-gray-700">{{ comment.content }}</p>
                    </div>
                    <div v-else class="space-y-3">
                      <textarea
                        v-model="comment.editContent"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        required
                      ></textarea>
                      <div class="flex justify-end space-x-2">
                        <button
                          @click="cancelEdit(comment)"
                          class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors"
                        >
                          Annuler
                        </button>
                        <button
                          @click="saveEdit(comment)"
                          :disabled="isSubmitting"
                          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        >
                          Sauvegarder
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="canEditComment(comment)" class="ml-4 flex space-x-2">
                    <button
                      @click="editComment(comment)"
                      class="text-gray-400 hover:text-blue-600 transition-colors"
                      title="Modifier"
                    >
                      ✏️
                    </button>
                    <button
                      @click="deleteComment(comment)"
                      class="text-gray-400 hover:text-red-600 transition-colors"
                      title="Supprimer"
                    >
                      🗑️
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <p class="text-lg">💬 Aucun commentaire pour le moment</p>
              <p class="text-sm mt-2">Soyez le premier à commenter cet article !</p>
            </div>
            
            <div v-if="authStore.isLoggedIn" class="bg-blue-50 rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-3">Ajouter un commentaire</h4>
              <form @submit.prevent="submitComment" class="space-y-3">
                <textarea
                  v-model="newComment"
                  placeholder="Votre commentaire..."
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                  required
                ></textarea>
                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    {{ isSubmitting ? 'Envoi...' : 'Publier' }}
                  </button>
                </div>
              </form>
            </div>
            
            <div v-else class="bg-gray-50 rounded-lg p-4 text-center">
              <p class="text-gray-600 mb-2">Connectez-vous pour ajouter un commentaire</p>
              <NuxtLink 
                to="/login" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              >
                Se connecter
              </NuxtLink>
            </div>
          </div>
          
          <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center space-x-4">
              <button
                @click="toggleLike(post)"
                class="flex items-center space-x-2 text-gray-500 hover:text-red-500 transition-colors"
                :class="{ 'text-red-500': post?.isLiked }"
              >
                <span class="text-xl">{{ post?.isLiked ? '❤️' : '🤍' }}</span>
                <span class="text-sm font-medium">{{ post?.likes || 0 }}</span>
              </button>
              
              <div class="flex items-center space-x-2 text-gray-500">
                <span class="text-lg">💬</span>
                <span class="text-sm font-medium">{{ post?.commentsCount || 0 }}</span>
              </div>
            </div>
            
            <button
              @click="closeModal"
              class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
import { useNotificationStore } from '~/stores/notification'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  post: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'toggle-like', 'comment-added', 'comment-deleted'])
const authStore = useAuthStore()
const { success, error } = useNotificationStore()

// État des commentaires
const comments = ref([])
const newComment = ref('')
const isSubmitting = ref(false)
const isLoadingComments = ref(false)

const closeModal = () => {
  emit('close')
}

const toggleLike = (post) => {
  emit('toggle-like', post)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Charger les commentaires
const loadComments = async () => {
  if (!props.post?.id) return
  
  try {
    isLoadingComments.value = true
    const { $api } = useNuxtApp()
    const response = await $api.get(`/api/comments/blog/${props.post.id}`)
    comments.value = response || []
  } catch (err) {
    
    comments.value = []
    error('Erreur lors du chargement des commentaires')
  } finally {
    isLoadingComments.value = false
  }
}

// Ajouter un commentaire
const submitComment = async () => {
  if (!newComment.value.trim() || !props.post?.id) return
  
  try {
    isSubmitting.value = true
    const { $api } = useNuxtApp()
    
    const commentData = {
      content: newComment.value.trim(),
      blogPostId: props.post.id
    }
    
    const response = await $api.post('/api/comments', commentData)
    
    if (response) {
      // Ajouter le nouveau commentaire à la liste
      const newCommentObj = {
        id: response.id || Date.now(),
        content: newComment.value.trim(),
        author: {
          firstName: authStore.user?.firstName || 'Utilisateur',
          lastName: authStore.user?.lastName || ''
        },
        createdAt: new Date().toISOString(),
        isApproved: true,
        isEditing: false
      }
      
      comments.value.unshift(newCommentObj)
      newComment.value = ''
      
      // Émettre un événement pour mettre à jour le compteur de commentaires
      emit('comment-added')
      success('Commentaire ajouté avec succès !')
    }
  } catch (err) {
    
    error('Erreur lors de l\'ajout du commentaire. Veuillez réessayer.')
  } finally {
    isSubmitting.value = false
  }
}

// Vérifier si l'utilisateur peut modifier/supprimer un commentaire
const canEditComment = (comment) => {
  return authStore.isLoggedIn && (
    comment.author?.id === authStore.user?.id || 
            authStore.getUserRoles.includes('ROLE_ADMIN')
  )
}

// Modifier un commentaire
const editComment = (comment) => {
  comment.isEditing = true
  comment.editContent = comment.content
}

// Annuler l'édition
const cancelEdit = (comment) => {
  comment.isEditing = false
  comment.editContent = ''
}

// Sauvegarder les modifications
const saveEdit = async (comment) => {
  if (!comment.editContent?.trim()) return
  
  try {
    isSubmitting.value = true
    const { $api } = useNuxtApp()
    
    const response = await $api.put(`/api/comments/${comment.id}`, {
      content: comment.editContent.trim()
    })
    
    if (response) {
      comment.content = comment.editContent.trim()
      comment.isEditing = false
      comment.editContent = ''
      success('Commentaire modifié avec succès !')
    }
  } catch (err) {
    
    error('Erreur lors de la modification du commentaire. Veuillez réessayer.')
  } finally {
    isSubmitting.value = false
  }
}

// Supprimer un commentaire
const deleteComment = async (comment) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return
  
  try {
    isSubmitting.value = true
    const { $api } = useNuxtApp()
    
    const response = await $api.delete(`/api/comments/${comment.id}`)
    
    if (response) {
      const index = comments.value.findIndex(c => c.id === comment.id)
      if (index > -1) {
        comments.value.splice(index, 1)
      }
      
      // Émettre un événement pour mettre à jour le compteur de commentaires
      emit('comment-deleted')
      success('Commentaire supprimé avec succès !')
    }
  } catch (err) {
    
    error('Erreur lors de la suppression du commentaire. Veuillez réessayer.')
  } finally {
    isSubmitting.value = false
  }
}

// Surveiller l'ouverture de la modale pour charger les commentaires
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.post) {
    loadComments()
  }
})

// Surveiller le changement de post
watch(() => props.post, (post) => {
  if (post && props.isOpen) {
    loadComments()
  }
})
</script>

<style scoped>
.prose {
  max-width: none;
}

.prose p {
  margin-bottom: 1rem;
  line-height: 1.7;
}
</style>
