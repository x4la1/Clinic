<?php

namespace App\Controller;

use App\Entity\Staff;
use App\Service\StaffManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StaffController extends AbstractController
{
    #[Route('/staff/create', name: 'staff_create', methods: ['POST'])]
    public function createStaff(StaffManagerService $staffService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateNewStaff($data)) {
            return new JsonResponse(['error' => "INVALID_STAFF_DATA", Response::HTTP_BAD_REQUEST]);
        }

        try {
            $staffService->createStaff($data);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/staff/update', name: 'staff_update', methods: ['POST'])]
    public function updateStaff(StaffManagerService $staffService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateNewStaff($data)) {
            return new JsonResponse(['error' => "INVALID_STAFF_DATA", Response::HTTP_BAD_REQUEST]);
        }

        try {
            $staffService->updateStaff($data);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/staff/delete', name: 'staff_delete', methods: ['POST'])]
    public function deleteStaff(StaffManagerService $staffService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data["id"] ?? null;

        if ($id === null) {
            return new JsonResponse(['error' => "INVALID_DATA"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $staffService->deleteStaff($id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/staff/{id}', name: 'show_staff_solo', methods: ['GET'])]
    public function showStaff(StaffManagerService $staffService, string $id): JsonResponse
    {
        try {
            $data = $staffService->getFullInfoStaffById($id);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/staffs', name: 'show_staffs', methods: ['GET'])]
    public function showAllStaff(StaffManagerService $staffService): JsonResponse
    {
        try {
            $data = $staffService->getAllStaffs();
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/staff/services/update', name: 'update_services', methods: ['POST'])]
    public function updateServiceToStaff(StaffManagerService $staffService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data["id"] ?? null;
        $services = $data["services"] ?? null;

        if (!is_array($services)) {
            return new JsonResponse(['error' => "INVALID_DATA"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $staffService->updateServicesInStaff($id, $services);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/staff/specialization/update', name: 'specialization_update', methods: ['POST'])]
    public function updateSpecialisationToStaff(StaffManagerService $staffService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data["id"] ?? null;
        $specializations = $data["specializations"] ?? null;

        if (!is_array($specializations)) {
            return new JsonResponse(['error' => "INVALID_DATA"], Response::HTTP_BAD_REQUEST);
        }

        try {
            $staffService->updateSpecialisationInStaff($id, $specializations);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateStaff($data): bool
    {
        $id = $data['id'] ?? null;
        $clinicId = $data['clinic_id'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;
        $experience = $data['experience'] ?? null;
        $phone = $data['phone'] ?? null;

        if (empty(trim($clinicId)) || empty(trim($firstName)) || empty(trim($lastName)) || empty(trim($experience)) || empty(trim($phone)) || empty($id)) {
            return false;
        }

        return true;
    }

    private function validateNewStaff(array $data): bool
    {
        $clinicId = $data['clinic_id'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;
        $experience = $data['experience'] ?? null;
        $phone = $data['phone'] ?? null;

        if (empty(trim($clinicId)) || empty(trim($firstName)) || empty(trim($lastName)) || empty(trim($experience)) || empty(trim($phone))) {
            return false;
        }

        return true;
    }


}
