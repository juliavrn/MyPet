<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Crée un utilisateur administrateur',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un utilisateur administrateur');

        // Vérifier si l'utilisateur admin existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@mypet.com']);
        
        if ($existingUser) {
            $io->warning('Un utilisateur avec l\'email admin@mypet.com existe déjà.');
            
            if ($existingUser->isIsAdmin()) {
                $io->success('Cet utilisateur est déjà administrateur.');
            } else {
                $io->info('Mise à jour des privilèges administrateur...');
                $existingUser->setIsAdmin(true);
                $existingUser->setCanPublish(true);
                $existingUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
                
                $this->entityManager->flush();
                $io->success('Privilèges administrateur accordés avec succès !');
            }
            
            return Command::SUCCESS;
        }

        // Créer un nouvel utilisateur admin
        $user = new User();
        $user->setFirstName('Admin');
        $user->setLastName('MyPet');
        $user->setEmail('admin@mypet.com');
        $user->setIsAdmin(true);
        $user->setCanPublish(true);
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin123');
        $user->setPassword($hashedPassword);

        // Persister l'utilisateur
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success([
            'Utilisateur administrateur créé avec succès !',
            '',
            'Identifiants de connexion :',
            'Email: admin@mypet.com',
            'Mot de passe: admin123',
            '',
            'Privilèges:',
            '- Administrateur: OUI',
            '- Publication: OUI',
            '- Rôles: ROLE_ADMIN, ROLE_USER'
        ]);

        return Command::SUCCESS;
    }
}
