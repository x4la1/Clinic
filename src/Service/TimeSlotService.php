<?php

namespace App\Service;

use App\Entity\TimeSlot;
use App\Repository\TimeSlotRepository;

class TimeSlotService
{
    public function __construct(private TimeSlotRepository $timeSlotRepository)
    {
    }

    public function getAllTimeSlots(): array
    {
        $timeSlots = $this->timeSlotRepository->findAll();
        $result = [];

        foreach ($timeSlots as $timeSlot) {
            $result[] = [
                'id' => $timeSlot->getId(),
                'slot' => $timeSlot->getSlot()->format('H:i'),
            ];
        }

        return $result;
    }

    public function getFreeTimeSlotByStaffId(int $staffId, string $date): array
    {
        $newDate = new \DateTime($date);
        $timeSlots = $this->timeSlotRepository->getFreeTimeSlotForStuff($staffId, $newDate);

        return $timeSlots;
    }
}
