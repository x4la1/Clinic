<?php

namespace App\Service;

use App\Entity\Specialization;
use App\Repository\SpecializationRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SpecializationService
{
    private SpecializationRepository $specializationRepository;

    public function __construct(SpecializationRepository $specializationRepository)
    {
        $this->specializationRepository = $specializationRepository;
    }

    public function createSpecialization(string $name): void
    {
        if ($this->specializationRepository->findOneBy(['name' => $name])) {
            throw new BadRequestHttpException('SPECIALIZATION_ALREADY_EXIST');
        }

        $specialization = new Specialization();
        $specialization->setName($name);
        $this->specializationRepository->save($specialization);
    }

    public function delete(string $id): void
    {
        $specialization = $this->specializationRepository->find($id);
        if ($specialization === null) {
            throw new BadRequestHttpException('SPECIALIZATION_NOT_FOUND');
        }
        $this->specializationRepository->delete($specialization);
    }

    public function findAllSpecializations(): array
    {
        $specializations = $this->specializationRepository->findAll();

        $result = [];
        foreach ($specializations as $specialization) {
            $result[] = [
                'id' => $specialization->getId(),
                'name' => $specialization->getName()
            ];
        }

        return $result;
    }


}
