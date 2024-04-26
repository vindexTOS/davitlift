<?php

namespace App\Http\Controllers\API;
use App\Models\Card;
use App\Models\CompanyTransaction;
use http\Env\Response;
use Illuminate\Support\Facades\Hash;

use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }
    public function getBalance()
    {
        $user = User::where('id', Auth::id())->first();
        return response()->json(['balance' => $user->balance]);
    }
    public function cashback($user_id, $cashback)
    {
        if ($cashback < 0 || $cashback > 100) {
            return response()->json([
                'message' => 'შეიყვანეთ მნიშვნელობა 0 და 100 შორის',
            ]);
        }
        return User::where('id', $user_id)->update(['cashback' => $cashback]);
    }
    public function addToDevice($user_search, $device_id)
    {
        $device = Device::where('id', $device_id)->first();
        if (empty($device)) {
            return response()->json(
                ['message' => 'ასეთი დივაისი არ არსებობს'],
                422
            );
        }
        $user = User::where('phone', $user_search)
            ->orWhere('email', $user_search)
            ->first();
        if (empty($user)) {
            return response()->json(
                ['message' => 'ასეთი მომხამრებელი არ არსებობს'],
                422
            );
        }
        $isAdd = DeviceUser::where('user_id', $user->id)
            ->where('device_id', $device_id)
            ->first();
        if (!empty($isAdd)) {
            return response()->json(
                ['message' => 'ასეთი მომხამრებელი უკვე დამატებულია'],
                422
            );
        }
        $create = [
            'user_id' => $user->id,
            'device_id' => $device_id,
        ];

        $currentDay = Carbon::now()->day;
        if ($device->op_mode == '0') {
            if ($device->tariff_amount <= $user->balance) {
                if ($currentDay < $device->pay_day) {
                    $create['subscription'] = Carbon::now()
                        ->startOfMonth()
                        ->addDays($device->pay_day - 1);
                } else {
                    $create['subscription'] = Carbon::now()
                        ->addMonth()
                        ->startOfMonth()
                        ->addDays($device->pay_day - 1);
                }
            }
        }
        DeviceUser::create($create);
        return response()->json(
            ['message' => 'ასეთი მომხამრებელი უკვე დამატებულია'],
            200
        );
    }
    public function removeToDevice($user_id, $device_id)
    {
        try {
            DeviceUser::where('user_id', $user_id)
                ->where('device_id', $device_id)
                ->delete();
            Card::where('user_id', $user_id)
                ->where('device_id', $device_id)
                ->delete();
            return response()->json(['a' => 'b'], 200);
        } catch (\Throwable $th) {
            return response()->json(['err' => 'err'], 500);
        }
    }

    public function store(Request $request)
    {
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return $user;
    }

    // public function update(Request $request, User $user)
    // {
    //     $validated = $request->validate([
    //         'id' => 'required|exists:users,id',
    //         'name' => 'string|max:255',
    //         'email' => 'email|max:255',
    //         'balance' => 'integer',
    //         'phone' => 'string|min:5|max:15',
    //         'role' => 'string',
    //     ]);

    //     $user = User::findOrFail($validated['id']);

    //     $user->update($validated);

    //     return response()->json(['msg' => 'user updated']);
    // }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
    public function generateElevatorCode(Request $request)
    {
        $code = Str::random(4); // Generates a random 4-character code
        $expiresAt = Carbon::now()->addHour(); // Set the expiration timestamp to 1 hour from now

        DB::table('elevator_codes')->insert([
            'code' => $code,
            'user_id' => Auth::id(),
            'device_id' => $request->device,
            'expires_at' => $expiresAt,
        ]);

        return $code;
    }
    public function changeManager($company_id, $user_id, $new_email)
    {
        $newUser = User::where('email', $new_email)->first();
        $oldUser = User::where('id', $user_id)->first();

        if (empty($newUser)) {
            return response()->json(
                ['message' => 'ასეთი მომხმარებელი არ არსებობს'],
                422
            );
        }
        $newUser->update(['role' => 'manager']);
        $oldUser->update(['role' => 'member']);
        $newUser->cashback = $oldUser->cashback;
        $newUser->save();
        CompanyTransaction::where('manager_id', $oldUser->id)
            ->where('company_id', $company_id)
            ->withTrashed()
            ->update(['manager_id' => $newUser->id]);
        return Device::where('users_id', $oldUser->id)
            ->where('company_id', $company_id)
            ->withTrashed()
            ->update(['users_id' => $newUser->id]);
    }
    public function changeUserPassword(Request $request)
    {
        // Define the validation rules
        $validator = $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if the provided old password is correct
        if (!Hash::check($validator['old_password'], $user->password)) {
            return response()->json(
                ['message' => 'ძველი პარაოლი არასწორია'],
                422
            );
        }

        // Set the new password and save the user
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'პაროლი შეცვლილია']);

        // Optionally, logout the user
    }
    public function changePassword($user_id, $password)
    {
        $user = User::where('id', $user_id)->first();
        $user->password = Hash::make($password);
        $user->save();
    }
    public function neededCashback($user_id)
    {
    }

    public function updateUserSubscription(Request $request)
    {
        $validator = $request->validate([
            'balance' => 'required',
            'freezed_balance' => 'required',
            'email' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'id' => 'required',
            'subscription' => 'required',
            'role' => 'required',
        ]);

        $user = User::find($request->id);
        $deviceUser = DeviceUser::where('user_id', $request->id)->first();
        if (!$user || !$deviceUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update([
            'balance' => $request->balance,
            'freezed_balance' => $request->freezed_balance,
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role,
        ]);

        $deviceUser->update([
            'subscription' => $request->subscription,
        ]);

        return response()->json(['message' => $request->freezed_balance]);
    }
}

// balance
// :
// 2603
// cards_count
// :
// 2
// cashback
// :
// 0
// created_at
// :
// "2023-10-19T12:03:45.000000Z"
// email
// :
// "nica.16@mail.ru"
// email_verified_at
// :
// null
// freezed_balance
// :
// 2400
// hide_statistic
// :
// 0
// id
// :
// 34
// isBlocked
// :
// 0
// name
// :
// "მარიამ"
// phone
// :
// "568446044"
// pivot
// :
// {device_id: 4, user_id: 34, subscription: '2024-04-28 00:00:00'}
// role
// :
// "member"
// saved_card_status
// :
// 0
// saved_order_id
// :
// null
// subscription
// :
// "2024-04-28 00:00:00"
// updated_at
// :
// "2024-03-29T07:12:43.000000Z"
