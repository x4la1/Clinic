<?php

namespace App\Controller;

use App\Service\UserService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/register', name: 'user_register', methods: ['POST'])]
    public function registerUser(UserService $userService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateNewUserData($data)) {
            return new JsonResponse(['error' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $userService->createUser($data);
            return new JsonResponse(
                [
                    'user_id' => $user->getId(),
                    'user_role' => $user->getRole()->getRoleName(),
                ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/login', name: 'user_login', methods: ['POST'])]
    public function loginUser(UserService $userService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $userService->login($data["login"], $data["password"]);
            return new JsonResponse(['user_id' => $user['id'], 'user_role' => $user['role']], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/update', name: 'user_update', methods: ['POST'])]
    public function updateUser(UserService $userService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->validateUserData($data)) {
            return new JsonResponse(['error' => 'INVALID_DATA'], Response::HTTP_BAD_REQUEST);
        }


        try {
            $userService->updateUser($data);
            return new JsonResponse([], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}', name: 'user_profile', methods: ['GET'])]
    public function getUserProfile(UserService $userService, string $id): JsonResponse
    {
        try {
            $user = $userService->findUser($id);
            return new JsonResponse([
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'patronymic' => $user->getPatronymic(),
                'phone' => $user->getPhone(),
                'login' => $user->getLogin(),
                'user_role' => $user->getRole()->getId(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/delete', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(UserService $userService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data["id"] ?? null;

        if ($id === null) {
            throw new BadRequestHttpException("INVALID_DATA");
        }


        try {
            $userService->deleteUser($id);
            return new JsonResponse([], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/reviews/{id}', name: 'user_reviews', methods: ['GET'])]
    public function showUserReviews(UserService $userService, string $id): JsonResponse
    {
        try {
            $user = $userService->findUser($id);
            $data = $userService->getUserReviews($id);
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/users', name: 'users_all', methods: ['GET'])]
    public function showUsers(UserService $userService): JsonResponse
    {
        try {
            $users = $userService->getAllUsers();
            return new JsonResponse(['users' => $users], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateUserData(array $data): bool
    {
        $login = $data['login'] ?? null;
        $phone = $data['phone'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;

        if (empty($login) || empty($firstName) || empty($lastName) || empty($phone)) {
            return false;
        }

        return true;
    }

    private function validateNewUserData(array $data): bool
    {
        $login = $data['login'] ?? null;
        $phone = $data['phone'] ?? null;
        $firstName = $data['firstname'] ?? null;
        $lastName = $data['lastname'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($login) || empty($password) || empty($firstName) || empty($lastName) || empty($phone)) {
            return false;
        }

        return true;
    }

}
