<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\BlogPost;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
        private BlogPostRepository $blogPostRepository,
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

    #[Route('', name: 'api_comments_options', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        $response = new JsonResponse();
        return $this->addCorsHeaders($response);
    }

    #[Route('', name: 'api_comments_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['content']) || !isset($data['blogPostId'])) {
            return new JsonResponse(['error' => 'Contenu et blogPostId requis'], 400);
        }

        $blogPost = $this->blogPostRepository->find($data['blogPostId']);
        if (!$blogPost) {
            return new JsonResponse(['error' => 'Article de blog non trouvé'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setBlogPost($blogPost);
        $comment->setAuthor($user);
        $comment->setIsApproved(true); // Approuver automatiquement pour les utilisateurs connectés

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // Retourner le commentaire créé
        $commentData = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('c'),
            'author' => [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ],
            'isApproved' => $comment->isIsApproved()
        ];

        return $this->addCorsHeaders(new JsonResponse($commentData, 201));
    }

    #[Route('/blog/{id}', name: 'api_comments_by_blog', methods: ['GET'])]
    public function getByBlogPost(int $id): JsonResponse
    {
        $blogPost = $this->blogPostRepository->find($id);
        if (!$blogPost) {
            return new JsonResponse(['error' => 'Article de blog non trouvé'], 404);
        }

        $comments = $this->commentRepository->findBy(
            ['blogPost' => $blogPost, 'isApproved' => true],
            ['createdAt' => 'DESC']
        );

        $commentsData = array_map(function (Comment $comment) {
            return [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('c'),
                'author' => [
                    'id' => $comment->getAuthor()->getId(),
                    'firstName' => $comment->getAuthor()->getFirstName(),
                    'lastName' => $comment->getAuthor()->getLastName()
                ],
                'isApproved' => $comment->isIsApproved()
            ];
        }, $comments);

        return $this->addCorsHeaders(new JsonResponse($commentsData));
    }

    #[Route('/{id}', name: 'api_comments_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Vérifier que l'utilisateur est l'auteur du commentaire ou un admin
        if ($comment->getAuthor()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['content'])) {
            $comment->setContent($data['content']);
            $comment->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire mis à jour avec succès']));
    }

    #[Route('/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Vérifier que l'utilisateur est l'auteur du commentaire ou un admin
        if ($comment->getAuthor()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire supprimé avec succès']));
    }

    #[Route('/{id}/approve', name: 'api_comments_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approve(int $id): JsonResponse
    {
        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Commentaire non trouvé'], 404);
        }

        $comment->setIsApproved(true);
        $this->entityManager->flush();

        return $this->addCorsHeaders(new JsonResponse(['message' => 'Commentaire approuvé avec succès']));
    }
}
