<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private $entityManager;
    private $validator;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string|null $username
     * @param string|null $email
     * @param string|null $password
     * @return User
     * @throws \Exception
     */
    public function create(?string $username, ?string $email, ?string $password): User
    {
        $user = new User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new \InvalidArgumentException($errorsString);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param string|null $username
     * @param string|null $email
     * @return User
     */
    public function update(User $user, ?string $username, ?string $email): User
    {
        $user->setUsername($username)
            ->setEmail($email);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new \InvalidArgumentException($errorsString);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}