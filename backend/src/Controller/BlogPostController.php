<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
class BlogPostController extends AbstractController
{
    #[Route('/posts/{id}', name: 'api_blog_post_show', methods: ['GET'])]
    public function show(BlogPost $post): JsonResponse
    {
        // Vérifier si l'article est publié
        if (!$post->isIsPublished()) {
            return $this->json([
                'message' => 'Article non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        // Préparer les données de l'article
        $postData = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'image' => $post->getImage(),
            'createdAt' => $post->getCreatedAt()->format('c'),
            'updatedAt' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('c') : null,
            'isPublished' => $post->isIsPublished(),
            'author' => $post->getAuthor() ? [
                'id' => $post->getAuthor()->getId(),
                'firstName' => $post->getAuthor()->getFirstName(),
                'lastName' => $post->getAuthor()->getLastName(),
            ] : null,
            'category' => $post->getCategory() ?? 'general',
            'likes' => $post->getLikesCount(),
            'commentsCount' => $post->getApprovedCommentsCount(),
        ];

        return $this->json($postData);
    }

    #[Route('/posts', name: 'api_blog_posts_list', methods: ['GET'])]
    public function list(BlogPostRepository $blogPostRepository, Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        // Construire les critères de recherche
        $criteria = ['isPublished' => true];
        if ($category) {
            $criteria['category'] = $category;
        }

        // Récupérer les articles publiés
        $posts = $blogPostRepository->findBy($criteria, ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);

        // Préparer les données
        $postsData = array_map(function (BlogPost $post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'image' => $post->getImage(),
                'createdAt' => $post->getCreatedAt()->format('c'),
                'author' => $post->getAuthor() ? [
                    'firstName' => $post->getAuthor()->getFirstName(),
                    'lastName' => $post->getAuthor()->getLastName(),
                ] : null,
                'category' => $post->getCategory() ?? 'general',
                'likes' => $post->getLikesCount(),
                'commentsCount' => $post->getApprovedCommentsCount(),
            ];
        }, $posts);

        return $this->json([
            'posts' => $postsData,
            'total' => count($posts),
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/posts', name: 'api_blog_post_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'message' => 'Données invalides'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation des données requises
        if (empty($data['title']) || empty($data['content'])) {
            return $this->json([
                'message' => 'Le titre et le contenu sont requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Créer l'article
        $post = new BlogPost();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setImage($data['image'] ?? null);
        $post->setCategory($data['category'] ?? 'general');
        $post->setIsPublished($data['isPublished'] ?? false);
        $post->setAuthor($this->getUser());

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Article créé avec succès',
            'id' => $post->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/posts/{id}', name: 'api_blog_post_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(BlogPost $post, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier que l'utilisateur est l'auteur ou un admin
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'message' => 'Accès non autorisé'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }
        if (isset($data['image'])) {
            $post->setImage($data['image']);
        }
        if (isset($data['category'])) {
            $post->setCategory($data['category']);
        }
        if (isset($data['isPublished'])) {
            $post->setIsPublished($data['isPublished']);
        }

        $post->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json([
            'message' => 'Article mis à jour avec succès'
        ]);
    }

    #[Route('/posts/{id}', name: 'api_blog_post_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(BlogPost $post, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier que l'utilisateur est l'auteur ou un admin
        if ($post->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'message' => 'Accès non autorisé'
            ], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Article supprimé avec succès'
        ]);
    }
}
