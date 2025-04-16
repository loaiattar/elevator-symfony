<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Adapter\BasicHardwareAdapter;
use App\Service\ElevatorControlService;

$hardware = new class {
    public function goUp(){
        echo "Going Up...\n";
        return true;
    }
    public function goDown(){
      echo "Going Down ...\n";
      return true;
    }
};
$adapter = new BasicHardwareAdapter($hardware);
$elevator = new ElevatorControlService($adapter);
$elevator->moveToFloor(6);
