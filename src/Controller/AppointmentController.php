<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Service\AppointmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppointmentController extends AbstractController
{
    #[Route('/appointments/all', name: 'appointment', methods: ['GET'])] //TODO
    public function index(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/appointment/create', name: 'create_appointment', methods: ['POST'])]
    public function createAppointment(Request $request, AppointmentService $appointmentService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $appointmentService->createAppointment($data);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/appointment/status/update', name: 'appointment_status_update', methods: ['POST'])]
    public function updateAppointmentStatus(Request $request, AppointmentService $appointmentService): Response
    {
        $data = json_decode($request->getContent(), true);
        try {
            $appointmentService->updateAppointmentStatus($data);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/appointment/result/update', name: 'appointment_result_update', methods: ['POST'])]
    public function updateAppointmentResult(Request $request, AppointmentService $appointmentService): Response
    {
        $data = json_decode($request->getContent(), true);
        $result = $data['result'] ?? null;
        if (empty(trim($result))) {
            return new JsonResponse(['error' => 'INVALID_RESULT'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $appointmentService->updateAppointmentResult($data);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/appointments/{id}', name: 'user_appointments', methods: ['GET'])]
    public function getUserAppointments(AppointmentService $appointmentService, string $id): JsonResponse
    {
        try {
            $appointments = $appointmentService->getAllUserAppointments($id);
            return new JsonResponse(['appointments' => $appointments], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
