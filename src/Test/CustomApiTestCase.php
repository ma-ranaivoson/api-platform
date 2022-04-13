<?php

namespace App\Test;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CustomApiTestCase extends ApiTestCase
{

    protected function createUser(string $email, string $password): User
    {
        // boot kernel
        self::bootKernel();

        // get container
        $container = static::getContainer();

        // Creating user
        $user = new  User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@')));

        // Hashing password
        $passwordHasherFactory = $container->get('security.password_hasher_factory');
        $hasher = new UserPasswordHasher($passwordHasherFactory);
        $encoded = $hasher->hashPassword($user, $password);
        $user->setPassword($encoded);

        // Creating entity manager to persist data
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $logger = self::getContainer()->get('monolog.logger');

        try {
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
           $logger->error($e->getMessage());
           $em->rollback();
        }

        return $user;
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function logIn(Client $client, string $email, string $password)
    {
        // Login the user
        $client->request('POST', '/login', [
            'json' => [
                'email' => $email,
                'password' => $password
            ]
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    protected function createUserAndLogIn(Client $client, string $email, string $password): User
    {
        $user = $this->createUser($email, $password);
        $this->logIn($client, $email, $password);

        return $user;
    }

}