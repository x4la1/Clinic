<?php

namespace App\Repository;

use App\Entity\Staff;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    public function save(TimeSlot $timeSlot)
    {
        $this->getEntityManager()->persist($timeSlot);
        $this->getEntityManager()->flush();
    }

    public function delete(TimeSlot $timeSlot)
    {
        $this->getEntityManager()->remove($timeSlot);
        $this->getEntityManager()->flush();
    }

    public function createTimeSlots(Staff $staffId, array $timeSlots): void
    {
        foreach ($timeSlots as $timeSlot) {
            $TimeSlot = new TimeSlot();
            $TimeSlot->setStaff($staffId);
            $TimeSlot->setSlot(new \DateTime($timeSlot));
            $this->getEntityManager()->persist($TimeSlot);
        }
        $this->getEntityManager()->flush();
    }

    public function deleteTimeSlot(string $id): void
    {
        $timeSlot = $this->getEntityManager()->getRepository(TimeSlot::class)->find($id);
        $this->getEntityManager()->remove($timeSlot);
    }

    public function getSlotsByStaffId(string $staffId)
    {
        $qb = $this->createQueryBuilder('ts')
            ->select('
            ts.id,
            ts.slot,
            IDENTITY(ts.staff) as staff_id,
            s.firstName as staff_first_name,
            s.lastName as staff_last_name
        ')
            ->innerJoin('ts.staff', 's')
            ->where('s.id = :staffId')
            ->setParameter('staffId', $staffId)
            ->orderBy('ts.slot', 'ASC')
            ->getQuery();

        return $qb->getArrayResult();
    }
}
