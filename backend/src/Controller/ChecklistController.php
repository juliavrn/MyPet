<?php

namespace App\Controller;

use App\Entity\Checklist;
use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/checklist')]
#[IsGranted('ROLE_USER')]
class ChecklistController extends AbstractController
{
    #[Route('', name: 'api_checklist_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer les paramètres de filtrage
        $petId = $request->query->get('pet');
        $completed = $request->query->get('completed');
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
           ->from(Checklist::class, 'c')
           ->join('c.pet', 'p')
           ->where('p.owner = :user')
           ->setParameter('user', $user);
        
        // Filtrage par animal
        if ($petId) {
            $qb->andWhere('c.pet = :petId')
               ->setParameter('petId', $petId);
        }
        
        // Filtrage par statut
        if ($completed !== null) {
            $qb->andWhere('c.isCompleted = :completed')
               ->setParameter('completed', $completed === 'true');
        }
        
        $qb->orderBy('c.dueDate', 'ASC');
        
        $checklists = $qb->getQuery()->getResult();
        
        $checklistsData = [];
        foreach ($checklists as $checklist) {
            $checklistsData[] = [
                'id' => $checklist->getId(),
                'title' => $checklist->getTitle(),
                'description' => $checklist->getDescription(),
                'isCompleted' => $checklist->isIsCompleted(),
                'priority' => $checklist->getPriority(),
                'dueDate' => $checklist->getDueDate() ? $checklist->getDueDate()->format('Y-m-d H:i:s') : null,
                'createdAt' => $checklist->getCreatedAt() ? $checklist->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $checklist->getUpdatedAt() ? $checklist->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'pet' => [
                    'id' => $checklist->getPet()->getId(),
                    'name' => $checklist->getPet()->getName(),
                    'species' => $checklist->getPet()->getSpecies()
                ]
            ];
        }
        
        return $this->json($checklistsData);
    }

    #[Route('', name: 'api_checklist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validation des champs requis
            $requiredFields = ['title', 'dueDate', 'petId'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'message' => sprintf('Field "%s" is required', $field)
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Vérifier que l'animal appartient à l'utilisateur
            $pet = $entityManager->getRepository(Pet::class)->find($data['petId']);
            if (!$pet || $pet->getOwner() !== $this->getUser()) {
                return $this->json([
                    'message' => 'Pet not found or access denied'
                ], Response::HTTP_FORBIDDEN);
            }

            $checklist = new Checklist();
            $checklist->setTitle($data['title']);
            $checklist->setDescription($data['description'] ?? null);
            $checklist->setDueDate(new \DateTime($data['dueDate']));
            $checklist->setIsCompleted($data['isCompleted'] ?? false);
            $checklist->setPriority($data['priority'] ?? 'medium');
            $checklist->setPet($pet);

            $entityManager->persist($checklist);
            $entityManager->flush();

            return $this->json([
                'message' => 'Checklist created successfully',
                'checklist' => [
                    'id' => $checklist->getId(),
                    'title' => $checklist->getTitle(),
                    'description' => $checklist->getDescription(),
                    'isCompleted' => $checklist->isIsCompleted(),
                    'priority' => $checklist->getPriority(),
                    'dueDate' => $checklist->getDueDate() ? $checklist->getDueDate()->format('Y-m-d H:i:s') : null,
                    'pet' => [
                        'id' => $checklist->getPet()->getId(),
                        'name' => $checklist->getPet()->getName()
                    ]
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while creating the checklist',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_checklist_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $checklist = $entityManager->getRepository(Checklist::class)->find($id);
        
        if (!$checklist) {
            return $this->json([
                'message' => 'Checklist not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier que l'utilisateur a accès à cette checklist
        if ($checklist->getPet()->getOwner() !== $user) {
            return $this->json([
                'message' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }
        
        return $this->json([
            'id' => $checklist->getId(),
            'title' => $checklist->getTitle(),
            'description' => $checklist->getDescription(),
            'isCompleted' => $checklist->isIsCompleted(),
            'priority' => $checklist->getPriority(),
            'dueDate' => $checklist->getDueDate() ? $checklist->getDueDate()->format('Y-m-d H:i:s') : null,
            'createdAt' => $checklist->getCreatedAt() ? $checklist->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updatedAt' => $checklist->getUpdatedAt() ? $checklist->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            'pet' => [
                'id' => $checklist->getPet()->getId(),
                'name' => $checklist->getPet()->getName(),
                'species' => $checklist->getPet()->getSpecies()
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_checklist_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $checklist = $entityManager->getRepository(Checklist::class)->find($id);
            
            if (!$checklist) {
                return $this->json([
                    'message' => 'Checklist not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier que l'utilisateur a accès à cette checklist
            if ($checklist->getPet()->getOwner() !== $user) {
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

            if (isset($data['title'])) {
                $checklist->setTitle($data['title']);
            }
            
            if (isset($data['description'])) {
                $checklist->setDescription($data['description']);
            }
            
            if (isset($data['dueDate'])) {
                $checklist->setDueDate(new \DateTime($data['dueDate']));
            }
            
            if (isset($data['isCompleted'])) {
                $checklist->setIsCompleted($data['isCompleted']);
            }
            
            if (isset($data['priority'])) {
                $checklist->setPriority($data['priority']);
            }
            
            if (isset($data['petId'])) {
                $pet = $entityManager->getRepository(Pet::class)->find($data['petId']);
                if (!$pet || $pet->getOwner() !== $user) {
                    return $this->json([
                        'message' => 'Pet not found or access denied'
                    ], Response::HTTP_FORBIDDEN);
                }
                $checklist->setPet($pet);
            }

            $checklist->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->json([
                'message' => 'Checklist updated successfully',
                'checklist' => [
                    'id' => $checklist->getId(),
                    'title' => $checklist->getTitle(),
                    'description' => $checklist->getDescription(),
                    'isCompleted' => $checklist->isIsCompleted(),
                    'priority' => $checklist->getPriority(),
                    'dueDate' => $checklist->getDueDate() ? $checklist->getDueDate()->format('Y-m-d H:i:s') : null,
                    'pet' => [
                        'id' => $checklist->getPet()->getId(),
                        'name' => $checklist->getPet()->getName()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while updating the checklist',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_checklist_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $checklist = $entityManager->getRepository(Checklist::class)->find($id);
            
            if (!$checklist) {
                return $this->json([
                    'message' => 'Checklist not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier que l'utilisateur a accès à cette checklist
            if ($checklist->getPet()->getOwner() !== $user) {
                return $this->json([
                    'message' => 'Access denied'
                ], Response::HTTP_FORBIDDEN);
            }
            
            $entityManager->remove($checklist);
            $entityManager->flush();

            return $this->json([
                'message' => 'Checklist deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while deleting the checklist',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/toggle', name: 'api_checklist_toggle', methods: ['PATCH'])]
    public function toggle(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $checklist = $entityManager->getRepository(Checklist::class)->find($id);
            
            if (!$checklist) {
                return $this->json([
                    'message' => 'Checklist not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier que l'utilisateur a accès à cette checklist
            if ($checklist->getPet()->getOwner() !== $user) {
                return $this->json([
                    'message' => 'Access denied'
                ], Response::HTTP_FORBIDDEN);
            }
            
            $checklist->setIsCompleted(!$checklist->isIsCompleted());
            $checklist->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->json([
                'message' => 'Checklist status updated successfully',
                'isCompleted' => $checklist->isIsCompleted()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while updating the checklist status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 