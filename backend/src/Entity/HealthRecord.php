<?php

namespace App\Entity;

use App\Repository\HealthRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HealthRecordRepository::class)]
// API Platform désactivé pour la sécurité - Utilisation des contrôleurs personnalisés
class HealthRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['health_record:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'healthRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?Pet $pet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?\DateTimeInterface $date = null;

    // 1. Santé et bien-être
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $signsOfIllness = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $fever = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $vomiting = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $limping = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $observedInjuries = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $otherHealthObservations = null;

    // 2. Alimentation
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $ateAllMeals = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $appetite = null; // Très bon / Normal / Faible

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $waterIntake = null; // Normale / Faible / Élevée

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $foodsGiven = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $treatsGiven = null;

    // 3. Hygiène et toilettage
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $brushingDone = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $bathOrCleaning = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $nailsChecked = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $earsCleaned = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $coatCondition = null; // Propre / Sali / Perte de poils excessive

    // 4. Activité physique
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?int $walkingTime = null; // En minutes

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $activityType = null; // Promenade / Jeu / Entraînement / Sport

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $energyLevel = null; // Faible / Normal / Élevé

    // 5. Comportement
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $stressed = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $unusualSigns = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $moodChanges = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $behaviorDetails = null;

    // 6. Éducation et socialisation
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $obedienceExercises = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $metOtherAnimals = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $positiveHumanInteraction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $newLearnings = null;

    // 7. Environnement
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $livingSpaceCleaned = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $correctTemperature = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $environmentChanged = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $environmentIssues = null;

    // 8. Suivi des traitements
    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $medicationGiven = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $supplementsGiven = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?bool $antiparasiticTreatment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $otherSpecificCare = null;

    // 9. Objectifs et progrès
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $workedObjective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $observedProgress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $necessaryAdjustments = null;

    // Note libre générale
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['health_record:read', 'health_record:write'])]
    private ?string $generalNotes = null;

    #[ORM\Column]
    #[Groups(['health_record:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['health_record:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): static
    {
        $this->pet = $pet;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    // Getters et setters pour tous les champs
    public function getSignsOfIllness(): ?bool
    {
        return $this->signsOfIllness;
    }

    public function setSignsOfIllness(?bool $signsOfIllness): static
    {
        $this->signsOfIllness = $signsOfIllness;
        return $this;
    }

    public function getFever(): ?bool
    {
        return $this->fever;
    }

    public function setFever(?bool $fever): static
    {
        $this->fever = $fever;
        return $this;
    }

    public function getVomiting(): ?bool
    {
        return $this->vomiting;
    }

    public function setVomiting(?bool $vomiting): static
    {
        $this->vomiting = $vomiting;
        return $this;
    }

    public function getLimping(): ?bool
    {
        return $this->limping;
    }

    public function setLimping(?bool $limping): static
    {
        $this->limping = $limping;
        return $this;
    }

    public function getObservedInjuries(): ?string
    {
        return $this->observedInjuries;
    }

    public function setObservedInjuries(?string $observedInjuries): static
    {
        $this->observedInjuries = $observedInjuries;
        return $this;
    }

    public function getOtherHealthObservations(): ?string
    {
        return $this->otherHealthObservations;
    }

    public function setOtherHealthObservations(?string $otherHealthObservations): static
    {
        $this->otherHealthObservations = $otherHealthObservations;
        return $this;
    }

    public function getAteAllMeals(): ?bool
    {
        return $this->ateAllMeals;
    }

    public function setAteAllMeals(?bool $ateAllMeals): static
    {
        $this->ateAllMeals = $ateAllMeals;
        return $this;
    }

    public function getAppetite(): ?string
    {
        return $this->appetite;
    }

    public function setAppetite(?string $appetite): static
    {
        $this->appetite = $appetite;
        return $this;
    }

    public function getWaterIntake(): ?string
    {
        return $this->waterIntake;
    }

    public function setWaterIntake(?string $waterIntake): static
    {
        $this->waterIntake = $waterIntake;
        return $this;
    }

    public function getFoodsGiven(): ?string
    {
        return $this->foodsGiven;
    }

    public function setFoodsGiven(?string $foodsGiven): static
    {
        $this->foodsGiven = $foodsGiven;
        return $this;
    }

    public function getTreatsGiven(): ?bool
    {
        return $this->treatsGiven;
    }

    public function setTreatsGiven(?bool $treatsGiven): static
    {
        $this->treatsGiven = $treatsGiven;
        return $this;
    }

    public function getBrushingDone(): ?bool
    {
        return $this->brushingDone;
    }

    public function setBrushingDone(?bool $brushingDone): static
    {
        $this->brushingDone = $brushingDone;
        return $this;
    }

    public function getBathOrCleaning(): ?bool
    {
        return $this->bathOrCleaning;
    }

    public function setBathOrCleaning(?bool $bathOrCleaning): static
    {
        $this->bathOrCleaning = $bathOrCleaning;
        return $this;
    }

    public function getNailsChecked(): ?bool
    {
        return $this->nailsChecked;
    }

    public function setNailsChecked(?bool $nailsChecked): static
    {
        $this->nailsChecked = $nailsChecked;
        return $this;
    }

    public function getEarsCleaned(): ?bool
    {
        return $this->earsCleaned;
    }

    public function setEarsCleaned(?bool $earsCleaned): static
    {
        $this->earsCleaned = $earsCleaned;
        return $this;
    }

    public function getCoatCondition(): ?string
    {
        return $this->coatCondition;
    }

    public function setCoatCondition(?string $coatCondition): static
    {
        $this->coatCondition = $coatCondition;
        return $this;
    }

    public function getWalkingTime(): ?int
    {
        return $this->walkingTime;
    }

    public function setWalkingTime(?int $walkingTime): static
    {
        $this->walkingTime = $walkingTime;
        return $this;
    }

    public function getActivityType(): ?string
    {
        return $this->activityType;
    }

    public function setActivityType(?string $activityType): static
    {
        $this->activityType = $activityType;
        return $this;
    }

    public function getEnergyLevel(): ?string
    {
        return $this->energyLevel;
    }

    public function setEnergyLevel(?string $energyLevel): static
    {
        $this->energyLevel = $energyLevel;
        return $this;
    }

    public function getStressed(): ?bool
    {
        return $this->stressed;
    }

    public function setStressed(?bool $stressed): static
    {
        $this->stressed = $stressed;
        return $this;
    }

    public function getUnusualSigns(): ?bool
    {
        return $this->unusualSigns;
    }

    public function setUnusualSigns(?bool $unusualSigns): static
    {
        $this->unusualSigns = $unusualSigns;
        return $this;
    }

    public function getMoodChanges(): ?bool
    {
        return $this->moodChanges;
    }

    public function setMoodChanges(?bool $moodChanges): static
    {
        $this->moodChanges = $moodChanges;
        return $this;
    }

    public function getBehaviorDetails(): ?string
    {
        return $this->behaviorDetails;
    }

    public function setBehaviorDetails(?string $behaviorDetails): static
    {
        $this->behaviorDetails = $behaviorDetails;
        return $this;
    }

    public function getObedienceExercises(): ?bool
    {
        return $this->obedienceExercises;
    }

    public function setObedienceExercises(?bool $obedienceExercises): static
    {
        $this->obedienceExercises = $obedienceExercises;
        return $this;
    }

    public function getMetOtherAnimals(): ?bool
    {
        return $this->metOtherAnimals;
    }

    public function setMetOtherAnimals(?bool $metOtherAnimals): static
    {
        $this->metOtherAnimals = $metOtherAnimals;
        return $this;
    }

    public function getPositiveHumanInteraction(): ?bool
    {
        return $this->positiveHumanInteraction;
    }

    public function setPositiveHumanInteraction(?bool $positiveHumanInteraction): static
    {
        $this->positiveHumanInteraction = $positiveHumanInteraction;
        return $this;
    }

    public function getNewLearnings(): ?string
    {
        return $this->newLearnings;
    }

    public function setNewLearnings(?string $newLearnings): static
    {
        $this->newLearnings = $newLearnings;
        return $this;
    }

    public function getLivingSpaceCleaned(): ?bool
    {
        return $this->livingSpaceCleaned;
    }

    public function setLivingSpaceCleaned(?bool $livingSpaceCleaned): static
    {
        $this->livingSpaceCleaned = $livingSpaceCleaned;
        return $this;
    }

    public function getCorrectTemperature(): ?bool
    {
        return $this->correctTemperature;
    }

    public function setCorrectTemperature(?bool $correctTemperature): static
    {
        $this->correctTemperature = $correctTemperature;
        return $this;
    }

    public function getEnvironmentChanged(): ?bool
    {
        return $this->environmentChanged;
    }

    public function setEnvironmentChanged(?bool $environmentChanged): static
    {
        $this->environmentChanged = $environmentChanged;
        return $this;
    }

    public function getEnvironmentIssues(): ?string
    {
        return $this->environmentIssues;
    }

    public function setEnvironmentIssues(?string $environmentIssues): static
    {
        $this->environmentIssues = $environmentIssues;
        return $this;
    }

    public function getMedicationGiven(): ?bool
    {
        return $this->medicationGiven;
    }

    public function setMedicationGiven(?bool $medicationGiven): static
    {
        $this->medicationGiven = $medicationGiven;
        return $this;
    }

    public function getSupplementsGiven(): ?bool
    {
        return $this->supplementsGiven;
    }

    public function setSupplementsGiven(?bool $supplementsGiven): static
    {
        $this->supplementsGiven = $supplementsGiven;
        return $this;
    }

    public function getAntiparasiticTreatment(): ?bool
    {
        return $this->antiparasiticTreatment;
    }

    public function setAntiparasiticTreatment(?bool $antiparasiticTreatment): static
    {
        $this->antiparasiticTreatment = $antiparasiticTreatment;
        return $this;
    }

    public function getOtherSpecificCare(): ?string
    {
        return $this->otherSpecificCare;
    }

    public function setOtherSpecificCare(?string $otherSpecificCare): static
    {
        $this->otherSpecificCare = $otherSpecificCare;
        return $this;
    }

    public function getWorkedObjective(): ?string
    {
        return $this->workedObjective;
    }

    public function setWorkedObjective(?string $workedObjective): static
    {
        $this->workedObjective = $workedObjective;
        return $this;
    }

    public function getObservedProgress(): ?string
    {
        return $this->observedProgress;
    }

    public function setObservedProgress(?string $observedProgress): static
    {
        $this->observedProgress = $observedProgress;
        return $this;
    }

    public function getNecessaryAdjustments(): ?string
    {
        return $this->necessaryAdjustments;
    }

    public function setNecessaryAdjustments(?string $necessaryAdjustments): static
    {
        $this->necessaryAdjustments = $necessaryAdjustments;
        return $this;
    }

    public function getGeneralNotes(): ?string
    {
        return $this->generalNotes;
    }

    public function setGeneralNotes(?string $generalNotes): static
    {
        $this->generalNotes = $generalNotes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
