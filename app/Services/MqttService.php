<?php

// app/Services/MqttService.php

namespace App\Services;

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
use App\Models\Transaction;
use App\Models\LastUserAmount;
use App\Models\UpdatingDevice;
use PhpMqtt\Client\MqttClient;
use App\Models\UnregisteredDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isNull;
use function PHPUnit\Framework\exactly;
use Illuminate\Support\Facades\Storage;

class MqttService
{
    public $mqtt;
    
    public function __construct()
    {
        $mqttService = app(MqttConnectionService::class);
        $this->mqtt = $mqttService->connect();
    }
    
    public function run()
    {
        $this->mqtt->subscribe(
            'Lift/+/events/general',
            function ($topic, $payload) {
                $parts = explode('/', $topic);
                $date = $this->parseHexPayload($payload);
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
                        $device->lastBeat = Carbon::now(
                            'Asia/Tbilisi'
                            )->addMinutes(10);
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
                        $device = UnregisteredDevice::where(
                            'dev_id',
                            $device_id
                            )->first();
                            if (empty($device)) {
                                $newDevice = new UnregisteredDevice();
                                $newDevice->dev_id = $device_id; // replace with actual device id
                                if ($date['command'] == 253) {
                                    $newDevice->hardware_version = substr(
                                        $date['payload'],
                                        0,
                                        3
                                    );
                                    $newDevice->soft_version = substr(
                                        $date['payload'],
                                        3,
                                        3
                                    );
                                }
                                $newDevice->save();
                            }
                            if ($date['command'] == 253 && !empty($device)) {
                                $device->hardware_version = substr(
                                    $date['payload'],
                                    0,
                                    3
                                );
                                $device->soft_version = substr($date['payload'], 3, 3);
                                $device->save();
                            }
                        }
                    },
                    0
                );
                
                $this->mqtt->subscribe(
                    'Lift/+/events/heartbeat',
                    function ($topic, $payload) {
                        $parts = explode('/', $topic);
                        $device_id = $parts[1];
                        $payload = $this->parseHexPayload($payload);
                        $data = unpack('Cnetwork/Csignal', $payload['payload']);
                        $device = Device::where('dev_id', $device_id)->first();
                        $device->lastBeat = Carbon::now('Asia/Tbilisi')->addMinutes(10);
                        $device->network = $data['network'];
                        $device->signal = $data['signal'];
                        $device->save();
                    },
                    0
                );
                
                $this->mqtt->loop(true);
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
                                                Log::info("celural number", ['info'=>" service"]);

                                                $deviceIds = Device::where('users_id', $device->users_id)
                                                ->pluck('id')
                                                ->toArray();
                                                
