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

use function PHPUnit\Framework\isEmpty;

class   UserController extends Controller
{
    public function index()
    {
        return User::all();
    }


    public function getCompanyUsers(Request $request, string $companyId)
    {
        // Step 1: Get query parameters for search and pagination
        $searchQuery = $request->input('search'); // Get the search parameter
        $perPage = $request->input('per_page', 10); // Number of items per page (default to 10)
        $page = $request->input('page', 1); // Current page (default to 1)

        try {
            // Step 2: Get the company and its devices
            $company = Company::where("admin_id", $companyId)->first();
            if ($company) {




                // Log::info(["info", ["info"=> $company->company_name]]);


                $devices = Device::where('company_id', $company->id)->get();


                // Extract device IDs into an array
                $deviceIds = $devices->pluck('id')->toArray();

                // Step 3: Get all device users associated with these device IDs
                $deviceUsers = DeviceUser::whereIn('device_id', $deviceIds)->get();

                // Extract user IDs from device users
                $userIds = $deviceUsers->pluck('user_id')->toArray();

                // Step 4: Get users based on user IDs with pagination and search functionality
                $usersQuery = User::query();

                if ($company->company_name !== 'eideas') {
                    $usersQuery = User::whereIn('id', $userIds);
                }
                // Apply search filters if search query is provided
                if ($searchQuery) {
                    $usersQuery->where(function ($query) use ($searchQuery) {
                        $query->where('email', 'like', "%$searchQuery%")
                            ->orWhere('name', 'like', "%$searchQuery%")
                            ->orWhere('id', $searchQuery)->orWhere('phone', 'like', "%$searchQuery%");
                    });
                }

                // Apply pagination
                $users = $usersQuery->paginate($perPage, ['*'], 'page', $page);

                // Step 5: Transform the user data to remove unnecessary fields
                $filteredUsers = $users->getCollection()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'balance' => $user->balance,
                    ];
                });

                // Replace the original collection with the transformed collection
                $users->setCollection($filteredUsers);

