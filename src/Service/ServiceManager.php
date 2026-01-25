<?php

namespace App\Service;

use App\Entity\Service;
use App\Entity\Staff;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceManager
{
    private ServiceRepository $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function create(string $name): void
    {
        if ($this->serviceRepository->findOneBy(['name' => $name])) {
            throw new BadRequestHttpException('SERVICE_ALREADY_EXIST');
        }

        $service = new Service();
        $service->setName($name);
        $this->serviceRepository->save($service);
    }

    public function delete(string $id): void
    {
        $service = $this->serviceRepository->find($id);
        if ($service === null) {
            throw new BadRequestHttpException('SERVICE_NOT_FOUND');
        }
        $this->serviceRepository->delete($service);
    }

    public function findAllServices(): array
    {
        $services = $this->serviceRepository->findAll();

        $result = [];
        foreach ($services as $service) {
            $result[] = [
                'id' => $service->getId(),
                'name' => $service->getName()
            ];
        }

        return $result;
    }
}
