<?php

namespace App\Repository;

use App\Entity\Cabinet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cabinet>
 */
class CabinetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cabinet::class);
    }

    public function save(Cabinet $cabinet)
    {
        $this->getEntityManager()->persist($cabinet);
        $this->getEntityManager()->flush();
    }

    public function delete(Cabinet $cabinet)
    {
        $this->getEntityManager()->remove($cabinet);
        $this->getEntityManager()->flush();
    }

    public function findAllByClinicId(string $id)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.clinic', 'clinic')
            ->addSelect('clinic')
            ->where('clinic.id = :clinicId')
            ->setParameter('clinicId', $id)
            ->orderBy('c.number', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFreeCabinetsByClinicId(string $id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT c.id, c.number, c.description
        FROM cabinet c
        LEFT JOIN staff s ON c.id = s.cabinet_id
        WHERE c.clinic_id = :clinicId
        AND s.id IS NULL
        ORDER BY c.number
    ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['clinicId' => $id]);

        return $result->fetchAllAssociative();
    }
}
