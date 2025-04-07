<?php

namespace App\Controller;

use App\Entity\Elevator;
use App\Repository\ElevatorRepository;
use App\Service\ElevatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElevatorController extends AbstractController
{
    private $elevatorRepository;
    private $elevatorService;

    public function __construct(
        ElevatorRepository $elevatorRepository,
        ElevatorService $elevatorService
    ) {
        $this->elevatorRepository = $elevatorRepository;
        $this->elevatorService = $elevatorService;
    }

    #[Route('/elevator/{id}/move-up', name: 'app_elevator_move_up', methods: ['POST'])]
    public function moveUp(Elevator $elevator): Response
    {
        $result = $this->elevatorService->moveUp($elevator);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('warning', $result['message']);
        }

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }

    #[Route('/elevator/{id}/move-down', name: 'app_elevator_move_down', methods: ['POST'])]
    public function moveDown(Elevator $elevator): Response
    {
        $result = $this->elevatorService->moveDown($elevator);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('warning', $result['message']);
        }

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }

    #[Route('/elevator/{id}/open-door', name: 'app_elevator_open_door', methods: ['POST'])]
    public function openDoor(Elevator $elevator): Response
    {
        $result = $this->elevatorService->openDoor($elevator);

        $this->addFlash('success', $result['message']);

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }

    #[Route('/elevator/{id}/close-door', name: 'app_elevator_close_door', methods: ['POST'])]
    public function closeDoor(Elevator $elevator): Response
    {
        $result = $this->elevatorService->closeDoor($elevator);

        $this->addFlash('success', $result['message']);

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }

    #[Route('/elevator/{id}/add-destination', name: 'app_elevator_add_destination', methods: ['POST'])]
    public function addDestination(Elevator $elevator, Request $request): Response
    {
        $floor = (int)$request->request->get('floor');

        $result = $this->elevatorService->addDestination($elevator, $floor);

        if ($result['success']) {
            $this->addFlash('success', $result['message']);
        } else {
            $this->addFlash('danger', $result['message']);
        }

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }

    #[Route('/elevator/{id}/emergency-stop', name: 'app_elevator_emergency_stop', methods: ['POST'])]
    public function emergencyStop(Elevator $elevator): Response
    {
        $result = $this->elevatorService->emergencyStop($elevator);

        $this->addFlash('warning', $result['message']);

        return $this->redirectToRoute('app_elevator_system_show', [
            'id' => $elevator->getElevatorSystem()->getId()
        ]);
    }
}
