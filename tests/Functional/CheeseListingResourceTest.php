<?php

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Test\CustomApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CheeseListingResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCheeseListing()
    {
        // Creating a test client to test API
        $client = self::createClient();
        $client->request('POST', '/api/cheeses', [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(401);

        $this->createUserAndLogIn($client,'test@test.com', 'foo');

        $client->request('POST', '/api/cheeses', [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(422);
    }
}