<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notifications;
use App\Exceptions\UserNotFound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\Notification;

class NotificationsController extends Controller
{
    public function index($user_id, Request $request): JsonResponse
    {
        try {
            $user = User::find($user_id);
    
            if (!$user) {
                throw new UserNotFound("User with ID $user_id does not exist.");
            }
    
            // Ensure that the current page is an integer
            $currentPage = (int) $request->input('page', 1);
    
            // Ensure $user_id is an integer as well, if it isn't already
            $userId = (int) $user_id;
    
            $notifications = Notifications::where("user_id", $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'page', $currentPage);
    
            return response()->json($notifications);
        } catch (UserNotFound $e) {
            return $e->render(request());
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    public function checkAllNotificationsAsRead($user_id)
    {
        try {
            Notifications::where("user_id", $user_id)->update([
                "isRead" => 1
            ]);
            return response()->json(["msg" => "Notifications marked as read"], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
