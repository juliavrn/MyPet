<?php

namespace App\Controller;

use App\Entity\HealthRecord;
use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/health-records')]
#[IsGranted('ROLE_USER')]
class HealthRecordController extends AbstractController
{
    #[Route('', name: 'api_health_records_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer les paramètres de filtrage
        $petId = $request->query->get('pet');
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('h')
           ->from(HealthRecord::class, 'h')
           ->join('h.pet', 'p')
           ->where('p.owner = :user')
           ->setParameter('user', $user);
        
        // Filtrage par animal
        if ($petId) {
            $qb->andWhere('h.pet = :petId')
               ->setParameter('petId', $petId);
        }
        
        $qb->orderBy('h.date', 'DESC');
        
        $records = $qb->getQuery()->getResult();
        
        $recordsData = [];
        foreach ($records as $record) {
            $recordsData[] = [
                'id' => $record->getId(),
                'pet' => [
                    'id' => $record->getPet()->getId(),
                    'name' => $record->getPet()->getName(),
                    'species' => $record->getPet()->getSpecies()
                ],
                'date' => $record->getDate() ? $record->getDate()->format('Y-m-d') : null,
                'signsOfIllness' => $record->getSignsOfIllness(),
                'fever' => $record->getFever(),
                'vomiting' => $record->getVomiting(),
                'limping' => $record->getLimping(),
                'observedInjuries' => $record->getObservedInjuries(),
                'otherHealthObservations' => $record->getOtherHealthObservations(),
                'ateAllMeals' => $record->getAteAllMeals(),
                'appetite' => $record->getAppetite(),
                'waterIntake' => $record->getWaterIntake(),
                'foodsGiven' => $record->getFoodsGiven(),
                'treatsGiven' => $record->getTreatsGiven(),
                'brushingDone' => $record->getBrushingDone(),
                'bathOrCleaning' => $record->getBathOrCleaning(),
                'nailsChecked' => $record->getNailsChecked(),
                'earsCleaned' => $record->getEarsCleaned(),
                'coatCondition' => $record->getCoatCondition(),
                'walkingTime' => $record->getWalkingTime(),
                'activityType' => $record->getActivityType(),
                'energyLevel' => $record->getEnergyLevel(),
                'stressed' => $record->getStressed(),
                'unusualSigns' => $record->getUnusualSigns(),
                'moodChanges' => $record->getMoodChanges(),
                'behaviorDetails' => $record->getBehaviorDetails(),
                'obedienceExercises' => $record->getObedienceExercises(),
                'metOtherAnimals' => $record->getMetOtherAnimals(),
                'positiveHumanInteraction' => $record->getPositiveHumanInteraction(),
                'newLearnings' => $record->getNewLearnings(),
                'livingSpaceCleaned' => $record->getLivingSpaceCleaned(),
                'correctTemperature' => $record->getCorrectTemperature(),
                'environmentChanged' => $record->getEnvironmentChanged(),
                'environmentIssues' => $record->getEnvironmentIssues(),
                'medicationGiven' => $record->getMedicationGiven(),
                'supplementsGiven' => $record->getSupplementsGiven(),
                'antiparasiticTreatment' => $record->getAntiparasiticTreatment(),
                'otherSpecificCare' => $record->getOtherSpecificCare(),
                'workedObjective' => $record->getWorkedObjective(),
                'observedProgress' => $record->getObservedProgress(),
                'necessaryAdjustments' => $record->getNecessaryAdjustments(),
                'generalNotes' => $record->getGeneralNotes(),
                'createdAt' => $record->getCreatedAt() ? $record->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $record->getUpdatedAt() ? $record->getUpdatedAt()->format('Y-m-d H:i:s') : null
            ];
        }
        
