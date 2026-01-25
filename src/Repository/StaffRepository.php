<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\Specialization;
use App\Entity\Staff;
use App\Entity\StaffService;
use App\Entity\StaffSpecialization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @extends ServiceEntityRepository<Staff>
 */
class StaffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Staff::class);
    }

    public function findUserByPhone(string $phone): ?Staff
    {
        return $this->createQueryBuilder('s')
            ->where('s.phone = :phone')
            ->setParameter('phone', $phone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllStaff(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = $sql = "
                    SELECT
                        staff.*,
                        clinic.name as clinic_name,
                        clinic.address as clinic_address,
                        clinic.phone as clinic_phone,
                        cabinet.number as cabinet_number,

                        GROUP_CONCAT(DISTINCT CONCAT(specialization.id, '||', specialization.name)) as specializations,
                        GROUP_CONCAT(DISTINCT CONCAT(service.id, '||', service.name)) as services,
                        (SELECT COUNT(*) FROM appointment WHERE appointment.staff_id = staff.id) as appointments_count
                    FROM staff
                    LEFT JOIN clinic ON staff.clinic_id = clinic.id
                    LEFT JOIN cabinet ON staff.cabinet_id = cabinet.id
                    LEFT JOIN staff_specialization ON staff.id = staff_specialization.staff_id
                    LEFT JOIN specialization ON staff_specialization.specialization_id = specialization.id
                    LEFT JOIN staff_service ON staff.id = staff_service.staff_id
                    LEFT JOIN service ON staff_service.service_id = service.id
                    GROUP BY staff.id
                    ORDER BY staff.last_name, staff.first_name
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        $data = $result->fetchAllAssociative();


        $formatted = [];
        foreach ($data as $row) {

            $specializations = [];
            if ($row['specializations']) {
                $pairs = explode(',', $row['specializations']);
                foreach ($pairs as $pair) {
                    // Разделяем по разделителю '||'
                    if (strpos($pair, '||') !== false) {
                        list($id, $name) = explode('||', $pair, 2);
                        $specializations[] = [
                            'id' => (int)$id,           // ← ID!
                            'name' => trim($name)       // ← Name!
                        ];
                    }
                }
            }


            $services = [];
            if ($row['services']) {
                $pairs = explode(',', $row['services']);
                foreach ($pairs as $pair) {
                    if (strpos($pair, '||') !== false) {
                        list($id, $name) = explode('||', $pair, 2);
                        $services[] = [
                            'id' => (int)$id,           // ← ID!
                            'name' => trim($name)       // ← Name!
                        ];
                    }
                }
            }

            $formatted[] = [
                'id' => $row['id'],
                'full_name' => trim($row['last_name'] . ' ' . $row['first_name'] . ' ' . ($row['patronymic'] ?? '')),
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'patronymic' => $row['patronymic'],
                'phone' => $row['phone'],
                'experience' => $row['experience'],
                'experience_years' => $this->calculateExperienceYears(new \DateTime($row['experience'])),

                'clinic' => [
                    'id' => $row['clinic_id'],
                    'name' => $row['clinic_name'],
                    'address' => $row['clinic_address'],
                    'phone' => $row['clinic_phone']
                ],

                'cabinet' => $row['cabinet_id'] ? [
                    'id' => $row['cabinet_id'],
                    'number' => $row['cabinet_number'],
                    'description' => $row['cabinet_description']
                ] : null,

                'specializations' => $specializations,
                'services' => $services,
            ];
        }

        return $formatted;
    }

    public function getFullInfoById(string $id): ?array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT
            staff.*,
            clinic.name as clinic_name,
            clinic.address as clinic_address,
            clinic.phone as clinic_phone,
            cabinet.number as cabinet_number,
            cabinet.description as cabinet_description,
            GROUP_CONCAT(DISTINCT CONCAT(specialization.id, '||', specialization.name)) as specializations,
            GROUP_CONCAT(DISTINCT CONCAT(service.id, '||', service.name)) as services,
            (SELECT COUNT(*) FROM appointment WHERE appointment.staff_id = staff.id) as appointments_count
        FROM staff
        LEFT JOIN clinic ON staff.clinic_id = clinic.id
        LEFT JOIN cabinet ON staff.cabinet_id = cabinet.id
        LEFT JOIN staff_specialization ON staff.id = staff_specialization.staff_id
        LEFT JOIN specialization ON staff_specialization.specialization_id = specialization.id
        LEFT JOIN staff_service ON staff.id = staff_service.staff_id
        LEFT JOIN service ON staff_service.service_id = service.id
        WHERE staff.id = :id
        GROUP BY staff.id
    ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['id' => $id]);
        $row = $result->fetchAssociative();

        // Специализации
        $specializations = [];
        if ($row['specializations']) {
            $pairs = explode(',', $row['specializations']);
            foreach ($pairs as $pair) {
                if (strpos($pair, '||') !== false) {
                    list($id, $name) = explode('||', $pair, 2);
                    $specializations[] = [
                        'id' => (int)$id,
                        'name' => trim($name)
                    ];
                }
            }
        }

        // Услуги
        $services = [];
        if ($row['services']) {
            $pairs = explode(',', $row['services']);
            foreach ($pairs as $pair) {
                if (strpos($pair, '||') !== false) {
                    list($id, $name) = explode('||', $pair, 2);
                    $services[] = [
                        'id' => (int)$id,
                        'name' => trim($name)
                    ];
                }
            }
        }

        return [
            'id' => $row['id'],
            'full_name' => trim($row['last_name'] . ' ' . $row['first_name'] . ' ' . ($row['patronymic'] ?? '')),
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'patronymic' => $row['patronymic'],
            'phone' => $row['phone'],
            'experience' => $row['experience'],
            'experience_years' => $this->calculateExperienceYears(new \DateTime($row['experience'])),

            'clinic' => [
                'id' => $row['clinic_id'],
                'name' => $row['clinic_name'],
                'address' => $row['clinic_address'],
                'phone' => $row['clinic_phone']
            ],

            'cabinet' => $row['cabinet_id'] ? [
                'id' => $row['cabinet_id'],
                'number' => $row['cabinet_number'],
                'description' => $row['cabinet_description']
            ] : null,

            'specializations' => $specializations,
            'services' => $services,
        ];
    }

    public function updateStaffServices(string $staffId, array $newServiceIds): void
    {
        $staff = $this->findStaffById($staffId);
        if (!$staff) {
            throw new BadRequestException('STAFF_NOT_FOUND');
        }

        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement(
            "DELETE FROM staff_service WHERE staff_id = :staffId",
            ['staffId' => $staffId]
        );

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

    public function updateStaffSpecialisation(string $staffId, array $newSpecialisationIds): void
    {
        $staff = $this->findStaffById($staffId);
        if (!$staff) {
            throw new BadRequestException('STAFF_NOT_FOUND');
        }

        $conn = $this->getEntityManager()->getConnection();
        $conn->executeStatement(
            "DELETE FROM staff_specialization WHERE staff_id = :staffId",
            ['staffId' => $staffId]
        );

        foreach ($newSpecialisationIds as $specialisationId) {
            $specialization = $this->getEntityManager()->getRepository(Specialization::class)->find($specialisationId);
            if ($specialization) {
                $staffSpecialization = new StaffSpecialization();
                $staffSpecialization->setStaff($staff);
                $staffSpecialization->setSpecialization($specialization);

                $this->getEntityManager()->persist($staffSpecialization);
            }
        }

        $this->getEntityManager()->flush();
    }

    private function findStaffById(string $id): ?Staff
    {
        return $this->find($id);
    }

    private function calculateExperienceYears(\DateTime $date): int
    {
        return (new \DateTime())->diff($date)->y;
    }

    public function save(Staff $staff): void
    {
        $this->getEntityManager()->persist($staff);
        $this->getEntityManager()->flush();
    }

    public function delete(Staff $staff): void
    {
        $this->getEntityManager()->remove($staff);
        $this->getEntityManager()->flush();
    }
}