                // Return the paginated user data
                return response()->json($users);
            } else {
                $devices =   Device::where('users_id', $companyId)->get();

                // Extract device IDs into an array
                $deviceIds = $devices->pluck('id')->toArray();

                // Step 3: Get all device users associated with these device IDs
                $deviceUsers = DeviceUser::whereIn('device_id', $deviceIds)->get();

                // Extract user IDs from device users
                $userIds = $deviceUsers->pluck('user_id')->toArray();

                // Step 4: Get users based on user IDs with pagination and search functionality
                $usersQuery = User::query();


                $usersQuery = User::whereIn('id', $userIds);
                // Apply search filters if search query is provided
                if ($searchQuery) {
                    $usersQuery->where(function ($query) use ($searchQuery) {
                        $query->where('email', 'like', "%$searchQuery%")
                            ->orWhere('name', 'like', "%$searchQuery%")
                            ->orWhere('id', $searchQuery)->orWhere('phone', 'like', "%$searchQuery%");
                    });
                }

                // Apply pagination
                $users = $usersQuery->paginate($perPage, ['*'], 'page', $page);

                // Step 5: Transform the user data to remove unnecessary fields
                $filteredUsers = $users->getCollection()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'balance' => $user->balance,
                    ];
                });

                // Replace the original collection with the transformed collection
                $users->setCollection($filteredUsers);

                // Return the paginated user data
                return response()->json($users);
            }
        } catch (\Throwable $th) {
            return response()->json(['err' => $th->getMessage()], 500);
        }
    }

    public function getSingleUserCards($userId)
    {
        try {
            // Step 1: Find the user
            $user = User::find($userId);

            // Check if the user exists
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Step 2: Get all cards associated with the user
            $cards = Card::where('user_id', $userId)->get();

            // Step 3: Return the list of cards
            return response()->json($cards, 200);
        } catch (\Throwable $th) {
            return response()->json(['err' => $th->getMessage()], 500);
        }
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
        if (Carbon::parse($subscriptionDate) < Carbon::now()->startOfDay()) {
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

    public function UpdateUsersFixedIndividualTarriff(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
        ]);

        $amount = $request->input('amount');
        $userId = $request->input('user_id');

        try {
            User::where('id', $userId)->update([
                'fixed_individual_amount' => $amount,
            ]);

            return response()->json([
                'msg' => 'User fixed individual tariff updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An error occurred while updating the user tariff.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function UpdateUsersFixedPhoneNumberTarriff(Request $request)
    {
        $deviceId = $request["device_id"];
        $amount = $request["amount"];
        try {
            $device = Device::find($deviceId);
            $device->fixed_phone_amount = $amount;
            $device->save();
            $deviceUsers = DeviceUser::where('device_id', $deviceId)->get();
            $users = [];
            foreach ($deviceUsers as $deviceUser) {
                $user = $deviceUser->user;

                $user->update(['fixed_phone_amount' => $amount]);
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



    public function addPhoneNumber(Request $request)
    {
        $userId = $request["user_id"];
        $number = $request['number'];


        try {
            Phonenumbers::create([
                "user_id" => $userId,
                "number" => $number,
            ]);
            return response()->json(["msg" => "Number has been added"], 201);
        } catch (\Throwable $e) {
            return response()->json(["msg" => $e]);
        }
    }

    public function getPhoneNumbers($user_id)
    {
        try {
            $data = Phonenumbers::where("user_id", $user_id)->get();

            return response()->json(["data" => $data]);
        } catch (\Throwable $e) {
            return response()->json(["msg" => $e]);
        }
    }

    public function deletePhoneNumber($id)
    {
        try {
            $phoneNumber = Phonenumbers::find($id);

            if (!$phoneNumber) {
                return response()->json(["msg" => "Phone number not found"], 404);
            }

            $phoneNumber->delete();

            return response()->json(["msg" => "Number has been deleted"]);
        } catch (\Throwable $e) {
            return response()->json(["msg" => $e->getMessage()], 500);
        }
    }


    public function registerMultipleUsers(Request $request)
    {
        try {
            $users = $request['users'];
            $deviceId = $request['device_id'];
            foreach ($users as $user) {
                $this->uploadMultipleUsersChecker($user);
                if (!isset($user["email"])  ) {


                  $u =  User::create([
                        'name' => $user['name'],
                        'id_number' => $user["id_number"],
                        "phone" => $user["phone_number"],
                        "password" => Hash::make($user["phone_number"])

                    ]);

                    DeviceUser::create([
                        "device_Id"=>$deviceId,
                        "user_id"=> $u->id,
                    ]);
                } else {
                   $u = User::create([
                        'name' => $user['name'],
                        'id_number' => $user["id_number"],
                        "email" => $user['email'],
                        "phone" => $user["phone_number"],
                        "password" => Hash::make($user["phone_number"])

                    ]);
                    DeviceUser::create([
                        "device_Id"=>$deviceId,
                        "user_id"=> $u->id,
                    ]);
                }
            }
            return response()->json(["msg" => "მომხმარებლები წარმატებით აიტვირთა"], 200);
        } catch (\Exception $e) {
            return response()->json(["msg" => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(["msg" => $e->getMessage()], 500);
        }
    }

    private function uploadMultipleUsersChecker($data)
    {
        $idNumber = $data['id_number'];
        $phoneNumber = $data['phone_number'];

        // Check if the ID number is exactly 11 digits long and contains only digits
        if (!preg_match('/^\d{11}$/', $idNumber)) {
            throw new \Exception('აიდი ნომერი უნდა იყოს 11 ციფრით: ' . $idNumber);
        }

        // Check if the phone number is exactly 9 digits long and contains only digits
        if (!preg_match('/^\d{9}$/', $phoneNumber)) {
            throw new \Exception('ტელეფონის ნომერი უნდა იყოს ცხრა ციფრიანი: ' . $phoneNumber);
        }
    }
}

    // public function setPhoneNumberTarrif(Request $request){

    //     try {
    //         $fixedPhoneAmount = $request["fixed_phone_amount"];
    //         $userId = $request["user_id"];
    //         $user = User::find($userId);

    //         if(!$user){
    //             return response()->json(['msg'=>"not found"]);
    //         }

    //         $user->fixed_phone_amount = $fixedPhoneAmount;
    //         $user->save();

    //         return response()->json(["msg"=>"updated"]);

    //     } catch (\Throwable $e) {
    //         return response()->json(["msg" => $e]);
    //     }
    // }
 
        
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
