<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\Company;
use App\Models\DeviceUser;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use App\Models\TbcTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Exceptions\InvalidHashCodeException;
use Symfony\Component\HttpFoundation\Response;

class BankOfGeorgia extends Controller
{
//   TO DO გაატანე ფაილ აიდი წარმატების შესრულებისას რომ შეინახონ თავიანთ მხარეს "2#400357449#591914946" 
//  http://localhost:8000/api/ipay/ping/?OP=ping&USERNAME=ipay&PASSWORD=ipay123&HASH_CODE=12341234058923958023
public function CheckPing(Request $request)
{
    try {
        $OP = $request->query("OP");
        $USERNAME = $request->query("USERNAME");
        $PASSWORD = $request->query("PASSWORD");
        $HASH_CODE = $request->query("HASH_CODE");

        $this->CheckHashCode($OP . $USERNAME . $PASSWORD, $HASH_CODE);

        return response()->json(
            ['code' => 0, 'timestamp' => date('Y-m-d H:i:s')],
            Response::HTTP_OK
        );
    } catch (InvalidHashCodeException $e) {
        return $e->render();
    } catch (\Exception $e) {
        return response()->json(
            ['code' => 99, "msg" => $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}


  //    /api/ipay/verification/?OP=verify&CUSTOMER_ID=574151953
  public function VerifyUser(Request $request)
  {
      $CUSTOMER_ID = $request->query('CUSTOMER_ID');
      $OP = $request->query("OP");

      try {
          $user = User::where('phone', $CUSTOMER_ID)->first();

          if ($user) {
              return response()->json(
                  ['phone' =>  $user->phone, 'user' => $user->name, "code" => 0],
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



 
    //    http://localhost:8000/api/ipay/payment/?OP=pay&USERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=574151953&PAY_AMOUNT=1000&PAYMENT_ID=1&HASH_CODE=97708186f1577e90c98b6c2a7bfed5eb
 
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
    
        $this->CheckHashCode($phone . $amount . $paymentID,   $hash );
        // checking if payment already happend
        if ($this->checkIfTransactionAlreadyHappend($paymentID)) {
            return response()->json(
                ['code' => 8],
                Response::HTTP_BAD_REQUEST
            );
        };


        try {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                $fileId = $this->MakeFileId($user->id);
                $this->createTransactionFastPay($amount, $user->id,  $paymentID, $fileId, "BO");
                $transaction = Tbctransaction::where(
                    'order_id',
                    $paymentID
                )->first();

              
                if ($transaction) {
                    $this->updateUserData(
                        $amount,
                        $transaction->user_id,

                        'fast_pay'
                    );
                }

                return response()->json(['code' => 0, 'message' => 'Payment successful', 'FileId' =>$fileId], Response::HTTP_OK);
            } else {
                return response()->json(['code' => 6], Response::HTTP_NOT_FOUND); // User not found
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json(['code' => 99], Response::HTTP_INTERNAL_SERVER_ERROR); // General error
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
            'amount' =>  number_format($amount / 100, 2) ,
            'FileId' => $FileId,
            'order_id' => $order_id,
            'type' => $type,
        ]);
    }


  
    public function checkIfTransactionAlreadyHappend($PAYMENT_ID)
    {

        $order = Tbctransaction::where('order_id', $PAYMENT_ID)->first();

        if ($order) {
            return true;
        }

        return  false;
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
                    Log::debug('op_mode = 0');

                    $subscriptionDate = $device->pivot->subscription
                        ? Carbon::parse($device->pivot->subscription)
                        : null;
                    $currentDay = Carbon::now()->day;
                    if ($currentDay < $device->pay_day) {
                        $nextMonthPayDay = Carbon::now()

                            ->startOfMonth()
                            ->addDays($device->pay_day - 1);
                        Log::debug('შემდეგი თარიღი>>> 1' . $nextMonthPayDay);
                    } else {
                        $nextMonthPayDay = Carbon::now()
                            ->addMonth()
                            ->startOfMonth()
                            ->addDays($device->pay_day - 1);

                        Log::debug('შემდეგი თარიღი>>> 2' . $nextMonthPayDay);
                    }
                    if (
                        is_null($subscriptionDate) ||
                        ($subscriptionDate &&
                            $subscriptionDate->lt($nextMonthPayDay))
                    ) {
                        Log::debug('is_null');

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
                ['code' => 99],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
