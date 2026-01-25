<?php

namespace App\Service;

use App\Entity\Review;
use App\Repository\ClinicRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ReviewService
{
    private ReviewRepository $reviewRepository;
    private ClinicRepository $clinicRepository;
    private UserRepository $userRepository;

    public function __construct(ReviewRepository $reviewRepository, ClinicRepository $clinicRepository, UserRepository $userRepository)
    {
        $this->reviewRepository = $reviewRepository;
        $this->clinicRepository = $clinicRepository;
        $this->userRepository = $userRepository;
    }


    public function getAllReviews()
    {
        $reviews = $this->reviewRepository->findAll();

    }

    public function createReview(array $data): void
    {
        $userId = $data['id'] ?? null;
        $clinicId = $data['clinic_id'] ?? null;
        $description = $data['description'] ?? null;

        $user = $this->userRepository->find($userId);
        $clinic = $this->clinicRepository->find($clinicId);

        if (!$clinic || !$user) {
            throw new BadRequestException("INVALID_DATA");
        }

        $review = new Review();
        $review->setUser($user);
        $review->setClinic($clinic);
        $review->setDescription($description);

        $this->reviewRepository->save($review);
    }

    public function deleteReview(string $id): void
    {
        $review = $this->reviewRepository->find($id);
        if (!$review) {
            throw new BADRequestException("REVIEW_NOT_FOUND");
        }

        $this->reviewRepository->delete($review);
    }

    public function findReviewsByClinicId(string $clinicId): array
    {
        $qb = $this->reviewRepository->createQueryBuilder('r')
            ->select('r.id, r.description')
            ->addSelect('u.id as userId, u.firstName, u.lastName, u.lastName')
            ->innerJoin('r.user', 'u')
            ->where('r.clinic = :clinicId')
            ->setParameter('clinicId', $clinicId)
            ->getQuery();

        $reviews = $qb->getResult();

        $result = [];

        foreach ($reviews as $row) {
            $result[] = [
                'id' => $row['id'],
                'description' => $row['description'],
                'user' => [
                    'id' => $row['userId'],
                    'fullname' => trim($row['firstName'] . ' ' . $row['lastName']),
                ]
            ];
        }

        return $result;
    }

}
