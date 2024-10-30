<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

use http\Env\Response;
use App\Models\Company;
use App\Models\DeviceUser;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TbcTransaction;
use App\Models\CompanyTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ElevatorUse;
use App\Models\Phonenumbers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

      
        // $create['subscription'] = Carbon::now()->format('Y-m-d H:i:s');
     
        $create['subscription'] = '2020-01-01 00:00:00';
        $subscriptionDate = $isAdd ? Carbon::parse($isAdd->subscription) : null; // Get existing subscription date
    
        // Combined logic for subscription date
        if ( Carbon::parse(  $subscriptionDate) < Carbon::now()->startOfDay()) {
            // Keep the current subscription
            $create['subscription'] = $subscriptionDate->format('Y-m-d H:i:s');
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

    public function UserTransactionsBasedOnDevice($device_id)
    {
        $users = DeviceUser::where('device_id', $device_id)->get();
        $transactionsData = [];
        $formattedTransactions = collect();
        $formattedTbcTransactions = collect();
        $combinedTransactions = collect();
        foreach ($users as $user) {
            $id = $user->id;
            $transactions = Transaction::where('user_id', $id)
                ->where('status', 'Succeeded')
                ->get();
            // Format transactions
            $formattedTransactionsForUser = $transactions->map(function (
                $transaction
            ) {
                return [
                    'amount' => +$transaction->amount,
                    'transaction_id' => $transaction->transaction_id,
                    'created_at' => $transaction->created_at->format(
                        'Y-m-d H:i:s'
                    ),
                ];
            });

            // Append formatted transactions for this user to the accumulated array
            $formattedTransactions = $formattedTransactions->merge(
                $formattedTransactionsForUser
            );

            // Fetch TbcTransactions for the same user
            $tbcTransactions = TbcTransaction::where('user_id', $id)->get();

            // Format TbcTransactions
            $formattedTbcTransactionsForUser = $tbcTransactions->map(function (
                $tbcTransaction
            ) {
                // Format and return the transaction data
                return [
                    'amount' => +$tbcTransaction->amount,
                    'transaction_id' => $tbcTransaction->order_id,
                    'created_at' => $tbcTransaction->created_at->format(
                        'Y-m-d H:i:s'
                    ),
                ];
            });

            $formattedTbcTransactions = $formattedTbcTransactions->merge(
                $formattedTbcTransactionsForUser
            );
            if (!$formattedTransactions->isEmpty()) {
                // Merge formatted transactions with formatted TbcTransactions
                $combinedTransactions = $combinedTransactions->merge(
                    $formattedTbcTransactions
                );
                $combinedTransactions = $combinedTransactions->merge(
                    $formattedTransactions
                );
                // Append combined transactions to transactionsData for this user
                $transactionsData[$id] = $combinedTransactions;
            }
        }

        $result = [];
        $ids = [];
        // Accumulate transactions outside the loop

        foreach ($transactionsData as $singleTransaction) {
            foreach ($singleTransaction as $transaction) {
                try {
                    // Extract month and year from the created_at field
                    $monthYear = date(
                        'Y-m',
                        strtotime($transaction['created_at'])
                    );

                    // If the month doesn't exist in $result yet, initialize it to 0
                    if (!isset($result[$monthYear])) {
                        $result[$monthYear] = 0;
                    }

                    // Add the amount to the corresponding month
                    if (!in_array($transaction['transaction_id'], $ids)) {
                        // Add the amount to the corresponding month
                        if (!isset($result[$monthYear])) {
                            $result[$monthYear] = 0;
                        }
                        $result[$monthYear] += $transaction['amount'];

                        // Add the transaction ID to the $ids array to mark it as processed
                        $ids[] = $transaction['transaction_id'];
                    }
                } catch (\Exception $e) {
                    // Log or handle the error as needed
                    // For now, skipping the transaction
                    continue;
                }
            }
        }

        return response()->json([
            'data' => $result,
        ]);
    }



    public function UpdateUsersFixedCardTarriff(Request $request)
    {
        $deviceId = $request["device_id"];
        $amount = $request["amount"];
        try {
            $device = Device::find($deviceId);
            $device->fixed_card_amount = $amount;
            $device->save();
            $deviceUsers = DeviceUser::where('device_id', $deviceId)->get();
            $users = [];
            foreach ($deviceUsers as $deviceUser) {
                $user = $deviceUser->user;

                $user->update(['fixed_card_amount' => $amount]);
            }
            return response()->json(["msg" => "Device Has Been Updated"]);
        } catch (\Exception $e) {
            return response()->json(["msg" => $e]);
        }
    }

    public function GetUsersElevatorUse(string $user_id)
    {

        try {

            $eleveatorUses = ElevatorUse::where("user_id", $user_id)->get();

            if (count($eleveatorUses) <= 0) {
                return [];
            }
            return response()->json(["data" => $eleveatorUses]);
        } catch (\Throwable $e) {
            return response()->json(["msg" => $e]);
        }
    }


// / phone number user phone number creation



 public function addPhoneNumber(Request $request){
   $userId = $request["user_id"];
   $number = $request['number'];


   try {
   Phonenumbers::create([
     "user_id"=>$userId,
     "number"=>$number,
   ]);
   return response()->json(["msg"=>"Number has been added"], 201);

   } catch (\Throwable $e) {
     return response()->json(["msg" => $e]);
   }
 }

public function getPhoneNumbers($user_id){
 try {
      $data = Phonenumbers::where("user_id", $user_id)->get();

      return response()->json(["data"=>$data]);
 } catch (\Throwable $e) {
    return response()->json(["msg" => $e]);

 }

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
