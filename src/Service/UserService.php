<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserService
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();

        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'patronymic' => $user->getPatronymic(),
                'phone' => $user->getPhone(),
                'roleId' => $user->getRole()->getId(),
            ];
        }

        return $result;
    }

    public function createUser(array $data): User
    {
        $login = $data['login'] ?? null;
        $password = $data['password'] ?? null;
        $phone = $data['phone'] ?? null;
        $firstname = $data['firstname'] ?? null;
        $lastname = $data['lastname'] ?? null;
        $patronymic = $data['patronymic'] ?? null;

        if ($this->userRepository->findUserByEmailOrPhone($login, $phone)) {
            throw new BadRequestHttpException('USER_ALREADY_EXIST');
        }

        $role = $this->roleRepository->findOneBy(["roleName" => "PATIENT"]);

        if ($role == null) {
            throw new BadRequestHttpException('ROLE_NOT_FOUND');
        }

        $user = new User();
        $user->setLogin($login)
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setPatronymic($patronymic)
            ->setPhone($phone)
            ->setRole($role);

        $this->userRepository->save($user);

        return $user;
    }

    public function updateUser(array $data): void
    {
        $id = $data['id'] ?? null;
        $login = $data['login'] ?? null;
        $phone = $data['phone'] ?? null;
        $firstname = $data['firstname'] ?? null;
        $lastname = $data['lastname'] ?? null;
        $patronymic = $data['patronymic'] ?? null;

        $user = $this->findUser($id);

        if ($login !== $user->getLogin()) {
            $existingUser = $this->userRepository->findOneBy(["login" => $login]);

            if ($existingUser !== null) {
                throw new BadRequestHttpException('LOGIN_ALREADY_EXIST');
            }
        }

        if ($phone !== $user->getPhone()) {
            $existingByPhone = $this->userRepository->findOneBy(["phone" => $phone]);

            if ($existingByPhone !== null) {
                throw new BadRequestHttpException('PHONE_ALREADY_EXIST');
            }
        }

        $user->setLogin($login)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setPatronymic($patronymic)
            ->setPhone($phone);

        $this->userRepository->save($user);
    }

    public function deleteUser(string $id): void
    {
        $user = $this->findUser($id);
        $this->userRepository->delete($user);
    }

    public function login(string $login, string $password): array
    {
        $user = $this->userRepository->findByLogin($login);

        if ($user == null) {
            throw new BadRequestHttpException('USER_NOT_FOUND');
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new BadRequestHttpException('USER_NOT_FOUND');
        }

        return [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'patronymic' => $user->getPatronymic(),
            'role' => $user->getRole()->getId(),
        ];
    }

    public function getUserAppointment(string $id): array
    {
        $appointments = $this->userRepository->getAllUserAppointments($id);

        $result = [];
        foreach ($appointments as $appointment) {
            $result[] = [
                'id' => $appointment->getId(),
                'date' => $appointment->getDate()->format('Y-m-d H:i'),
                'result' => $appointment->getResult(),
                'status' => $appointment->getStatus() ? [
                    'id' => $appointment->getStatus()->getId(),
                    'name' => $appointment->getStatus()->getName()
                ] : null,
                'staff' => $appointment->getStaff() ? [
                    'id' => $appointment->getStaff()->getId(),
                    'firstName' => $appointment->getStaff()->getFirstName(),
                    'lastName' => $appointment->getStaff()->getLastName(),
                    'patronymic' => $appointment->getStaff()->getPatronymic()
                ] : null,
                'service' => $appointment->getService() ? [
                    'id' => $appointment->getService()->getId(),
                    'name' => $appointment->getService()->getName()
                ] : null
            ];
        }

        return [
            'id' => $id,
            'appointments' => $result,
        ];
    }

    public function getUserReviews(string $id): array
    {
        $reviews = $this->userRepository->getAllUserReviews($id);

        $result = [];
        foreach ($reviews as $review) {
            $result[] = [
                'id' => $review->getId(),
                'description' => $review->getDescription(),
                'clinic' => $review->getClinic() ? [
                    'id' => $review->getClinic()->getId(),
                    'name' => $review->getClinic()->getName(),
                    'address' => $review->getClinic()->getAddress()
                ] : null,
            ];
        }

        return [
            'id' => $id,
            'reviews' => $result,
        ];
    }


    public function findUser(string $id): User
    {
        $user = $this->userRepository->find($id);

        if ($user == null) {
            throw new BadRequestHttpException('USER_NOT_FOUND');
        }

        return $user;
    }


}
