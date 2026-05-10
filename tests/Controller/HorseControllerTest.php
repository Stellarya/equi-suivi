<?php

namespace App\Tests\Controller;

use App\Entity\AppUser;
use App\Entity\Breed;
use App\Entity\Coat;
use App\Entity\Horse;
use App\Entity\Rider;
use App\Repository\HorseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HorseControllerTest extends WebTestCase
{
    public function testIndexDisplaysConnectedRiderHorses(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager);
        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $horse = $this->createHorse(
            entityManager: $entityManager,
            owner: $user,
            breed: $breed,
            coat: $coat,
            name: 'Sarabi'
        );

        $client->loginUser($user);
        $client->request('GET', '/horses/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#horse-list');
        self::assertAnySelectorTextContains('.app-card', $horse->getName());
    }

    public function testIndexDoesNotDisplayAnotherUserHorse(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $ownerUser = $this->createRiderUser($entityManager);
        $connectedUser = $this->createRiderUser($entityManager);

        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $this->createHorse(
            entityManager: $entityManager,
            owner: $ownerUser,
            breed: $breed,
            coat: $coat,
            name: 'Cheval privé'
        );

        $client->loginUser($connectedUser);
        $client->request('GET', '/horses/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#horse-list');
        self::assertSelectorTextNotContains('body', 'Cheval privé');
    }

    public function testNewCreatesHorseForConnectedUser(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager);
        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/horses/');

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="horse"]')->form([
            'horse[name]' => 'Tempête',
            'horse[affix]' => 'des Prés',
            'horse[birthDate]' => '2020-05-07',
            'horse[sire]' => '12345678A',
            'horse[breed]' => $breed->getId(),
            'horse[coat]' => $coat->getId(),
        ]);

        $client->submit($form);

        self::assertResponseRedirects();

        $horseRepository = static::getContainer()->get(HorseRepository::class);

        $createdHorse = $horseRepository->findOneBy([
            'name' => 'Tempête',
            'owner' => $user,
        ]);

        self::assertNotNull($createdHorse);
        self::assertSame('des Prés', $createdHorse->getAffix());
        self::assertSame('12345678A', $createdHorse->getSire());
        self::assertSame($breed->getId(), $createdHorse->getBreed()?->getId());
        self::assertSame($coat->getId(), $createdHorse->getCoat()?->getId());

        $riderIds = $createdHorse
            ->getRiders()
            ->map(static fn (Rider $rider): ?int => $rider->getId())
            ->toArray();

        self::assertContains($user->getRider()?->getId(), $riderIds);
    }

    public function testNewReopensModalWhenFormIsInvalid(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/horses/');

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="horse"]')->form([
            'horse[name]' => '',
        ]);

        $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#horse-add-modal.is-open');
    }

    private function createRiderUser(EntityManagerInterface $entityManager): AppUser
    {
        $user = new AppUser();
        $user->setEmail(sprintf('horse-user-%s@example.com', uniqid()));
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test-password');

        $rider = new Rider();
        $rider->setFirstName('Marie');
        $rider->setLastName(sprintf('Test-%s', uniqid()));
        $rider->setAppUser($user);

        $user->setRider($rider);

        $entityManager->persist($user);
        $entityManager->persist($rider);
        $entityManager->flush();

        return $user;
    }

    private function createBreed(EntityManagerInterface $entityManager, string $label = 'Race test'): Breed
    {
        $breed = new Breed();
        $breed->setMnemonique(sprintf('BREED_%s', uniqid()));
        $breed->setLibelle(sprintf('%s %s', $label, uniqid()));
        $breed->setEstActif(true);

        $entityManager->persist($breed);
        $entityManager->flush();

        return $breed;
    }

    private function createCoat(EntityManagerInterface $entityManager, string $label = 'Robe test'): Coat
    {
        $coat = new Coat();
        $coat->setMnemonique(sprintf('COAT_%s', uniqid()));
        $coat->setLibelle(sprintf('%s %s', $label, uniqid()));
        $coat->setEstActif(true);

        $entityManager->persist($coat);
        $entityManager->flush();

        return $coat;
    }

    private function createHorse(
        EntityManagerInterface $entityManager,
        AppUser $owner,
        Breed $breed,
        Coat $coat,
        string $name,
    ): Horse {
        $horse = new Horse();
        $horse->setName($name);
        $horse->setAffix('du Test');
        $horse->setBirthDate(new \DateTime('2020-05-07'));
        $horse->setSire('12345678A');
        $horse->setBreed($breed);
        $horse->setCoat($coat);
        $horse->setOwner($owner);
        $horse->setStatus(Horse::STATUS_ACTIVE);

        if ($owner->getRider() !== null) {
            $horse->addRider($owner->getRider());
        }

        $entityManager->persist($horse);
        $entityManager->flush();

        return $horse;
    }
}