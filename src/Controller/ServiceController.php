<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\ServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    #[Route('/service/create', name: 'service_create', methods: ['POST'])]
    public function createService(ServiceManager $serviceManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        if (!$this->validateService($data)) {
            return new JsonResponse(['error' => "INVALID_SERVICE_DATA"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $serviceManager->create($name);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/service/delete', name: 'service_delete', methods: ['POST'])]
    public function deleteService(ServiceManager $serviceManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            return new JsonResponse(['error' => "INVALID_SERVICE_DATA"], Response::HTTP_BAD_REQUEST);
        }
        try {
            $serviceManager->delete($id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/services', name: 'service_show', methods: ['GET'])]
    public function showAllServices(ServiceManager $serviceManager, Request $request): JsonResponse
    {
        try {
            $services = $serviceManager->findAllServices();
            return new JsonResponse($services, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateService(array $data): bool
    {
        $name = $data['name'] ?? null;
        if (empty(trim($name))) {
            return false;
        }

        return true;
    }
}
