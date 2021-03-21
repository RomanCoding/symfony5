<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class UserControllerTest extends WebTestCase
{
    public function testCreateWithValidData()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $email = 'newEmailToBeCreated@example.com';
        $username = 'newEmail';
        $password = 'newPassword';

        $newUserData = [
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ];

        $client->request('POST', '/user', $newUserData);
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($email, $client->getResponse()->getContent());

        $savedItem = $userRepository->findOneByEmail($email);

        $this->assertEquals($username, $savedItem->getUsername());
    }

    public function testCreateWithInvalidData()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $email = 'newEmailToBeCreated@example.com';
        $username = '';
        $password = '';

        $newUserData = [
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ];

        $client->request('POST', '/user', $newUserData);
        $this->assertStringContainsString('"error":"Validation failed"', $client->getResponse()->getContent());
    }

    public function testListUsers()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $client->request('GET', '/user');
        $user = $userRepository->findOneBy([]);
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString($user->getEmail(), $client->getResponse()->getContent());
        $this->assertStringContainsString($user->getUsername(), $client->getResponse()->getContent());
    }

    public function testListUsersWithSearch()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);

        $client->request('GET', '/user?search=john');
        $anotherUser = $userRepository->findOneByUsername('jan');
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString($user->getEmail(), $client->getResponse()->getContent());
        $this->assertStringNotContainsString($anotherUser->getEmail(), $client->getResponse()->getContent());
    }
}
