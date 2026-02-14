<?php

namespace App\Controller;

use App\Service\TimeSlotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TimeSlotController extends AbstractController
{
    #[Route('/timeslots/all', name: 'timeslots_all', methods: ['GET'])]
    public function getAllTimeSlots(TimeSlotService $timeSlotService, Request $request): JsonResponse
    {
        try {
            $timeSlots = $timeSlotService->getAllTimeSlots();
            return new JsonResponse(['timeslots' => $timeSlots], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/staff/timeslots/{id}/{date}', name: 'timeslots_show', methods: ['GET'])]
    public function getFreeTimeSlotsForStaff(TimeSlotService $timeSlotService, Request $request, string $id, string $date): JsonResponse
    {
        try{
            $slots = $timeSlotService->getFreeTimeSlotByStaffId($id, $date);
            return new JsonResponse(['timeslots' => $slots], Response::HTTP_OK);
        }catch (\Exception $e){
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


}
