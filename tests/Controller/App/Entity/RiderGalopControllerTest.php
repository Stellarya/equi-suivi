<?php

namespace App\Tests\Controller\App\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RiderGalopControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app/entity/rider/galop');

        self::assertResponseIsSuccessful();
    }
}
