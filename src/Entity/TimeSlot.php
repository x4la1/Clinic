<?php

namespace App\Entity;

use App\Repository\TimeSlotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSlotRepository::class)]
class TimeSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $slot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlot(): ?\DateTime
    {
        return $this->slot;
    }

    public function setSlot(\DateTime $slot): static
    {
        $this->slot = $slot;

        return $this;
    }
}
