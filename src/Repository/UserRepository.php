<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByUsernameOrEmail(string $needle, $orderBy = null): Collection
    {
        $criteria = Criteria::create()
            ->orWhere(Criteria::expr()->contains('email', $needle))
            ->orWhere(Criteria::expr()->contains('username', $needle));

        if (isset($orderBy)) {
            $criteria->orderBy($orderBy);
        }

        return $this->matching($criteria);
    }
}
