<?php

namespace App\Service;

use App\interfaces\AdvancedHardwareInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method goUp(int $int)
 */
class ElevatorControlService
{
    private AdvancedHardwareInterface $hardware;

    public function __construct(AdvancedHardwareInterface $hardware)
    {
        $this->hardware = $hardware;
    }

    /**
     * @throws \Exception
     */
    public function moveToFloor (int $targetFloor): void
    {
         $current  = $this->hardware->getCurrentFloor();
         if ($current === null) {
             throw new \Exception("Floor does not exist");
         }
         echo "Moving $targetFloor to $current\n";
         while ($current !== null) {
             if ($current !== $targetFloor) {
                 $this->hardware->goUp();
                 $current++;
             }elseif ($current > $targetFloor) {
                 $this->hardware->goDown();
                 $current--;
             }
         }
         $this->hardware->stop();
         $this->hardware->openDoors();
    }
}