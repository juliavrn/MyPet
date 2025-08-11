<?php

// Serveur web complet pour MyPet
use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Symfony\Component\Dotenv\Dotenv;
$uri = $_SERVER['REQUEST_URI'];

// Si c'est une image, la servir directement AVEC headers CORS spécifiques
if (strpos($uri, '/uploads/images/') === 0) {
    // Headers CORS uniquement pour les images
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Gérer les requêtes OPTIONS (preflight CORS) pour les images
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    $filePath = __DIR__ . '/public' . $uri;
    
    if (is_file($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'JPG' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit();
    }
}

// Pour toutes les autres routes (API), rediriger vers Symfony sans headers CORS
// Symfony gérera ses propres headers CORS via nelmio_cors.yaml

// Si c'est une route API, charger Symfony et laisser Symfony gérer la réponse
if (strpos($uri, '/api/') === 0) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        
        // Charger les variables d'environnement
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__ . '/.env');
        
        // Créer le kernel
        $kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
        $kernel->boot();
        
        // Créer la requête HTTP
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        
        // Traiter la requête avec Symfony
        $response = $kernel->handle($request);
        
        // Envoyer la réponse
        $response->send();
        
        // Terminer le kernel
        $kernel->terminate($request, $response);
        
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Erreur serveur: ' . $e->getMessage()]);
    }
    exit();
}

// Si c'est une route blog/posts, utiliser la logique personnalisée
if (strpos($uri, '/blog/posts') === 0) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        
        // Charger les variables d'environnement
        $dotenv = new Dotenv();
        $dotenv->loadEnv(__DIR__ . '/.env');
        
        // Créer le kernel
        $kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
        $kernel->boot();
        
        // Récupérer l'EntityManager
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        
        header('Content-Type: application/json');
        
        // Route pour récupérer un article par ID
        if (preg_match('/^\/blog\/posts\/(\d+)$/', $uri, $matches)) {
            $id = $matches[1];
            
            $blogPostRepository = $entityManager->getRepository(BlogPost::class);
            $blogPost = $blogPostRepository->find($id);
            
            if (!$blogPost || !$blogPost->isIsPublished()) {
                http_response_code(404);
                echo json_encode(['message' => 'Article non trouvé']);
                exit();
            }
            
            // Préparer les données de l'article
            $postData = [
                'id' => $blogPost->getId(),
                'title' => $blogPost->getTitle(),
                'content' => $blogPost->getContent(),
                'image' => $blogPost->getImage(),
                'createdAt' => $blogPost->getCreatedAt()->format('c'),
                'updatedAt' => $blogPost->getUpdatedAt() ? $blogPost->getUpdatedAt()->format('c') : null,
                'isPublished' => $blogPost->isIsPublished(),
                'author' => $blogPost->getAuthor() ? [
                    'id' => $blogPost->getAuthor()->getId(),
                    'firstName' => $blogPost->getAuthor()->getFirstName(),
                    'lastName' => $blogPost->getAuthor()->getLastName(),
                ] : null,
                'category' => $blogPost->getCategory() ?? 'general',
                'likes' => $blogPost->getLikesCount(),
                'commentsCount' => $blogPost->getApprovedCommentsCount(),
            ];
            
            echo json_encode($postData, JSON_UNESCAPED_UNICODE);
            
        } 
        // Route pour lister les articles
        elseif ($uri === '/blog/posts') {
            $blogPostRepository = $entityManager->getRepository(BlogPost::class);
            $posts = $blogPostRepository->findBy(['isPublished' => true], ['createdAt' => 'DESC']);
            
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
            
            echo json_encode([
                'posts' => $postsData,
                'total' => count($posts)
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Erreur serveur: ' . $e->getMessage()]);
    }
    exit();
}

// Si c'est un fichier statique dans public, le servir
$filePath = __DIR__ . '/public' . $uri;
if (is_file($filePath)) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'html' => 'text/html',
        'htm' => 'text/html',
        'txt' => 'text/plain',
        'pdf' => 'application/pdf'
    ];
    
    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }
    
    readfile($filePath);
    exit();
}

// Si c'est un dossier, essayer index.php
if (is_dir($filePath)) {
    $indexFile = $filePath . '/index.php';
    if (is_file($indexFile)) {
        require_once $indexFile;
        exit();
    }
}

// Sinon, essayer index.php à la racine
$indexFile = __DIR__ . '/public/index.php';
if (is_file($indexFile)) {
    require_once $indexFile;
    exit();
}

// 404 si rien n'est trouvé
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<h1>404 - Page non trouvée</h1>';
echo '<p>La page demandée n\'existe pas.</p>';
echo '<p>URI: ' . htmlspecialchars($uri) . '</p>';
echo '<p>Fichiers disponibles dans uploads/images:</p>';
echo '<ul>';
$imagesDir = __DIR__ . '/public/uploads/images';
if (is_dir($imagesDir)) {
    $files = scandir($imagesDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo '<li><a href="/uploads/images/' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a></li>';
        }
    }
}
echo '</ul>';
