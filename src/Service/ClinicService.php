<?php

namespace App\Service;

use App\Entity\Clinic;
use App\Repository\CabinetRepository;
use App\Repository\ClinicRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClinicService
{
    private ClinicRepository $clinicRepository;

    private CabinetRepository $cabinetRepository;
    private ImageService $imageService;

    public function __construct(ClinicRepository $clinicRepository, ImageService $imageService, CabinetRepository $cabinetRepository)
    {
        $this->clinicRepository = $clinicRepository;
        $this->imageService = $imageService;
        $this->cabinetRepository = $cabinetRepository;
    }

    public function createClinic(array $data): Clinic
    {
        $name = $data['name'] ?? null;
        $address = $data['address'] ?? null;
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;
        $image = $data['image'] ?? null;

        if ($this->clinicRepository->findByPhoneOrEmail($phone, $email)) {
            throw new BadRequestHttpException('CLINIC_ALREADY_EXIST');
        }

        $clinic = new Clinic();
        $clinic->setName($name)
            ->setAddress($address)
            ->setPhone($phone)
            ->setEmail($email);

        $this->clinicRepository->save($clinic);

        if ($image) {
            $imagePath = $this->saveClinicImage($clinic->getId(), $image);
            $clinic->setImagePath($imagePath);

            $this->clinicRepository->save($clinic);
        }


        return $clinic;
    }

    public function updateClinic(array $data): void
    {
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $address = $data['address'] ?? null;
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;
        $image = $data['image'] ?? null;

        $clinic = $this->findClinic($id);

        if ($email !== $clinic->getEmail()) {
            $existingByEmail = $this->clinicRepository->findOneBy(["email" => $email]);

            if ($existingByEmail !== null) {
                throw new BadRequestHttpException('LOGIN_ALREADY_EXIST');
            }
        }

        if ($phone !== $clinic->getPhone()) {
            $existingByPhone = $this->clinicRepository->findOneBy(["phone" => $phone]);

            if ($existingByPhone !== null) {
                throw new BadRequestHttpException('PHONE_ALREADY_EXIST');
            }
        }

        if ($image) {
            $this->updateClinicImage($clinic, $image);
        }

        $clinic->setName($name)
            ->setAddress($address)
            ->setPhone($phone)
            ->setEmail($email);

        $this->clinicRepository->save($clinic);
    }

    public function deleteClinic(string $id): void
    {
        $clinic = $this->findClinic($id);
        $this->clinicRepository->delete($clinic);
    }

    public function getAllClinics(): array
    {
        $clinics = $this->clinicRepository->findAll();

        $result = [];
        foreach ($clinics as $clinic) {
            $result[] = [
                'id' => $clinic->getId(),
                'name' => $clinic->getName(),
                'address' => $clinic->getAddress(),
                'phone' => $clinic->getPhone(),
                'email' => $clinic->getEmail(),
                'imagePath' => $clinic->getImagePath()
            ];
        }

        return $result;
    }

    public function findClinic(string $id): ?Clinic
    {
        $clinic = $this->clinicRepository->find($id);

        if ($clinic === null) {
            throw new BadRequestHttpException('CLINIC_NOT_FOUND');
        }

        return $clinic;
    }

    public function findFreeCabinet(string $clinidId): array
    {
        return $this->cabinetRepository->findFreeCabinetsByClinicId($clinidId);
    }

    private
    function saveClinicImage(int $clinidId, string $imageData): string
    {
        $filename = 'clinic_image_' . $clinidId;

        return $this->imageService->saveImage($imageData, $filename);
    }

    private
    function updateClinicImage(Clinic $clinic, string $imageData): void
    {
        $oldImage = $clinic->getImagePath();
        if ($oldImage) {
            $this->imageService->deleteImage($oldImage);
        }

        $filename = $this->saveClinicImage($clinic->getId(), $imageData);
        $clinic->setImagePath($filename);
    }


}
