<?php

namespace App\Entity;

use App\Repository\StaffServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;

#[ORM\Entity(repositoryClass: StaffServiceRepository::class)]
class StaffService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Staff::class)]
    #[ORM\JoinColumn(name: 'staff_id', nullable: false, onDelete: 'CASCADE')]
    private Staff|null $staff = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'service_id', nullable: false, onDelete: 'RESTRICT')]
    private Service|null $service = null;

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(?Staff $staff): void
    {
        $this->staff = $staff;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): void
    {
        $this->service = $service;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
