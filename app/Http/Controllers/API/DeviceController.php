<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Device;
use App\Models\Company;
 
use App\Models\DeviceEarn;
use App\Models\DeviceUser;
use App\Models\DeviceError;
use Illuminate\Http\Request;
use App\Services\MqttService;
use PhpMqtt\Client\MqttClient;
use App\Models\CompanyTransaction;
use App\Models\UnregisteredDevice;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Exceptions\MqttNodeException;
use App\Services\MqttConnectionService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateDeviceRequest;

class DeviceController extends Controller
{

    
    public function index()
    {
        if (Auth::user()->email == config('app.super_admin_email')) {
            return Device::with('user')->withTrashed()->with('earnings')->with('errors')->get();
        }
        $companies_id = Company::where('admin_id', '=', Auth::id())->pluck('id')->toArray();
        return Device::with('user')->withTrashed()->with('earnings')->whereIn('company_id', $companies_id)->orWhere('users_id', Auth::id())->get();
    }
    public function userDeviceUser($id) {
        $deviceIds = DeviceUser::where('user_id', $id)->pluck('device_id')->toArray();

        // Get the admin IDs associated with those devices
        $adminIds = Device::whereIn('id', $deviceIds)->pluck('users_id')->unique()->toArray();

        // Get all devices that have the same admin_id as any of the previously found admin_ids
        $data['device'] = Device::whereIn('users_id', $adminIds)->with('user')->with('earnings')->with('errors')->with('withdrawals')->get();
        $earnings = [];
        $manager = [];
        $devicesActivity = [
            'active' => 0,
            'inactive' => 0
        ];
        $currentTimestamp = time();

        foreach ($data['device'] as $key => $device) {
            if(count($device['earnings']) !== 0) {
                $earnings = [...$earnings, ...$device['earnings']];
                unset($device['earnings']);
            }
            $manager = $device['user'];
            unset($device['user']);
            if(strtotime($device['lastBeat']) > $currentTimestamp) {
                $devicesActivity['active'] += 1;
            } else {
                $devicesActivity['inactive'] += 1;
            }
        }
        $data['deviceActivity'] = $devicesActivity;
        $data['earnings'] = $this->getEarnings($earnings);
        $data['manager'] = $manager;
         
        if(empty($manager)){
            $data['payedCashback'] = 0;
        } else {
            $data['payedCashback'] = CompanyTransaction::where('manager_id',$manager->id)->sum('amount');
        }
        return $data;
    }
    public function userDevice(): \Illuminate\Database\Eloquent\Collection|array
    {
        $deviceIds = DeviceUser::where('user_id', Auth::id())->pluck('device_id')->toArray();

        // Get the admin IDs associated with those devices
        $adminIds = Device::whereIn('id', $deviceIds)->pluck('users_id')->unique()->toArray();

        // Get all devices that have the same admin_id as any of the previously found admin_ids
        $data['device'] = Device::whereIn('users_id', $adminIds)->with('user')->with('earnings')->with('errors')->with('withdrawals')->get();
        $earnings = [];
        $manager = [];
        $devicesActivity = [
            'active' => 0,
            'inactive' => 0
        ];
        $currentTimestamp = time();

        foreach ($data['device'] as $key => $device) {
            if(count($device['earnings']) !== 0) {
                $earnings = [...$earnings, ...$device['earnings']];
                unset($device['earnings']);
            }
            $manager = $device['user'];
            unset($device['user']);
            if(strtotime($device['lastBeat']) > $currentTimestamp) {
                $devicesActivity['active'] += 1;
            } else {
                $devicesActivity['inactive'] += 1;
            }
        }
        $data['deviceActivity'] = $devicesActivity;
        $data['earnings'] = $this->getEarnings($earnings);
        $data['manager'] = $manager;
        if(empty($manager)){
            $data['payedCashback'] = 0;
        } else {
            $data['payedCashback'] = CompanyTransaction::where('manager_id',$manager->id)->where('type',3)->sum('amount');
        }
        return $data;
    }
    public function getEarnings($allEarnings) {
        $groupedEarnings = [];
        foreach ($allEarnings as $earning) {
            $month = $earning->month;
            $year = $earning->year;
            $key = "$month-$year"; // e.g., "9-2023"
            if (isset($groupedEarnings[$key])) {
                // If the key already exists, sum the earnings
                $groupedEarnings[$key]["earnings"] += floatval($earning["earnings"]);
            } else {
                // Or create a new key with initial earning
                $groupedEarnings[$key] = [
                    "month" => $month,
                    "year" => $year,
                    "earnings" => floatval($earning["earnings"])
                ];
            }
        }
        return $groupedEarnings;
    }
    public function store(CreateDeviceRequest $request)
    {
        $data = $request->all();
        if($request->pay_day < 1 || $request->pay_day > 28 ){
            return response()->json(['message' => 'არასწორი გადახდის თარიღია მითითებული'], 422);
        }
        $UnregisteredDevice = UnregisteredDevice::where('dev_id',$data['dev_id'])->first();
        if(empty($UnregisteredDevice)) return response()->json(['message' => 'ასეთი დივაისის აიდი არაა უცხო დივაისთა სიაში'],422);

        $user = User::where('email', $data['admin_email'])->first();
        if(empty($user)) return response()->json(['message' => 'მომხმარებელი აღნიშნული მაილით ვერ მოიძებნა'],422);
        $user->update(['role' =>  "manager"]);

        $device = Device::create(['users_id' => $user->id,...$data,
            'soft_version' => $UnregisteredDevice->soft_version,'hardware_version' => $UnregisteredDevice->hardware_version,]);
        if(!empty($device)) {
            $mqttService = app(MqttConnectionService::class);
            $mqtt = $mqttService->connect();
            $this->publishMessage( $device->dev_id, $this->sendDeviceParameters($device));
            $UnregisteredDevice->delete();
        } else {
            return response()->json('something go wrong', 422);
        }
        return response()->json($device, 201);
    }
    public function sendExtendConf($device) {
        return $this->generateHexPayload(241,[
            //startup
            [
                'type' => 'number',
                'value' => 0
            ],
            [
                'type' => 'number',
                'value' => 0
            ],
            [
                'type' => 'number',
                'value' => $device->relay1_node
            ],

        ]);
    }

