<?php

namespace App\Controller;

use App\Entity\Clinic;
use App\Service\ClinicService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ClinicController extends AbstractController
{
    #[Route('/clinic/create', name: 'create_clinic', methods: ['POST'])]
    public function createClinic(ClinicService $clinicService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateClinic($data)) {
            throw new BadRequestHttpException("INVALID_CLINIC_DATA");
        }

        try {
            $clinicService->createClinic($data);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    #[Route('/clinic/update', name: 'update_clinic', methods: ['POST'])]
    public function updateClinic(ClinicService $clinicService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateClinic($data)) {
            return new JsonResponse(['error' => 'INVALID_CLINIC_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $clinicService->updateClinic($data);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    #[Route('/clinic/delete', name: 'delete_clinic', methods: ['POST'])]
    public function deleteClinic(ClinicService $clinicService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;

        if ($id == null) {
            return new JsonResponse(['error' => 'INVALID_CLINIC_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $clinicService->deleteClinic($id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    #[Route('/clinic/{id}', name: 'show_clinic', methods: ['GET'])]
    public function showClinic(ClinicService $clinicService, string $id): JsonResponse
    {
        try {
            $clinic = $clinicService->findClinic($id);
            return new JsonResponse([
                'id' => $clinic->getId(),
                'name' => $clinic->getName(),
                'email' => $clinic->getEmail(),
                'phone' => $clinic->getPhone(),
                'address' => $clinic->getAddress(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    #[Route('/clinic/free_cabinet/{id}', name: 'free_cabinet_clinic', methods: ['GET'])]
    public function getFreeCabinetInClinic(ClinicService $clinicService, string $id): JsonResponse
    {
        if ($id == null) {
            return new JsonResponse(['error' => 'INVALID_CLINIC_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $cabinets = $clinicService->findFreeCabinet($id);
            return new JsonResponse($cabinets, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage(), Response::HTTP_BAD_REQUEST]);
        }

    }

    #[Route('/clinics/all', name: 'all_clinics', methods: ['GET'])]
    public function getAllClinics(ClinicService $clinicService): JsonResponse
    {
        try {
            $clinics = $clinicService->getAllClinics();
            return new JsonResponse($clinics, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    private function validateClinic(array $data): bool
    {
        $name = $data['name'] ?? null;
        $address = $data['address'] ?? null;
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;

        if (empty(trim($name)) || empty(trim($address)) || empty(trim($phone)) || empty(trim($email))) {
            return false;
        }

        return true;
    }
}
