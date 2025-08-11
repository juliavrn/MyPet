<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Repository\PetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PetRepository::class)]
// API Platform désactivé pour la sécurité - Utilisation des contrôleurs personnalisés
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pet:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $species = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $breed = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 10)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $gender = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $color = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?string $photo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'pets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['pet:read', 'pet:write'])]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'pet', targetEntity: Calendar::class)]
    #[Groups(['pet:read'])]
    private Collection $calendars;

    #[ORM\OneToMany(mappedBy: 'pet', targetEntity: HealthRecord::class)]
    #[Groups(['pet:read'])]
    private Collection $healthRecords;

    #[ORM\OneToMany(mappedBy: 'pet', targetEntity: Checklist::class, orphanRemoval: true)]
    #[Groups(['pet:read'])]
    private Collection $checklists;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->calendars = new ArrayCollection();
        $this->healthRecords = new ArrayCollection();
        $this->checklists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSpecies(): ?string
    {
        return $this->species;
    }

    public function setSpecies(string $species): static
    {
        $this->species = $species;
        return $this;
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(?string $breed): static
    {
        $this->breed = $breed;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return Collection<int, Calendar>
     */
    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

    public function addCalendar(Calendar $calendar): static
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars->add($calendar);
            $calendar->setPet($this);
        }
        return $this;
    }

    public function removeCalendar(Calendar $calendar): static
    {
        if ($this->calendars->removeElement($calendar)) {
            if ($calendar->getPet() === $this) {
                $calendar->setPet(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, HealthRecord>
     */
    public function getHealthRecords(): Collection
    {
        return $this->healthRecords;
    }

    public function addHealthRecord(HealthRecord $healthRecord): static
    {
        if (!$this->healthRecords->contains($healthRecord)) {
            $this->healthRecords->add($healthRecord);
            $healthRecord->setPet($this);
        }
        return $this;
    }

    public function removeHealthRecord(HealthRecord $healthRecord): static
    {
        if ($this->healthRecords->removeElement($healthRecord)) {
            if ($healthRecord->getPet() === $this) {
                $healthRecord->setPet(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Checklist>
     */
    public function getChecklists(): Collection
    {
        return $this->checklists;
    }

    public function addChecklist(Checklist $checklist): static
    {
        if (!$this->checklists->contains($checklist)) {
            $this->checklists->add($checklist);
            $checklist->setPet($this);
        }

        return $this;
    }

    public function removeChecklist(Checklist $checklist): static
    {
        if ($this->checklists->removeElement($checklist)) {
            // set the owning side to null (unless already changed)
            if ($checklist->getPet() === $this) {
                $checklist->setPet(null);
            }
        }

        return $this;
    }
}
