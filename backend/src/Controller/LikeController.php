<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/likes')]
class LikeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LikeRepository $likeRepository
    ) {}

    #[Route('/toggle/{blogPostId}', name: 'api_like_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleLike(int $blogPostId, Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            if (!$user) {
                return $this->json([
                    'message' => 'User not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $blogPost = $this->entityManager->getRepository(BlogPost::class)->find($blogPostId);
            
            if (!$blogPost) {
                return $this->json([
                    'message' => 'Blog post not found'
                ], Response::HTTP_NOT_FOUND);
            }

            // Vérifier si l'utilisateur a déjà liké cet article
            $existingLike = $this->likeRepository->findOneBy([
                'user' => $user,
                'blogPost' => $blogPost
            ]);

            if ($existingLike) {
                // Supprimer le like existant
                $this->entityManager->remove($existingLike);
                $this->entityManager->flush();
                
                return $this->json([
                    'message' => 'Like removed',
                    'liked' => false,
                    'likesCount' => $this->likeRepository->count(['blogPost' => $blogPost])
                ], Response::HTTP_OK);
            } else {
                // Créer un nouveau like
                $like = new Like();
                $like->setUser($user);
                $like->setBlogPost($blogPost);
                
                $this->entityManager->persist($like);
                $this->entityManager->flush();
                
                return $this->json([
                    'message' => 'Like added',
                    'liked' => true,
                    'likesCount' => $this->likeRepository->count(['blogPost' => $blogPost])
                ], Response::HTTP_CREATED);
            }
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while toggling like',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/count/{blogPostId}', name: 'api_like_count', methods: ['GET'])]
    public function getLikeCount(int $blogPostId): JsonResponse
    {
        try {
            $blogPost = $this->entityManager->getRepository(BlogPost::class)->find($blogPostId);
            
            if (!$blogPost) {
                return $this->json([
                    'message' => 'Blog post not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $likesCount = $this->likeRepository->count(['blogPost' => $blogPost]);
            
            return $this->json([
                'likesCount' => $likesCount
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while getting like count',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/status/{blogPostId}', name: 'api_like_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getLikeStatus(int $blogPostId): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            
            if (!$user) {
                return $this->json([
                    'message' => 'User not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $blogPost = $this->entityManager->getRepository(BlogPost::class)->find($blogPostId);
            
            if (!$blogPost) {
                return $this->json([
                    'message' => 'Blog post not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $existingLike = $this->likeRepository->findOneBy([
                'user' => $user,
                'blogPost' => $blogPost
            ]);

            $likesCount = $this->likeRepository->count(['blogPost' => $blogPost]);
            
            return $this->json([
                'liked' => $existingLike !== null,
                'likesCount' => $likesCount
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while getting like status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'api_likes_options', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        $response = new JsonResponse();
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }
}
