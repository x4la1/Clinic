<?php

namespace App\Service;

use App\Entity\Cabinet;
use App\Repository\CabinetRepository;
use App\Repository\ClinicRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CabinetService
{
    private CabinetRepository $cabinetRepository;
    private ClinicRepository $clinicRepository;

    public function __construct(CabinetRepository $cabinetRepository, ClinicRepository $clinicRepository)
    {
        $this->cabinetRepository = $cabinetRepository;
        $this->clinicRepository = $clinicRepository;
    }

    public function createCabinet(array $data): void
    {
        $clinidId = $data['id'] ?? null;
        $number = $data['number'] ?? null;
        $description = $data['description'] ?? null;

        $clinic = $this->clinicRepository->find($clinidId);
        if ($clinic == null) {
            throw new BadRequestHttpException("CLINIC_NOT_FOUND");
        }

        if ($this->cabinetRepository->findOneBy(['number' => $number])) {
            throw new BadRequestHttpException("NUMBER_ALREADY_EXISTS");
        }

        $cabinet = new Cabinet();
        $cabinet->setNumber($number)
            ->setDescription($description)
            ->setClinic($clinic);

        $this->cabinetRepository->save($cabinet);
    }

    public function getAllCabinetsByClinicId(string $id): array
    {
        $cabinets = $this->cabinetRepository->findAllByClinicId($id);

        $result = [];
        foreach ($cabinets as $cabinet) {
            $result[] = [
                'id' => $cabinet->getId(),
                'number' => $cabinet->getNumber(),
                'description' => $cabinet->getDescription()
            ];
        }

        return
            [
                'clinic_id' => $id,
                'cabinets' => $result
            ];

    }

    public function deleteCabinet(string $id): void
    {
        $cabinet = $this->findCabinet($id);
        $this->cabinetRepository->delete($cabinet);
    }

    public function findCabinet(string $id): Cabinet
    {
        $cabinet = $this->cabinetRepository->find($id);

        if ($cabinet == null) {
            throw new BadRequestHttpException('CABINET_NOT_FOUND');
        }

        return $cabinet;
    }
}
