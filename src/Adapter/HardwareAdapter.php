<?php

namespace App\Adapter;

use App\Interfaces\BasicHardwareInterface;
use App\Interfaces\AdvancedHardwareInterface;

class HardwareAdapter implements BasicHardwareInterface, AdvancedHardwareInterface
{
    protected $currentFloor = 1;
    protected $isMoving = false;
    protected $doorsOpen = false;
    protected $targetFloor = null;

    public function stop(): bool
    {
        $this->isMoving = false;
        return true;
    }
    public function getCurrentFloor(): ?int
    {
        return $this->currentFloor;
    }
    public function isMoving(): bool
    {
        return $this->isMoving;
    }
    public function areDoorOpen(): bool
    {
        return $this->doorsOpen;
    }
    public function setTargetFloor(int $floorNumber): bool
    {
        $this->targetFloor = $floorNumber;
        return true;
    }
    public function emergencyStop(): bool
    {
        return $this->stop();
    }

    public function openDoors(): bool
    {
        $this->doorsOpen = true;
        return true;
    }

    public function closeDoors(): bool
    {
        $this->doorsOpen = false;
        return true;
    }

    public function areDoorsOpen(): bool
    {
        return $this->doorsOpen;
    }

    public function goUp(): bool
    {
        $this->isMoving= true;
        $this->currentFloor++;
        return true;
    }

    public function goDown(): bool
    {
         $this->isMoving= true;
         $this->currentFloor--;
         return true;
    }
}