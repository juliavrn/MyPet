<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/user', name: 'api_user_info', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserInfo(): JsonResponse
    {
        $user = $this->getUser();
        
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);
    }
}
