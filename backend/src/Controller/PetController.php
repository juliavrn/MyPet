<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pets')]
class PetController extends AbstractController
{
    private function addCorsHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
        return $response;
    }

    #[Route('', name: 'api_pets_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $pets = $entityManager->getRepository(Pet::class)->findBy(['owner' => $user]);
        
        $petsData = [];
        foreach ($pets as $pet) {
            $petsData[] = [
                'id' => $pet->getId(),
                'name' => $pet->getName(),
                'species' => $pet->getSpecies(),
                'breed' => $pet->getBreed(),
                'birthDate' => $pet->getBirthDate() ? $pet->getBirthDate()->format('Y-m-d') : null,
                'gender' => $pet->getGender(),
                'color' => $pet->getColor(),
                'description' => $pet->getDescription(),
                'photo' => $pet->getPhoto(),
                'createdAt' => $pet->getCreatedAt() ? $pet->getCreatedAt()->format('Y-m-d H:i:s') : null
            ];
        }
        
        return $this->addCorsHeaders($this->json($petsData));
    }

    #[Route('', name: 'api_pets_create', methods: ['POST'])]
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
            $requiredFields = ['name', 'species', 'birthDate', 'gender'];
            $fieldNames = [
                'name' => 'nom',
                'species' => 'espèce',
                'birthDate' => 'date de naissance',
                'gender' => 'genre'
            ];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'message' => sprintf('Le champ "%s" est obligatoire', $fieldNames[$field])
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $pet = new Pet();
            $pet->setName($data['name']);
            $pet->setSpecies($data['species']);
            $pet->setBreed($data['breed'] ?? null);
            $pet->setBirthDate(new \DateTime($data['birthDate']));
            $pet->setGender($data['gender']);
            $pet->setColor($data['color'] ?? null);
            $pet->setDescription($data['description'] ?? null);
            $pet->setPhoto($data['photo'] ?? null);
            $pet->setOwner($this->getUser());

            $entityManager->persist($pet);
            $entityManager->flush();

            return $this->addCorsHeaders($this->json([
                'message' => 'Animal créé avec succès',
                'pet' => [
                    'id' => $pet->getId(),
                    'name' => $pet->getName(),
                    'species' => $pet->getSpecies(),
                    'breed' => $pet->getBreed(),
                    'birthDate' => $pet->getBirthDate() ? $pet->getBirthDate()->format('Y-m-d') : null,
                    'gender' => $pet->getGender(),
                    'color' => $pet->getColor(),
                    'description' => $pet->getDescription(),
                    'photo' => $pet->getPhoto()
                ]
            ], Response::HTTP_CREATED));
        } catch (\Exception $e) {
            return $this->addCorsHeaders($this->json([
                'message' => 'Une erreur est survenue lors de la création de l\'animal',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    #[Route('/{id}', name: 'api_pets_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $pet = $entityManager->getRepository(Pet::class)->findOneBy([
            'id' => $id,
            'owner' => $user
        ]);

        if (!$pet) {
            return $this->addCorsHeaders($this->json([
                'message' => 'Pet not found'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->addCorsHeaders($this->json([
            'id' => $pet->getId(),
            'name' => $pet->getName(),
            'species' => $pet->getSpecies(),
            'breed' => $pet->getBreed(),
            'birthDate' => $pet->getBirthDate() ? $pet->getBirthDate()->format('Y-m-d') : null,
            'gender' => $pet->getGender(),
            'color' => $pet->getColor(),
            'description' => $pet->getDescription(),
            'photo' => $pet->getPhoto(),
            'createdAt' => $pet->getCreatedAt() ? $pet->getCreatedAt()->format('Y-m-d H:i:s') : null
        ]));
    }

    #[Route('/{id}', name: 'api_pets_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $pet = $entityManager->getRepository(Pet::class)->findOneBy([
                'id' => $id,
                'owner' => $user
            ]);

            if (!$pet) {
                return $this->json([
                    'message' => 'Pet not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['name'])) $pet->setName($data['name']);
            if (isset($data['species'])) $pet->setSpecies($data['species']);
            if (isset($data['breed'])) $pet->setBreed($data['breed']);
            if (isset($data['birthDate'])) $pet->setBirthDate(new \DateTime($data['birthDate']));
            if (isset($data['gender'])) $pet->setGender($data['gender']);
            if (isset($data['color'])) $pet->setColor($data['color']);
            if (isset($data['description'])) $pet->setDescription($data['description']);
            if (isset($data['photo'])) $pet->setPhoto($data['photo']);

            $pet->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->addCorsHeaders($this->json([
                'message' => 'Pet updated successfully',
                'pet' => [
                    'id' => $pet->getId(),
                    'name' => $pet->getName(),
                    'species' => $pet->getSpecies(),
                    'breed' => $pet->getBreed(),
                    'birthDate' => $pet->getBirthDate() ? $pet->getBirthDate()->format('Y-m-d') : null,
                    'gender' => $pet->getGender(),
                    'color' => $pet->getColor(),
                    'description' => $pet->getDescription(),
                    'photo' => $pet->getPhoto()
                ]
            ]));
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while updating the pet',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_pets_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            $pet = $entityManager->getRepository(Pet::class)->findOneBy([
                'id' => $id,
                'owner' => $user
            ]);

            if (!$pet) {
                return $this->json([
                    'message' => 'Pet not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($pet);
            $entityManager->flush();

            return $this->addCorsHeaders($this->json([
                'message' => 'Pet deleted successfully'
            ]));
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while deleting the pet',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 