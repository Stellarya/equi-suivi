<?php

namespace App\Tests\Controller;

use App\Entity\AppUser;
use App\Entity\Rider;
use App\Repository\RiderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RiderProfileControllerTest extends WebTestCase
{
    public function testIndexDisplaysConnectedRiderProfile(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager, 'Marie', 'Test');

        $client->loginUser($user);
        $client->request('GET', '/rider/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#rider');
        self::assertAnySelectorTextContains('.rider-info-card', 'Marie');
        self::assertAnySelectorTextContains('.rider-info-card', 'Test');
    }

    public function testEditProfileUpdatesConnectedRider(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager, 'Marie', 'AncienNom');

        $client->loginUser($user);

        $crawler = $client->request('GET', '/rider/profile');

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="rider"]')->form([
            'rider[firstName]' => 'Marie',
            'rider[lastName]' => 'NouveauNom',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/rider/profile');

        $riderRepository = static::getContainer()->get(RiderRepository::class);

        $updatedRider = $riderRepository->findOneBy([
            'appUser' => $user,
        ]);

        self::assertNotNull($updatedRider);
        self::assertSame('Marie', $updatedRider->getFirstName());
        self::assertSame('NouveauNom', $updatedRider->getLastName());
    }

    private function createRiderUser(
        EntityManagerInterface $entityManager,
        string $firstName,
        string $lastName
    ): AppUser {
        $user = new AppUser();
        $user->setEmail(sprintf('rider-profile-%s@example.com', uniqid()));
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test-password');

        $rider = new Rider();
        $rider->setFirstName($firstName);
        $rider->setLastName($lastName);
        $rider->setAppUser($user);

        $user->setRider($rider);

        $entityManager->persist($user);
        $entityManager->persist($rider);
        $entityManager->flush();

        return $user;
    }
}