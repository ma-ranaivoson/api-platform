<?php

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CheeseListingResourceTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     * @throws ORMException
     */
    public function testCreateCheeseListing()
    {
        // Creating a test client to test API
        $client = self::createClient();
        $client->request('POST', '/api/cheeses', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(401);

        // Creating user
        $user = new  User();
        $user->setEmail('test@test.com');
        $user->setUsername('test');
        // password: foo
        $user->setPassword('$2y$13$sjyIOf9SOwWTrWUxjDooOuNNzFpcSDtl0/NnZHyBXTHrlAatHqAuu');

        // Creating entity manager to persist data
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($user);
        $em->flush();

        // Login the user
        $client->request('POST', '/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test_8@test.com',
                'password' => 'foo'
            ]
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}