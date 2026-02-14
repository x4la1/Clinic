<?php

namespace App\Repository;

use App\Entity\Staff;
use App\Entity\StaffTimeSlot;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends ServiceEntityRepository<StaffTimeSlot>
 */
class StaffTimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StaffTimeSlot::class);
    }

    public function updateStaffTimeSlots(string $staffId, array $newTimeSlotsIds)
    {
        $staff = $this->getEntityManager()->getRepository(Staff::class)->find($staffId);
        if (!$staff) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        $this->createQueryBuilder('sts')
            ->delete()
            ->where('sts.staff = :staffId')
            ->setParameter('staffId', $staffId)
            ->getQuery()
            ->execute();

        foreach ($newTimeSlotsIds as $timeSlotId) {
            $timeSlot = $this->getEntityManager()->getRepository(TimeSlot::class)->find($timeSlotId);
            if ($timeSlot) {
                $staffTimeSlot = new StaffTimeSlot();
                $staffTimeSlot->setStaff($staff);
                $staffTimeSlot->setTimeSlot($timeSlot);

                $this->getEntityManager()->persist($staffTimeSlot);
            }
        }

        $this->getEntityManager()->flush();
    }

    public function save(StaffTimeSlot $staffTimeSlot): void
    {
        $this->getEntityManager()->persist($staffTimeSlot);
        $this->getEntityManager()->flush();
    }

    public function delete(StaffTimeSlot $staffTimeSlot): void
    {
        $this->getEntityManager()->remove($staffTimeSlot);
        $this->getEntityManager()->flush();
    }
}