    public function sendDeviceParameters($device) {
        return $this->generateHexPayload(240, [
            //startup
            [
                'type' => 'number',
                'value' => 0
            ],
            [
                'type' => 'number',
                'value' => 0
            ],
            //op_mode

            [
                'type' => 'number',
                'value' => $device->op_mode
            ],
            //relay
            [
                'type' => 'number',
                'value' => $device->relay_pulse_time
            ],
            //lcdBright
            [
                'type' => 'number',
                'value' => $device->lcd_brightness
            ],
            //ledBright
            [
                'type' => 'number',
                'value' => $device->led_brightness
            ],
            //msgAppearTime
            [
                'type' => 'number',
                'value' => $device->msg_appear_time
            ],
            //card Read delay
            [
                'type' => 'number',
                'value' => $device->card_read_delay
            ],
            //tariff
            [
                'type' => 'number16',
                'value' => $device->tariff_amount
            ],
            //timezone
            [
                'type' => 'number',
                'value' => 4
            ],
            //storage Disable
            [
                'type' => 'number',
                'value' => $device->storage_disable
            ],

            // dev id
            [
                'type' => 'string',
                'value' => str_pad($device->id, 5, '0', STR_PAD_LEFT)
            ],
            [
                'type' => 'number',
                'value' => 0
            ],
            // guest 1
            [
                'type' => 'string',
                'value' => $device->guest_msg_L1
            ],
            ...$this->generateZeropast($device->guest_msg_L1),
            [
                'type' => 'string',
                'value' => $device->guest_msg_L2
            ],
            ...$this->generateZeropast($device->guest_msg_L2),

            [
                'type' => 'string',
                'value' => $device->guest_msg_L3
            ],
            ...$this->generateZeropast($device->guest_msg_L3),

            [
                'type' => 'string',
                'value' => $device->validity_msg_L1
            ],
            ...$this->generateZeropast($device->validity_msg_L1),

            [
                'type' => 'string',
                'value' => $device->validity_msg_L2
            ],
            ...$this->generateZeropast($device->validity_msg_L2),
        ]);
    }

