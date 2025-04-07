<?php

namespace App\Controller\Api;

use App\Entity\Elevator;
use App\Entity\ElevatorSystem;
use App\Repository\ElevatorRepository;
use App\Repository\ElevatorSystemRepository;
use App\Service\ElevatorService;
use App\Service\ElevatorSystemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/elevator-system', name: 'api_elevator_system_')]
class ElevatorSystemController extends AbstractController
{
    private $elevatorSystemService;
    private $elevatorService;
    private $elevatorSystemRepository;
    private $elevatorRepository;

    public function __construct(
        ElevatorSystemService $elevatorSystemService,
        ElevatorService $elevatorService,
        ElevatorSystemRepository $elevatorSystemRepository,
        ElevatorRepository $elevatorRepository
    ) {
        $this->elevatorSystemService = $elevatorSystemService;
        $this->elevatorService = $elevatorService;
        $this->elevatorSystemRepository = $elevatorSystemRepository;
        $this->elevatorRepository = $elevatorRepository;
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $numElevators = $data['num_elevators'] ?? 2;
        $maxFloor = $data['max_floor'] ?? 10;
        $minFloor = $data['min_floor'] ?? 0;

        $elevatorSystem = $this->elevatorSystemService->createElevatorSystem(
            $numElevators,
            $maxFloor,
            $minFloor
        );

        return $this->json([
            'success' => true,
            'message' => 'Elevator system created successfully',
            'system_id' => $elevatorSystem->getId(),
            'system_info' => $elevatorSystem->getSystemInfo()
        ]);
    }

    #[Route('/{id}/status', name: 'status', methods: ['GET'])]
    public function getStatus(ElevatorSystem $elevatorSystem): JsonResponse
    {
        return $this->json([
            'system_info' => $this->elevatorSystemService->getSystemStatus($elevatorSystem),
            'elevators' => $this->elevatorSystemService->getAllStatus($elevatorSystem)
        ]);
    }

    #[Route('/{id}/statistics', name: 'statistics', methods: ['GET'])]
    public function getStatistics(ElevatorSystem $elevatorSystem): JsonResponse
    {
        return $this->json([
            'statistics' => $this->elevatorSystemService->getCallStatistics($elevatorSystem)
        ]);
    }

    #[Route('/{id}/call', name: 'call', methods: ['POST'])]
    public function callElevator(ElevatorSystem $elevatorSystem, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $floor = $data['floor'] ?? null;
        $direction = $data['direction'] ?? 'up';

        if ($floor === null) {
            return $this->json([
                'success' => false,
                'message' => 'Floor parameter is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->elevatorSystemService->callElevator($elevatorSystem, $floor, $direction);

        return $this->json($result);
    }

    #[Route('/{id}/emergency-stop', name: 'emergency_stop', methods: ['POST'])]
    public function emergencyStop(ElevatorSystem $elevatorSystem): JsonResponse
    {
        $result = $this->elevatorSystemService->emergencyStopAll($elevatorSystem);

        return $this->json($result);
    }

    #[Route('/{id}/resume', name: 'resume', methods: ['POST'])]
    public function resumeOperation(ElevatorSystem $elevatorSystem): JsonResponse
    {
        $result = $this->elevatorSystemService->resumeOperation($elevatorSystem);

        return $this->json($result);
    }
}
