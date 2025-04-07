<?php

namespace App\Controller;

use App\Entity\ElevatorSystem;
use App\Repository\ElevatorSystemRepository;
use App\Service\ElevatorSystemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElevatorSystemController extends AbstractController
{
    private $elevatorSystemRepository;
    private $elevatorSystemService;

    public function __construct(
        ElevatorSystemRepository $elevatorSystemRepository,
        ElevatorSystemService $elevatorSystemService
    ) {
        $this->elevatorSystemRepository = $elevatorSystemRepository;
        $this->elevatorSystemService = $elevatorSystemService;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Redirect to the elevator systems list page instead of rendering an empty template
        return $this->redirectToRoute('app_elevator_system_index');
    }

    #[Route('/elevator-system', name: 'app_elevator_system_index')]
    public function listSystems(): Response
    {
        $elevatorSystems = $this->elevatorSystemRepository->findAll();

        return $this->render('elevator_system/index.html.twig', [
            'elevator_systems' => $elevatorSystems
        ]);
    }


    #[Route('/elevator-system/new', name: 'app_elevator_system_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $numElevators = (int)$request->request->get('num_elevators', 2);
            $maxFloor = (int)$request->request->get('max_floor', 10);
            $minFloor = (int)$request->request->get('min_floor', 0);

            $elevatorSystem = $this->elevatorSystemService->createElevatorSystem(
                $numElevators,
                $maxFloor,
                $minFloor
            );

            $this->addFlash('success', 'Elevator system created successfully.');

            return $this->redirectToRoute('app_elevator_system_show', [
                'id' => $elevatorSystem->getId()
            ]);
        }

        return $this->render('elevator_system/new.html.twig');
    }

    #[Route('/elevator-system/{id}', name: 'app_elevator_system_show')]
    public function show(ElevatorSystem $elevatorSystem): Response
    {
        $systemStatus = $this->elevatorSystemService->getSystemStatus($elevatorSystem);
        $elevatorStatuses = $this->elevatorSystemService->getAllStatus($elevatorSystem);

        return $this->render('elevator_system/show.html.twig', [
            'elevator_system' => $elevatorSystem,
            'system_status' => $systemStatus,
            'elevator_statuses' => $elevatorStatuses
        ]);
    }

    #[Route('/elevator-system/{id}/statistics', name: 'app_elevator_system_statistics')]
    public function statistics(ElevatorSystem $elevatorSystem): Response
    {
        $statistics = $this->elevatorSystemService->getCallStatistics($elevatorSystem);

        return $this->render('elevator_system/statistics.html.twig', [
            'elevator_system' => $elevatorSystem,
            'statistics' => $statistics
        ]);
    }

    #[Route('/elevator-system/{id}/call', name: 'app_elevator_system_call', methods: ['POST'])]
    public function callElevator(ElevatorSystem $elevatorSystem, Request $request): Response
    {
        $floor = (int)$request->request->get('floor');
        $direction = $request->request->get('direction', 'up');

        $result = $this->elevatorSystemService->callElevator($elevatorSystem, $floor, $direction);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('danger', $result['message']);
        }

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevatorSystem->getId()
        ]);
    }

    #[Route('/elevator-system/{id}/emergency-stop', name: 'app_elevator_system_emergency_stop', methods: ['POST'])]
    public function emergencyStop(ElevatorSystem $elevatorSystem): Response
    {
        $result = $this->elevatorSystemService->emergencyStopAll($elevatorSystem);

        $this->addFlash('warning', 'Emergency stop activated for all elevators.');

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevatorSystem->getId()
        ]);
    }

    #[Route('/elevator-system/{id}/resume', name: 'app_elevator_system_resume', methods: ['POST'])]
    public function resumeOperation(ElevatorSystem $elevatorSystem): Response
    {
        $result = $this->elevatorSystemService->resumeOperation($elevatorSystem);

        if ($result['success']) {
            $this->addFlash('success', 'System resumed normal operation.');
        } else {
            $this->addFlash('info', 'System is not in emergency state.');
        }

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevatorSystem->getId()
        ]);
    }
}