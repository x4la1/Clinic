<?php

namespace App\Controller;

use App\Service\SpecializationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpecializationController extends AbstractController
{
    #[Route('/specialization/create', name: 'create_specialization', methods: ['POST'])]
    public function createSpecialization(SpecializationService $specializationService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        if (!$name) {
            return new JsonResponse(['error' => 'INVALID_SPECIALIZATION_DATA'], Response::HTTP_BAD_REQUEST);
        }


        try {
            $specializationService->createSpecialization($name);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/specialization/delete', name: 'delete_specialization', methods: ['POST'])]
    public function deleteSpecialization(SpecializationService $specializationService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            return new JsonResponse(['error' => 'INVALID_SPECIALIZATION_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $specializationService->delete($id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/specializations', name: 'show_specialization', methods: ['GET'])]
    public function showAllSpecializations(SpecializationService $specializationService): JsonResponse
    {
        try{
            $specializations = $specializationService->findAllSpecializations();
            return new JsonResponse($specializations, Response::HTTP_OK);
        }catch (\Exception $exception){
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
