<?php

namespace App\Interfaces;

 // Basic Interface for hardware operations
// this interface is the configuration layer between the application and the hardware
// this interface is for the old elevators that have only 2 functions goUp() and goDown()

interface BasicHardwareInterface
{
    public function  goUp(): bool;
    public function  goDown(): bool;
}