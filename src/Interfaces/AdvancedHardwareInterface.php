<?php

namespace App\Interfaces;

interface AdvancedHardwareInterface extends BasicHardwareInterface
{
    public function stop(): bool;

    public function openDoors(): bool;

    public function closeDoors(): bool;

    public function getCurrentFloor(): ?int;

    public function isMoving(): bool;

    public function emergencyStop(): bool;

    public function areDoorsOpen(): bool;
}