    public function show($device)
    {
 
  $device = Device::where('id', $device)
  ->with('user')
  ->with('users')
  ->with('earnings')
  ->with('errors')
  ->with('withdrawals')
  ->with('company')
  ->with('lastUpdate')
  ->first();

 
if ($device) {
   $subscriptions =DeviceUser::where('device_id', $device->id)
      ->pluck('subscription', 'user_id');

 
  foreach ($device->users as $user) {
      $user->subscription = $subscriptions[$user->id] ?? null;
  }
}

return  $device;  

}

    public function update( $request,  $device)
    {
        $data = $request->all();
        if($request->pay_day < 1 || $request->pay_day > 28 ){
            return response()->json(['message' => 'არასწორი გადახდის თარიღია მითითებული'], 422);
        }
        $user = User::where('email', $data['admin_email'])->first();
        if(empty($user)) return response()->json(['message' => 'მომხმარებელი აღნიშნული მაილით ვერ მოიძებნა'],422);
        if(!empty($data['pay_day']) && $data['pay_day'] >= 31 )  return response()->json(['message' => 'გადახდის თარიღი დიდი რიცხვია'],422);
        $device->update([...$request->all(),'users_id' => $user->id]);

        return response()->json($device);
    }
    public function resetDevice (Request $request, Device $device) {
        $data = $request->all();
        $user = User::where('email', $data['admin_email'])->first();
        if(empty($user)) return response()->json(['message' => 'მომხმარებელი აღნიშნული მაილით ვერ მოიძებნა'],422);

        if($device->update($request->all())) {
            $mqttService = app(MqttConnectionService::class);
            $mqtt = $mqttService->connect();
            $this->publishMessage( $device->dev_id, $this->generateHexPayload(255, []));
            $mqtt->loop(true, true);
        } else {
            return response()->json('something go wrong', 422);
        }
        return $device;

    }
    public function setAppConf(Request $request, Device $device) {
      



        
        $this->update($request,$device);
        $mqttService = app(MqttConnectionService::class);
        $mqtt = $mqttService->connect();
      
         if($device->op_mode == 2){
            $device->op_mode = 0;
         }   


     
        $this->publishMessage( $device->dev_id, $this->sendDeviceParameters($device));
        $mqtt->loop(true, true);

        return $device;
    }
    public function setExtConf(Request $request, Device $device) {
        Log::info(time());
        $this->update($request,$device);
        $mqttService = app(MqttConnectionService::class);
        $mqtt = $mqttService->connect();
        $this->publishMessage($device->dev_id, $this->sendExtendConf($device));
        $mqtt->loop(true, true);

        return $device;

    }


    public function destroy(Device $device)
    {
        $device->delete();
        return response()->json(null, 204);
    }

    public function generateZeropast($string,$needZero = 16): array
    {
        $array = [];
        for($i = 0; $i < $needZero - strlen($string);$i++) {
            $array[] = [
                'type' => 'number',
                'value' => 0
            ] ;
        }
        return $array;
    }
    public function generateHexPayload($command, $payload)
    {
        return [
            'command' => $command,
            'payload' => $payload
        ];
    }
    public function deleteError($id) {
        return DeviceError::where('id',$id)->delete();
    }
    public function publishMessage($device_id,$payload)
    {
        $data = [
            'device_id' => $device_id,
            'payload' => $payload
        ];
        $queryParams = http_build_query($data);
        // $response = Http::get('http://localhost:3000/mqtt/general?' . $queryParams);
        // return $response->json(['data' => ['dasd']]);

    }
    public function updateDeviceTariff($id,  Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        $device = Device::find($id);
    
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
    
        $device->deviceTariffAmount = $request->input('amount');
        $device->save();
    
        return response()->json(["data" => $device]);
    }

    public function updateManyDeviceTariff($managerId, Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        Device::where('users_id', $managerId)->update(['deviceTariffAmount' => $request->input('amount')]);
    
        return response()->json(["message" => "Devices updated successfully"]);
    }

    public function EditDevicEarn (Request $request){
    $id = $request->id;

    $deviceEarn = DeviceEarn::where("id", $id);
         
    $deviceEarn->cashback = $request->cashback;
     $deviceEarn->update([
        'cashback' =>  $request->cashback ,
        'deviceTariff' => $request->deviceTariff,
        "earnings" => $request->earnings
    ]);
        return response()->json(["data"=>  "item updated" ]);
     } 
}
