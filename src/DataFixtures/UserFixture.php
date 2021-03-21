<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $users = [
            ['email' => 'john@example.com', 'username' => 'john'],
            ['email' => 'jan@example.com', 'username' => 'jan'],
            ['email' => 'jacob@example.com', 'username' => 'jacob'],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email'])
                ->setUsername($userData['username'])
                ->setPassword($this->encoder->encodePassword($user, 'secure'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
