<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }


    public function findUserByEmailOrPhone(string $login, string $phone): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.login = :login OR u.phone = :phone')
            ->setParameter('login', $login)
            ->setParameter('phone', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function getAllUserAppointments(string $userId): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(Appointment::class, 'a')
            ->where('a.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function getAllUserReviews(string $userId): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('r')
            ->from(Review::class, 'r')
            ->where('r.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByLogin(string $login): ?User
    {
        return $this->findOneBy(['login' => $login]);
    }
}
