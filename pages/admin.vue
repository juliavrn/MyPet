<template>
  <div class="admin-dashboard">
    <div class="admin-header">
      <h1>🏛️ Tableau de Bord Administratif</h1>
      <div class="admin-info">
        <span>Connecté en tant que: {{ user?.email }}</span>
        <button @click="logout" class="logout-btn">Déconnexion</button>
      </div>
    </div>

    <div class="admin-nav">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="['nav-btn', { active: activeTab === tab.id }]"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="activeTab === 'dashboard'" class="stats-grid">
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner">Chargement...</div>
      </div>
      
      <div class="stat-card">
        <h3>👥 Utilisateurs</h3>
        <div class="stat-number">{{ stats?.users?.total || 0 }}</div>
        <div class="stat-details">
          <span class="stat-active">{{ stats?.users?.active || 0 }} actifs</span>
          <span class="stat-admin">{{ stats?.users?.admin || 0 }} admins</span>
        </div>
      </div>
      
      <div class="stat-card">
        <h3>📝 Articles</h3>
        <div class="stat-number">{{ stats?.blogPosts?.total || 0 }}</div>
        <div class="stat-details">
          <span class="stat-published">{{ stats?.blogPosts?.published || 0 }} publiés</span>
          <span class="stat-draft">{{ stats?.blogPosts?.draft || 0 }} brouillons</span>
        </div>
      </div>
      
      <div class="stat-card">
        <h3>💬 Commentaires</h3>
        <div class="stat-number">{{ stats?.comments?.total || 0 }}</div>
        <div class="stat-details">
          <span class="stat-approved">{{ stats?.comments?.approved || 0 }} approuvés</span>
          <span class="stat-pending">{{ stats?.comments?.pending || 0 }} en attente</span>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'users'" class="content-section">
      <div class="section-header">
        <h2>👥 Gestion des Utilisateurs</h2>
        <button @click="showUserModal = true" class="add-btn">+ Ajouter un utilisateur</button>
      </div>
      
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
                              <th>Rôle</th>
                <th>Peut publier</th>
                <th>Créé le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.firstName }} {{ user.lastName }}</td>
              <td>{{ user.email }}</td>
                              <td>
                  <span :class="['role-badge', user.isAdmin ? 'admin' : 'user']">
                    {{ user.isAdmin ? 'Admin' : 'Utilisateur' }}
                  </span>
                </td>
                <td>
                  <span :class="['publish-badge', user.canPublish ? 'can-publish' : 'cannot-publish']">
                    {{ user.canPublish ? 'Oui' : 'Non' }}
                  </span>
                </td>
              <td>{{ formatDate(user.createdAt) }}</td>
              <td>
                <button @click="editUser(user)" class="action-btn edit">✏️</button>
                <button @click="deleteUser(user.id)" class="action-btn delete">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="activeTab === 'blog'" class="content-section">
      <div class="section-header">
        <h2>📝 Gestion des Articles</h2>
        <button @click="showBlogModal = true" class="add-btn">+ Nouvel article</button>
      </div>
      
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Catégorie</th>
              <th>Auteur</th>
              <th>Statut</th>
              <th>Commentaires</th>
              <th>Likes</th>
              <th>Créé le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in blogPosts" :key="post.id">
              <td>{{ post.id }}</td>
              <td class="title-cell">{{ post.title }}</td>
              <td>{{ post.category || '-' }}</td>
              <td>{{ post.author.firstName }} {{ post.author.lastName }}</td>
              <td>
                <span :class="['status-badge', post.isPublished ? 'published' : 'draft']">
                  {{ post.isPublished ? 'Publié' : 'Brouillon' }}
                </span>
              </td>
              <td>{{ post.commentsCount }}</td>
              <td>{{ post.likesCount }}</td>
              <td>{{ formatDate(post.createdAt) }}</td>
              <td>
                <button @click="editBlogPost(post)" class="action-btn edit">✏️</button>
                <button @click="deleteBlogPost(post.id)" class="action-btn delete">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="activeTab === 'comments'" class="content-section">
      <div class="section-header">
        <h2>💬 Gestion des Commentaires</h2>
      </div>
      
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Contenu</th>
              <th>Auteur</th>
              <th>Article</th>
              <th>Statut</th>
              <th>Créé le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="comment in comments" :key="comment.id">
              <td>{{ comment.id }}</td>
              <td class="content-cell">{{ comment.content }}</td>
              <td>{{ comment.author.firstName }} {{ comment.author.lastName }}</td>
              <td>{{ comment.blogPost.title }}</td>
              <td>
                <span :class="['status-badge', comment.isApproved ? 'approved' : 'pending']">
                  {{ comment.isApproved ? 'Approuvé' : 'En attente' }}
                </span>
              </td>
              <td>{{ formatDate(comment.createdAt) }}</td>
              <td>
                <button 
                  v-if="!comment.isApproved" 
                  @click="approveComment(comment.id)" 
                  class="action-btn approve"
                >
                  ✅
                </button>
                <button 
                  v-if="comment.isApproved" 
                  @click="rejectComment(comment.id)" 
                  class="action-btn reject"
                >
                  ❌
                </button>
                <button @click="deleteComment(comment.id)" class="action-btn delete">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <UserModal 
      v-if="showUserModal" 
      :user="editingUser" 
      @close="closeUserModal"
      @save="saveUser"
    />
    
    <BlogModal 
      v-if="showBlogModal" 
      :post="editingBlogPost" 
      @close="closeBlogModal"
      @save="saveBlogPost"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useNotificationStore } from '~/stores/notification'

