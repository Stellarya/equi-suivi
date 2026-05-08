<?php

namespace App\Tests\Controller;

use App\Entity\AppUser;
use App\Entity\Galop;
use App\Entity\Rider;
use App\Repository\RiderGalopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RiderGalopControllerTest extends WebTestCase
{
    public function testAddGalopToConnectedRider(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = new AppUser();
        $user->setEmail(sprintf('rider-galop-%s@example.com', uniqid()));
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test-password');

        $rider = new Rider();
        $rider->setFirstName('Marie');
        $rider->setLastName('Test');
        $rider->setAppUser($user);

        $user->setRider($rider);

        $galop = new Galop();
        $galop->setMnemonique(sprintf('GALOP_TEST_%s', uniqid()));
        $galop->setLibelle('Galop test');
        $galop->setEstActif(true);

        $entityManager->persist($user);
        $entityManager->persist($rider);
        $entityManager->persist($galop);
        $entityManager->flush();

        $client->loginUser($user);

        $crawler = $client->request('GET', '/rider/profile');

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="rider_galop"]')->form([
            'rider_galop[galop]' => $galop->getId(),
            'rider_galop[obtainedYear]' => 2024,
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/rider/profile');

        $riderGalopRepository = static::getContainer()->get(RiderGalopRepository::class);

        $createdRiderGalop = $riderGalopRepository->findOneBy([
            'rider' => $rider,
            'galop' => $galop,
        ]);

        self::assertNotNull($createdRiderGalop);
        self::assertSame(2024, $createdRiderGalop->getObtainedYear());
    }
}