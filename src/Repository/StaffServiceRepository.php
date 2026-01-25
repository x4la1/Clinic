<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Staff;
use App\Entity\StaffService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StaffService>
 */
class StaffServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffService::class);
    }

    public function save(StaffService $staffService): void
    {
        $this->getEntityManager()->persist($staffService);
        $this->getEntityManager()->flush();
    }

    public function delete(StaffService $staffService): void
    {
        $this->getEntityManager()->remove($staffService);
        $this->getEntityManager()->flush();
    }
}