// Composables
const { $api } = useNuxtApp()
const authStore = useAuthStore()
const { show: showNotification } = useNotificationStore()

// État réactif
const activeTab = ref('dashboard')
const loading = ref(true)
const stats = ref({
  users: { total: 0, active: 0, admin: 0 },
  blogPosts: { total: 0, published: 0, draft: 0 },
  comments: { total: 0, approved: 0, pending: 0 }
})
const users = ref([])
const blogPosts = ref([])
const comments = ref([])
const showUserModal = ref(false)
const showBlogModal = ref(false)
const editingUser = ref(null)
const editingBlogPost = ref(null)

// Onglets disponibles
const tabs = [
  { id: 'dashboard', label: '📊 Tableau de bord' },
  { id: 'users', label: '👥 Utilisateurs' },
  { id: 'blog', label: '📝 Articles' },
  { id: 'comments', label: '💬 Commentaires' }
]

// Computed
const user = computed(() => authStore.user)

// Méthodes
const loadStats = async () => {
  try {
    const response = await $api.get('/api/admin/stats')
    
    if (response && typeof response === 'object') {
      stats.value = response
    } else {
      showNotification('Format de réponse invalide pour les statistiques', 'error')
    }
  } catch (error) {
    console.error('Erreur loadStats:', error)
    showNotification('Erreur lors du chargement des statistiques', 'error')
  }
}

const loadUsers = async () => {
  try {
    const response = await $api.get('/api/admin/users')
    users.value = response
  } catch (error) {
    showNotification('Erreur lors du chargement des utilisateurs', 'error')
  }
}

const loadBlogPosts = async () => {
  try {
    const response = await $api.get('/api/admin/blog-posts')
    blogPosts.value = response
  } catch (error) {
    showNotification('Erreur lors du chargement des articles', 'error')
  }
}

const loadComments = async () => {
  try {
    const response = await $api.get('/api/admin/comments')
    comments.value = response
  } catch (parsedError) {
    showNotification('Erreur lors du chargement des commentaires', 'error')
  }
}

const editUser = (user) => {
  editingUser.value = { ...user }
  showUserModal.value = true
}

const editBlogPost = (post) => {
  editingBlogPost.value = { ...post }
  showBlogModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value = null
}

const closeBlogModal = () => {
  showBlogModal.value = false
  editingBlogPost.value = null
}

const saveUser = async (userData) => {
  try {
    if (userData.id) {
      await $api.put(`/api/admin/users/${userData.id}`, userData)
      showNotification('Utilisateur mis à jour avec succès', 'success')
    } else {
      await $api.post('/api/admin/users', userData)
      showNotification('Utilisateur créé avec succès', 'success')
    }
    closeUserModal()
    await loadUsers()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors de la sauvegarde', 'error')
  }
}

const saveBlogPost = async (postData) => {
  try {
    if (postData.id) {
      await $api.put(`/api/admin/blog-posts/${postData.id}`, postData)
      showNotification('Article mis à jour avec succès', 'success')
    } else {
      await $api.post('/api/admin/blog-posts', postData)
      showNotification('Article créé avec succès', 'success')
    }
    closeBlogModal()
    await loadBlogPosts()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors de la sauvegarde', 'error')
  }
}

const deleteUser = async (userId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return
  
  try {
    await $api.delete(`/api/admin/users/${userId}`)
    showNotification('Utilisateur supprimé avec succès', 'success')
    await loadUsers()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors de la suppression', 'error')
  }
}

