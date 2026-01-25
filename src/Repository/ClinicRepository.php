<?php

namespace App\Repository;

use App\Entity\Clinic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clinic>
 */
class ClinicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clinic::class);
    }

    public function findByPhoneOrEmail(string $phone, string $email): ?Clinic
    {
        return $this->createQueryBuilder('c')
            ->where('c.phone = :phone OR c.email = :email')
            ->setParameter('phone', $phone)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Clinic $clinic): void
    {
        $this->getEntityManager()->persist($clinic);
        $this->getEntityManager()->flush();
    }

    public function delete(Clinic $clinic): void
    {
        $this->getEntityManager()->remove($clinic);
        $this->getEntityManager()->flush();
    }

}
