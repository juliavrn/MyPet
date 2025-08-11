<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/upload')]
class FileUploadController extends AbstractController
{
    private function addCorsHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
        return $response;
    }

    #[Route('/image', name: 'api_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $uploadedFile = $request->files->get('image');
            
            if (!$uploadedFile) {
                return $this->json([
                    'message' => 'Aucun fichier fourni'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier le type de fichier
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
                return $this->json([
                    'message' => 'Type de fichier non autorisé. Seuls les fichiers JPG et PNG sont acceptés.'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier la taille (max 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($uploadedFile->getSize() > $maxSize) {
                return $this->json([
                    'message' => 'Fichier trop volumineux. Taille maximale : 5MB'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Générer un nom de fichier unique
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = uniqid() . '_' . time() . '.' . $extension;
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Déplacer le fichier
            $uploadedFile->move($uploadDir, $filename);
            
            // Retourner l'URL du fichier (absolue)
            $request = $this->container->get('request_stack')->getCurrentRequest();
            $baseUrl = $request->getSchemeAndHttpHost();
            $fileUrl = $baseUrl . '/uploads/images/' . $filename;

            // Debug
            error_log("URL générée: " . $fileUrl);

            return $this->addCorsHeaders($this->json([
                'message' => 'Fichier téléversé avec succès',
                'url' => $fileUrl,
                'filename' => $filename
            ]));

        } catch (\Exception $e) {
            return $this->addCorsHeaders($this->json([
                'message' => 'Erreur lors du téléversement du fichier',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
} 