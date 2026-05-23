<?php

namespace App\Command;

use App\Entity\AppUser;
use App\Entity\Ranch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-ranch-user',
    description: 'Create an AppUser account with ROLE_ECURIE linked to a Ranch',
)]
class CreateRanchUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('ranchName', InputArgument::REQUIRED, 'The name of the Ranch/Stable')
            ->addArgument('address', InputArgument::REQUIRED, 'The physical address of the Ranch')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');
        $ranchName = (string) $input->getArgument('ranchName');
        $address = (string) $input->getArgument('address');

        // 1. Verification if user already exists
        $existingUser = $this->entityManager
            ->getRepository(AppUser::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser !== null) {
            $io->error(sprintf('User "%s" already exists.', $email));
            return Command::FAILURE;
        }

        // 2. Create user with ROLE_ECURIE
        $appUser = new AppUser();
        $appUser->setEmail($email);
        $appUser->setRoles(['ROLE_ECURIE']);
        $appUser->setPassword($this->passwordHasher->hashPassword($appUser, $password));

        // 3. Create the associated Ranch entity
        $ranch = new Ranch();
        $ranch->setName($ranchName);
        $ranch->setAddress($address);
        $ranch->setOwner($appUser);

        // 4. Cascade save
        $this->entityManager->persist($appUser);
        $this->entityManager->persist($ranch);
        $this->entityManager->flush();

        $io->success(sprintf('Ranch user "%s" and stable "%s" created successfully.', $email, $ranchName));

        return Command::SUCCESS;
    }
}