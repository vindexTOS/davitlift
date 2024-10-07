<?php 


namespace App\Services;

 


trait  TransactionHandlerForOpMode{
  use TarriffCardOpModeService;

  use FixedTarrifOpModeService;

   public function handleOpMode($OP_MODE, $user,$device , $dataPayload = null){
     switch ($OP_MODE) {


             case "0":
                 $this-> handleOpModeZeroTransaction($user, $device ,$dataPayload);
                 break; 
             case "1":
              
                  $this-> handleOpModeOneTransaction($user, $device, $OP_MODE, $dataPayload );
                  break; 
             default:
               break;
   };
}








}