 <?php 

//    dzveli wasashleli methodi  NOMERI 1 
    // public function saveOrUpdateEarnings($deviceId, $earningsValue, $companyId)
    // {
    //     // Generate the date for month_year
    //     // $this->Logsaver('868', $companyId, 'შემოსვლა ეივ ერნინგშ');
    //     // TO DO find company cashback and add  to DeviceEarn find device tariff with deviceID
    //     $now = Carbon::now();
    //     $user = User::where('id', $companyId)->first();
    //     $device = Device::where('id', $deviceId)->first();

    //     if ($user->cashback == 0) {
    //         // $this->Logsaver('876', $user->cashback, 'უსერის ქეშბექი');

    //         $user = User::where('id', $device->users_id)->first();
    //     }
    //     $this->Logsaver('879', $earningsValue, ' ერნიგნები');

    //     // $this->Logsaver('881', $user->id, 'მენეჯერის id');

    //     // Try to retrieve the entry for the given device and month_year
    //     $deviceEarnings = DeviceEarn::where('device_id', $deviceId)
    //         ->where('month', $now->month)
    //         ->where('year', $now->year)
    //         ->first();
    //     if (!empty($deviceEarnings)) {
    //         // $this->Logsaver('889', $user->id, 'devais ერნინგები ცარიელია');

    //         if ($user && $device) {
    //             // $this->Logsaver(
    //             //     $earningsValue,
    //             //     $user->cashback,
    //             //     'device->deviceTariffAmount'
    //             // );

    //             if ($device->deviceTariffAmount != null) {
    //                 $deviceEarnings->earnings =
    //                     $deviceEarnings->earnings + $earningsValue;
    //                 $deviceEarnings->cashback = $user->cashback;
    //                 $deviceEarnings->deviceTariff = $device->deviceTariffAmount;
    //                 $deviceEarnings->save();
    //             } else {
    //                 $deviceEarnings->earnings =
    //                     $deviceEarnings->earnings + $earningsValue;
    //                 $deviceEarnings->cashback = $user->cashback;
    //                 $deviceEarnings->save();
    //             }
    //         } else {
    //             $deviceEarnings->earnings += $earningsValue;


    //             $deviceEarnings->save();
    //         }
    //     } else {
    //         // $this->Logsaver('906', $user->id, 'BIG ELSE');

    //         if ($user && $device) {
    //             // $this->Logsaver('909', $user->id, 'user && device 2 ');


    //             DeviceEarn::create([
    //                 'company_id' => $companyId,
    //                 'device_id' => $deviceId,
    //                 'month' => $now->month,
    //                 'year' => $now->year,
    //                 'earnings' => $earningsValue,
    //                 'cashback' => $user->cashback,
    //                 'deviceTariff' => $device->deviceTariffAmount,
    //             ]);
    //         } else {
    //             // $this->Logsaver('921', $user->id, 'user && device 2  ELSE');


    //             DeviceEarn::create([
    //                 'company_id' => $companyId,
    //                 'device_id' => $deviceId,
    //                 'month' => $now->month,
    //                 'year' => $now->year,
    //                 'earnings' => $earningsValue,
    //             ]);
    //         }
    //     }
    //     // Save the model (either updates or creates based on existence)
    // }



