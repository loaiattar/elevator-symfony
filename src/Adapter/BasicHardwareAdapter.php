<?php

namespace App\Adapter;

use App\Adapter\HardwareAdapter;

class BasicHardwareAdapter extends HardwareAdapter
{
    private $hardwareDriver;

    public function __construct($hardwareDriver)
    {
        $this->hardwareDriver = $hardwareDriver;
    }
    public function goUp(): bool
    {
        $result = $this->hardwareDriver->goUp();
        if ($result) {
            $this->isMoving = true;
            $this->currentFloor++;
        }
        return $result;
    }
    public function goDown(): bool
    {
       $result = $this->hardwareDriver->goDown();
       if ($result) {
           $this->isMoving = true;
           $this->currentFloor--;
       }
       return $result;
    }
    public function openDoors(): bool
    {
        $this->doorsOpen = true;
        return true;
    }

    public function  closeDoors(): bool
    {
        $this->doorsOpen = false;
        return true;
    }


}