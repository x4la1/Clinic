<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function save(Appointment $appointment): void
    {
        $this->getEntityManager()->persist($appointment);
        $this->getEntityManager()->flush();
    }

    public function delete(Appointment $appointment): void
    {
        $this->getEntityManager()->remove($appointment);
        $this->getEntityManager()->flush();
    }


}