//   მეორე 2 
  


    // $user = User::where('id', $card->user_id)->first();
    //         $userCardAmount = Card::where('user_id', $user->id)->count();

    //         if ($device->op_mode == 0) {

    //             $userDevice = DeviceUser::where('user_id', $user->id)
    //                 ->where('device_id', $card->device_id)
    //                 ->first();
    //             if (time()  > Carbon::parse($userDevice->subscription)->timestamp) {
    //                 Log::debug("No date");
    //                 $this->noMoney($device->dev_id);
    //                 return;
    //             }
    //             if (
    //                 time() < Carbon::parse($userDevice->subscription)->timestamp
    //             ) {
    //                 Log::debug("MQTT CONTROLLER Carbon");
    //                 $payload = $this->generateHexPayload(4, [
    //                     [
    //                         'type' => 'timestamp',
    //                         'value' => Carbon::parse($userDevice->subscription)
    //                             ->timestamp,
    //                     ],
    //                     [
    //                         'type' => 'string',
    //                         'value' => $data['payload'],
    //                     ],
    //                     [
    //                         'type' => 'number',
    //                         'value' => 0,
    //                     ],
    //                 ]);
    //                 $this->publishMessage($device->dev_id, $payload);


                
    //             } else if (  $userCardAmount > 0 &&  $userCardAmount > 0 &&  $device->tariff_amount == 0 || $device->tariff_amount <= 0 || $device->tariff_amount == "0" ) {
    //                 $userFixedBalnce = $user->fixed_card_amount;
    //                 $fixedCard = $userFixedBalnce * $userCardAmount;


    //                 $userBalance = $user->balance;

    //                 $user->freezed_balance = $fixedCard;




    //                 if ($user->balance - $user->freezed_balance >= $fixedCard &&   $userCardAmount > 0 ) {
    //                     Log::debug("შემოვიდა mqttController");
    //                     $user->balance -= $fixedCard;
    //                     $user->freezed_balance -= $fixedCard;
    //                     $currentDay = Carbon::now()->day;
    //                     if ($currentDay < $device->pay_day) {
    //                         $nextMonthPayDay = Carbon::now()
    //                             ->startOfMonth()
    //                             ->addDays($device->pay_day - 1);
    //                     } else {
    //                         $nextMonthPayDay = Carbon::now()
    //                             ->addMonth()
    //                             ->startOfMonth()
    //                             ->addDays($device->pay_day - 1);
    //                     }
    //                     $userDevice->subscription = $nextMonthPayDay;

    //                     $userDevice->save();
    //                     $payload = $this->generateHexPayload(4, [
    //                         [
    //                             'type' => 'timestamp',
    //                             'value' => Carbon::parse($nextMonthPayDay)
    //                                 ->timestamp,
    //                         ],
    //                         [
    //                             'type' => 'string',
    //                             'value' => $data['payload'],
    //                         ],
    //                         [
    //                             'type' => 'number',
    //                             'value' => 0,
    //                         ],
    //                     ]);
    //                     $this->publishMessage($device->dev_id, $payload);
    //                 }
    //                 // თუ დევაისი ტარიფი ნულია
    //                 else if (
    //                     $user->balance - $user->freezed_balance >=
    //                     $device->tariff_amount &&   $userCardAmount > 0 
    //                 ) {
    //                     $user->freezed_balance =
    //                         $user->freezed_balance + $device->tariff_amount;
    //                     $user->save();
    //                     $currentDay = Carbon::now()->day;
    //                     if ($currentDay < $device->pay_day) {
    //                         $nextMonthPayDay = Carbon::now()
    //                             ->startOfMonth()
    //                             ->addDays($device->pay_day - 1);
    //                     } else {
    //                         $nextMonthPayDay = Carbon::now()
    //                             ->addMonth()
    //                             ->startOfMonth()
    //                             ->addDays($device->pay_day - 1);
    //                     }
    //                     $userDevice->subscription = $nextMonthPayDay;

    //                     $userDevice->save();
    //                     $payload = $this->generateHexPayload(4, [
    //                         [
    //                             'type' => 'timestamp',
    //                             'value' => Carbon::parse($nextMonthPayDay)
    //                                 ->timestamp,
    //                         ],
    //                         [
    //                             'type' => 'string',
    //                             'value' => $data['payload'],
    //                         ],
    //                         [
    //                             'type' => 'number',
    //                             'value' => 0,
    //                         ],
    //                     ]);
    //                     $this->publishMessage($device->dev_id, $payload);
    //                 } else {
    //                     $this->noMoney($device->dev_id);
    //                 }
    //             }
    //         }
            
    //         else if (
    //             (int) $user->balance - $user->freezed_balance >=
    //             $device->tariff_amount 
    //         ) {
    //             $lastAmount = LastUserAmount::where('user_id', $user->id)
    //                 ->where('device_id', $device->id)
    //                 ->first();

    //             if (empty($lastAmount->user_id)) {
    //                 LastUserAmount::insert([
    //                     'user_id' => $user->id,
    //                     'device_id' => $device->id,
    //                     'last_amount' =>
    //                     $user->balance - $user->freezed_balance,
    //                 ]);
    //             } else {
    //                 $lastAmount->last_amount =
    //                     $user->balance - $user->freezed_balance;
    //                 $lastAmount->save();
    //             }
    //             $payload = $this->generateHexPayload(3, [
    //                 [
    //                     'type' => 'string',
    //                     'value' => str_pad($user->id, 6, '0', STR_PAD_LEFT),
    //                 ],
    //                 [
    //                     'type' => 'number',
    //                     'value' => 0,
    //                 ],
    //                 ['type' => 'string', 'value' => $data['payload']],
    //                 [
    //                     'type' => 'number',
    //                     'value' => 0,
    //                 ],
    //                 [
    //                     'type' => 'number16',
    //                     'value' => $user->balance - $user->freezed_balance,
    //                 ],
    //             ]);
    //             $user->save();
    //             //  adre es  iyo aq 
    //             // $this->saveOrUpdateEarnings(
    //             //     $device->id,
    //             //     $device->tariff_amount,
    //             //     $device->company_id
    //             // );
    //             $this->UpdateDevicEarn($device);
    //             // 
    //             $this->publishMessage($device->dev_id, $payload);
    //         } else {
    //             $this->noMoney($device->dev_id);
    //         }