<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\DeviceEarn;
use App\Models\DeviceUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\MqttConnectionService;

class CardController extends Controller
{
    public function index()
    {
        return Card::where('user_id', Auth::id())->get();
    }
    public function getUserCards($id)
    {
        return Card::where('user_id', $id)->get();
    }
    public function store(Request $request)
    {
        $data = DB::table('activation_codes')
        ->where('code', $request->card_number)
        ->first();
        if (empty($data)) {
            return response()->json(['message' => 'incorrect_code'], 422);
        }
        
        $device = Device::where('id', $data->device_id)->first();
        $userCards = Card::where('device_id', $data->device_id)
        ->where('user_id', Auth::id())
        ->pluck('user_id')
        ->toArray();
        $relate = DeviceUser::where('user_id', Auth::id())
        ->where('device_id', $data->device_id)
        ->first();
        //        if(empty($relate)) {
        //            DeviceUser::create([
        //                'user_id' => Auth::id(),
        //                'device_id' => $data->device_id,
        //                'subscription' => Carbon::now()->addMonth()
        //            ]);
        //        }
        if ($device->limit > count($userCards) && !empty($relate)) {
            Card::create([
                'name' => $request->name,
                'card_number' => $data->card,
                'user_id' => Auth::id(),
                'device_id' => $data->device_id,
            ]);
        } else {
            if ($device->limit > count($userCards)) {
                return response()->json(
                    [
                        'message' =>
                        'contact_to_your_company_for_adding_you_to_there_list',
                    ],
                    422
                );
            }
            if ($device->limit <= count($userCards)) {
                return response()->json(['message' => 'to_many_cards'], 422);
            }
        }
        return response()->json(['message' => 'create_successfully'], 201);
    }
    
    public function storeForUser(Request $request)
    {
        $data = DB::table('activation_codes')
        ->where('code', $request->card_number)
        ->first();
        if (empty($data)) {
            return response()->json(['message' => 'incorrect_code'], 422);
        }
        
        $device = Device::where('id', $data->device_id)->first();
        $userCards = Card::where('device_id', $data->device_id)
        ->where('user_id', $request->user_id)
        ->pluck('user_id')
        ->toArray();
        $relate = DeviceUser::where('user_id', $request->user_id)
        ->where('device_id', $data->device_id)
        ->first();
        
        if ($device->limit > count($userCards) && !empty($relate)) {
            Card::create([
                'name' => $request->name,
                'card_number' => $data->card,
                'user_id' => $request->user_id,
                'device_id' => $data->device_id,
            ]);
        } else {
            if ($device->limit > count($userCards)) {
                return response()->json(
                    [
                        'message' =>
                        'contact_to_your_company_for_adding_you_to_there_list',
                    ],
                    422
                );
            }
            if ($device->limit <= count($userCards)) {
                return response()->json(['message' => 'to_many_cards'], 422);
            }
        }
        return response()->json(['message' => 'create_successfully'], 201);
    }
    
    public function show(Card $card)
    {
        return $card;
    }
    
