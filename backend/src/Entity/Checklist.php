<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Repository\ChecklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChecklistRepository::class)]
// API Platform désactivé pour la sécurité - Utilisation des contrôleurs personnalisés
class Checklist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['checklist:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?bool $isCompleted = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?string $priority = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['checklist:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['checklist:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'checklists')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['checklist:read', 'checklist:write'])]
    private ?Pet $pet = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isCompleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function isIsCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
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
}
