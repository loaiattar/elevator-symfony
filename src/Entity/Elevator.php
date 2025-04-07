<?php

namespace App\Entity;

use App\Repository\ElevatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ElevatorRepository::class)]
class Elevator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?int $maxFloor = null;

    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?int $minFloor = null;

    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?int $currentFloor = null;

    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?bool $moving = false;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['elevator:read'])]
    private ?string $direction = null;

    #[ORM\Column]
    #[Groups(['elevator:read'])]
    private ?bool $doorOpen = false;

    #[ORM\Column(type: "json", nullable: true)]
    #[Groups(['elevator:read'])]
    private array $queue = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['elevator:read'])]
    private ?string $nextMaintenance = null;

    #[ORM\ManyToOne(inversedBy: 'elevators')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ElevatorSystem $elevatorSystem = null;

    public function __construct(int $maxFloor, int $minFloor, ElevatorSystem $elevatorSystem)
    {
        $this->maxFloor = $maxFloor;
        $this->minFloor = $minFloor;
        $this->currentFloor = $minFloor;
        $this->moving = false;
        $this->direction = null;
        $this->doorOpen = false;
        $this->queue = [];
        $this->nextMaintenance = "Not scheduled";
        $this->elevatorSystem = $elevatorSystem;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxFloor(): ?int
    {
        return $this->maxFloor;
    }

    public function setMaxFloor(int $maxFloor): static
    {
        $this->maxFloor = $maxFloor;

        return $this;
    }

    public function getMinFloor(): ?int
    {
        return $this->minFloor;
    }

    public function setMinFloor(int $minFloor): static
    {
        $this->minFloor = $minFloor;

        return $this;
    }

    public function getCurrentFloor(): ?int
    {
        return $this->currentFloor;
    }

    public function setCurrentFloor(int $currentFloor): static
    {
        $this->currentFloor = $currentFloor;

        return $this;
    }

    public function isMoving(): ?bool
    {
        return $this->moving;
    }

    public function setMoving(bool $moving): static
    {
        $this->moving = $moving;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function isDoorOpen(): ?bool
    {
        return $this->doorOpen;
    }

    public function setDoorOpen(bool $doorOpen): static
    {
        $this->doorOpen = $doorOpen;

        return $this;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function setQueue(?array $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function addToQueue(int $floor): static
    {
        if (!in_array($floor, $this->queue)) {
            $this->queue[] = $floor;
        }

        return $this;
    }

    public function removeFromQueue(int $floor): static
    {
        $this->queue = array_filter($this->queue, function($value) use ($floor) {
            return $value !== $floor;
        });

        return $this;
    }

    public function getNextMaintenance(): ?string
    {
        return $this->nextMaintenance;
    }

    public function setNextMaintenance(?string $nextMaintenance): static
    {
        $this->nextMaintenance = $nextMaintenance;

        return $this;
    }

    public function getElevatorSystem(): ?ElevatorSystem
    {
        return $this->elevatorSystem;
    }

    public function setElevatorSystem(?ElevatorSystem $elevatorSystem): static
    {
        $this->elevatorSystem = $elevatorSystem;

        return $this;
    }

    public function getStatus(): array
    {
        return [
            "id" => $this->id,
            "current_floor" => $this->currentFloor,
            "moving" => $this->moving,
            "direction" => $this->direction,
            "door_open" => $this->doorOpen,
            "queue" => $this->queue,
            "next_maintenance" => $this->nextMaintenance
        ];
    }
}
