<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\BlogPostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private BlogPostRepository $blogPostRepository,
        private CommentRepository $commentRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer
    ) {}

    private function addCorsHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }

    #[Route('', name: 'api_admin_options', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        $response = new JsonResponse();
        return $this->addCorsHeaders($response);
    }

    #[Route('/stats', name: 'api_admin_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $userStats = $this->userRepository->getUserStats();
        $blogStats = $this->blogPostRepository->getBlogPostStats();
        $commentStats = $this->commentRepository->getCommentStats();

        $stats = [
            'users' => $userStats,
            'blogPosts' => $blogStats,
            'comments' => $commentStats
        ];

        return $this->addCorsHeaders(new JsonResponse($stats));
    }

    #[Route('/users', name: 'api_admin_users_list', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        
        $usersData = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'isAdmin' => $user->isIsAdmin(),
                'canPublish' => $user->isCanPublish(),
                'createdAt' => $user->getCreatedAt()->format('c'),
                'updatedAt' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('c') : null,
                'petsCount' => $user->getPets()->count(),
                'blogPostsCount' => $user->getBlogPosts()->count()
            ];
        }, $users);

        return $this->addCorsHeaders(new JsonResponse($usersData));
    }

    #[Route('/users', name: 'api_admin_users_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['firstName']) || !isset($data['lastName'])) {
            return new JsonResponse(['error' => 'Email, mot de passe, prénom et nom requis'], 400);
        }

        // Vérifier si l'email existe déjà
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setIsAdmin($data['isAdmin'] ?? false);
        $user->setCanPublish($data['canPublish'] ?? false);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse([
            'message' => 'Utilisateur créé avec succès',
            'id' => $user->getId()
        ], 201));
    }

    #[Route('/users/{id}', name: 'api_admin_users_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['isAdmin'])) {
            $user->setIsAdmin($data['isAdmin']);
        }
        if (isset($data['canPublish'])) {
            $user->setCanPublish($data['canPublish']);
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Utilisateur mis à jour avec succès']));
    }

    #[Route('/users/{id}', name: 'api_admin_users_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Empêcher la suppression de l'admin principal
        if ($user->getEmail() === 'admin@mypet.com') {
            return new JsonResponse(['error' => 'Impossible de supprimer l\'administrateur principal'], 403);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Utilisateur supprimé avec succès']));
    }

    #[Route('/blog-posts', name: 'api_admin_blog_posts_list', methods: ['GET'])]
    public function getBlogPosts(): JsonResponse
    {
        $blogPosts = $this->blogPostRepository->findAllWithAuthorAndStats();
        
        $blogPostsData = array_map(function (BlogPost $post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'category' => $post->getCategory(),
                'image' => $post->getImage(),
                'isPublished' => $post->isIsPublished(),
                'createdAt' => $post->getCreatedAt()->format('c'),
                'updatedAt' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('c') : null,
                'author' => [
                    'id' => $post->getAuthor()->getId(),
                    'firstName' => $post->getAuthor()->getFirstName(),
                    'lastName' => $post->getAuthor()->getLastName(),
                    'email' => $post->getAuthor()->getEmail()
                ],
                'commentsCount' => $post->getComments()->count(),
                'likesCount' => $post->getLikes()->count()
            ];
        }, $blogPosts);

        return $this->addCorsHeaders(new JsonResponse($blogPostsData));
    }

    #[Route('/blog-posts', name: 'api_admin_blog_posts_create', methods: ['POST'])]
    public function createBlogPost(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['title']) || !isset($data['content'])) {
            return new JsonResponse(['error' => 'Titre et contenu requis'], 400);
        }

        $blogPost = new BlogPost();
        $blogPost->setTitle($data['title']);
        $blogPost->setContent($data['content']);
        $blogPost->setCategory($data['category'] ?? null);
        $blogPost->setImage($data['image'] ?? null);
        $blogPost->setIsPublished($data['isPublished'] ?? false);
        $blogPost->setAuthor($this->getUser());

        $this->entityManager->persist($blogPost);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse([
            'message' => 'Article créé avec succès',
            'id' => $blogPost->getId()
        ], 201));
    }

    #[Route('/blog-posts/{id}', name: 'api_admin_blog_posts_update', methods: ['PUT'])]
    public function updateBlogPost(int $id, Request $request): JsonResponse
    {
        $blogPost = $this->blogPostRepository->find($id);
        if (!$blogPost) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['title'])) {
            $blogPost->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $blogPost->setContent($data['content']);
        }
        if (isset($data['category'])) {
            $blogPost->setCategory($data['category']);
        }
        if (isset($data['image'])) {
            $blogPost->setImage($data['image']);
        }
        if (isset($data['isPublished'])) {
            $blogPost->setIsPublished($data['isPublished']);
        }

        $blogPost->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Article mis à jour avec succès']));
    }

    #[Route('/blog-posts/{id}', name: 'api_admin_blog_posts_delete', methods: ['DELETE'])]
    public function deleteBlogPost(int $id): JsonResponse
    {
        $blogPost = $this->blogPostRepository->find($id);
        if (!$blogPost) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Article supprimé avec succès']));
    }

    #[Route('/comments', name: 'api_admin_comments_list', methods: ['GET'])]
    public function getComments(): JsonResponse
    {
        $comments = $this->commentRepository->findAllWithAuthorAndBlogPost();
        
        $commentsData = array_map(function (Comment $comment) {
            return [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'isApproved' => $comment->isIsApproved(),
                'createdAt' => $comment->getCreatedAt()->format('c'),
                'author' => [
                    'id' => $comment->getAuthor()->getId(),
                    'firstName' => $comment->getAuthor()->getFirstName(),
                    'lastName' => $comment->getAuthor()->getLastName(),
                    'email' => $comment->getAuthor()->getEmail()
                ],
                'blogPost' => [
                    'id' => $comment->getBlogPost()->getId(),
                    'title' => $comment->getBlogPost()->getTitle()
                ]
            ];
        }, $comments);

        return $this->addCorsHeaders(new JsonResponse($commentsData));
    }

    #[Route('/comments/{id}/approve', name: 'api_admin_comments_approve', methods: ['POST'])]
    public function approveComment(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $comment->setIsApproved(true);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire approuvé avec succès']));
    }

    #[Route('/comments/{id}/reject', name: 'api_admin_comments_reject', methods: ['POST'])]
    public function rejectComment(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $comment->setIsApproved(false);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire rejeté avec succès']));
    }

    #[Route('/comments/{id}', name: 'api_admin_comments_delete', methods: ['DELETE'])]
    public function deleteComment(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire supprimé avec succès']));
    }
} 