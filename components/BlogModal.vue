<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>{{ isEditing ? 'Modifier l\'article' : 'Nouvel article' }}</h3>
        <button @click="closeModal" class="close-btn">&times;</button>
      </div>
      
      <form @submit.prevent="saveBlogPost" class="modal-form">
        <div class="form-group">
          <label for="title">Titre *</label>
          <input 
            id="title"
            v-model="form.title" 
            type="text" 
            required 
            placeholder="Titre de l'article"
          />
        </div>
        
        <div class="form-group">
          <label for="category">Catégorie</label>
          <select id="category" v-model="form.category">
            <option value="">Sélectionner une catégorie</option>
            <option value="Soins">Soins</option>
            <option value="Alimentation">Alimentation</option>
            <option value="Santé">Santé</option>
            <option value="Éducation">Éducation</option>
            <option value="Loisirs">Loisirs</option>
            <option value="Conseils">Conseils</option>
            <option value="Actualités">Actualités</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="content">Contenu *</label>
          <textarea 
            id="content"
            v-model="form.content" 
            required 
            placeholder="Contenu de l'article..."
            rows="8"
          ></textarea>
        </div>
        
        <div class="form-group">
          <label for="image">URL de l'image</label>
          <input 
            id="image"
            v-model="form.image" 
            type="url" 
            placeholder="https://exemple.com/image.jpg"
          />
        </div>
        
        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input 
              v-model="form.isPublished" 
              type="checkbox"
            />
            <span class="checkmark"></span>
            Publier immédiatement
          </label>
        </div>
        
        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-secondary">
            Annuler
          </button>
          <button type="submit" class="btn-primary">
            {{ isEditing ? 'Mettre à jour' : 'Créer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
  post: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])

const form = ref({
  title: '',
  content: '',
  category: '',
  image: '',
  isPublished: false
})

const isEditing = computed(() => !!props.post)

onMounted(() => {
  if (props.post) {
    form.value = {
      title: props.post.title || '',
      content: props.post.content || '',
      category: props.post.category || '',
      image: props.post.image || '',
      isPublished: props.post.isPublished ?? false
    }
  }
})

const closeModal = () => {
  emit('close')
}

const saveBlogPost = () => {
  const postData = { ...form.value }
  
  if (props.post) {
    postData.id = props.post.id
  }
  
  emit('save', postData)
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #ecf0f1;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7f8c8d;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s ease;
}

.close-btn:hover {
  background: #ecf0f1;
}

.modal-form {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input[type="text"],
.form-group input[type="url"],
.form-group select {
  width: 100%;
  padding: 12px;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  min-height: 120px;
  transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.checkbox-group {
  margin-bottom: 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-weight: 500;
  color: #2c3e50;
}

.checkbox-label input[type="checkbox"] {
  margin-right: 10px;
  width: 18px;
  height: 18px;
  accent-color: #3498db;
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ecf0f1;
}

.btn-primary,
.btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover {
  background: #2980b9;
}

.btn-secondary {
  background: #ecf0f1;
  color: #2c3e50;
}

.btn-secondary:hover {
  background: #d5dbdb;
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    margin: 20px;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>
