<?php

namespace App\Tests\Controller;

use App\Entity\Horse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HorseControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Horse> */
    private EntityRepository $horseRepository;
    private string $path = '/horse/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->horseRepository = $this->manager->getRepository(Horse::class);

        foreach ($this->horseRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Horse index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'horse[name]' => 'Testing',
            'horse[affix]' => 'Testing',
            'horse[birthDate]' => 'Testing',
            'horse[sire]' => 'Testing',
            'horse[rider]' => 'Testing',
            'horse[breed]' => 'Testing',
            'horse[coat]' => 'Testing',
            'horse[owner]' => 'Testing',
        ]);

        self::assertResponseRedirects('/horse');

        self::assertSame(1, $this->horseRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Horse();
        $fixture->setName('My Title');
        $fixture->setAffix('My Title');
        $fixture->setBirthDate('My Title');
        $fixture->setSire('My Title');
        $fixture->setRider('My Title');
        $fixture->setBreed('My Title');
        $fixture->setCoat('My Title');
        $fixture->setOwner('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Horse');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Horse();
        $fixture->setName('Value');
        $fixture->setAffix('Value');
        $fixture->setBirthDate('Value');
        $fixture->setSire('Value');
        $fixture->setRider('Value');
        $fixture->setBreed('Value');
        $fixture->setCoat('Value');
        $fixture->setOwner('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'horse[name]' => 'Something New',
            'horse[affix]' => 'Something New',
            'horse[birthDate]' => 'Something New',
            'horse[sire]' => 'Something New',
            'horse[rider]' => 'Something New',
            'horse[breed]' => 'Something New',
            'horse[coat]' => 'Something New',
            'horse[owner]' => 'Something New',
        ]);

        self::assertResponseRedirects('/horse');

        $fixture = $this->horseRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getAffix());
        self::assertSame('Something New', $fixture[0]->getBirthDate());
        self::assertSame('Something New', $fixture[0]->getSire());
        self::assertSame('Something New', $fixture[0]->getRider());
        self::assertSame('Something New', $fixture[0]->getBreed());
        self::assertSame('Something New', $fixture[0]->getCoat());
        self::assertSame('Something New', $fixture[0]->getOwner());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Horse();
        $fixture->setName('Value');
        $fixture->setAffix('Value');
        $fixture->setBirthDate('Value');
        $fixture->setSire('Value');
        $fixture->setRider('Value');
        $fixture->setBreed('Value');
        $fixture->setCoat('Value');
        $fixture->setOwner('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/horse');
        self::assertSame(0, $this->horseRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
