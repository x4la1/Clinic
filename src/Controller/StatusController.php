<?php

namespace App\Controller;

use App\Repository\StatusRepository;
use App\Service\StatusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{
    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    #[Route('/statuses', name: 'show_statuses', methods: ['GET'])]
    public function showAllStatuses(StatusService $statusService, Request $request): JsonResponse
    {
        try {
            $statuses = $statusService->getAllStatus();
            return new JsonResponse($statuses, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }
}
