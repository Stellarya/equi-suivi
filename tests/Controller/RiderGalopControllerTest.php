<?php

namespace App\Tests\Controller;

use App\Entity\AppUser;
use App\Entity\Galop;
use App\Entity\Rider;
use App\Entity\RiderGalop;
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

    public function testEditGalopUpdatesConnectedRiderGalop(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = new AppUser();
        $user->setEmail(sprintf('rider-galop-edit-%s@example.com', uniqid()));
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test-password');

        $rider = new Rider();
        $rider->setFirstName('Marie');
        $rider->setLastName('Test');
        $rider->setAppUser($user);

        $user->setRider($rider);

        $oldGalop = new Galop();
        $oldGalop->setMnemonique(sprintf('GALOP_OLD_%s', uniqid()));
        $oldGalop->setLibelle('Galop ancien');
        $oldGalop->setEstActif(true);

        $newGalop = new Galop();
        $newGalop->setMnemonique(sprintf('GALOP_NEW_%s', uniqid()));
        $newGalop->setLibelle('Galop nouveau');
        $newGalop->setEstActif(true);

        $riderGalop = new RiderGalop();
        $riderGalop->setGalop($oldGalop);
        $riderGalop->setObtainedYear(2020);

        $rider->addGalopHistory($riderGalop);

        $entityManager->persist($user);
        $entityManager->persist($rider);
        $entityManager->persist($oldGalop);
        $entityManager->persist($newGalop);
        $entityManager->persist($riderGalop);
        $entityManager->flush();

        $client->loginUser($user);

        $crawler = $client->request('GET', '/rider/profile');

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filterXPath(sprintf(
            '//form[contains(@action, "/rider/galop/%d/edit")]',
            $riderGalop->getId()
        ));

        self::assertCount(1, $formCrawler);

        $form = $formCrawler->form([
            'rider_galop[galop]' => $newGalop->getId(),
            'rider_galop[obtainedYear]' => 2024,
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/rider/profile');

        $riderGalopRepository = static::getContainer()->get(RiderGalopRepository::class);

        $updatedRiderGalop = $riderGalopRepository->find($riderGalop->getId());

        self::assertNotNull($updatedRiderGalop);
        self::assertSame($newGalop->getId(), $updatedRiderGalop->getGalop()->getId());
        self::assertSame(2024, $updatedRiderGalop->getObtainedYear());
    }

    public function testEditGalopIsForbiddenForAnotherRider(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $ownerUser = new AppUser();
        $ownerUser->setEmail(sprintf('owner-%s@example.com', uniqid()));
        $ownerUser->setRoles(['ROLE_USER']);
        $ownerUser->setPassword('test-password');

        $ownerRider = new Rider();
        $ownerRider->setFirstName('Owner');
        $ownerRider->setLastName('Rider');
        $ownerRider->setAppUser($ownerUser);
        $ownerUser->setRider($ownerRider);

        $otherUser = new AppUser();
        $otherUser->setEmail(sprintf('other-%s@example.com', uniqid()));
        $otherUser->setRoles(['ROLE_USER']);
        $otherUser->setPassword('test-password');

        $otherRider = new Rider();
        $otherRider->setFirstName('Other');
        $otherRider->setLastName('Rider');
        $otherRider->setAppUser($otherUser);
        $otherUser->setRider($otherRider);

        $galop = new Galop();
        $galop->setMnemonique(sprintf('GALOP_SECURITY_%s', uniqid()));
        $galop->setLibelle('Galop sécurité');
        $galop->setEstActif(true);

        $riderGalop = new RiderGalop();
        $riderGalop->setGalop($galop);
        $riderGalop->setObtainedYear(2020);

        $ownerRider->addGalopHistory($riderGalop);

        $entityManager->persist($ownerUser);
        $entityManager->persist($ownerRider);
        $entityManager->persist($otherUser);
        $entityManager->persist($otherRider);
        $entityManager->persist($galop);
        $entityManager->persist($riderGalop);
        $entityManager->flush();

        $client->loginUser($otherUser);

        $client->request('POST', sprintf('/rider/galop/%d/edit', $riderGalop->getId()), [
            'rider_galop' => [
                'galop' => $galop->getId(),
                'obtainedYear' => 2024,
                '_token' => 'invalid-token',
            ],
        ]);

        self::assertResponseStatusCodeSame(403);
    }
}