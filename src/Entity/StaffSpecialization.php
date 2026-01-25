<?php

namespace App\Entity;

use App\Repository\StaffSpecializationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StaffSpecializationRepository::class)]
class StaffSpecialization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Staff::class)]
    #[ORM\JoinColumn(name: 'staff_id', nullable: false, onDelete: 'CASCADE')]
    private Staff|null $staff = null;

    #[ORM\ManyToOne(targetEntity: Specialization::class)]
    #[ORM\JoinColumn(name: 'specialization_id', nullable: false, onDelete: 'CASCADE')]
    private Specialization|null $specialization = null;

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): void
    {
        $this->staff = $staff;
    }

    public function getSpecialization(): ?Specialization
    {
        return $this->specialization;
    }

    public function setSpecialization(?Specialization $specialization): void
    {
        $this->specialization = $specialization;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
