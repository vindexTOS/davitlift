<?php


namespace App\Services;

use App\Models\Notifications;
use Illuminate\Support\Facades\DB;

trait NotificationsService
{

    public function storeNotification(string $user_id, string $message, string $metaData, string $messageType)
    {
        DB::beginTransaction();

        try {
            Notifications::create([
                "user_id" => $user_id,
                'message' => $message,
                'meta-data' => $metaData,
                'message-type' => $messageType
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
