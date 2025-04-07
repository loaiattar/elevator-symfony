<?php

namespace App\Entity;

use App\Repository\ElevatorSystemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ElevatorSystemRepository::class)]
class ElevatorSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['system:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['system:read'])]
    private ?int $maxFloor = null;

    #[ORM\Column]
    #[Groups(['system:read'])]
    private ?int $minFloor = null;

    #[ORM\Column(length: 20)]
    #[Groups(['system:read'])]
    private ?string $systemStatus = 'operational';

    #[ORM\OneToMany(targetEntity: Elevator::class, mappedBy: 'elevatorSystem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['system:read'])]
    private Collection $elevators;

    #[ORM\Column(type: "json", nullable: true)]
    #[Groups(['system:read'])]
    private array $callHistory = [];

    public function __construct(int $maxFloor = 10, int $minFloor = 0)
    {
        $this->maxFloor = $maxFloor;
        $this->minFloor = $minFloor;
        $this->systemStatus = 'operational';
        $this->elevators = new ArrayCollection();
        $this->callHistory = [];
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

    public function getSystemStatus(): ?string
    {
        return $this->systemStatus;
    }

    public function setSystemStatus(string $systemStatus): static
    {
        $this->systemStatus = $systemStatus;

        return $this;
    }

    /**
     * @return Collection<int, Elevator>
     */
    public function getElevators(): Collection
    {
        return $this->elevators;
    }

    public function addElevator(Elevator $elevator): static
    {
        if (!$this->elevators->contains($elevator)) {
            $this->elevators->add($elevator);
            $elevator->setElevatorSystem($this);
        }

        return $this;
    }

    public function removeElevator(Elevator $elevator): static
    {
        if ($this->elevators->removeElement($elevator)) {
            // set the owning side to null (unless already changed)
            if ($elevator->getElevatorSystem() === $this) {
                $elevator->setElevatorSystem(null);
            }
        }

        return $this;
    }

    public function getCallHistory(): array
    {
        return $this->callHistory;
    }

    public function setCallHistory(array $callHistory): static
    {
        $this->callHistory = $callHistory;

        return $this;
    }

    public function addToCallHistory(int $floor, string $direction): static
    {
        $this->callHistory[] = [
            'time' => time(),
            'floor' => $floor,
            'direction' => $direction
        ];

        return $this;
    }

    public function getSystemInfo(): array
    {
        return [
            "status" => $this->systemStatus,
            "elevators" => count($this->elevators),
            "max_floor" => $this->maxFloor,
            "min_floor" => $this->minFloor,
            "recent_calls" => array_slice($this->callHistory, -5)
        ];
    }
    
    public function isEmergencyStopped(): bool
    {
        return $this->systemStatus === 'emergency_stopped';
    }
}