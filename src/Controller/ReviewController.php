<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\AppointmentRepository;
use App\Repository\ReviewRepository;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/review/create', name: 'review_create', methods: ['POST'])]
    public function createReview(ReviewService $reviewService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$this->validateReview($data)) {
            return new JsonResponse(['error' => 'INVALID_REVIEW_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $reviewService->createReview($data);
            return new JsonResponse(null, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/review/delete', name: 'review_delete', methods: ['POST'])]
    public function deleteReview(ReviewService $reviewService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;
        if (!$id) {
            return new JsonResponse(['error' => 'INVALID_REVIEW_DATA'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $reviewService->deleteReview($id);
            return new JsonResponse(null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/reviews/all', name: 'review_all', methods: ['GET'])]
    public function getAllReviews(ReviewService $reviewService, Request $request): JsonResponse
    {
        try{
            $reviews = $reviewService->getAllReviews();
            return new JsonResponse(['reviews' => $reviews], Response::HTTP_OK);
        }catch (\Exception $e)
        {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }
}
