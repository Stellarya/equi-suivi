<?php

namespace App\Command;

use App\Entity\AppUser;
use App\Entity\Rider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-rider-user',
    description: 'Create an AppUser account linked to a Rider profile',
)]
class CreateRiderUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('firstName', InputArgument::REQUIRED, 'Rider first name')
            ->addArgument('lastName', InputArgument::REQUIRED, 'Rider last name')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Create admin user instead of rider')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');
        $firstName = (string) $input->getArgument('firstName');
        $lastName = (string) $input->getArgument('lastName');
        $isAdmin = (bool) $input->getOption('admin');

        $existingUser = $this->entityManager
            ->getRepository(AppUser::class)
            ->findOneBy(['email' => $email]);
        if($existingUser !== null) {
            $output->writeln(sprintf('<error>User "$s" already exists.</error>', $email));
        
            return Command::FAILURE;
        }

        $appUser = new AppUser();
        $appUser->setEmail($email);
        $appUser->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $appUser->setPassword($this->passwordHasher->hashPassword($appUser, $password));

        $rider = new Rider();
        $rider->setFirstName($firstName);
        $rider->setLastName($lastName);
        $rider->setAppUser($appUser);

        $this->entityManager->persist($appUser);
        $this->entityManager->persist($rider);
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Rider user "$s" created successfully.</info>', $email));

        return Command::SUCCESS;
    }
}
