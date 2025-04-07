<?php

namespace App\Service;

use App\Entity\Elevator;
use App\Entity\ElevatorSystem;
use App\Repository\ElevatorRepository;
use App\Repository\ElevatorSystemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ElevatorService
{
    private $entityManager;
    private $elevatorRepository;
    private $elevatorSystemRepository;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ElevatorRepository $elevatorRepository,
        ElevatorSystemRepository $elevatorSystemRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->elevatorRepository = $elevatorRepository;
        $this->elevatorSystemRepository = $elevatorSystemRepository;
        $this->logger = $logger;
    }

    public function moveUp(Elevator $elevator): array
    {
        $message = '';

        if ($elevator->getCurrentFloor() < $elevator->getMaxFloor()) {
            $elevator->setCurrentFloor($elevator->getCurrentFloor() + 1);
            $elevator->setMoving(true);
            $elevator->setDirection('up');
            $message = sprintf("Elevator %d moving up to floor %d.", $elevator->getId(), $elevator->getCurrentFloor());
            $this->logger->info($message);
        } else {
            $message = sprintf("Elevator %d cannot move up. Already at the top floor.", $elevator->getId());
            $this->logger->info($message);
        }

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }

    public function moveDown(Elevator $elevator): array
    {
        $message = '';

        if ($elevator->getCurrentFloor() > $elevator->getMinFloor()) {
            $elevator->setCurrentFloor($elevator->getCurrentFloor() - 1);
            $elevator->setMoving(true);
            $elevator->setDirection('down');
            $message = sprintf("Elevator %d moving down to floor %d.", $elevator->getId(), $elevator->getCurrentFloor());
            $this->logger->info($message);
        } else {
            $message = sprintf("Elevator %d cannot move down. Already at the bottom floor.", $elevator->getId());
            $this->logger->info($message);
        }

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }

    public function openDoor(Elevator $elevator): array
    {
        $elevator->setDoorOpen(true);
        $message = sprintf("Elevator %d doors are now open.", $elevator->getId());
        $this->logger->info($message);

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }

    public function closeDoor(Elevator $elevator): array
    {
        $elevator->setDoorOpen(false);
        $message = sprintf("Elevator %d doors are now closed.", $elevator->getId());
        $this->logger->info($message);

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }

    public function addDestination(Elevator $elevator, int $floor): array
    {
        if ($floor >= $elevator->getMinFloor() && $floor <= $elevator->getMaxFloor()) {
            $elevator->addToQueue($floor);
            $message = sprintf("Elevator %d has added floor %d to its queue.", $elevator->getId(), $floor);
            $this->logger->info($message);

            $this->entityManager->flush();

            return [
                'success' => true,
                'message' => $message,
                'elevator' => $elevator->getStatus()
            ];
        }

        $message = sprintf("Invalid floor %d for elevator %d.", $floor, $elevator->getId());
        $this->logger->warning($message);

        return [
            'success' => false,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }

    public function emergencyStop(Elevator $elevator): array
    {
        $elevator->setMoving(false);
        $elevator->setDirection(null);
        $elevator->setDoorOpen(true);

        $message = sprintf("Elevator %d has been emergency stopped.", $elevator->getId());
        $this->logger->alert($message);

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'elevator' => $elevator->getStatus()
        ];
    }
}
