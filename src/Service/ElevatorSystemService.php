<?php

namespace App\Service;

use App\Entity\Elevator;
use App\Entity\ElevatorSystem;
use App\Repository\ElevatorRepository;
use App\Repository\ElevatorSystemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ElevatorSystemService
{
    private $entityManager;
    private $elevatorRepository;
    private $elevatorSystemRepository;
    private $elevatorService;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ElevatorRepository $elevatorRepository,
        ElevatorSystemRepository $elevatorSystemRepository,
        ElevatorService $elevatorService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->elevatorRepository = $elevatorRepository;
        $this->elevatorSystemRepository = $elevatorSystemRepository;
        $this->elevatorService = $elevatorService;
        $this->logger = $logger;
    }

    public function createElevatorSystem(int $numElevators = 2, int $maxFloor = 10, int $minFloor = 0): ElevatorSystem
    {
        $elevatorSystem = new ElevatorSystem($maxFloor, $minFloor);
        $this->elevatorSystemRepository->save($elevatorSystem, true);

        for ($i = 0; $i < $numElevators; $i++) {
            $elevator = new Elevator($maxFloor, $minFloor, $elevatorSystem);
            $this->elevatorRepository->save($elevator, true);
        }

        $this->logger->info(sprintf("Created new elevator system with %d elevators, max floor %d, min floor %d",
            $numElevators, $maxFloor, $minFloor));

        return $elevatorSystem;
    }

    public function getElevator(ElevatorSystem $elevatorSystem, int $elevatorId): ?Elevator
    {
        foreach ($elevatorSystem->getElevators() as $elevator) {
            if ($elevator->getId() === $elevatorId) {
                return $elevator;
            }
        }

        $this->logger->warning(sprintf("Invalid elevator ID: %d", $elevatorId));
        return null;
    }

    public function callElevator(ElevatorSystem $elevatorSystem, int $floor, string $direction = "up"): array
    {
        if (!($elevatorSystem->getMinFloor() <= $floor && $floor <= $elevatorSystem->getMaxFloor())) {
            $message = sprintf("Invalid floor: %d", $floor);
            $this->logger->warning($message);
            return [
                'success' => false,
                'message' => $message
            ];
        }

        // Record this call in history
        $elevatorSystem->addToCallHistory($floor, $direction);

        // Check if an elevator is already at this floor and not moving
        foreach ($elevatorSystem->getElevators() as $elevator) {
            if ($elevator->getCurrentFloor() == $floor && !$elevator->isMoving()) {
                $this->elevatorService->openDoor($elevator);
                $message = sprintf("Elevator %d already at floor %d, opening doors", $elevator->getId(), $floor);
                $this->logger->info($message);

                $this->entityManager->flush();

                return [
                    'success' => true,
                    'message' => $message,
                    'elevator_id' => $elevator->getId()
                ];
            }
        }

        // Find the best elevator for this request
        $bestElevator = null;
        $bestScore = PHP_INT_MAX;

        foreach ($elevatorSystem->getElevators() as $elevator) {
            $score = $this->calculateElevatorScore($elevator, $floor, $direction);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestElevator = $elevator;
            }
        }

        if ($bestElevator) {
            $this->elevatorService->addDestination($bestElevator, $floor);
            $message = sprintf("Assigned elevator %d to floor %d, direction %s",
                $bestElevator->getId(), $floor, $direction);
            $this->logger->info($message);

            return [
                'success' => true,
                'message' => $message,
                'elevator_id' => $bestElevator->getId()
            ];
        }

        $message = sprintf("Failed to find suitable elevator for floor %d", $floor);
        $this->logger->error($message);

        return [
            'success' => false,
            'message' => $message
        ];
    }

    private function calculateElevatorScore(Elevator $elevator, int $requestedFloor, string $requestedDirection): int
    {
        $score = 0;

        // Base score is distance (absolute value)
        $distance = abs($elevator->getCurrentFloor() - $requestedFloor);
        $score += $distance * 10;

        // Penalize elevators that are already moving in wrong direction
        if ($elevator->isMoving()) {
            if (($requestedDirection == "up" && $elevator->getDirection() == "down") ||
                ($requestedDirection == "down" && $elevator->getDirection() == "up")) {
                $score += 50;
            }
        }

        // Consider queue length - busy elevators get penalty
        $score += count($elevator->getQueue()) * 5;

        // If elevator is already heading in the right direction and will pass the requested floor
        if ($elevator->isMoving() && $elevator->getDirection() == $requestedDirection) {
            if (($requestedDirection == "up" && $elevator->getCurrentFloor() < $requestedFloor) ||
                ($requestedDirection == "down" && $elevator->getCurrentFloor() > $requestedFloor)) {
                $score -= 20;
            }
        }

        // Penalize if doors are open
        if ($elevator->isDoorOpen()) {
            $score += 10;
        }

        return $score;
    }

    public function emergencyStopAll(ElevatorSystem $elevatorSystem): array
    {
        $this->logger->alert("SYSTEM EMERGENCY STOP ACTIVATED");
        $elevatorSystem->setSystemStatus("emergency");

        foreach ($elevatorSystem->getElevators() as $elevator) {
            $this->elevatorService->emergencyStop($elevator);
        }

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => "All elevators emergency stopped",
            'system_status' => $elevatorSystem->getSystemStatus()
        ];
    }

    public function resumeOperation(ElevatorSystem $elevatorSystem): array
    {
        if ($elevatorSystem->getSystemStatus() == "emergency") {
            $elevatorSystem->setSystemStatus("operational");
            $this->logger->info("System resuming normal operation after emergency");

            $this->entityManager->flush();

            return [
                'success' => true,
                'message' => "System resumed normal operation",
                'system_status' => $elevatorSystem->getSystemStatus()
            ];
        }

        return [
            'success' => false,
            'message' => "System is not in emergency state",
            'system_status' => $elevatorSystem->getSystemStatus()
        ];
    }

    public function getAllStatus(ElevatorSystem $elevatorSystem): array
    {
        $statuses = [];
        foreach ($elevatorSystem->getElevators() as $elevator) {
            $statuses[] = $elevator->getStatus();
        }

        return $statuses;
    }

    public function getSystemStatus(ElevatorSystem $elevatorSystem): array
    {
        return $elevatorSystem->getSystemInfo();
    }

    public function getCallStatistics(ElevatorSystem $elevatorSystem): array
    {
        $callHistory = $elevatorSystem->getCallHistory();

        if (empty($callHistory)) {
            return ["No elevator calls recorded"];
        }

        // Count calls per floor
        $floorCounts = [];
        $directionCounts = ["up" => 0, "down" => 0];

        foreach ($callHistory as $call) {
            $floor = $call['floor'];
            $direction = $call['direction'];

            if (!isset($floorCounts[$floor])) {
                $floorCounts[$floor] = 0;
            }
            $floorCounts[$floor]++;
            $directionCounts[$direction]++;
        }

        // Sort by most requested floors
        arsort($floorCounts);

        return [
            "total_calls" => count($callHistory),
            "most_requested_floors" => $floorCounts,
            "direction_statistics" => $directionCounts
        ];
    }
}
