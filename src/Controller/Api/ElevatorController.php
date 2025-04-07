<?php

namespace App\Controller\Api;

use App\Entity\Elevator;
use App\Repository\ElevatorRepository;
use App\Service\ElevatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/elevator', name: 'api_elevator_')]
class ElevatorController extends AbstractController
{
    private $elevatorService;
    private $elevatorRepository;

    public function __construct(
        ElevatorService $elevatorService,
        ElevatorRepository $elevatorRepository
    ) {
        $this->elevatorService = $elevatorService;
        $this->elevatorRepository = $elevatorRepository;
    }

    #[Route('/{id}/status', name: 'status', methods: ['GET'])]
    public function getStatus(Elevator $elevator): JsonResponse
    {
        return $this->json([
            'elevator' => $elevator->getStatus()
        ]);
    }

    #[Route('/{id}/move-up', name: 'move_up', methods: ['POST'])]
    public function moveUp(Elevator $elevator): JsonResponse
    {
        $result = $this->elevatorService->moveUp($elevator);

        return $this->json($result);
    }

    #[Route('/{id}/move-down', name: 'move_down', methods: ['POST'])]
    public function moveDown(Elevator $elevator): JsonResponse
    {
        $result = $this->elevatorService->moveDown($elevator);

        return $this->json($result);
    }

    #[Route('/{id}/open-door', name: 'open_door', methods: ['POST'])]
    public function openDoor(Elevator $elevator): JsonResponse
    {
        $result = $this->elevatorService->openDoor($elevator);

        return $this->json($result);
    }

    #[Route('/{id}/close-door', name: 'close_door', methods: ['POST'])]
    public function closeDoor(Elevator $elevator): JsonResponse
    {
        $result = $this->elevatorService->closeDoor($elevator);

        return $this->json($result);
    }

    #[Route('/{id}/add-destination', name: 'add_destination', methods: ['POST'])]
    public function addDestination(Elevator $elevator, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $floor = $data['floor'] ?? null;

        if ($floor === null) {
            return $this->json([
                'success' => false,
                'message' => 'Floor parameter is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->elevatorService->addDestination($elevator, $floor);

        return $this->json($result);
    }

    #[Route('/{id}/emergency-stop', name: 'emergency_stop', methods: ['POST'])]
    public function emergencyStop(Elevator $elevator): JsonResponse
    {
        $result = $this->elevatorService->emergencyStop($elevator);

        return $this->json($result);
    }
}
