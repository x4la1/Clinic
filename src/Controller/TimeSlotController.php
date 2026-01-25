<?php

namespace App\Controller;

use App\Repository\TimeSlotRepository;
use App\Service\TimeSlotService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TimeSlotController
{
    public function __construct(TimeSlotRepository $timeSlotRepository)
    {
        $this->timeSlotRepository = $timeSlotRepository;
    }

    #[Route('/timeslots/create', name: 'timeslot_create', methods: ['POST'])]
    public function addTimeSlot(TimeSlotService $timeSlotService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if(!$this->validateTimeSlots($data)){
            throw new BadRequestHttpException("INVALID_SLOTS_DATA");
        }


    }

    private function validateTimeSlots(array $data): bool
    {
        $id = $data["id"] ?? null;
        $slots = $data["slots"] ?? null;
        if (empty($id) || empty($slots) || !is_array($slots)) {
            return false;
        }
        return true;
    }
}
