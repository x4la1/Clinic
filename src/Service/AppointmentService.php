<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\ServiceRepository;
use App\Repository\StaffRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use DateTime;
use http\Env\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppointmentService
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private StaffRepository       $staffRepository,
        private ServiceRepository     $serviceRepository,
        private UserRepository        $userRepository,
        private StatusRepository      $statusRepository,
    )
    {
    }

    public function createAppointment(array $data): void
    {
        $userId = $data['user_id'] ?? null;
        $staffId = $data['staff_id'] ?? null;
        $serviceId = $data['service_id'] ?? null;
        $date = $data['date'] ?? null;

        $dateTime = new \DateTime($date);
        $now = new \DateTime('now');
        $maxDate = new \DateTime('+1 year');

        if (!$date || $dateTime < $now || $dateTime > $maxDate) {
            throw new \Exception('INVALID_DATE');
        }

        $staff = $this->staffRepository->find($staffId);
        if (!$staff) {
            throw new \Exception('STAFF_NOT_FOUND');
        }

        $service = $this->serviceRepository->find($serviceId);
        if (!$service) {
            throw new \Exception('SERVICE_NOT_FOUND');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \Exception('USER_NOT_FOUND');
        }

        $appointment = $this->appointmentRepository->findOneBy(['date' => $dateTime, 'staff' => $staff]);
        if ($appointment) {
            throw new \Exception('DATE_ALREADY_BOOKED');
        }

        $status = $this->statusRepository->findOneBy(['id' => '1']);

        $appointment = new Appointment();
        $appointment
            ->setDate($dateTime)
            ->setUser($user)
            ->setStaff($staff)
            ->setStatus($status)
            ->setService($service);

        $this->appointmentRepository->save($appointment);
    }

    public function updateAppointmentStatus(array $data): void
    {
        $id = $data['id'] ?? null;
        $statusId = $data['status_id'] ?? null;

        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            throw new \Exception('APPOINTMENT_NOT_FOUND');
        }

        $status = $this->statusRepository->find($statusId);
        if (!$status) {
            throw new \Exception('STATUS_NOT_FOUND');
        }

        $appointment->setStatus($status);
        $this->appointmentRepository->save($appointment);
    }

    public function updateAppointmentResult(array $data): void
    {
        $id = $data['id'] ?? null;
        $result = $data['result'] ?? null;

        $appointment = $this->appointmentRepository->find($id);
        if (!$appointment) {
            throw new \Exception('APPOINTMENT_NOT_FOUND');
        }

        $appointment->setResult($result);
        $this->appointmentRepository->save($appointment);
    }

    public function getAllUserAppointments(string $id): array
    {
        $user = $this->userRepository->find($id);
        $appointments = $this->appointmentRepository->findBy(['user' => $user]);
        $result = [];

        foreach ($appointments as $appointment) {
            $result[] = [
                'id' => $appointment->getId(),
                'user_id' => $appointment->getUser()->getId(),
                'staff_id' => $appointment->getStaff()->getId(),
                'status_id' => $appointment->getStatus()->getId(),
                'date' => $appointment->getDate()->format('Y-m-d H:i'),
                'result' => $appointment->getResult(),
            ];
        }

        return $result;
    }

    public function getAllAppointments(): array
    {
        $appointments = $this->appointmentRepository->findAll();
        $result = [];
        foreach ($appointments as $appointment) {
            $result[] = [
                'id' => $appointment->getId(),
                'date' => $appointment->getDate()->format('Y-m-d H:i'),
                'result' => $appointment->getResult(),
                'status' => $appointment->getStatus(),
                'staff' => [
                    'id' => $appointment->getStaff()->getId(),
                    'firstName' => $appointment->getStaff()->getFirstName(),
                    'lastName' => $appointment->getStaff()->getLastName(),
                    'patronymic' => $appointment->getStaff()->getPatronymic(),
                ],
                'service' => [
                    'id' => $appointment->getService()->getId(),
                    'name' => $appointment->getService()->getName(),
                ]
            ];
        }

        return $result;
    }

}
