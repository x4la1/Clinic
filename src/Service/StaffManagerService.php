<?php

namespace App\Service;

use App\Entity\Staff;
use App\Repository\CabinetRepository;
use App\Repository\ClinicRepository;
use App\Repository\StaffRepository;
use App\Repository\StaffServiceRepository;
use App\Repository\StaffSpecializationRepository;
use DateTime;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StaffManagerService
{
    private StaffRepository $staffRepository;
    private ClinicRepository $clinicRepository;
    private CabinetRepository $cabinetRepository;

    private StaffServiceRepository $staffServiceRepository;
    private StaffSpecializationRepository $staffSpecializationRepository;

    public function __construct(
        StaffRepository               $staffRepository,
        ClinicRepository              $clinicRepository,
        CabinetRepository             $cabinetRepository,
        StaffServiceRepository        $staffServiceRepository,
        StaffSpecializationRepository $staffSpecializationRepository)
    {
        $this->staffRepository = $staffRepository;
        $this->clinicRepository = $clinicRepository;
        $this->cabinetRepository = $cabinetRepository;
        $this->staffServiceRepository = $staffServiceRepository;
        $this->staffSpecializationRepository = $staffSpecializationRepository;

    }

    public function createStaff(array $data): Staff
    {
        $clinicId = $data['clinic_id'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;
        $patronymic = $data['patronymic'] ?? null;
        $experience = $data['experience'] ?? null;
        $phone = $data['phone'] ?? null;
        $cabinetId = $data['cabinet_id'] ?? null;

        if ($this->staffRepository->findUserByPhone($phone)) {
            throw new BadRequestHttpException('PHONE_ALREADY_EXISTS');
        }

        $clinic = $this->clinicRepository->find($clinicId);
        if (!$clinic) {
            throw new BadRequestHttpException('CLINIC_NOT_FOUND');
        }

        $cabinet = null;
        if ($cabinetId) {
            $cabinet = $this->cabinetRepository->find($cabinetId);
            if (!$cabinet) {
                throw new BadRequestHttpException('CABINET_NOT_FOUND');
            }

            if (!$cabinet->getStaff()->isEmpty()) {
                throw new BadRequestHttpException('CABINET_ALREADY_OCCUPIED');
            }
        }

        try {
            $experienceDate = new \DateTime($experience);
            if ($experienceDate > new \DateTime()) {
                throw new BadRequestHttpException('EXPERIENCE_DATE_CANNOT_BE_IN_FUTURE');
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('INVALID_EXPERIENCE_DATE_FORMAT');
        }

        $staff = new Staff();
        $staff->setClinic($clinic)
            ->setCabinet($cabinet)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPatronymic($patronymic)
            ->setExperience($experienceDate)
            ->setPhone($phone);

        $this->staffRepository->save($staff);

        return $staff;
    }

    public function updateStaff(array $data): void
    {
        $id = $data['id'] ?? null;
        $clinicId = $data['clinic_id'] ?? null;
        $cabinetId = $data['cabinet_id'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;
        $patronymic = $data['patronymic'] ?? null;
        $experience = $data['experience'] ?? null;
        $phone = $data['phone'] ?? null;

        $staff = $this->staffRepository->find($id);
        if (!$staff) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        if ($phone !== $staff->getPhone()) {
            $existingByPhone = $this->staffRepository->findOneBy(['phone' => $phone]);

            if ($existingByPhone !== null) {
                throw new BadRequestHttpException('PHONE_ALREADY_EXIST');
            }
        }

        $clinic = $this->clinicRepository->find($clinicId);
        if (!$clinic) {
            throw new BadRequestHttpException('CLINIC_NOT_FOUND');
        }

        $cabinet = null;
        if ($cabinetId) {
            $cabinet = $this->cabinetRepository->find($cabinetId);
            if (!$cabinet) {
                throw new BadRequestHttpException('CABINET_NOT_FOUND');
            }

            if (!$cabinet->getStaff()->isEmpty()) {
                throw new BadRequestHttpException('CABINET_ALREADY_OCCUPIED');
            }
        }

        try {
            $experienceDate = new \DateTime($experience);
            if ($experienceDate > new \DateTime()) {
                throw new BadRequestHttpException('EXPERIENCE_DATE_CANNOT_BE_IN_FUTURE');
            }
        } catch (\Exception $e) {
            throw new BadRequestHttpException('INVALID_EXPERIENCE_DATE_FORMAT');
        }

    }

    public function getFullInfoStaffById(string $id): array
    {
        $staff = $this->staffRepository->find($id);
        if (!$staff) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        return $this->staffRepository->getFullInfoById($id);
    }

    public function deleteStaff(string $id): void
    {
        $staff = $this->staffRepository->find($id);
        if (!$staff) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        $this->staffRepository->delete($staff);
    }

    public function getAllStaffs(): array
    {
        $staffs = $this->staffRepository->getAllStaff();

        return $staffs;
    }

    public function updateServicesInStaff(string $staffId, array $services): void
    {
        $this->staffServiceRepository->updateStaffServices($staffId, $services);
    }

    public function updateSpecialisationInStaff(string $staffId, array $specialisations): void
    {
        $this->staffRepository->updateStaffSpecialisation($staffId, $specialisations);
    }

    private function findStaff(string $id): Staff
    {
        $staff = $this->staffRepository->find($id);

        if ($staff == null) {
            throw new BadRequestHttpException('STAFF_NOT_FOUND');
        }

        return $staff;
    }
}
