<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    public function setUp(): void
    {
        /** @var EntityManagerInterface */
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')
            ->willReturn([]);

        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $this->userService = new UserService($this->entityManager, $validator, $passwordEncoder);
    }

    public function testCreate(): void
    {
        $email = 'newEmailToBeCreated@example.com';
        $username = 'newEmail';
        $password = 'newPassword';

        $expectedObject = new User();
        $expectedObject->setEmail($email)
            ->setUsername($username);

        $this->entityManager->expects($this->once())->method('persist')->with($expectedObject);

        $this->userService->create($username, $email, $password);
    }

    public function testUpdate(): void
    {
        /** @var User */
        $email = 'newEmailToBeCreated@example.com';
        $username = 'newEmail';

        $expectedObject = new User();
        $expectedObject->setEmail($email)
            ->setUsername($username);

        $this->entityManager->expects($this->once())->method('persist')->with($expectedObject);

        $this->userService->update($expectedObject, 'n123', 'n123@example.com');
        $this->assertEquals('n123', $expectedObject->getUsername());
    }
}

