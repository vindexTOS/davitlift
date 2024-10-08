<?php

namespace App\Http\Controllers;

use PDOException;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use RuntimeException;
use App\Models\Device;
use App\Models\ErrorLogs;
use App\Models\DeviceEarn;
use App\Models\DeviceUser;
use App\Models\DeviceError;
use App\Models\ElevatorUse;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use App\Models\UpdatingDevice;
use PhpMqtt\Client\MqttClient;
use App\Services\DeviceMessages;
use App\Models\UnregisteredDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Services\LastUserAmountUpdate;
use Illuminate\Support\Facades\Storage;
use App\Services\TransactionHandlerForOpMode;

class MqttController extends Controller
{

    use DeviceMessages;
    use  LastUserAmountUpdate;
    use TransactionHandlerForOpMode;
    // Handle general events
    public function handleGeneralEvent(Request $request)
    {


        // $this->Logsaver('', 'htpp shemosvla', '');

        // Process the general event data
        $msg = $request->all();
        $date = $msg['payload'];
        $topic = $msg['topic'];

        $parts = explode('/', $topic);
        $device_id = $parts[1];
        $device = Device::where('dev_id', $parts[1])->first();
        if (!empty($device)) {
            if ($device->isBlocked) {
                $payload = $this->generateHexPayload(6, [
                    [
                        'type' => 'string',
                        'value' => 'servisi',
                    ],
                    [
                        'type' => 'number',
                        'value' => 0,
                    ],
                    [
                        'type' => 'string',
                        'value' => 'droebiT',
                    ],
                    [
                        'type' => 'number',
                        'value' => 0,
                    ],
                    [
                        'type' => 'string',
                        'value' => 'SezRudulia',
                    ],
                    [
                        'type' => 'number',
                        'value' => 0,
                    ],
                ]);
                $this->publishMessage($device_id, $payload);
            } else {
                $this->callToNeededFunction(
                    $device,
                    $date,
                    $device_id,
                    $date['command']
                );
                $device->lastBeat = Carbon::now('Asia/Tbilisi')->addMinutes(10);
                $device->save();
            }
        } else {
            $payload = $this->generateHexPayload(6, [
                [
                    'type' => 'string',
                    'value' => 'mowyobiloba',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'araa',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'sistemaSi',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
            ]);
            $this->publishMessage($device_id, $payload);
            $device = UnregisteredDevice::where('dev_id', $device_id)->first();
            if (empty($device)) {
                $newDevice = new UnregisteredDevice();
                $newDevice->dev_id = $device_id; // replace with actual device id
                if ($date['command'] == 253) {
                    $newDevice->hardware_version = substr(
                        $date['payload'],
                        0,
                        3
                    );
                    $newDevice->soft_version = substr($date['payload'], 3, 3);
                }
                $newDevice->save();
            }
            if ($date['command'] == 253 && !empty($device)) {
                $device->hardware_version = substr($date['payload'], 0, 3);
                $device->soft_version = substr($date['payload'], 3, 3);
                $device->save();
            }
        }

        // You can store the data in the database or perform other actions
        return response()->json(['message' => 'General event processed'], 200);
    }

    public function Logsaver($errorMessage, $line, $value)
    {
        ErrorLogs::create([
            'errorMessage' => $errorMessage,
            'line' => $line,
            'value' => $value,
        ]);
    }
    // Handle heartbeat events
    public function handleHeartbeatEvent(
        Request $request
    ): \Illuminate\Http\JsonResponse {
        // Process the heartbeat event data
        $msg = $request->all();

        $date = $msg['payload'];
        $topic = $msg['topic'];

        $parts = explode('/', $topic);
        $device = Device::where('dev_id', $parts[1])->first();
        $data = unpack('Cnetwork/Csignal', $date['payload']);
        $device->lastBeat = Carbon::now('Asia/Tbilisi')->addMinutes(10);
        $device->network = $data['network'];
        $device->signal = $data['signal'];
        $device->save();
        // You can store the data in the database or perform other actions

        return response()->json(
            ['message' => 'Heartbeat event processed'],
            200
        );
    }
    private function callToNeededFunction(
        $device,
        $data,
        $device_id,
        $commandValue
    ) {
        switch ($commandValue) {
            case 1:
                $this->accessRequestForCellularRemoteNumber(
                    $device,
                    $data,
                    $device_id
                );
                break;
            case 2:
                $this->accessRequestForRFIDCard($device, $data, $device_id);
                break;
            case 3:
                $this->accessWithOneTimeCode($device, $data, $device_id);
                break;
            case 4:
                $this->remainedAmountUpdateToApplication(
                    $device,
                    $data,
                    $device_id
                );
                break;
            case 253:
                $this->deviceCurrentSetupPacket($device, $data, $device_id);
                break;
            case 254:
                $this->logDeviceError($device, $data, $device_id);
                break;
        }
    }
    private function accessRequestForCellularRemoteNumber(
        $device,
        $data,
        $device_id
    ) {

         $deviceIds = Device::where('users_id', $device->users_id)
            ->pluck('id')
            ->toArray();

        $phone = substr($data['payload'], 3); //This will output 'lo, World!'
        $user = User::where('phone', $phone)->first();
        $userDevice = DeviceUser::where('user_id', $user->id)
            ->whereIn('device_id', $deviceIds)
            ->first();

        if (empty($userDevice)) {
            $payload = $this->generateHexPayload(6, [
                [
                    'type' => 'string',
                    'value' => 'nomeri',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'ver',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'moiZebna',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
            ]);
            $this->publishMessage($device_id, $payload);
        } else {
            $card = Card::where('card_number', $data['payload'])
            ->whereIn('device_id', $deviceIds)
            ->first();
            $user = User::where('id', $card->user_id)->first();
            if ($device->op_mode == 0) {
                $userDevice = DeviceUser::where('user_id', $user->id)
                ->where('device_id', $card->device_id)
                ->first();
         //   dsvzeli kods naxav garbage.php servicebshi   MEORE 2  nomrad
             // თუ საბსქრიბშენ თარიღი ამოწურლი აქვს უსერს დავუბრუნებთ რომ ფული არ არის დევაის
             if (time()  > Carbon::parse($userDevice->subscription)->timestamp) {
                //და გავაჩერებთ ყველაფერს რეთურნით
                //  aq vart ///

                $this->handleOpMode($device->op_mode, $user, $device, $data);
                 return;
            }
            //    თუ ავქს საბსქრიბშენი უსერს , დევაის გავუგზავნით საბსქრიბშენის თარიღს და გავაგრძელებთ სხვა მოქმედებას 
          
            if(time()  < Carbon::parse($userDevice->subscription)->timestamp){
                $this->ReturnSubscriptionTypeToDevice($userDevice, $data, $device);

            }
            }  else if (
                $user->balance - $device->tariff_amount >
                $device->tariff_amount
            ) {
                $user->balance = $user->balance - $device->tariff_amount;
                $lastAmount = LastUserAmount::where('user_id', $user->id)
                    ->where('device_id', $device->id)
                    ->first();

                if (empty($lastAmount->user_id)) {
                    LastUserAmount::insert([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'last_amount' =>
                        $user->balance - $user->freezed_balance,
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
                $this->publishMessage($device_id, $payload);
                $user->save();
                Log::info("calll twice ?v ", ["info"=> "321" ]);

                $this->saveOrUpdateEarnings(
                    $device->id,
                    $device->tariff_amount,
                    $device->company_id
                );
                $devices_ids = Device::where(
                    'users_id',
                    $device->users_id
                )->get();
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
            } else {
                $this->noMoney($device_id);
            }
        }
    }

    private function accessRequestForRFIDCard($device, $data)
    {
        $deviceIds = Device::where('users_id', $device->users_id)
            ->pluck('id')
            ->toArray();

        $card = Card::where('card_number', $data['payload'])
            ->whereIn('device_id', $deviceIds)
            ->first();
        if (empty($card)) {
            $code = $this->getActivationCode($device->id, $data['payload']);
            $payload = $this->generateHexPayload(6, [
                [
                    'type' => 'string',
                    'value' => 'Tqveni',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'kodia',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => $code,
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
            ]);
            $this->publishMessage($device->dev_id, $payload);
        } else {
            $user = User::where('id', $card->user_id)->first();
            if ($device->op_mode == 0) {
                $userDevice = DeviceUser::where('user_id', $user->id)
                ->where('device_id', $card->device_id)
                ->first();
         //   dsvzeli kods naxav garbage.php servicebshi   MEORE 2  nomrad
             // თუ საბსქრიბშენ თარიღი ამოწურლი აქვს უსერს დავუბრუნებთ რომ ფული არ არის დევაის
             if (time()  > Carbon::parse($userDevice->subscription)->timestamp) {
                //და გავაჩერებთ ყველაფერს რეთურნით
                //  aq vart ///

                $this->handleOpMode($device->op_mode, $user, $device, $data);
                 return;
            }
            //    თუ ავქს საბსქრიბშენი უსერს , დევაის გავუგზავნით საბსქრიბშენის თარიღს და გავაგრძელებთ სხვა მოქმედებას 
          
            if(time()  < Carbon::parse($userDevice->subscription)->timestamp){
                $this->ReturnSubscriptionTypeToDevice($userDevice, $data, $device);

            }
            } else if (
                (int) $user->balance - $user->freezed_balance >=
                $device->tariff_amount
            ) {
                $lastAmount = LastUserAmount::where('user_id', $user->id)
                    ->where('device_id', $device->id)
                    ->first();

                if (empty($lastAmount->user_id)) {
                    LastUserAmount::insert([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'last_amount' =>
                        $user->balance -$device->tariff_amount,
                    ]);
                } else {
                    $lastAmount->last_amount =
                        $user->balance -$device->tariff_amount;
                    $lastAmount->save();
                }
                $payload = $this->generateHexPayload(3, [
                    [
                        'type' => 'string',
                        'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
                    ],
                    [
                        'type' => 'number',
                        'value' => 0,
                    ],
                    ['type' => 'string', 'value' => $data['payload']],
                    [
                        'type' => 'number',
                        'value' => 0,
                    ],
                    [
                        'type' => 'number16',
                        'value' => $user->balance -$device->tariff_amount,
                    ],
                ]);
                $user->save();
                Log::info("calll twice ?v ", ["info"=> "479" ]);

                $this->saveOrUpdateEarnings(
                    $device->id,
                    $device->tariff_amount,
                    $device->company_id
                );
                $this->publishMessage($device->dev_id, $payload);
            } else {
                $this->noMoney($device->dev_id);
            }
        }
    }

    private function accessWithOneTimeCode($device, $data)
    {
        $code = DB::table('elevator_codes')
            ->where('code', $data['payload'])
            ->where('device_id', $device->id)
            ->where('expires_at', '>', Carbon::now())
            ->first();
        if (empty($code)) {
            $payload = $this->generateHexPayload(6, [
                [
                    'type' => 'string',
                    'value' => 'araswori',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => 'kodi',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
                [
                    'type' => 'string',
                    'value' => '',
                ],
                [
                    'type' => 'number',
                    'value' => 0,
                ],
            ]);
            $this->publishMessage($device->dev_id, $payload);
        }
        $user = User::where('id', $code->user_id)->first();
        if ($device->op_mode == 0) {
            Log::debug("MQTT CONTROLLER shemsvla 2");

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
            DB::table('elevator_codes')
                ->where('id', '=', $code->id)
                ->delete();
        } else {
            if ((int) $user->balance > $device->tariff_amount) {
                $user->balance = (int) $user->balance - $device->tariff_amount;
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
                $user->save();
                $devices_ids = Device::where(
                    'users_id',
                    $device->users_id
                )->get();
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
                $devices_ids = Device::where(
                    'users_id',
                    $device->users_id
                )->get();
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
                DB::table('elevator_codes')
                    ->where('id', '=', $code->id)
                    ->delete();
            } else {
                $this->noMoney($device->dev_id);
            }
        }
    }

    private function remainedAmountUpdateToApplication($device, $data)
    {
        // $this->Logsaver('პრობლემური', $device->id, 'შემოსვლა');

        $bigEndianValue = $data['amount'];
        $cardNumber = $data['card'];
        $deviceIds = Device::where('users_id', $device->users_id)
            ->pluck('id')
            ->toArray();

        $card = Card::where('card_number', $cardNumber)
            ->whereIn('device_id', $deviceIds)
            ->first();

        $user = User::where('id', $card->user_id)->first();
        $lastAmount = LastUserAmount::where('user_id', $user->id)
            ->where('device_id', $device->id)
            ->first();

        $deviceTarff = $device->tariff_amount;
        $userBalance = $user->balance;

        $diff = $lastAmount->last_amount - $bigEndianValue;
        if ($diff <= 0 || $diff == 0) {
            // $this->Logsaver('730', $device->id, 'diff');
            if ($userBalance >= $deviceTarff) {
                $diff = $deviceTarff;
            } else {
                return;
            }
        }

        // $this->Logsaver(
        //     'პირველი ლაინი bigEnd and lastAmount',
        //     $bigEndianValue,
        //     $lastAmount->last_amount
        // );
        $user->balance = $user->balance - $diff;
 
        // $this->Logsaver(
        //     'მეორე ლაინი userBalance and diff',
        //     $user->balance,
        //     $diff
        // );

        $sendPrice = $user->balance - $user->freezed_balance;
        $lastAmount->last_amount = $sendPrice;
        // $this->Logsaver(
        //     'მეოთხე ლაინი',
        //     $lastAmount->last_amount,
        //     'მეოთხე ლაინი'
        // );

        $lastAmount->save();

        $user->save();
        Log::info("calll twice ?v ", ["info"=> "730" ]);

        // $this->saveOrUpdateEarnings($device->id, $diff, $device->company_id);
        // $this->Logsaver('762', $device->id, 'ერნინგები დასეივდა');

        $devices_ids = Device::where('users_id', $device->users_id)->get();
        // $this->Logsaver($device_id, '178', $commandValue);
        // $this->Logsaver('760', $device->id, 'დევაისი არსებობს');

        foreach ($devices_ids as $key2 => $value2) {
            // $this->Logsaver('763', $value2->id, 'ლუპში შესვლა');

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
                        'last_amount' => $sendPrice,
                    ]);
                } else {
                    $lastAmountCurrentDevice->last_amount = $sendPrice;
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
                        'value' => $sendPrice,
                    ],
                ]);
                $this->publishMessage($value2->dev_id, $payload);
            }
        }
        $this->trackElevetorUses($user->id, $device->id, 1, strval($deviceTarff), strval($user->balance));
    }

    private function deviceCurrentSetupPacket($device, $data)
    {
        $hard = substr($data['payload'], 0, 3);
        $soft = substr($data['payload'], 3, 3);
        $device->hardware_version = $hard;
        $device->soft_version = $soft;
        $lastCreated = UpdatingDevice::latest('created_at')->first();
        if (!empty($lastCreated)) {
            $uptDev = UpdatingDevice::where('dev_id', $device->dev_id)
                ->where('created_at', $lastCreated->created_at)
                ->where('status', 4)
                ->where('is_checked', false)
                ->first();
            if (!empty($uptDev)) {
                if ($uptDev->previous_version == $soft) {
                    $uptDev->status = 2;
                }
                if ($uptDev->previous_version != $soft) {
                    $uptDev->status = 1;
                }
                $uptDev->save();
            }
        }
        $device->save();
    }

    private function logDeviceError($device, $data)
    {
        $errorCode = unpack('Cerror', $data['payload']);
        $errors = [
            '1' => 'SYS_ERR_PERIPHERIAL_FAILED ',
            '2' => 'SYS_ERR_BAD_MQTT_PACKET ',
            '3' => 'SYS_ERR_MEMORY_FULL ',
            '17' => 'SYS_ERR_FOTA_INVALID_SW_VER ',
            '18' => 'SYS_ERR_FOTA_INVALID_HW_REV',
            '19' => 'SYS_ERR_FOTA_INVALID_BIN_FILE ',
            '20' => 'SYS_ERR_FOTA_INVALID_BIN_FILE_SIZE',
            '21' => 'SYS_ERR_FOTA_HTTP_CONNECTION_FAILED',
            '22' => 'SYS_ERR_FOTA_CRC_VERIFY_FAILED',
            '23' => 'SYS_ERR_FOTA_MEMORY_VERIFY_FAILED',
            '24' => 'SYS_ERR_FOTA_ROLLBACK_CANCEL_FAILED',
            '255' => 'SYS_ERR_UNKNOWN',
        ];
        if ($errorCode['error'] >= 17 && $errorCode['error'] <= 24) {
            UpdatingDevice::where('dev_id', $device->dev_id)
                ->where('is_checked', false)
                ->update(['status' => 2]);
        }
        $errorText = 'reserved';
        if (isset($errors['' . $errorCode['error']])) {
            $errorText = $errors['' . $errorCode['error']];
        }

        DeviceError::create([
            'device_id' => $device->id,
            'errorCode' => $errorCode['error'],
            'errorText' => $errorText,
        ]);
    }

    public function saveOrUpdateEarnings($deviceId, $earningsValue, $companyId)
    {


        // Generate the date for month_year
        // $this->Logsaver('868', $companyId, 'შემოსვლა ეივ ერნინგშ');
        // TO DO find company cashback and add  to DeviceEarn find device tariff with deviceID
        $now = Carbon::now();
        $user = User::where('id', $companyId)->first();
        $device = Device::where('id', $deviceId)->first();

        if ($user->cashback == 0) {
            // $this->Logsaver('876', $user->cashback, 'უსერის ქეშბექი');

        $user = User::where('id', $device->users_id)->first();
        }
        $this->Logsaver('879', $earningsValue, ' ერნიგნები');

        // $this->Logsaver('881', $user->id, 'მენეჯერის id');

        // Try to retrieve the entry for the given device and month_year
        $deviceEarnings = DeviceEarn::where('device_id', $deviceId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->first();
        if (!empty($deviceEarnings)) {
            // $this->Logsaver('889', $user->id, 'devais ერნინგები ცარიელია');

            if ($user && $device) {
                // $this->Logsaver(
                //     $earningsValue,
                //     $user->cashback,
                //     'device->deviceTariffAmount'
                // );

                if ($device->deviceTariffAmount != null) {
                    $deviceEarnings->earnings =  $deviceEarnings->earnings + $earningsValue;
                    $deviceEarnings->cashback = $user->cashback;
                    $deviceEarnings->deviceTariff = $device->deviceTariffAmount;
                    $deviceEarnings->save();
                    Log::info("if", ["info"=> $deviceEarnings->earnings ]);

                } else {
                 
                 
                    $deviceEarnings->earnings=  $deviceEarnings->earnings + $earningsValue;
                    $deviceEarnings->cashback = $user->cashback;
                    $deviceEarnings->save();

                    Log::info("else", ["info"=> $deviceEarnings->earnings ]);
                }
            } else {
                $deviceEarnings->earnings  =  $deviceEarnings->earnings + $earningsValue;

                Log::info("second else", ["info"=> $deviceEarnings->earnings ]);

                $deviceEarnings->save();
            }
        } else {
            // $this->Logsaver('906', $user->id, 'BIG ELSE');

            if ($user && $device) {
                // $this->Logsaver('909', $user->id, 'user && device 2 ');


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
                // $this->Logsaver('921', $user->id, 'user && device 2  ELSE');


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

    public function getActivationCode($dev_id, $card)
    {
        $code = rand(100000, 999999); // Generates a random 6-character code
        $expiresAt = Carbon::now()->addMinutes(5); // Set the expiration timestamp to 5 hour from now
        DB::table('activation_codes')->insert([
            'code' => $code,
            'device_id' => $dev_id,
            'card' => $card,
            'expires_at' => $expiresAt,
        ]);
        echo $code;
        return $code;
    }

    public function generatePayloadStarter($command)
    {
        $hexString = str_pad(dechex(time()), 8, '0', STR_PAD_LEFT);
        $hexString .= str_pad(dechex($command), 2, '0', STR_PAD_LEFT);
        return $hexString;
    }

    public function noMoney($device_id)
    {
        $payload = $this->generateHexPayload(6, [
            [
                'type' => 'string',
                'value' => 'araa',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'sakmarisi',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
            [
                'type' => 'string',
                'value' => 'Tanxa',
            ],
            [
                'type' => 'number',
                'value' => 0,
            ],
        ]);
        $this->publishMessage($device_id, $payload);
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

    //  tracking elevetors 

    public function trackElevetorUses(string $userId, string $deviceId, int $type, string $tariff, string $currentBalance)
    {
        try {
            ElevatorUse::create([
                'user_id' => $userId,
                'device_id' => $deviceId,
                'type' => $type,
                "tariff" => $tariff,
                'current_balance' => $currentBalance,
'created_at' => Carbon::now('Asia/Tbilisi')->addHours(4),
        ]);
        } catch (PDOException $e) {
            throw new RuntimeException("Elevetor Use Error: " . $e->getMessage());
        }
    }
}
