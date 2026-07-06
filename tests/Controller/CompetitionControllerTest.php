<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Competition;
use App\Entity\StatusCompetition; 
use App\Entity\Ranch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompetitionControllerTest extends WebTestCase
{
    public function testCompetitionPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/competition/');
        $this->assertResponseRedirects();
    }

    public function testCompetitionNewRequiresAdminRole(): void
    {
        $client = static::createClient();
        $client->request('POST', '/competition/new');
        $this->assertResponseRedirects();
    }

    public function testCompetitionEditRequiresAdminRole(): void
    {
        $client = static::createClient();

        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $status = new StatusCompetition();
        $status->setLibelle('Planifiée'); 
        $em->persist($status);

        $ranch = new Ranch();
        $ranch->setName('Ranch A');
        $ranch->setAddress('123 Rue du Galop');
        $ranch->setZipCode(29000);
        $ranch->setCity('Brest');
        $em->persist($ranch);

        $competition = new Competition();
        $competition->setName('Compétition de test');
        $competition->setStartDate(new \DateTime()); 
        $competition->setEndDate(new \DateTime('+2 days'));

        $competition->setStatusCompetition($status); 
        $competition->setLocation($ranch);

        $em->persist($competition);
        $em->flush();

        // Execute
        $client->request('GET', '/competition/' . $competition->getId() . '/edit');

        $this->assertResponseRedirects();
    }
}