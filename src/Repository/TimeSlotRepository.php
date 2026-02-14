<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\StaffTimeSlot;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

    public function getFreeTimeSlotForStuff(int $stuffId, \DateTime $date): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT ts.id, DATE_FORMAT(ts.slot, '%H:%i') AS slot
        FROM time_slot ts
        INNER JOIN staff_time_slot sts ON ts.id = sts.slot_id
        WHERE
            sts.staff_id = :staffId
            AND ts.id NOT IN (
                SELECT ts_booked.id
                FROM appointment a
                INNER JOIN time_slot ts_booked ON TIME(a.date) = ts_booked.slot
                WHERE
                    a.staff_id = :staffId
                    AND DATE(a.date) = :selectedDate
            )
    ";

        $result = $conn->fetchAllAssociative($sql, [
            'staffId' => $stuffId,
            'selectedDate' => $date->format('Y-m-d')
        ]);

        return $result;
    }
}