                                                $phone = substr($data['payload'], 3); //This will output 'lo, World!'
                                                $user = User::where('phone', $phone)->first();
                                                if (empty($user)) {
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
                                                    if ($device->op_mode == 0) {
                                                        $userDevice = DeviceUser::where('user_id', $user->id)
                                                        ->whereIn('device_id', $deviceIds)
                                                        ->first();
                                                        Log::debug("MQTT SERVICE shemsvla 1");
                                                        if (
                                                            time() < Carbon::parse($userDevice->subscription)->timestamp
                                                            ) {
                                                                $payload = $this->generateHexPayload(2, [
                                                                    [
                                                                        'type' => 'timestamp',
                                                                        'value' => Carbon::parse($userDevice->subscription)
                                                                        ->timestamp,
                                                                    ],
                                                                    [
                                                                        'type' => 'string',
                                                                        'value' => $data['payload'],
                                                                    ],
                                                                    [
                                                                        'type' => 'number',
                                                                        'value' => 0,
                                                                    ],
                                                                ]);
                                                                
                                                                $this->publishMessage($device_id, $payload);
                                                            } else {
                                                                $this->noMoney($device_id);
                                                            }
                                                        } else {
                                                            if ($user->balance > $device->tariff_amount) {
                                                                $payload = $this->generateHexPayload(1, []);
                                                                $this->publishMessage($device_id, $payload);
                                                                $user->balance = $user->balance - $device->tariff_amount;
                                                                $user->save();
                                                                $this->saveOrUpdateEarnings(
                                                                    $device->id,
                                                                    $device->tariff_amount,
                                                                    $device->company_id
                                                                );
                                                            } else {
                                                                $this->noMoney($device_id);
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                private function accessRequestForRFIDCard($device, $data)
                                                {
                                                    Log::debug("TWICE SHEMOSVLA", ["info"=>["dont ru service"]]);
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
                                                                'value' => "20024",
                                                            ],
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                        ]);
                                                        $this->publishMessage($device->dev_id, $payload);
                                                    } else {
                                                        // უსერის ნახავა
                                                        $user = User::where('id', $card->user_id)->first();
                                                        /////////////////////////////////////
                                                        if ($device->op_mode == 0) {
                                                            Log::debug("MQTT SERVICE shemsvla 3");
                                                            $userFixedBalnce = $user->fixed_card_amount;
                                                            $userCardAmount = Card::where('user_id', $user->id)->count();
                                                            $fixedCard = $userFixedBalnce * $userCardAmount;
                                                            
                                                            $deviceTariffWithCardBalance =
                                                            $device->tariff_amount + $fixedCard;
                                                            $userDevice = DeviceUser::where('user_id', $user->id)
                                                            ->where('device_id', $card->device_id)
                                                            ->first();
                                                            if( time()  > Carbon::parse($userDevice->subscription)->timestamp ){
                                                                $this->noMoney($device->dev_id);
                                                                
                                                            }
                                                            if (
                                                                time() < Carbon::parse($userDevice->subscription)->timestamp
                                                                ) {
                                                                    $payload = $this->generateHexPayload(4, [
                                                                        [
                                                                            'type' => 'timestamp',
                                                                            'value' => Carbon::parse($userDevice->subscription)
                                                                            ->timestamp,
                                                                        ],
                                                                        [
                                                                            'type' => 'string',
                                                                            'value' => $data['payload'],
                                                                        ],
                                                                        [
                                                                            'type' => 'number',
                                                                            'value' => 0,
                                                                        ],
                                                                    ]);
                                                                    $this->publishMessage($device->dev_id, $payload);
                                                                    
                                                                } else if( time()  > Carbon::parse($userDevice->subscription)->timestamp) {
                                                                    
                                                                    $this->noMoney($device->dev_id);
                                                                    
                                                                }else if ($device->tariff_amount == 0 || $device->tariff_amount <= 0 || $device->tariff_amount == "0") {
                                                                    
                                                                    $userFixedBalnce = $user->fixed_card_amount;
                                                                    $userCardAmount = Card::where('user_id', $user->id)->count();
                                                                    $fixedCard = $userFixedBalnce * $userCardAmount;
                                                                    
                                                                    
                                                                    $userBalance = $user->balance;
                                                                    
                                                                    $user->freezed_balance = $fixedCard;
                                                                    
                                                                    
                                                                    
                                                                    
                                                                    if ($user->balance - $user->freezed_balance >= $fixedCard) {
                                                                        
                                                                        $user->balance -= $fixedCard;
                                                                        $user->freezed_balance -= $fixedCard;
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
                                                                        $userDevice->subscription = $nextMonthPayDay;
                                                                        
                                                                        $userDevice->save();
                                                                        $payload = $this->generateHexPayload(4, [
                                                                            [
                                                                                'type' => 'timestamp',
                                                                                'value' => Carbon::parse($nextMonthPayDay)
                                                                                ->timestamp,
                                                                            ],
                                                                            [
                                                                                'type' => 'string',
                                                                                'value' => $data['payload'],
                                                                            ],
                                                                            [
                                                                                'type' => 'number',
                                                                                'value' => 0,
                                                                            ],
                                                                        ]);
                                                                        $this->publishMessage($device->dev_id, $payload);
                                                                    }
                                                                } else if ($user->balance >= $deviceTariffWithCardBalance) {
                                                                    $user->freezed_balance = $device->tariff_amount;
                                                                    $user->save();
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
                                                                    $userDevice->subscription = $nextMonthPayDay;
                                                                    
                                                                    $userDevice->save();
                                                                    $payload = $this->generateHexPayload(4, [
                                                                        [
                                                                            'type' => 'timestamp',
                                                                            'value' => Carbon::parse($nextMonthPayDay)
                                                                            ->timestamp,
                                                                        ],
                                                                        [
                                                                            'type' => 'string',
                                                                            'value' => $data['payload'],
                                                                        ],
                                                                        [
                                                                            'type' => 'number',
                                                                            'value' => 0,
                                                                        ],
                                                                    ]);
                                                                    $this->publishMessage($device->dev_id, $payload);
                                                                } else {
                                                                    $this->noMoney($device->dev_id);
                                                                }
                                                                // 
                                                            }
                                                            ///////////////////////////////////////////////////////////////////////
                                                            else {
                                                                if ((int) $user->balance > $device->tariff_amount) {
                                                                    $lastAmount = LastUserAmount::where('user_id', $user->id)
                                                                    ->where('device_id', $device->id)
                                                                    ->first();
                                                                    //   ბალანსი არის 30
                                                                    if (empty($lastAmount->user_id)) {
                                                                        LastUserAmount::insert([
                                                                            'user_id' => $user->id,
                                                                            'device_id' => $device->id,
                                                                            'last_amount' => $user->balance,
                                                                        ]);
                                                                    } else {
                                                                        $lastAmount->last_amount = $user->balance;
                                                                        $lastAmount->save();
                                                                        // შეინახა ლესთ ემაუნთი 30 თეთრი
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
                                                                            'value' => $user->balance,
                                                                        ],
                                                                    ]);
                                                                    $this->publishMessage($device->dev_id, $payload);
                                                                    $user->save();
                                                                    $this->saveOrUpdateEarnings(
                                                                        $device->id,
                                                                        $device->tariff_amount,
                                                                        $device->company_id
                                                                    );
                                                                } else {
                                                                    $this->noMoney($device->dev_id);
                                                                }
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
                                                        $user = User::where('id', $code->user_id)->first();
                                                        if ($device->op_mode == 0) {
                                                            Log::debug("MQTT SERVICE shemsvla 2");
                                                            $payload = $this->generateHexPayload(1, []);
                                                            $this->publishMessage($device->dev_id, $payload);
                                                        } else {
                                                            if ((int) $user->balance > $device->tariff_amount) {
                                                                $user->balance = (int) $user->balance - $device->tariff_amount;
                                                                $payload = $this->generateHexPayload(1, []);
                                                                $this->publishMessage($device->dev_id, $payload);
                                                                $user->save();
                                                                DB::table('elevator_codes')
                                                                ->where('id', '=', $code->id)
                                                                ->delete();
                                                            } else {
                                                                $this->noMoney($device->dev_id);
                                                            }
                                                        }
                                                    }
                                                    public function Logsaver($errorMessage, $line, $value)
                                                    {
                                                        ErrorLogs::create([
                                                            'error_message' => $errorMessage,
                                                            'line' => $line,
                                                            'value' => $value,
                                                        ]);
                                                    }
                                                    private function remainedAmountUpdateToApplication($device, $data)
                                                    {
                                                        // აჭრის უსერს
                                                        $unpacked_data = unpack('ntwo_bytes/A*card_number', $data['payload']);
                                                        print_r($unpacked_data);
                                                        $card = Card::where('card_number', $unpacked_data['card_number'])
                                                        ->where('device_id', $device->id)
                                                        ->first();
                                                        $user = User::where('id', $card->user_id)->first();
                                                        $lastAmount = LastUserAmount::where('user_id', $user->id)
                                                        ->where('device_id', $device->id)
                                                        ->first();
                                                        print_r($lastAmount);
                                                        // აკლებს თანხას ლასთ ამაუNთს
                                                        //  ბოლო არსებული თანხა შესაძლოა იყოს 10 თეთრი , two_bytes  შეიძლება იყოს 0 თეთრი
                                                        // ?? კითხვა ??  დევაის ლოკალური ბაზის ბალანსი გადაყავს მინუსში ?
                                                        $diff = $lastAmount->last_amount - $unpacked_data['two_bytes']; // 10 - 0 = 10
                                                        print_r($diff);
                                                        //  უსერის ბალანს აადეითბს რაც სერვერზე უნდა იყოს ისევ 10 თეთრი, 10 - 10 = 0
                                                        $user->balance = $user->balance - $diff;
                                                        // $this->Logsaver('ლოკალური ბაზიდან შემოსვლა', '507', $diff);
                                                        // აადეითბს ასევე ბოლო ცნობილ თანხასაც ბაზაზე რაც იქნება 0
                                                        $lastAmount->last_amount = $unpacked_data['two_bytes']; // two_bytes არის სავარაუდოდ დევაისის ბაზის ბალანსი
                                                        $lastAmount->save();
                                                        // უსერის ბალანსიც და ლესთ ემაუნთიც ორივე არის 0
                                                        
                                                        $user->save();
                                                        $this->saveOrUpdateEarnings($device->id, $diff, $device->company_id); // დევაის ერნინგზე კი წავა 10 თეთრი
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
                                                    
                                                    public function publishMessage($device_id, $payload): void
                                                    {
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>$payload]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        Log::debug("info", ["SS"=>"<<<<<<<<<<<<<<<<<<<<<<<<<<<<"]);
                                                        $this->mqtt->publish(
                                                            'Lift/' . $device_id . '/commands/general',
                                                            $payload,
                                                            // MqttClient::QOS_AT_LEAST_ONCE
                                                            // //     ეხლა უშვებს მინიმუმ ერთხელ
                                                            // //  მეორეს შემთხვევაში გაუშვებს მაქსიმუმ ერთხელ
                                                            MqttClient::QOS_AT_MOST_ONCE
                                                        );
                                                    }
                                                    
                                                    public function saveOrUpdateEarnings($deviceId, $earningsValue, $companyId)
                                                    {
                                                        // Generate the date for month_year
                                                        
                                                        $now = Carbon::now();
                                                        
                                                        // Try to retrieve the entry for the given device and month_year
                                                        $deviceEarnings = DeviceEarn::where('device_id', $deviceId)
                                                        ->where('month', $now->month)
                                                        ->where('year', $now->year)
                                                        ->first();
                                                        if (!empty($deviceEarnings)) {
                                                            $deviceEarnings->earnings += $earningsValue;
                                                            $deviceEarnings->save();
                                                        } else {
                                                            DeviceEarn::create([
                                                                'company_id' => $companyId,
                                                                'device_id' => $deviceId,
                                                                'month' => $now->month,
                                                                'year' => $now->year,
                                                                'earnings' => $earningsValue,
                                                            ]);
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
                                                    
                                                    public function parseHexPayload($byteString)
                                                    {
                                                        $data = unpack('Ntimestamp/Ccommand/Clength', $byteString);
                                                        $payloadLength = $data['length'];
                                                        $data['payload'] = substr($byteString, 6, $payloadLength);
                                                        return [
                                                            'timestamp' => $data['timestamp'],
                                                            'command' => $data['command'],
                                                            'length' => $data['length'],
                                                            'payload' => $data['payload'],
                                                        ];
                                                    }
                                                    
                                                    public function generateHexPayload($command, $payload)
                                                    {
                                                        $byteString = pack('VC', time(), $command);
                                                        $payloadStr = '';
                                                        foreach ($payload as $key => $value) {
                                                            if ($value['type'] === 'string') {
                                                                $payloadStr .= $value['value'];
                                                            }
                                                            if ($value['type'] === 'timestamp') {
                                                                $payloadStr .= pack('V', $value['value']);
                                                            }
                                                            if ($value['type'] === 'number') {
                                                                $payloadStr .= pack('C', $value['value']);
                                                            }
                                                            if ($value['type'] === 'number16') {
                                                                $payloadStr .= pack('v', $value['value']);
                                                            }
                                                            if ($value['type'] === 'crc32') {
                                                                $filePath = 'files/Lift_gateway_factory_ver1.0.0.bin'; // Adjust the path accordingly
                                                                $fileContent = Storage::get($filePath);
                                                                $fileSize = strlen($fileContent);
                                                                $crc = $this->crc32_custom(0xffffffff, $fileContent);
                                                                $payloadStr .= pack('V', $fileSize);
                                                                $payloadStr .= pack('V', $crc);
                                                            }
                                                        }
                                                        $byteString .= pack('C', strlen($payloadStr)) . $payloadStr;
                                                        
                                                        return $byteString;
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
                                                    
                                                    public function appConfig($data = [], $device_id)
                                                    {
                                                        $payload = $this->generateHexPayload(240, [
                                                            //startup
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                            [
                                                                'type' => 'number',
                                                                'value' => 2,
                                                            ],
                                                            //op_mode
                                                            [
                                                                'type' => 'number',
                                                                'value' => 1,
                                                            ],
                                                            //lcdBright
                                                            [
                                                                'type' => 'number',
                                                                'value' => 40,
                                                            ],
                                                            //ledBright
                                                            [
                                                                'type' => 'number',
                                                                'value' => 30,
                                                            ],
                                                            //msgAppearTime
                                                            [
                                                                'type' => 'number',
                                                                'value' => 6,
                                                            ],
                                                            //card Read delay
                                                            [
                                                                'type' => 'number',
                                                                'value' => 3,
                                                            ],
                                                            //tariff
                                                            [
                                                                'type' => 'number',
                                                                'value' => 20,
                                                            ],
                                                            //timezone
                                                            [
                                                                'type' => 'number',
                                                                'value' => 4,
                                                            ],
                                                            //storage Disable
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'lifti',
                                                            ],
                                                            ...$this->generateZeropast('lifti', 7),
                                                            
                                                            [
                                                                'type' => 'string',
                                                                'value' => '000002',
                                                            ],
                                                            ...$this->generateZeropast('000002', 7),
                                                            
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'gamarjoba',
                                                            ],
                                                            ...$this->generateZeropast('gamarjoba'),
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'rogor',
                                                            ],
                                                            ...$this->generateZeropast('rogor'),
                                                            
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'xar',
                                                            ],
                                                            ...$this->generateZeropast('xar'),
                                                            
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'validacia',
                                                            ],
                                                            ...$this->generateZeropast('validacia'),
                                                            
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'mesiji',
                                                            ],
                                                            ...$this->generateZeropast('mesiji'),
                                                        ]);
                                                        $this->publishMessage($device_id, $payload);
                                                    }
                                                    
                                                    public function generateZeropast($string, $needZero = 16)
                                                    {
                                                        $array = [];
                                                        for ($i = 0; $i < $needZero - strlen($string); $i++) {
                                                            $array[] = [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ];
                                                        }
                                                        return $array;
                                                    }
                                                    
                                                    public function ExtendFunction($data = [], $device_id)
                                                    {
                                                        $payload = $this->generateHexPayload(255, []);
                                                        $this->publishMessage($device_id, $payload);
                                                    }
                                                    
                                                    public function fotaUpdate($device_id)
                                                    {
                                                        $payload = $this->generateHexPayload(250, [
                                                            [
                                                                'type' => 'string',
                                                                'value' => 'http://188.166.166.22/api/download',
                                                            ],
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                            [
                                                                'type' => 'string',
                                                                'value' => '100',
                                                            ],
                                                            [
                                                                'type' => 'number',
                                                                'value' => 0,
                                                            ],
                                                            [
                                                                'type' => 'crc32',
                                                                'value' => 0,
                                                            ],
                                                        ]);
                                                        $this->publishMessage($device_id, $payload);
                                                    }
                                                    
                                                    private function crc32_custom($crc, $data)
                                                    {
                                                        $length = strlen($data);
                                                        for ($i = 0; $i < $length; $i++) {
                                                            $byte = ord($data[$i]);
                                                            $crc ^= $byte;
                                                            for ($j = 0; $j < 8; $j++) {
                                                                $crc = ($crc >> 1) ^ ($crc & 1 ? 0xedb88320 : 0);
                                                            }
                                                        }
                                                        return $crc;
                                                    }
                                                    
                                                    
                                                }
                                                