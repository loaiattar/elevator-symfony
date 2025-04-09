<?php
namespace App\Examples;

use App\Adapter\BasicHardwareAdapter;
use App\Adapter\HardwareAdapter;
use App\Service\ElevatorControlService;

class usageExample
{
    public static function run(): void
    {
        //simulation for basic Hardware
        $clientHardware = new class {
            public function goUp(): bool
            {
                echo "Elevator going up!\n";
                return true;
            }

            public function goDown(): bool
            {
                echo "Elevator going down!\n";
                return true;
            }


        };
        $adapter = new BasicHardwareAdapter($clientHardware);

        $service = new ElevatorControlService($adapter);

        $service->goUp(3);

    }
}