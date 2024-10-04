<?php 


namespace App\Services;



trait  TransactionHandlerForOpMode{
  
use FixedTarrifOpModeService;
use TarriffCardOpModeService;



   public function handleOpMode($OP_MODE, $user,$device ){

    switch ($OP_MODE) {
             case "0":
                 $this-> handleOpModeZeroTransaction($user, $device);
             case "1":
                  $this-> handleOpModeOneTransaction($user, $device);
             default:
               break;
   };
}








}