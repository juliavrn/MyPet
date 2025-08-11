<template>
  <div class="space-y-4">
    <div class="flex items-center space-x-4">
      <div class="w-24 h-24 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
        <img
          v-if="previewUrl || modelValue"
          :src="previewUrl || modelValue"
          :alt="alt"
          class="w-full h-full object-cover"
          @error="handleImageError"
          @load="handleImageLoad"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <Icon name="ph:image" class="h-8 w-8 text-gray-400" />
        </div>
      </div>
      
      <div class="flex-1">
        <div
          @drop.prevent="handleDrop"
          @dragover.prevent
          @dragenter.prevent
          class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors cursor-pointer"
          :class="{ 'border-blue-400 bg-blue-50': isDragOver }"
        >
          <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/jpg,image/png"
            class="hidden"
            @change="handleFileSelect"
          />
          
          <div v-if="!uploading">
            <Icon name="ph:upload" class="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-600">
              <span class="font-medium text-blue-600 hover:text-blue-500" @click="triggerFileSelect">
                Cliquez pour sélectionner
              </span>
              ou glissez-déposez
            </p>
            <p class="text-xs text-gray-500 mt-1">
              JPG, PNG jusqu'à 5MB
            </p>
          </div>
          
          <div v-else class="flex items-center justify-center space-x-2">
            <Icon name="ph:spinner" class="animate-spin h-5 w-5 text-blue-600" />
            <span class="text-sm text-gray-600">Téléversement en cours...</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
      <div class="flex">
        <Icon name="ph:warning-circle" class="h-4 w-4 text-red-400 mt-0.5" />
        <div class="ml-2">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>
      </div>
    </div>

    <div v-if="modelValue || previewUrl" class="flex justify-end">
      <button
        @click="removeImage"
        type="button"
        class="text-sm text-red-600 hover:text-red-800 flex items-center space-x-1"
      >
        <Icon name="ph:trash" class="h-4 w-4" />
        <span>Supprimer l'image</span>
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: 'Image'
  }
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const previewUrl = ref('')
const uploading = ref(false)
const error = ref('')
const isDragOver = ref(false)

// Déclencher la sélection de fichier
const triggerFileSelect = () => {
  fileInput.value?.click()
}

// Gérer la sélection de fichier
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    processFile(file)
  }
}

// Gérer le drop
const handleDrop = (event) => {
  isDragOver.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    processFile(file)
  }
}

// Traiter le fichier
const processFile = async (file) => {
  error.value = ''
  
  // Vérifier le type
  if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
    error.value = 'Type de fichier non autorisé. Seuls les fichiers JPG et PNG sont acceptés.'
    return
  }
  
  // Vérifier la taille (5MB)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'Fichier trop volumineux. Taille maximale : 5MB'
    return
  }
  
  // Créer une prévisualisation
  previewUrl.value = URL.createObjectURL(file)
  
  // Téléverser le fichier
  await uploadFile(file)
}

// Téléverser le fichier
const uploadFile = async (file) => {
  try {
    uploading.value = true
    error.value = ''
    
    const config = useRuntimeConfig()
    const authStore = useAuthStore()
    
    // Vérifier que l'utilisateur est connecté
    if (!authStore.isLoggedIn) {
      error.value = 'Vous devez être connecté pour téléverser une image'
      return
    }
    
    const formData = new FormData()
    formData.append('image', file)
    
    const response = await $fetch(`${config.public.apiBase}/api/upload/image`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      },
      body: formData
    })
    
    if (response.url) {
      emit('update:modelValue', response.url)
      // Nettoyer la prévisualisation temporaire
      if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = ''
      }
    } else {
      error.value = 'Erreur lors du téléversement'
    }
  } catch (err) {
    
    if (err.status === 401) {
      error.value = 'Erreur d\'authentification. Veuillez vous reconnecter.'
    } else {
      error.value = err.data?.message || 'Erreur lors du téléversement du fichier'
    }
  } finally {
    uploading.value = false
  }
}

// Supprimer l'image
const removeImage = () => {
  previewUrl.value = ''
  emit('update:modelValue', '')
  error.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Gérer les erreurs d'image
const handleImageError = (event) => {
  
  error.value = 'Erreur de chargement de l\'image'
}

// Gérer le chargement réussi de l'image
const handleImageLoad = () => {
  
  error.value = ''
}

// Nettoyer l'URL de prévisualisation
onUnmounted(() => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
})
</script> 