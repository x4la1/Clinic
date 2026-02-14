<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Staff;
use App\Entity\StaffService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends ServiceEntityRepository<StaffService>
 */
class StaffServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffService::class);
    }

    public function updateStaffServices(string $staffId, array $newServiceIds): void
    {
        $staff = $this->getEntityManager()->getRepository(Staff::class)->find($staffId);
        if (!$staff) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        $this->createQueryBuilder('ss')
            ->delete()
            ->where('ss.staff = :staffId')
            ->setParameter('staffId', $staffId)
            ->getQuery()
            ->execute();

        foreach ($newServiceIds as $serviceId) {
            $service = $this->getEntityManager()->getRepository(Service::class)->find($serviceId);
            if ($service) {
                $staffService = new StaffService();
                $staffService->setStaff($staff);
                $staffService->setService($service);

                $this->getEntityManager()->persist($staffService);
            }
        }

        $this->getEntityManager()->flush();
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
