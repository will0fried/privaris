<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée (ou réinitialise) un compte administrateur.
 *
 * Utile en production, où les fixtures ne sont pas chargées :
 *   php bin/console app:create-admin
 */
#[AsCommand(name: 'app:create-admin', description: 'Crée ou réinitialise un administrateur du back-office.')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $io->ask('E-mail de l\'administrateur', 'admin@privaris.fr');
        $name = (string) $io->ask('Nom affiché', 'Will');
        $password = (string) $io->askHidden('Mot de passe');

        if ('' === trim($password)) {
            $io->error('Le mot de passe ne peut pas être vide.');

            return Command::FAILURE;
        }

        $user = $this->users->findOneBy(['email' => $email]) ?? new User();
        $user->setEmail($email)
            ->setDisplayName($name)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Administrateur « %s » enregistré. Connexion : /connexion', $email));

        return Command::SUCCESS;
    }
}
