<?php

namespace App\Repository;

use App\Entity\Specialization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialization>
 */
class SpecializationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialization::class);
    }

    public function save(Specialization $specialization)
    {
        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();
    }

    public function delete(Specialization $specialization)
    {
        $this->getEntityManager()->remove($specialization);
        $this->getEntityManager()->flush();
    }
}