    public function generateElevatorCode(Request $request)
    {
        $code = rand(1000, 9999); // Generates a random 4-character code
        $expiresAt = Carbon::now()->addMinutes(30); // Set the expiration timestamp to 5 hour from now
        $device = Device::where('id', $request->device_id)->first();
        $Balance = Auth::user()['balance'];
        $data = DB::table('elevator_codes')
        ->where('user_id', Auth::id())
        ->where('device_id', $request->device_id)
        ->where('expires_at', '>', Carbon::now())
        ->first();
        if (!empty($data)) {
            return $data->code;
        }
        $canCode = false;
        if ($device->op_mode == 0) {
            $deviceUser = DeviceUser::where('device_id', $device->id)
            ->where('user_id', Auth::id())
            ->first();
            $subscriptionDate = Carbon::parse($deviceUser->subscription);
            $now = Carbon::now();
            if ($subscriptionDate->gt($now)) {
                $canCode = true;
            }
        } else {
            if ($Balance >= $device->tariff_amount) {
                $canCode = true;
            }
        }
        if ($canCode) {
            DB::table('elevator_codes')->insert([
                'user_id' => Auth::id(),
                'code' => $code,
                'device_id' => $request->device_id,
                'expires_at' => $expiresAt,
            ]);
            return $code;
        } else {
            return response()->json(
                ['message' => 'გთხოვთ შეავსოთ ბალანსი'],
                422
            );
        }
    }
    public function calltolift(Request $request)
    {
        $device = Device::where('id', $request->device_id)->first();
        $Balance = Auth::user()['balance'];
        $user = User::where('id', Auth::id())->first();
        $canCode = false;
        if ($device->op_mode == 0) {
            $deviceIds = Device::where('users_id', $device->users_id)
            ->pluck('id')
            ->toArray();
            
            $card = Card::where('user_id', $user->id)
            ->whereIn('device_id', $deviceIds)
            ->first();
            $deviceUser = DeviceUser::where('device_id', $card->device_id)
            ->where('user_id', Auth::id())
            ->first();
            $subscriptionDate = Carbon::parse($deviceUser->subscription);
            $now = Carbon::now();
            if ($subscriptionDate->gt($now)) {
                $canCode = true;
            }
        } else {
            if ($Balance >= $device->tariff_amount) {
                $canCode = true;
                $user->balance = $user->balance - $device->tariff_amount;
                $user->save();
                $this->saveOrUpdateEarnings(
                    $device->id,
                    $device->tariff_amount,
                    $device->company_id
                );
            }
        }
        if ($canCode) {
            $lastAmount = LastUserAmount::where('user_id', $user->id)
            ->where('device_id', $device->id)
            ->first();
            
            if (empty($lastAmount->user_id)) {
                LastUserAmount::insert([
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                    'last_amount' => $user->balance - $user->freezed_balance,
                ]);
            } else {
                $lastAmount->last_amount =
                $user->balance - $user->freezed_balance;
                $lastAmount->save();
            }
            $payload = $this->generateHexPayload(1, [
                [
                    'type' => 'string',
                    'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'number16',
                    'value' => $user->balance - $user->freezed_balance,
                ],
            ]);
            $this->publishMessage($device->dev_id, $payload);
            $devices_ids = Device::where('users_id', $device->users_id)->get();
            foreach ($devices_ids as $key2 => $value2) {
                if ($value2->op_mode == '1') {
                    $lastAmountCurrentDevice = LastUserAmount::where(
                        'user_id',
                        $user->id
                        )
                        ->where('device_id', $value2->id)
                        ->first();
                        
                        if (empty($lastAmountCurrentDevice->user_id)) {
                            LastUserAmount::insert([
                                'user_id' => $user->id,
                                'device_id' => $value2->id,
                                'last_amount' =>
                                $user->balance - $user->freezed_balance,
                            ]);
                        } else {
                            $lastAmountCurrentDevice->last_amount =
                            $user->balance - $user->freezed_balance;
                            $lastAmountCurrentDevice->save();
                        }
                        $payload = $this->generateHexPayload(5, [
                            [
                                'type' => 'string',
                                'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                            ],
                            [
                                'type' => 'number',
                                'value' => 0,
                            ],
                            [
                                'type' => 'number16',
                                'value' => $user->balance - $user->freezed_balance,
                            ],
                        ]);
                        $this->publishMessage($value2->dev_id, $payload);
                    }
                }
            } else {
                return response()->json(
                    ['message' => 'გთხოვთ შეავსოთ ბალანსი'],
                    422
                );
            }
        }
        public function saveOrUpdateEarnings($deviceId, $earningsValue, $companyId)
        {
            // Generate the date for month_year
            // TO DO find company cashback and add  to DeviceEarn find device tariff with deviceID
            
            $now = Carbon::now();
            $user = User::where('id', $companyId)->first();
            $device = Device::where('id', $deviceId)->first();
            // Try to retrieve the entry for the given device and month_year
            $deviceEarnings = DeviceEarn::where('device_id', $deviceId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();
            if (!empty($deviceEarnings)) {
                if ($user && $device) {
                    $deviceEarnings->earnings += $earningsValue;
                    $deviceEarnings->cashback = $user->cashback;
                    $deviceEarnings->deviceTariff = $device->deviceTariffAmount;
                    $deviceEarnings->save();
                } else {
                    $deviceEarnings->earnings += $earningsValue;
                    
                    $deviceEarnings->save();
                }
            } else {
                if ($user && $device) {
                    DeviceEarn::create([
                        'company_id' => $companyId,
                        'device_id' => $deviceId,
                        'month' => $now->month,
                        'year' => $now->year,
                        'earnings' => $earningsValue,
                        'cashback' => $user->cashback,
                        'deviceTariff' => $device->deviceTariffAmount,
                    ]);
                } else {
                    DeviceEarn::create([
                        'company_id' => $companyId,
                        'device_id' => $deviceId,
                        'month' => $now->month,
                        'year' => $now->year,
                        'earnings' => $earningsValue,
                    ]);
                }
            }
            // Save the model (either updates or creates based on existence)
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


            Log::debug("Response from MQTT server: " . $response->body());
            return $response->json(['data' => ['dasd']]);
        }
        
        
        
        
        public function update(Request $request, Card $card)
        {
            $card->update($request->all());
            return response()->json($card, 200);
        }




 



      public function destroy(Card $card)
{ 
    $command = 7;  // Command 7 in hexadecimal
    
    // Generate the payload
    $payload = $this->generateHexPayload($command, [
        [
            'type' => 'string',
            'value' => str_pad($card->card_number, 8, "0", STR_PAD_RIGHT),  
        ]
    ]);
 
    // Publish the message using MQTT
    $this->publishMessage($card->device_id, $payload);
    
    return response()->json(null, 204);
}

            
            
            
            
            
        }
        