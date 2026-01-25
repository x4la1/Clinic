<?php

namespace App\Repository;

use App\Entity\StaffSpecialization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StaffSpecialization>
 */
class StaffSpecializationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffSpecialization::class);
    }

    public function save(StaffSpecialization $staffSpecialization)
    {
        $this->getEntityManager()->persist($staffSpecialization);
        $this->getEntityManager()->flush();
    }

    public function delete(StaffSpecialization $staffSpecialization)
    {
        $this->getEntityManager()->remove($staffSpecialization);
        $this->getEntityManager()->flush();
    }
}
