<?php

namespace App\Controller;

use App\Service\CabinetService;
use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CabinetController extends AbstractController
{
    #[Route('/cabinet/create', name: 'create_cabinet', methods: ['POST'])]
    public function createCabinet(CabinetService $cabinetService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$this->validateCabinet($data)) {
            return new JsonResponse(['error' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $cabinetService->createCabinet($data);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/cabinets/all', name: 'get_all_cabinets', methods: ['GET'])]
    public function getAllCabinets(CabinetService $cabinetService, Request $request): JsonResponse
    {
        try {
            $cabinets = $cabinetService->getAllCabinets();
            return new JsonResponse(['cabinets' => $cabinets], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/cabinet/delete', name: 'delete_cabinet', methods: ['POST'])]
    public function deleteCabinet(CabinetService $cabinetService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;

        if ($id == null) {
            return new JsonResponse(['error' => 'INVALID_CABINET_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $cabinetService->deleteCabinet($id);
            return new JsonResponse([], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/cabinet/list/{id}', name: 'list_cabinet', methods: ['GET'])]
    public function getAllCabinetsInClinic(CabinetService $cabinetService, string $id): JsonResponse
    {
        try {
            $cabinets = $cabinetService->getAllCabinetsByClinicId($id);
            return new JsonResponse($cabinets, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateCabinet(array $data): bool
    {
        $id = $data['id'];
        $number = $data['number'] ?? null;

        if (empty(trim($number)) || (int)$number <= 0 || empty(trim($id))) {
            return false;
        }
        return true;
    }

}
