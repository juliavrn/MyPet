<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérification des champs requis
            $requiredFields = ['email', 'password', 'firstName', 'lastName'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $fieldNames = [
                        'email' => 'email',
                        'password' => 'mot de passe',
                        'firstName' => 'prénom',
                        'lastName' => 'nom'
                    ];
                    return $this->json([
                        'message' => sprintf('Le champ "%s" est obligatoire', $fieldNames[$field])
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Validation de l'email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'message' => 'Format d\'email invalide'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validation de la longueur du mot de passe
            if (strlen($data['password']) < 6) {
                return $this->json([
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier si l'email existe déjà
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json([
                    'message' => 'Un compte avec cet email existe déjà'
                ], Response::HTTP_CONFLICT);
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setRoles(['ROLE_USER']);

            // Validation de l'entité
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return $this->json([
                'message' => 'Un compte avec cet email existe déjà'
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Email and password are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (!$user) {
                return $this->json([
                    'message' => 'Invalid credentials'
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
                return $this->json([
                    'message' => 'Invalid credentials'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([
                'token' => $JWTManager->create($user),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || empty($data['email'])) {
                return $this->json([
                    'message' => 'Email is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (!$user) {
                // Pour des raisons de sécurité, on ne révèle pas si l'email existe ou non
                return $this->json([
                    'message' => 'If an account with this email exists, a reset link has been sent.'
                ], Response::HTTP_OK);
            }

            // Générer un token de reset
            $resetToken = $tokenGenerator->generateToken();
            $user->setResetToken($resetToken);
            $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
            
            $entityManager->flush();

            // Envoyer l'email
            $email = (new Email())
                ->from('noreply@mypet.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe - MyPet')
                ->html($this->renderView('emails/reset_password.html.twig', [
                    'user' => $user,
                    'resetToken' => $resetToken
                ]));

            $mailer->send($email);

            return $this->json([
                'message' => 'If an account with this email exists, a reset link has been sent.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while processing your request'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['token']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'Token and password are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $data['token']]);

            if (!$user) {
                return $this->json([
                    'message' => 'Invalid or expired reset token'
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
                return $this->json([
                    'message' => 'Reset token has expired'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Mettre à jour le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            
            $entityManager->flush();

            return $this->json([
                'message' => 'Password has been reset successfully'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while resetting your password'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'message' => 'User not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'isAdmin' => $user->isIsAdmin(),
            'canPublish' => $user->isCanPublish(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('c'),
            'updatedAt' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('c') : null
        ]);
    }

    #[Route('/api/user/profile', name: 'api_update_profile', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'message' => 'User not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'message' => 'Invalid JSON data'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Mettre à jour les champs de base
            if (isset($data['firstName'])) {
                $user->setFirstName($data['firstName']);
            }
            
            if (isset($data['lastName'])) {
                $user->setLastName($data['lastName']);
            }
            
            if (isset($data['email'])) {
                // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    return $this->json([
                        'message' => 'Email already in use'
                    ], Response::HTTP_CONFLICT);
                }
                $user->setEmail($data['email']);
            }

            // Mettre à jour le mot de passe si fourni
            if (isset($data['password']) && !empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    return $this->json([
                        'message' => 'Password must be at least 6 characters long'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            }

            // Mettre à jour la date de modification
            $user->setUpdatedAt(new \DateTimeImmutable());

            // Validation de l'entité
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->flush();

            return $this->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'isAdmin' => $user->isIsAdmin(),
                    'canPublish' => $user->isCanPublish(),
                    'roles' => $user->getRoles(),
                    'createdAt' => $user->getCreatedAt()->format('c'),
                    'updatedAt' => $user->getUpdatedAt()->format('c')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An error occurred while updating profile',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 