        return $this->json($recordsData);
    }

    #[Route('', name: 'api_health_records_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Debug: Log les données reçues et l'utilisateur
        error_log('=== HEALTH RECORD CREATE DEBUG ===');
        error_log('Request method: ' . $request->getMethod());
        error_log('Request headers: ' . json_encode($request->headers->all()));
        error_log('Request content: ' . $request->getContent());
        
        $data = json_decode($request->getContent(), true);
        error_log('Parsed data: ' . json_encode($data));
        
        if (!$this->getUser()) {
            error_log('No user authenticated');
            return $this->json([
                'message' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        error_log('User authenticated: ' . $this->getUser()->getEmail());
        
        if (!isset($data['petId']) || !isset($data['date'])) {
            error_log('Missing required fields: petId=' . ($data['petId'] ?? 'NULL') . ', date=' . ($data['date'] ?? 'NULL'));
            return $this->json([
                'message' => 'Pet ID and date are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pet = $entityManager->getRepository(Pet::class)->find($data['petId']);
        error_log('Pet found: ' . ($pet ? $pet->getName() : 'NULL'));
        if ($pet && $pet->getOwner()) {
            error_log('Pet owner: ' . $pet->getOwner()->getEmail());
        }
        
        if (!$pet || $pet->getOwner() !== $this->getUser()) {
            error_log('Access denied: pet not found or owner mismatch');
            return $this->json([
                'message' => 'Pet not found or access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $healthRecord = new HealthRecord();
        $healthRecord->setPet($pet);
        $healthRecord->setDate(new \DateTime($data['date']));
        
        // Set other fields if provided
        if (isset($data['signsOfIllness'])) $healthRecord->setSignsOfIllness($data['signsOfIllness']);
        if (isset($data['fever'])) $healthRecord->setFever($data['fever']);
        if (isset($data['vomiting'])) $healthRecord->setVomiting($data['vomiting']);
        if (isset($data['limping'])) $healthRecord->setLimping($data['limping']);
        if (isset($data['observedInjuries'])) $healthRecord->setObservedInjuries($data['observedInjuries']);
        if (isset($data['otherHealthObservations'])) $healthRecord->setOtherHealthObservations($data['otherHealthObservations']);
        if (isset($data['ateAllMeals'])) $healthRecord->setAteAllMeals($data['ateAllMeals']);
        if (isset($data['appetite'])) $healthRecord->setAppetite($data['appetite']);
        if (isset($data['waterIntake'])) $healthRecord->setWaterIntake($data['waterIntake']);
        if (isset($data['foodsGiven'])) $healthRecord->setFoodsGiven($data['foodsGiven']);
        if (isset($data['treatsGiven'])) $healthRecord->setTreatsGiven($data['treatsGiven']);
        if (isset($data['brushingDone'])) $healthRecord->setBrushingDone($data['brushingDone']);
        if (isset($data['bathOrCleaning'])) $healthRecord->setBathOrCleaning($data['bathOrCleaning']);
        if (isset($data['nailsChecked'])) $healthRecord->setNailsChecked($data['nailsChecked']);
        if (isset($data['earsCleaned'])) $healthRecord->setEarsCleaned($data['earsCleaned']);
        if (isset($data['coatCondition'])) $healthRecord->setCoatCondition($data['coatCondition']);
        if (isset($data['walkingTime'])) $healthRecord->setWalkingTime($data['walkingTime']);
        if (isset($data['activityType'])) $healthRecord->setActivityType($data['activityType']);
        if (isset($data['energyLevel'])) $healthRecord->setEnergyLevel($data['energyLevel']);
        if (isset($data['stressed'])) $healthRecord->setStressed($data['stressed']);
        if (isset($data['unusualSigns'])) $healthRecord->setUnusualSigns($data['unusualSigns']);
        if (isset($data['moodChanges'])) $healthRecord->setMoodChanges($data['moodChanges']);
        if (isset($data['behaviorDetails'])) $healthRecord->setBehaviorDetails($data['behaviorDetails']);
        if (isset($data['obedienceExercises'])) $healthRecord->setObedienceExercises($data['obedienceExercises']);
        if (isset($data['metOtherAnimals'])) $healthRecord->setMetOtherAnimals($data['metOtherAnimals']);
        if (isset($data['positiveHumanInteraction'])) $healthRecord->setPositiveHumanInteraction($data['positiveHumanInteraction']);
        if (isset($data['newLearnings'])) $healthRecord->setNewLearnings($data['newLearnings']);
        if (isset($data['livingSpaceCleaned'])) $healthRecord->setLivingSpaceCleaned($data['livingSpaceCleaned']);
        if (isset($data['correctTemperature'])) $healthRecord->setCorrectTemperature($data['correctTemperature']);
        if (isset($data['environmentChanged'])) $healthRecord->setEnvironmentChanged($data['environmentChanged']);
        if (isset($data['environmentIssues'])) $healthRecord->setEnvironmentIssues($data['environmentIssues']);
        if (isset($data['medicationGiven'])) $healthRecord->setMedicationGiven($data['medicationGiven']);
        if (isset($data['supplementsGiven'])) $healthRecord->setSupplementsGiven($data['supplementsGiven']);
        if (isset($data['antiparasiticTreatment'])) $healthRecord->setAntiparasiticTreatment($data['antiparasiticTreatment']);
        if (isset($data['otherSpecificCare'])) $healthRecord->setOtherSpecificCare($data['otherSpecificCare']);
        if (isset($data['workedObjective'])) $healthRecord->setWorkedObjective($data['workedObjective']);
        if (isset($data['observedProgress'])) $healthRecord->setObservedProgress($data['observedProgress']);
        if (isset($data['necessaryAdjustments'])) $healthRecord->setNecessaryAdjustments($data['necessaryAdjustments']);
        if (isset($data['generalNotes'])) $healthRecord->setGeneralNotes($data['generalNotes']);

        try {
            $entityManager->persist($healthRecord);
            $entityManager->flush();
            
            error_log('Health record created successfully with ID: ' . $healthRecord->getId());
            
            return $this->json([
                'message' => 'Health record created successfully',
                'record' => [
                    'id' => $healthRecord->getId(),
                    'petId' => $healthRecord->getPet()->getId(),
                    'date' => $healthRecord->getDate()->format('Y-m-d')
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            error_log('Error creating health record: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'message' => 'Error creating health record: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_health_records_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $record = $entityManager->getRepository(HealthRecord::class)->find($id);
        
        if (!$record) {
            return $this->json([
                'message' => 'Health record not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier que l'utilisateur a accès à cette record
        if ($record->getPet()->getOwner() !== $user) {
            return $this->json([
                'message' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }
        
        return $this->json([
            'id' => $record->getId(),
            'pet' => [
                'id' => $record->getPet()->getId(),
                'name' => $record->getPet()->getName(),
                'species' => $record->getPet()->getSpecies()
            ],
            'date' => $record->getDate() ? $record->getDate()->format('Y-m-d') : null,
            'signsOfIllness' => $record->getSignsOfIllness(),
            'fever' => $record->getFever(),
            'vomiting' => $record->getVomiting(),
            'limping' => $record->getLimping(),
            'observedInjuries' => $record->getObservedInjuries(),
            'otherHealthObservations' => $record->getOtherHealthObservations(),
            'ateAllMeals' => $record->getAteAllMeals(),
            'appetite' => $record->getAppetite(),
            'waterIntake' => $record->getWaterIntake(),
            'foodsGiven' => $record->getFoodsGiven(),
            'treatsGiven' => $record->getTreatsGiven(),
            'brushingDone' => $record->getBrushingDone(),
            'bathOrCleaning' => $record->getBathOrCleaning(),
            'nailsChecked' => $record->getNailsChecked(),
            'earsCleaned' => $record->getEarsCleaned(),
            'coatCondition' => $record->getCoatCondition(),
            'walkingTime' => $record->getWalkingTime(),
            'activityType' => $record->getActivityType(),
            'energyLevel' => $record->getEnergyLevel(),
            'stressed' => $record->getStressed(),
            'unusualSigns' => $record->getUnusualSigns(),
            'moodChanges' => $record->getMoodChanges(),
            'behaviorDetails' => $record->getBehaviorDetails(),
            'obedienceExercises' => $record->getObedienceExercises(),
            'metOtherAnimals' => $record->getMetOtherAnimals(),
            'positiveHumanInteraction' => $record->getPositiveHumanInteraction(),
            'newLearnings' => $record->getNewLearnings(),
            'livingSpaceCleaned' => $record->getLivingSpaceCleaned(),
            'correctTemperature' => $record->getCorrectTemperature(),
            'environmentChanged' => $record->getEnvironmentChanged(),
            'environmentIssues' => $record->getEnvironmentIssues(),
            'medicationGiven' => $record->getMedicationGiven(),
            'supplementsGiven' => $record->getSupplementsGiven(),
            'antiparasiticTreatment' => $record->getAntiparasiticTreatment(),
            'otherSpecificCare' => $record->getOtherSpecificCare(),
            'workedObjective' => $record->getWorkedObjective(),
            'observedProgress' => $record->getObservedProgress(),
            'necessaryAdjustments' => $record->getNecessaryAdjustments(),
            'generalNotes' => $record->getGeneralNotes(),
            'createdAt' => $record->getCreatedAt() ? $record->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updatedAt' => $record->getUpdatedAt() ? $record->getUpdatedAt()->format('Y-m-d H:i:s') : null
        ]);
    }

    #[Route('/{id}', name: 'api_health_records_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $record = $entityManager->getRepository(HealthRecord::class)->find($id);
            
            if (!$record) {
                return $this->json([
                    'message' => 'Health record not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier que l'utilisateur a accès à cette record
            if ($record->getPet()->getOwner() !== $user) {
                return $this->json([
                    'message' => 'Access denied'
                ], Response::HTTP_FORBIDDEN);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Mise à jour des champs
            if (isset($data['date'])) {
                $record->setDate(new \DateTime($data['date']));
            }
            
            // 1. Santé et bien-être
            if (isset($data['signsOfIllness'])) $record->setSignsOfIllness($data['signsOfIllness']);
            if (isset($data['fever'])) $record->setFever($data['fever']);
            if (isset($data['vomiting'])) $record->setVomiting($data['vomiting']);
            if (isset($data['limping'])) $record->setLimping($data['limping']);
            if (isset($data['observedInjuries'])) $record->setObservedInjuries($data['observedInjuries']);
            if (isset($data['otherHealthObservations'])) $record->setOtherHealthObservations($data['otherHealthObservations']);
            
            // 2. Alimentation
            if (isset($data['ateAllMeals'])) $record->setAteAllMeals($data['ateAllMeals']);
            if (isset($data['appetite'])) $record->setAppetite($data['appetite']);
            if (isset($data['waterIntake'])) $record->setWaterIntake($data['waterIntake']);
            if (isset($data['foodsGiven'])) $record->setFoodsGiven($data['foodsGiven']);
            if (isset($data['treatsGiven'])) $record->setTreatsGiven($data['treatsGiven']);
            
            // 3. Hygiène et toilettage
            if (isset($data['brushingDone'])) $record->setBrushingDone($data['brushingDone']);
            if (isset($data['bathOrCleaning'])) $record->setBathOrCleaning($data['bathOrCleaning']);
            if (isset($data['nailsChecked'])) $record->setNailsChecked($data['nailsChecked']);
            if (isset($data['earsCleaned'])) $record->setEarsCleaned($data['earsCleaned']);
            if (isset($data['coatCondition'])) $record->setCoatCondition($data['coatCondition']);
            
            // 4. Activité physique
            if (isset($data['walkingTime'])) $record->setWalkingTime($data['walkingTime']);
            if (isset($data['activityType'])) $record->setActivityType($data['activityType']);
            if (isset($data['energyLevel'])) $record->setEnergyLevel($data['energyLevel']);
            
            // 5. Comportement
            if (isset($data['stressed'])) $record->setStressed($data['stressed']);
            if (isset($data['unusualSigns'])) $record->setUnusualSigns($data['unusualSigns']);
            if (isset($data['moodChanges'])) $record->setMoodChanges($data['moodChanges']);
            if (isset($data['behaviorDetails'])) $record->setBehaviorDetails($data['behaviorDetails']);
            
            // 6. Éducation et socialisation
            if (isset($data['obedienceExercises'])) $record->setObedienceExercises($data['obedienceExercises']);
            if (isset($data['metOtherAnimals'])) $record->setMetOtherAnimals($data['metOtherAnimals']);
            if (isset($data['positiveHumanInteraction'])) $record->setPositiveHumanInteraction($data['positiveHumanInteraction']);
            if (isset($data['newLearnings'])) $record->setNewLearnings($data['newLearnings']);
            
            // 7. Environnement
            if (isset($data['livingSpaceCleaned'])) $record->setLivingSpaceCleaned($data['livingSpaceCleaned']);
            if (isset($data['correctTemperature'])) $record->setCorrectTemperature($data['correctTemperature']);
            if (isset($data['environmentChanged'])) $record->setEnvironmentChanged($data['environmentChanged']);
            if (isset($data['environmentIssues'])) $record->setEnvironmentIssues($data['environmentIssues']);
            
            // 8. Suivi des traitements
            if (isset($data['medicationGiven'])) $record->setMedicationGiven($data['medicationGiven']);
            if (isset($data['supplementsGiven'])) $record->setSupplementsGiven($data['supplementsGiven']);
            if (isset($data['antiparasiticTreatment'])) $record->setAntiparasiticTreatment($data['antiparasiticTreatment']);
            if (isset($data['otherSpecificCare'])) $record->setOtherSpecificCare($data['otherSpecificCare']);
            
            // 9. Objectifs et progrès
            if (isset($data['workedObjective'])) $record->setWorkedObjective($data['workedObjective']);
            if (isset($data['observedProgress'])) $record->setObservedProgress($data['observedProgress']);
            if (isset($data['necessaryAdjustments'])) $record->setNecessaryAdjustments($data['necessaryAdjustments']);
            
            // Note générale
            if (isset($data['generalNotes'])) $record->setGeneralNotes($data['generalNotes']);
            
            if (isset($data['petId'])) {
                $pet = $entityManager->getRepository(Pet::class)->find($data['petId']);
                if (!$pet || $pet->getOwner() !== $user) {
                    return $this->json([
                        'message' => 'Pet not found or access denied'
                    ], Response::HTTP_FORBIDDEN);
                }
                $record->setPet($pet);
            }

            $entityManager->flush();

            return $this->json([
                'message' => 'Health record updated successfully',
                'record' => [
                    'id' => $record->getId(),
                    'pet' => [
                        'id' => $record->getPet()->getId(),
                        'name' => $record->getPet()->getName()
                    ],
                    'date' => $record->getDate() ? $record->getDate()->format('Y-m-d') : null,
                    'signsOfIllness' => $record->getSignsOfIllness(),
                    'fever' => $record->getFever(),
                    'vomiting' => $record->getVomiting(),
                    'limping' => $record->getLimping(),
                    'observedInjuries' => $record->getObservedInjuries(),
                    'otherHealthObservations' => $record->getOtherHealthObservations(),
                    'ateAllMeals' => $record->getAteAllMeals(),
                    'appetite' => $record->getAppetite(),
                    'waterIntake' => $record->getWaterIntake(),
                    'foodsGiven' => $record->getFoodsGiven(),
                    'treatsGiven' => $record->getTreatsGiven(),
                    'brushingDone' => $record->getBrushingDone(),
                    'bathOrCleaning' => $record->getBathOrCleaning(),
                    'nailsChecked' => $record->getNailsChecked(),
                    'earsCleaned' => $record->getEarsCleaned(),
                    'coatCondition' => $record->getCoatCondition(),
                    'walkingTime' => $record->getWalkingTime(),
                    'activityType' => $record->getActivityType(),
                    'energyLevel' => $record->getEnergyLevel(),
                    'stressed' => $record->getStressed(),
                    'unusualSigns' => $record->getUnusualSigns(),
                    'moodChanges' => $record->getMoodChanges(),
                    'behaviorDetails' => $record->getBehaviorDetails(),
                    'obedienceExercises' => $record->getObedienceExercises(),
                    'metOtherAnimals' => $record->getMetOtherAnimals(),
                    'positiveHumanInteraction' => $record->getPositiveHumanInteraction(),
                    'newLearnings' => $record->getNewLearnings(),
                    'livingSpaceCleaned' => $record->getLivingSpaceCleaned(),
                    'correctTemperature' => $record->getCorrectTemperature(),
                    'environmentChanged' => $record->getEnvironmentChanged(),
                    'environmentIssues' => $record->getEnvironmentIssues(),
                    'medicationGiven' => $record->getMedicationGiven(),
                    'supplementsGiven' => $record->getSupplementsGiven(),
                    'antiparasiticTreatment' => $record->getAntiparasiticTreatment(),
                    'otherSpecificCare' => $record->getOtherSpecificCare(),
                    'workedObjective' => $record->getWorkedObjective(),
                    'observedProgress' => $record->getObservedProgress(),
                    'necessaryAdjustments' => $record->getNecessaryAdjustments(),
                    'generalNotes' => $record->getGeneralNotes()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while updating the health record',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_health_records_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $record = $entityManager->getRepository(HealthRecord::class)->find($id);
            
            if (!$record) {
                return $this->json([
                    'message' => 'Health record not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier que l'utilisateur a accès à cette record
            if ($record->getPet()->getOwner() !== $user) {
                return $this->json([
                    'message' => 'Access denied'
                ], Response::HTTP_FORBIDDEN);
            }
            
            $entityManager->remove($record);
            $entityManager->flush();

            return $this->json([
                'message' => 'Health record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while deleting the health record',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 