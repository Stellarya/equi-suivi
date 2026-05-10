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
            name: 'Sarabi',
            status: Horse::STATUS_ACTIVE
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
            name: 'Cheval privé',
            status: Horse::STATUS_ACTIVE
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

    public function testShowDisplaysOwnedHorse(): void
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
            name: 'Sarabi',
            status: Horse::STATUS_ACTIVE
        );

        $client->loginUser($user);
        $client->request('GET', sprintf('/horses/%d', $horse->getId()));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#horse');
        self::assertSelectorTextContains('body', 'Sarabi');
    }

    public function testShowIsForbiddenForAnotherUserHorse(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $ownerUser = $this->createRiderUser($entityManager);
        $otherUser = $this->createRiderUser($entityManager);

        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $horse = $this->createHorse(
            entityManager: $entityManager,
            owner: $ownerUser,
            breed: $breed,
            coat: $coat,
            name: 'Cheval privé',
            status: Horse::STATUS_ACTIVE
        );

        $client->loginUser($otherUser);
        $client->request('GET', sprintf('/horses/%d', $horse->getId()));

        self::assertResponseStatusCodeSame(403);
    }

    public function testEditUpdatesOwnedHorse(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager);
        $oldBreed = $this->createBreed($entityManager, 'Ancienne race');
        $newBreed = $this->createBreed($entityManager, 'Nouvelle race');
        $coat = $this->createCoat($entityManager);

        $horse = $this->createHorse(
            entityManager: $entityManager,
            owner: $user,
            breed: $oldBreed,
            coat: $coat,
            name: 'Ancien nom',
            status: Horse::STATUS_ACTIVE
        );

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/horses/%d', $horse->getId()));

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filterXPath(sprintf(
            '//form[contains(@action, "/horses/%d/edit")]',
            $horse->getId()
        ));

        self::assertCount(1, $formCrawler);

        $form = $formCrawler->form([
            'horse[name]' => 'Nouveau nom',
            'horse[affix]' => 'du Val',
            'horse[birthDate]' => '2019-04-24',
            'horse[sire]' => '87654321B',
            'horse[breed]' => $newBreed->getId(),
            'horse[coat]' => $coat->getId(),
        ]);

        $client->submit($form);

        self::assertResponseRedirects(sprintf('/horses/%d', $horse->getId()));

        $horseRepository = static::getContainer()->get(HorseRepository::class);
        $updatedHorse = $horseRepository->find($horse->getId());

        self::assertNotNull($updatedHorse);
        self::assertSame('Nouveau nom', $updatedHorse->getName());
        self::assertSame('du Val', $updatedHorse->getAffix());
        self::assertSame('87654321B', $updatedHorse->getSire());
        self::assertSame($newBreed->getId(), $updatedHorse->getBreed()?->getId());
        self::assertSame($coat->getId(), $updatedHorse->getCoat()?->getId());
    }

    public function testInactivateCanSetHorseResting(): void
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
            name: 'Cheval au repos',
            status: Horse::STATUS_RESTING
        );

        $horseId = $horse->getId();

        self::assertNotNull($horseId);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/horses/%d', $horseId));

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filter('form[data-horse-status-form="resting"]');

        self::assertCount(1, $formCrawler);

        $client->submit($formCrawler->form());

        self::assertResponseRedirects(sprintf('/horses/%d', $horseId));

        $horseRepository = static::getContainer()->get(HorseRepository::class);
        $updatedHorse = $horseRepository->find($horseId);

        self::assertNotNull($updatedHorse);
        self::assertSame(Horse::STATUS_RESTING, $updatedHorse->getStatus());
    }

    public function testInactivateCanSetHorseRetired(): void
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
            name: 'Cheval retraité',
            status: Horse::STATUS_RETIRED
        );

        $horseId = $horse->getId();

        self::assertNotNull($horseId);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/horses/%d', $horseId));

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filter('form[data-horse-status-form="retired"]');

        self::assertCount(1, $formCrawler);

        $client->submit($formCrawler->form());

        self::assertResponseRedirects(sprintf('/horses/%d', $horseId));

        $horseRepository = static::getContainer()->get(HorseRepository::class);
        $updatedHorse = $horseRepository->find($horseId);

        self::assertNotNull($updatedHorse);
        self::assertSame(Horse::STATUS_RETIRED, $updatedHorse->getStatus());
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
        string $status = Horse::STATUS_ACTIVE,
    ): Horse {
        $horse = new Horse();
        $horse->setName($name);
        $horse->setAffix('du Test');
        $horse->setBirthDate(new \DateTime('2020-05-07'));
        $horse->setSire('12345678A');
        $horse->setBreed($breed);
        $horse->setCoat($coat);
        $horse->setOwner($owner);
        $horse->setStatus($status);

        if ($owner->getRider() !== null) {
            $horse->addRider($owner->getRider());
        }

        $entityManager->persist($horse);
        $entityManager->flush();

        return $horse;
    }

    public function testAttachedRiderCanViewHorse(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $ownerUser = $this->createRiderUser($entityManager);
        $attachedUser = $this->createRiderUser($entityManager);

        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $horse = $this->createHorse(
            entityManager: $entityManager,
            owner: $ownerUser,
            breed: $breed,
            coat: $coat,
            name: 'Cheval partagé',
            status: Horse::STATUS_ACTIVE
        );

        $attachedRider = $attachedUser->getRider();

        self::assertNotNull($attachedRider);

        $horse->addRider($attachedRider);
        $entityManager->flush();

        $client->loginUser($attachedUser);
        $client->request('GET', sprintf('/horses/%d', $horse->getId()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Cheval partagé');
    }

    public function testAttachedRiderCannotEditHorseWhenNotOwner(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $ownerUser = $this->createRiderUser($entityManager);
        $attachedUser = $this->createRiderUser($entityManager);

        $oldBreed = $this->createBreed($entityManager, 'Ancienne race');
        $newBreed = $this->createBreed($entityManager, 'Nouvelle race');
        $coat = $this->createCoat($entityManager);

        $horse = $this->createHorse(
            entityManager: $entityManager,
            owner: $ownerUser,
            breed: $oldBreed,
            coat: $coat,
            name: 'Cheval non modifiable',
            status: Horse::STATUS_ACTIVE
        );

        $attachedRider = $attachedUser->getRider();

        self::assertNotNull($attachedRider);

        $horse->addRider($attachedRider);
        $entityManager->flush();

        $client->loginUser($attachedUser);

        $client->request('POST', sprintf('/horses/%d/edit', $horse->getId()), [
            'horse' => [
                'name' => 'Nom interdit',
                'affix' => 'Interdit',
                'birthDate' => '2020-05-07',
                'sire' => '99999999Z',
                'breed' => $newBreed->getId(),
                'coat' => $coat->getId(),
            ],
        ]);

        self::assertResponseStatusCodeSame(403);

        $horseRepository = static::getContainer()->get(HorseRepository::class);
        $unchangedHorse = $horseRepository->find($horse->getId());

        self::assertNotNull($unchangedHorse);
        self::assertSame('Cheval non modifiable', $unchangedHorse->getName());
        self::assertSame($oldBreed->getId(), $unchangedHorse->getBreed()?->getId());
    }

    public function testIndexProvidesHorseStatusesForFrontFilter(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = $this->createRiderUser($entityManager);
        $breed = $this->createBreed($entityManager);
        $coat = $this->createCoat($entityManager);

        $this->createHorse(
            entityManager: $entityManager,
            owner: $user,
            breed: $breed,
            coat: $coat,
            name: 'Cheval actif',
            status: Horse::STATUS_ACTIVE
        );

        $this->createHorse(
            entityManager: $entityManager,
            owner: $user,
            breed: $breed,
            coat: $coat,
            name: 'Cheval archivé',
            status: Horse::STATUS_ARCHIVED
        );

        $client->loginUser($user);
        $crawler = $client->request('GET', '/horses/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-horse-active-filter-input]');
        self::assertCount(2, $crawler->filter('[data-horse-card]'));
        self::assertCount(1, $crawler->filter('[data-horse-card][data-horse-status="active"]'));
        self::assertCount(1, $crawler->filter('[data-horse-card][data-horse-status="archived"]'));
        self::assertSelectorTextContains('body', 'Cheval actif');
        self::assertSelectorTextContains('body', 'Cheval archivé');
    }

    public function testArchiveOwnedHorse(): void
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
            name: 'Cheval à archiver',
            status: Horse::STATUS_ARCHIVED
        );

        $horseId = $horse->getId();

        self::assertNotNull($horseId);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/horses/%d', $horseId));

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filterXPath(sprintf(
            '//form[contains(@action, "/horses/%d/archive")]',
            $horseId
        ));

        self::assertCount(1, $formCrawler);

        $client->submit($formCrawler->form());

        self::assertResponseRedirects('/horses/');

        $horseRepository = static::getContainer()->get(HorseRepository::class);
        $archivedHorse = $horseRepository->find($horseId);

        self::assertNotNull($archivedHorse);
        self::assertSame(Horse::STATUS_ARCHIVED, $archivedHorse->getStatus());
    }

    public function testDeleteOwnedHorse(): void
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
            name: 'Cheval à supprimer',
            status: Horse::STATUS_ARCHIVED
        );

        $horseId = $horse->getId();

        self::assertNotNull($horseId);

        $client->loginUser($user);

        $crawler = $client->request('GET', sprintf('/horses/%d', $horseId));

        self::assertResponseIsSuccessful();

        $formCrawler = $crawler->filterXPath(sprintf(
            '//form[contains(@action, "/horses/%d/delete")]',
            $horseId
        ));

        self::assertCount(1, $formCrawler);

        $client->submit($formCrawler->form());

        self::assertResponseRedirects('/horses/');

        $horseRepository = static::getContainer()->get(HorseRepository::class);

        self::assertNull($horseRepository->find($horseId));
    }
}