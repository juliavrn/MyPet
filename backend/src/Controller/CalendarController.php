<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/calendar')]
class CalendarController extends AbstractController
{
    private function addCorsHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
        return $response;
    }

    #[Route('', name: 'api_calendar_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer les paramètres de filtrage
        $startDate = $request->query->get('start');
        $endDate = $request->query->get('end');
        $petId = $request->query->get('pet');
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
           ->from(Calendar::class, 'c')
           ->join('c.pet', 'p')
           ->where('p.owner = :user')
           ->setParameter('user', $user);
        
        // Filtrage par date
        if ($startDate) {
            $qb->andWhere('c.startDate >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate));
        }
        
        if ($endDate) {
            $qb->andWhere('c.startDate <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate));
        }
        
        // Filtrage par animal
        if ($petId) {
            $qb->andWhere('c.pet = :petId')
               ->setParameter('petId', $petId);
        }
        
        $qb->orderBy('c.startDate', 'ASC');
        
        $events = $qb->getQuery()->getResult();
        
        $eventsData = [];
        foreach ($events as $event) {
            $eventsData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'startDate' => $event->getStartDate()->format('Y-m-d\TH:i:s'), 
                'endDate' => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d\TH:i:s') : null,
                'type' => $event->getType(),
                'pet' => [
                    'id' => $event->getPet()->getId(),
                    'name' => $event->getPet()->getName(),
                    'species' => $event->getPet()->getSpecies()
                ],
                'createdAt' => $event->getCreatedAt() ? $event->getCreatedAt()->format('Y-m-d\TH:i:s') : null
            ];
        }
        
        return $this->addCorsHeaders($this->json($eventsData));
    }

    #[Route('', name: 'api_calendar_create', methods: ['POST'])]
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
            $requiredFields = ['title', 'startDate', 'type', 'petId'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'message' => sprintf('Field "%s" is required', $field)
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Vérifier que l'animal appartient à l'utilisateur
            $pet = $entityManager->getRepository(Pet::class)->findOneBy([
                'id' => $data['petId'],
                'owner' => $this->getUser()
            ]);

            if (!$pet) {
                return $this->json([
                    'message' => 'Pet not found or access denied'
                ], Response::HTTP_NOT_FOUND);
            }

            // Validation et conversion des dates
            try {
                // Gérer le format datetime-local (YYYY-MM-DDTHH:MM) et ISO 8601
                $startDateStr = $data['startDate'];
                if (strpos($startDateStr, 'T') !== false) {
                    // Format ISO ou datetime-local, traiter comme date locale
                    $startDate = new \DateTime($startDateStr);
                    // Ne pas convertir en UTC, garder la date locale
                } else {
                    // Format classique
                    $startDate = new \DateTime($startDateStr);
                }
            } catch (\Exception $e) {
                return $this->json([
                    'message' => 'Format de date de début invalide. Format attendu: Y-m-d H:i:s ou ISO 8601',
                    'received' => $data['startDate']
                ], Response::HTTP_BAD_REQUEST);
            }

            $endDate = null;
            if (isset($data['endDate']) && !empty($data['endDate'])) {
                try {
                    $endDateStr = $data['endDate'];
                    if (strpos($endDateStr, 'T') !== false) {
                        $endDate = new \DateTime($endDateStr);
                    } else {
                        // Format classique
                        $endDate = new \DateTime($endDateStr);
                    }
                } catch (\Exception $e) {
                    return $this->json([
                        'message' => 'Format de date de fin invalide. Format attendu: Y-m-d H:i:s ou ISO 8601',
                        'received' => $data['endDate']
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $event = new Calendar();
            $event->setTitle($data['title']);
            $event->setDescription($data['description'] ?? null);
            $event->setStartDate($startDate);
            $event->setEndDate($endDate);
            $event->setType($data['type']);
            $event->setPet($pet);

            $entityManager->persist($event);
            $entityManager->flush();

            $eventData = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'startDate' => $event->getStartDate()->format('Y-m-d\TH:i:s'), 
                'endDate' => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d\TH:i:s') : null,
                'type' => $event->getType(),
                'pet' => [
                    'id' => $event->getPet()->getId(),
                    'name' => $event->getPet()->getName(),
                    'species' => $event->getPet()->getSpecies()
                ],
                'createdAt' => $event->getCreatedAt() ? $event->getCreatedAt()->format('Y-m-d\TH:i:s') : null
            ];

            return $this->addCorsHeaders($this->json($eventData, Response::HTTP_CREATED));
        } catch (\Exception $e) {
            return $this->addCorsHeaders($this->json([
                'message' => 'An error occurred while creating the event',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    #[Route('/{id}', name: 'api_calendar_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $event = $entityManager->getRepository(Calendar::class)->createQueryBuilder('c')
            ->join('c.pet', 'p')
            ->where('c.id = :id')
            ->andWhere('p.owner = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$event) {
            return $this->addCorsHeaders($this->json([
                'message' => 'Event not found'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->addCorsHeaders($this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'start' => $event->getStartDate()->format('c'), // Format ISO 8601 avec fuseau horaire
            'end' => $event->getEndDate() ? $event->getEndDate()->format('c') : null,
            'type' => $event->getType(),
            'pet' => [
                'id' => $event->getPet()->getId(),
                'name' => $event->getPet()->getName(),
                'species' => $event->getPet()->getSpecies()
            ],
            'createdAt' => $event->getCreatedAt() ? $event->getCreatedAt()->format('c') : null
        ]));
    }

    #[Route('/{id}', name: 'api_calendar_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $event = $entityManager->getRepository(Calendar::class)->createQueryBuilder('c')
                ->join('c.pet', 'p')
                ->where('c.id = :id')
                ->andWhere('p.owner = :user')
                ->setParameter('id', $id)
                ->setParameter('user', $user)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$event) {
                return $this->json([
                    'message' => 'Evénement non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['title'])) $event->setTitle($data['title']);
            if (isset($data['description'])) $event->setDescription($data['description']);
            if (isset($data['startDate'])) $event->setStartDate(new \DateTime($data['startDate']));
            if (isset($data['endDate'])) $event->setEndDate(new \DateTime($data['endDate']));
            if (isset($data['type'])) $event->setType($data['type']);

            $event->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->json([
                'message' => 'Evénement mis à jour avec succès',
                'event' => [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'description' => $event->getDescription(),
                    'start' => $event->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d H:i:s') : null,
                    'type' => $event->getType(),
                    'pet' => [
                        'id' => $event->getPet()->getId(),
                        'name' => $event->getPet()->getName()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'événement',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_calendar_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $event = $entityManager->getRepository(Calendar::class)->createQueryBuilder('c')
                ->join('c.pet', 'p')
                ->where('c.id = :id')
                ->andWhere('p.owner = :user')
                ->setParameter('id', $id)
                ->setParameter('user', $user)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$event) {
                return $this->json([
                    'message' => 'Event not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($event);
            $entityManager->flush();

            return $this->json([
                'message' => 'Evénement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Une erreur est survenue lors de la suppression de l\'événement',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 