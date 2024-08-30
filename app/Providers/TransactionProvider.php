<?php

namespace App\Providers;

use App\Models\Card;
use App\Models\Device;
use App\Models\Company;
use App\Models\DeviceUser;
use GuzzleHttp\Psr7\Response;
use App\Models\LastUserAmount;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\BankOfGeorgiaUserCheck;
use App\Exceptions\InvalidHashCodeException;
 


trait TransactionProvider  
{ 
    //   validations for bank of georgia 
    //  user name and password check
    private function checkUser($USERNAME, $PASSWORD)
    {
        $expectedUsername = env('BOG_USERNAME', 'ipay');
        $expectedPassword = env('BOG_PASSWORD', 'ipay123');

        if ($USERNAME !== $expectedUsername || $PASSWORD !== $expectedPassword) {
            throw new BankOfGeorgiaUserCheck();
        }
    }
    
      // Hash generation function
      private function generateHash($data)
      {
          return md5($data . "someseacret");
      }
      // hash checking function 
    
      private function CheckHashCode(string $data, string $hash)
      {
          $expectedHash = $this->generateHash($data);
  
          if (strtoupper($hash) !== strtoupper($expectedHash)) {
              throw new InvalidHashCodeException($expectedHash, $hash);
          }
      }
 

    //   make FileID 

    public function MakeFileId($userId)
    {

        $deviceId = DeviceUser::where('user_id', $userId)->first();
        $string = '' .$userId;
     
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
     //     update user info
     public function updateUserData($amount, $user_id,   $isFastPay)
     {
         try {
 
 
             $user = User::where('id', $user_id)
                 ->with('devices')
                 ->first();
             $transfer_amount = floatval($amount);
             $sakomisio = 0;
             if ($isFastPay == 'e_com') {
                 $sakomisio = $transfer_amount * 0.02;
 
                 $sakomisio = number_format($sakomisio, 2, '.', '');
             }
             $user->balance =
                 intval($user->balance) + $transfer_amount - $sakomisio;
             $userCardAmount = Card::where('user_id', $user->id)->count();
 
             foreach ($user->devices as $key => $device) {
                 if ($device->op_mode === '0') {
                 
 
                     $subscriptionDate = $device->pivot->subscription
                         ? Carbon::parse($device->pivot->subscription)
                         : null;
                     $currentDay = Carbon::now()->day;
                     if ($currentDay < $device->pay_day) {
                         $nextMonthPayDay = Carbon::now()
 
                             ->startOfMonth()
                             ->addDays($device->pay_day - 1);
                      
                     } else {
                         $nextMonthPayDay = Carbon::now()
                             ->addMonth()
                             ->startOfMonth()
                             ->addDays($device->pay_day - 1);
 
                      }
                     if (
                         is_null($subscriptionDate) ||
                         ($subscriptionDate &&
                             $subscriptionDate->lt($nextMonthPayDay))
                     ) {
                    
 
                         $cardAmount =
                             $userCardAmount * $user->fixed_card_amount;
                         if (
                             $user->balance - $user->freezed_balance >=
                             $device->tariff_amount + $cardAmount
                         ) {
                             DeviceUser::where('device_id', $device->id)
                                 ->where('user_id', $user->id)
                                 ->update(['subscription' => $nextMonthPayDay]);
 
                             $user->freezed_balance = $device->tariff_amount;
                         } elseif (
                             $user->balance >=
                             $device->tariff_amount + $cardAmount
                         ) {
                             DeviceUser::where('device_id', $device->id)
                                 ->where('user_id', $user->id)
                                 ->update(['subscription' => $nextMonthPayDay]);
                             $user->freezed_balance = $device->tariff_amount;
                         }
                     }
                 }
                 $devices_ids = Device::where(
                     'users_id',
                     $device->users_id
                 )->get();
                 foreach ($devices_ids as $key2 => $value2) {
                     if ($value2->op_mode == '1') {
                         $lastAmount = LastUserAmount::where(
                             'user_id',
                             $user->id
                         )
                             ->where('device_id', $value2->id)
                             ->first();
 
                         if (empty($lastAmount->user_id)) {
                             LastUserAmount::insert([
                                 'user_id' => $user->id,
                                 'device_id' => $value2->id,
                                 'last_amount' =>
                                 $user->balance - $user->freezed_balance,
                             ]);
                         } else {
                             $lastAmount->last_amount =
                                 $user->balance - $user->freezed_balance;
                             $lastAmount->save();
                         }
                         $payload = $this->generateHexPayload(5, [
                             [
                                 'type' => 'string',
                                 'value' => str_pad(
                                     $user->id,
                                     6,
                                     '0',
                                     STR_PAD_LEFT
                                 ),
                             ],
                             [
                                 'type' => 'number',
                                 'value' => 0,
                             ],
                             [
                                 'type' => 'number16',
                                 'value' =>
                                 $user->balance - $user->freezed_balance,
                             ],
                         ]);
                         $this->publishMessage($value2->dev_id, $payload);
                     }
                 }
             }
             $user->save();
         } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error(
                 'Error checking user existence: ' . $e->getMessage()
             );
 
             return response()->json(
                 ['code' => 99] 
             
             );
         }
     }


     public function generateHexPayload($command, $payload)
     {
         return [
             'command' => $command,
             'payload' => $payload,
         ];
     }
     
     public function publishMessage($device_id, $payload)
     {
         $data = [
             'device_id' => $device_id,
             'payload' => $payload,
         ];
         $queryParams = http_build_query($data);
         $response = Http::get(
             'http://localhost:3000/mqtt/general?' . $queryParams
         );
         return $response->json(['data' => ['dasd']]);
     }
//  XML processing 
     private function arrayToXml($data, &$xmlData) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }


    private function arrayToXmlWithAttributes($data, &$xmlData) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['attributes'])) {
                    $subnode = $xmlData->addChild($key);
                    foreach ($value['attributes'] as $attrKey => $attrValue) {
                        $subnode->addAttribute($attrKey, $attrValue);
                    }
                    if (isset($value['value'])) {
                        $subnode[0] = htmlspecialchars($value['value']);
                    }
                } else {
                    $subnode = $xmlData->addChild($key);
                    $this->arrayToXmlWithAttributes($value, $subnode);
                }
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }
     

  private function XmlResponse($data){
    $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
    $this->arrayToXmlWithAttributes($data, $xmlData);
    $xmlContent = $xmlData->asXML();

    return response($xmlContent,  \Illuminate\Http\Response::HTTP_OK)
        ->header('Content-Type', 'application/xml');
  }

    private function HandleErrorCodes(int $code, string $message){
    $data = [
        'status' => [
            'attributes' => [
                'code' => $code
            ],
            'value' =>  $message
        ],
        'timestamp' => now()->timestamp,
    ];
    $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
    $this->arrayToXmlWithAttributes($data, $xmlData);
    $xmlContent = $xmlData->asXML();
    return response($xmlContent, \Illuminate\Http\Response::HTTP_BAD_REQUEST)
    ->header('Content-Type', 'application/xml');
  
  }
  private function HandleServerError(){
        $data = [
            'status' => [
                'attributes' => [
                    'code' =>10
                ],
                'value' => 'OP services dose not exist'
            ],
            'timestamp' => now()->timestamp,
        ];
  
    $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
    $this->arrayToXmlWithAttributes($data, $xmlData);
    $xmlContent = $xmlData->asXML();
       
    return response($xmlContent,  \Illuminate\Http\Response::HTTP_BAD_REQUEST)
    ->header('Content-Type', 'application/xml');
    }
}
