<?php

namespace App\Service;

use App\Entity\TimeSlot;
use App\Repository\StaffRepository;
use App\Repository\TimeSlotRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TimeSlotService
{
    private TimeSlotRepository $timeSlotRepository;
    private StaffRepository $staffRepository;

    public function __construct(TimeSlotRepository $timeSlotRepository, StaffRepository $staffRepository)
    {
        $this->timeSlotRepository = $timeSlotRepository;
        $this->staffRepository = $staffRepository;
    }

    public function createTimeSlot(array $data): void
    {
        $staff_id = $data['staff_id'] ?? null;
        $time = $data['time'] ?? null;

        $staff = $this->staffRepository->find($staff_id);
        if ($staff == null) {
            throw new BadRequestHttpException("STAFF_NOT_FOUND");
        }

        $timeSlot = new TimeSlot();
        $timeSlot->setStaff($staff)
            ->setSlot($time);

        $this->timeSlotRepository->save($timeSlot);
    }

    public function createTimeSlots(array $data): void
    {
        $staff_id = $data['staff_id'] ?? null;
        $timeSlots = $data['timeSlots'] ?? null;

        $staff = $this->staffRepository->find($staff_id);
        if($staff == null) {
            throw new BadRequestHttpException("STAFF_NOT_FOUND");
        }

        $this->timeSlotRepository->createTimeSlots($staff, $timeSlots);

    }

    public function deleteTimeSlot(string $id): void
    {
        $timeslot = $this->timeSlotRepository->find($id);
        if (!$timeslot) {
            throw new BadRequestHttpException("TIMESLOT_NOT_FOUND");
        }

        $this->timeSlotRepository->delete($timeslot);
    }

    public function getTimeSlotByStaffId(string $id): array
    {

    }
}
