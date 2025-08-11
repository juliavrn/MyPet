<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="handleBackdropClick">
    <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white" @click.stop>
      <div class="sticky top-0 bg-white z-10 pb-4 border-b border-gray-200 mb-6">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-900">
            {{ record ? 'Modifier le suivi' : 'Nouveau suivi de santé' }}
          </h3>
          <button
            @click="handleClose"
            class="text-gray-600 hover:text-gray-800 transition-colors p-2 hover:bg-gray-100 rounded-full border border-gray-300 hover:border-gray-400"
            title="Fermer (Échap)"
          >
            <HeroIcon name="x-mark" size="md" />
          </button>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Animal *</label>
            <select
              v-model="form.petId"
              required
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Sélectionner un animal</option>
              <option v-for="pet in pets" :key="pet.id" :value="pet.id">
                {{ pet.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
            <input
              v-model="form.date"
              type="date"
              required
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>

        <div class="border-b border-gray-200">
          <div class="overflow-x-auto">
            <nav class="flex space-x-8 min-w-max">
              <button
                type="button"
                v-for="(section, index) in sections"
                :key="index"
                @click="currentSection = index"
                :class="[
                  currentSection === index
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                {{ section.title }}
              </button>
            </nav>
          </div>
        </div>

        <div class="min-h-96">
          <div v-if="currentSection === 0" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Santé et bien-être</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.signsOfIllness"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Signes de maladie</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.fever"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Fièvre</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.vomiting"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Vomissements</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.limping"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Boitement/Douleurs</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Blessures observées</label>
              <textarea
                v-model="form.observedInjuries"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Décrivez les blessures observées..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Autres observations santé</label>
              <textarea
                v-model="form.otherHealthObservations"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Autres observations sur la santé..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 1" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Alimentation</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.ateAllMeals"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">A mangé tous ses repas</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.treatsGiven"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Friandises données</label>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Appétit</label>
                <select
                  v-model="form.appetite"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Sélectionner</option>
                  <option value="Très bon">Très bon</option>
                  <option value="Normal">Normal</option>
                  <option value="Faible">Faible</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Consommation d'eau</label>
                <select
                  v-model="form.waterIntake"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Sélectionner</option>
                  <option value="Normale">Normale</option>
                  <option value="Faible">Faible</option>
                  <option value="Élevée">Élevée</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Aliments donnés aujourd'hui</label>
              <textarea
                v-model="form.foodsGiven"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Liste des aliments donnés..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 2" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Hygiène et toilettage</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.brushingDone"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Brossage effectué</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.bathOrCleaning"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Bain ou nettoyage</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.nailsChecked"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Ongles/griffes vérifiés</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.earsCleaned"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Oreilles nettoyées</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">État du pelage</label>
              <select
                v-model="form.coatCondition"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Sélectionner</option>
                <option value="Propre">Propre</option>
                <option value="Sali">Sali</option>
                <option value="Perte de poils excessive">Perte de poils excessive</option>
              </select>
            </div>
          </div>

          <div v-if="currentSection === 3" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Activité physique</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Temps de promenade (minutes)</label>
                <input
                  v-model="form.walkingTime"
                  type="number"
                  min="0"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="0"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type d'activité</label>
                <select
                  v-model="form.activityType"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Sélectionner</option>
                  <option value="Promenade">Promenade</option>
                  <option value="Jeu">Jeu</option>
                  <option value="Entraînement">Entraînement</option>
                  <option value="Sport">Sport</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Niveau d'énergie</label>
              <select
                v-model="form.energyLevel"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Sélectionner</option>
                <option value="Faible">Faible</option>
                <option value="Normal">Normal</option>
                <option value="Élevé">Élevé</option>
              </select>
            </div>
          </div>

          <div v-if="currentSection === 4" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Comportement</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.stressed"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Stressé</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.unusualSigns"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Signes inhabituels</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.moodChanges"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Changements d'humeur</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Détails sur le comportement</label>
              <textarea
                v-model="form.behaviorDetails"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Décrivez le comportement observé..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 5" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Éducation et socialisation</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.obedienceExercises"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Exercices d'obéissance</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.metOtherAnimals"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Rencontré d'autres animaux</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.positiveHumanInteraction"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Interaction positive avec humains</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nouveaux apprentissages</label>
              <textarea
                v-model="form.newLearnings"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Nouveaux tours ou apprentissages..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 6" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Environnement</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.livingSpaceCleaned"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Espace de vie nettoyé</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.correctTemperature"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Température correcte</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.environmentChanged"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Environnement changé</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Problèmes constatés</label>
              <textarea
                v-model="form.environmentIssues"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Dangers, saleté, dégradations..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 7" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Suivi des traitements</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.medicationGiven"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Médicaments donnés</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.supplementsGiven"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Suppléments alimentaires</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.antiparasiticTreatment"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Traitement antiparasitaire</label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Autres soins spécifiques</label>
              <textarea
                v-model="form.otherSpecificCare"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Autres soins ou traitements..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 8" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Objectifs et progrès</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Objectif travaillé aujourd'hui</label>
              <textarea
                v-model="form.workedObjective"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Perte de poids, éducation, etc..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Progrès observés</label>
              <textarea
                v-model="form.observedProgress"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Progrès réalisés..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ajustements nécessaires</label>
              <textarea
                v-model="form.necessaryAdjustments"
                rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Ajustements à apporter..."
              ></textarea>
            </div>
          </div>

          <div v-if="currentSection === 9" class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Notes générales</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes libres</label>
              <textarea
                v-model="form.generalNotes"
                rows="6"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Tout ce qui ne rentre pas dans les cases précédentes..."
              ></textarea>
            </div>
          </div>
        </div>

        <div class="flex justify-between pt-6 border-t border-gray-200">
          <button
            v-if="currentSection > 0"
            @click="currentSection--"
            type="button"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <HeroIcon name="arrow-left" size="sm" class="mr-2" />
            Précédent
          </button>
          <div v-else></div>

          <div class="flex space-x-3">
            <button
              v-if="currentSection < sections.length - 1"
              @click="currentSection++"
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Suivant
              <HeroIcon name="arrow-right" size="sm" class="ml-2" />
            </button>
            <button
              v-else
              type="submit"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
            >
              <HeroIcon v-if="loading" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
              <HeroIcon v-else name="check" class="w-4 h-4 mr-2" />
              {{ record ? 'Modifier' : 'Créer' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  record: {
    type: Object,
    default: null
  },
  pets: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['close', 'save'])

// État local
const currentSection = ref(0)
const loading = ref(false)

// Sections du formulaire
const sections = [
  { title: 'Santé' },
  { title: 'Alimentation' },
  { title: 'Hygiène' },
  { title: 'Activité' },
  { title: 'Comportement' },
  { title: 'Éducation' },
  { title: 'Environnement' },
  { title: 'Traitements' },
  { title: 'Objectifs' },
  { title: 'Notes' }
]

// Formulaire
const form = reactive({
  petId: '',
  date: new Date().toISOString().split('T')[0],
  // 1. Santé et bien-être
  signsOfIllness: false,
  fever: false,
  vomiting: false,
  limping: false,
  observedInjuries: '',
  otherHealthObservations: '',
  // 2. Alimentation
  ateAllMeals: false,
  appetite: '',
  waterIntake: '',
  foodsGiven: '',
  treatsGiven: false,
  // 3. Hygiène et toilettage
  brushingDone: false,
  bathOrCleaning: false,
  nailsChecked: false,
  earsCleaned: false,
  coatCondition: '',
  // 4. Activité physique
  walkingTime: '',
  activityType: '',
  energyLevel: '',
  // 5. Comportement
  stressed: false,
  unusualSigns: false,
  moodChanges: false,
  behaviorDetails: '',
  // 6. Éducation et socialisation
  obedienceExercises: false,
  metOtherAnimals: false,
  positiveHumanInteraction: false,
  newLearnings: '',
  // 7. Environnement
  livingSpaceCleaned: false,
  correctTemperature: false,
  environmentChanged: false,
  environmentIssues: '',
  // 8. Suivi des traitements
  medicationGiven: false,
  supplementsGiven: false,
  antiparasiticTreatment: false,
  otherSpecificCare: '',
  // 9. Objectifs et progrès
  workedObjective: '',
  observedProgress: '',
  necessaryAdjustments: '',
  // 10. Notes générales
  generalNotes: ''
})

// Initialiser le formulaire avec les données existantes
onMounted(() => {
  if (props.record) {
    form.petId = typeof props.record.pet === 'string' 
      ? parseInt(props.record.pet.split('/').pop()) 
      : props.record.pet?.id || props.record.pet
    form.date = props.record.date.split('T')[0]
    
    // Copier toutes les propriétés
    Object.keys(form).forEach(key => {
      if (props.record[key] !== undefined) {
        form[key] = props.record[key]
      }
    })
  }
})

// Méthodes
const handleSubmit = async () => {
  loading.value = true
  
  try {
    console.log('Submitting form data:', form)
    
    // Validation des champs requis
    if (!form.petId) {
      throw new Error('Veuillez sélectionner un animal')
    }
    
    if (!form.date) {
      throw new Error('Veuillez sélectionner une date')
    }
    
    // Nettoyer les données avant envoi
    const cleanData = { ...form }
    
    // Convertir les chaînes vides en null pour les champs texte
    Object.keys(cleanData).forEach(key => {
      if (typeof cleanData[key] === 'string' && cleanData[key].trim() === '') {
        cleanData[key] = null
      }
      // Convertir les chaînes vides en null pour walkingTime
      if (key === 'walkingTime' && cleanData[key] === '') {
        cleanData[key] = null
      }
    })
    
    console.log('Cleaned form data:', cleanData)
    
    const result = await emit('save', cleanData)
    console.log('Save result:', result)
    
    if (!result || !result.success) {
      throw new Error(result?.error || 'Erreur lors de la sauvegarde')
    }
  } catch (error) {
    console.error('Error in handleSubmit:', error)
    // L'erreur sera gérée par le composant parent
  } finally {
    loading.value = false
  }
}

const handleClose = () => {
  emit('close')
}

const handleBackdropClick = () => {
  emit('close')
}

// Écouter la touche Échap
onMounted(() => {
  const handleEscape = (event) => {
    if (event.key === 'Escape') {
      emit('close')
    }
  }
  
  document.addEventListener('keydown', handleEscape)
  
  // Nettoyer l'event listener
  onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape)
  })
})
</script> 