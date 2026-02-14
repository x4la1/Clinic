<?php

namespace App\Service;

use App\Entity\Staff;
use App\Entity\TimeSlot;
use App\Repository\StaffRepository;
use App\Repository\StaffTimeSlotRepository;
use App\Repository\TimeSlotRepository;

class StaffTimeSlotService
{
    public function __construct(
        private StaffRepository         $staffRepository,
        private StaffTimeSlotRepository $staffTimeSlotRepository,
        private TimeSlotRepository      $timeSlotRepository,
    )
    {
    }

    public function updateStaffTimeSlot(string $staffId, array $timeSlotIds): void
    {
        $this->staffTimeSlotRepository->updateStaffTimeSlots($staffId, $timeSlotIds);
    }
}
