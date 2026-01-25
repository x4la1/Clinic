<?php

namespace App\Entity;

use App\Repository\StaffRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StaffRepository::class)]
class Staff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 35)]
    private ?string $firstName = null;

    #[ORM\Column(length: 35)]
    private ?string $lastName = null;

    #[ORM\Column(length: 35)]
    private ?string $patronymic = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $experience = null;

    #[ORM\Column(length: 15)]
    private ?string $phone = null;

    #[ORM\ManyToOne(targetEntity: Clinic::class)]
    #[ORM\JoinColumn(name: 'clinic_id', nullable: false, onDelete: 'RESTRICT')]
    private Clinic|null $clinic = null;

    #[ORM\ManyToOne(targetEntity: Cabinet::class)]
    #[ORM\JoinColumn(name: 'cabinet_id', nullable: true, onDelete: 'SET NULL')]
    private Cabinet|null $cabinet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function setClinic(?Clinic $clinic): static
    {
        $this->clinic = $clinic;

        return $this;
    }

    public function getCabinet(): ?Cabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?Cabinet $cabinet): static
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(string $patronymic): static
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    public function getExperience(): ?\DateTime
    {
        return $this->experience;
    }

    public function setExperience(\DateTime $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