const deleteBlogPost = async (postId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) return
  
  try {
    await $api.delete(`/api/admin/blog-posts/${postId}`)
    showNotification('Article supprimé avec succès', 'success')
    await loadBlogPosts()
    await loadStats()
  } catch (parsedError) {
    showNotification('Erreur lors de la suppression', 'error')
  }
}

const approveComment = async (commentId) => {
  try {
    await $api.post(`/api/admin/comments/${commentId}/approve`)
    showNotification('Commentaire approuvé avec succès', 'success')
    await loadComments()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors de l\'approbation', 'error')
  }
}

const rejectComment = async (commentId) => {
  try {
    await $api.post(`/api/admin/comments/${commentId}/reject`)
    showNotification('Commentaire rejeté avec succès', 'success')
    await loadComments()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors du rejet', 'error')
  }
}

const deleteComment = async (commentId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return
  
  try {
    await $api.delete(`/api/admin/comments/${commentId}`)
    showNotification('Commentaire supprimé avec succès', 'success')
    await loadComments()
    await loadStats()
  } catch (error) {
    showNotification('Erreur lors de la suppression', 'error')
  }
}

const logout = () => {
  authStore.logout()
  navigateTo('/login')
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Chargement initial
onMounted(async () => {
  // Attendre que l'authentification soit initialisée
  if (!authStore.initialized) {
    await new Promise(resolve => {
      const checkAuth = () => {
        if (authStore.initialized) {
          resolve()
        } else {
          setTimeout(checkAuth, 100)
        }
      }
      checkAuth()
    })
  }
  
  if (!authStore.user?.isAdmin) {
    navigateTo('/')
    return
  }
  
  try {
    loading.value = true
    
    // Charger les données en parallèle pour plus de rapidité
    await Promise.all([
      loadStats(),
      loadUsers(),
      loadBlogPosts(),
      loadComments()
    ])
  } catch (error) {
    console.error('Erreur lors du chargement initial:', error)
    showNotification('Erreur lors du chargement des données', 'error')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.admin-dashboard {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  background: #f8f9fa;
  min-height: 100vh;
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.admin-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
}

.admin-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logout-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.logout-btn:hover {
  background: #c0392b;
}

.admin-nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  background: white;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-btn {
  background: #ecf0f1;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  background: #d5dbdb;
}

.nav-btn.active {
  background: #3498db;
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  text-align: center;
  position: relative;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  border-radius: 12px;
}

.loading-spinner {
  color: #3498db;
  font-weight: 500;
  font-size: 1.1rem;
}

.stat-card h3 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.stat-number {
  font-size: 3rem;
  font-weight: bold;
  color: #3498db;
  margin-bottom: 15px;
}

.stat-details {
  display: flex;
  justify-content: space-around;
  gap: 10px;
}

.stat-details span {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: 500;
}

.stat-active { background: #d5f4e6; color: #27ae60; }
.stat-admin { background: #d6eaf8; color: #3498db; }
.stat-published { background: #d5f4e6; color: #27ae60; }
.stat-draft { background: #fdebd0; color: #f39c12; }
.stat-approved { background: #d5f4e6; color: #27ae60; }
.stat-pending { background: #fdebd0; color: #f39c12; }

.content-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #ecf0f1;
}

.section-header h2 {
  margin: 0;
  color: #2c3e50;
}

.add-btn {
  background: #27ae60;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.add-btn:hover {
  background: #229954;
}

.table-container {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th,
.admin-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ecf0f1;
}

.admin-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #2c3e50;
}

.admin-table tr:hover {
  background: #f8f9fa;
}

.title-cell {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.content-cell {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.active { background: #d5f4e6; color: #27ae60; }
.status-badge.inactive { background: #fadbd8; color: #e74c3c; }
.status-badge.published { background: #d5f4e6; color: #27ae60; }
.status-badge.draft { background: #fdebd0; color: #f39c12; }
.status-badge.approved { background: #d5f4e6; color: #27ae60; }
.status-badge.pending { background: #fdebd0; color: #f39c12; }

.role-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.role-badge.admin { background: #d6eaf8; color: #3498db; }
.role-badge.user { background: #fdebd0; color: #f39c12; }

.action-btn {
  background: none;
  border: none;
  padding: 6px;
  margin: 0 2px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s ease;
}

.action-btn:hover {
  background: #ecf0f1;
}

.action-btn.edit:hover { background: #d6eaf8; }
.action-btn.delete:hover { background: #fadbd8; }
.action-btn.approve:hover { background: #d5f4e6; }
.action-btn.reject:hover { background: #fdebd0; }

@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .admin-nav {
    flex-wrap: wrap;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
}
</style> 