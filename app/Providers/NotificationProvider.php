<?php

namespace App\Providers;

use App\Models\Notifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

trait NotificationProvider  
{
    public function createUserGenericNotification(string $user_id, string $message, string $metaData, string $messageType)
    { 

        DB::beginTransaction();
        Log::info("დდდდ", ["baby"=> "შემოსვლა"]);

        try {
            Notifications::create([
                "user_id" => $user_id,
                'message' => $message,
                'meta-data' => $metaData,
                'message-type' => $messageType
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            Log::info("speak", ["baby"=> $th]);
            DB::rollback();
        }
    }



    public function createDeviceSpecificeNotification(string $user_id, string $device_id, string $message, string $metaData, string $messageType)
    {
        DB::beginTransaction();

        try {

            Notifications::create([
                "user_id" => $user_id,
                'message' => $message,
                'meta-data' => $metaData,
                'message-type' => $messageType,
                "device_id" => $device_id
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    public function readNotification(string $notification_id)
    {
        DB::beginTransaction();

        try {

            $notification = Notifications::find("id", $notification_id)->getFirst();
            $notification->isRead = true;
            $notification->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }



    public function getUserSpecificeNotification(string $user_id){


    }


    public function getDeviceSpecificeNotification(string $device_id){
         
    }
}
