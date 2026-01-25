<?php

namespace App\Service;

use App\Repository\StatusRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class StatusService
{
    private StatusRepository $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function getAllStatus(): array
    {
        $statuses = $this->statusRepository->findAll();

        $result = [];
        foreach ($statuses as $status) {
            $result[] = [
                'id' => $status->getId(),
                'name' => $status->getName()
            ];
        }

        return $result;
    }
}
