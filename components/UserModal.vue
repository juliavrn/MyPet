<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>{{ isEditing ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h3>
        <button @click="closeModal" class="close-btn">&times;</button>
      </div>
      
      <form @submit.prevent="saveUser" class="modal-form">
        <div class="form-group">
          <label for="firstName">Prénom *</label>
          <input 
            id="firstName"
            v-model="form.firstName" 
            type="text" 
            required 
            placeholder="Prénom"
          />
        </div>
        
        <div class="form-group">
          <label for="lastName">Nom *</label>
          <input 
            id="lastName"
            v-model="form.lastName" 
            type="text" 
            required 
            placeholder="Nom"
          />
        </div>
        
        <div class="form-group">
          <label for="email">Email *</label>
          <input 
            id="email"
            v-model="form.email" 
            type="email" 
            required 
            placeholder="email@exemple.com"
            :disabled="isEditing"
          />
        </div>
        

        
        <div class="form-group">
          <label for="password">{{ isEditing ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}</label>
          <input 
            id="password"
            v-model="form.password" 
            type="password" 
            :required="!isEditing"
            :placeholder="isEditing ? 'Nouveau mot de passe' : 'Mot de passe'"
          />
        </div>
        
        <div class="form-row">
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input 
                v-model="form.isAdmin" 
                type="checkbox"
              />
              <span class="checkmark"></span>
              Administrateur
            </label>
          </div>
          
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input 
                v-model="form.canPublish" 
                type="checkbox"
              />
              <span class="checkmark"></span>
              Peut publier des articles
            </label>
          </div>
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
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  isAdmin: false,
  canPublish: false
})

const isEditing = computed(() => !!props.user)

onMounted(() => {
  if (props.user) {
    form.value = {
      firstName: props.user.firstName || '',
      lastName: props.user.lastName || '',
      email: props.user.email || '',
      password: '',
      isAdmin: props.user.isAdmin ?? false,
      canPublish: props.user.canPublish ?? false
    }
  }
})

const closeModal = () => {
  emit('close')
}

const saveUser = () => {
  const userData = { ...form.value }
  
  if (props.user) {
    userData.id = props.user.id
  }
  
  // Ne pas envoyer le mot de passe s'il est vide en mode édition
  if (isEditing.value && !userData.password) {
    delete userData.password
  }
  
  emit('save', userData)
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
  max-width: 500px;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="password"] {
  width: 100%;
  padding: 12px;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
}

.form-group input:disabled {
  background: #f8f9fa;
  color: #7f8c8d;
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
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>
