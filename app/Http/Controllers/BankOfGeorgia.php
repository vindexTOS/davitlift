<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\Company;
use App\Models\DeviceUser;
use Illuminate\Http\Request;
use App\Models\Tbctransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BankOfGeorgia extends Controller
{
   // Hash generation function
   private function generateHash($data)
   {
       return md5($data . "someseacret");  
   }
//    http://localhost:8000/api/ipay/payment/?CUSTOMER_ID=574151953&PAY_AMOUNT=1000&PAYMENT_ID=1&HASH_CODE=97708186f1577e90c98b6c2a7bfed5eb
   // Handle incoming request
   public function handlePayment(Request $request)
   {
       $phone = $request->query('CUSTOMER_ID');
       $amount = $request->query('PAY_AMOUNT');
       $hash = $request->query('HASH_CODE');
        $paymentID = $request->query("PAYMENT_ID");
       // Validate required parameters
       if (!$phone || !$amount || !$hash || !$paymentID) {
           return response()->json(['code' => 4], Response::HTTP_BAD_REQUEST); // Missing required parameter
       }
           // Verify hash
       $expectedHash = $this->generateHash($phone . $amount . $paymentID);
       if (strtoupper($hash) !== strtoupper($expectedHash)) {
           return response()->json(['code' => 3, "hash"=>strtoupper($hash), "expected"=>$expectedHash], Response::HTTP_BAD_REQUEST); // Incorrect hash code
       }

        // checking if payment already happend
        if($this->checkIfTransactionAlreadyHappend($paymentID)){
            return response()->json(
                ['code' => 8 ],
                Response::HTTP_BAD_REQUEST
            );
        };
     

       try {
           $user = User::where('phone', $phone)->first();
           if ($user) {
                $fileId = $this->MakeFileId($user->id);
                $this->createTransactionFastPay($amount, $user->id,  $paymentID,$fileId ,"BO");
               return response()->json(['code' => 0, 'message' => 'Payment successful'], Response::HTTP_OK);
           } else {
               return response()->json(['code' => 6], Response::HTTP_NOT_FOUND); // User not found
           }
       } catch (\Exception $e) {
           Log::error('Error processing payment: ' . $e->getMessage());
           return response()->json(['code' => 99], Response::HTTP_INTERNAL_SERVER_ERROR); // General error
       }



 
   }

//   make FileID 

  public function MakeFileId( $userId){

    $deviceId = DeviceUser::where('user_id', $userId)->first();
    $string = '';
    if ($deviceId) {
        $device = Device::where('id', $deviceId->device_id)->first();
        $manager = User::where('id', $device->users_id)->first();
        $company = Company::where('id', $device->company_id)->first();
        if (
            isset($manager) &&
            isset($manager->phone) &&
            isset($company) &&
            isset($company->sk_code)
            ) {
                $string .= '#' . $company->sk_code;
                $length = strlen($string);
                
                // If the length is greater than 30, truncate the string to 30 characters
                if ($length > 30) {
                    $string = substr($string, 0, 30);
                }
                
                // Calculate the remaining length available for the manager's name
                $remainingLength = 30 - strlen($string);
                
                // Append the portion of the manager's name that fits into the remaining length
                $string .=
                '#' . substr($manager->phone, 0, $remainingLength);
            }
        }
        return $string;
  }
    //   payment creation 

   public function createTransactionFastPay(
    $amount,
    $userId,
    $order_id,
    $FileId,
    $type
    ) {
      
        Tbctransaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'FileId' => $FileId,
            'order_id' => $order_id,
            'type' => $type,
        ]);
    }


//    /api/ipay/verification/?CUSTOMER_ID=574151953
  public function VerifyUser(Request $request){
    $CUSTOMER_ID= $request->query('CUSTOMER_ID');

   try {
       $user = User::where('phone', $CUSTOMER_ID)->first();
       
       if ($user) {
           return response()->json(
               ['phone' =>  $user->phone, 'user' => $user->name ,"code"=>0],
               Response::HTTP_OK
           );
       } else {
           return response()->json(
               ['code' => 6],
               Response::HTTP_NOT_FOUND
           );
       }
   } catch (\Exception $e) {
       \Illuminate\Support\Facades\Log::error(
           'Error checking user existence: ' . $e->getMessage()
       );
       
       return response()->json(
           ['code' => 99],
           Response::HTTP_INTERNAL_SERVER_ERROR
       );
   }
  }

   public function checkIfTransactionAlreadyHappend($PAYMENT_ID)
   {
       $order = Tbctransaction::where('order_id', $PAYMENT_ID)->first();
       
       if ($order) {
           return true;
       } 
       
       return  false;

   }
}
