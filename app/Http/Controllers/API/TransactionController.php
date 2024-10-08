<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;

use Monolog\Logger;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use GuzzleHttp\Client;
use App\Models\Company;
use App\Models\DeviceUser;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use App\Models\TbcTransaction;
use App\Services\DeviceMessages;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use App\Providers\MQTTServiceProvider;
use App\Providers\TransactionProvider;
use App\Services\TransactionHandlerForOpMode;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{


    use TransactionHandlerForOpMode;
    use DeviceMessages;
    use  TransactionProvider;
    // private $client_id = '77841';
    // private $client_secret = 'OOsQRvWG33n4';




    public function index()
    {
        $userId = Auth::id();

        // Get transactions for the user
        $transactions = Transaction::where('user_id', $userId)
            ->where('status', 'Succeeded')
            ->get();

        // Fetch TbcTransactions for the same user
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'transaction_id' => $transaction->transaction_id,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'succeeded' => $transaction->status === 'Succeeded',
                'type' => 'TBC ონლაინ გადახდა',
            ];
        });

        // Fetch TbcTransactions for the same user
        $tbcTransactions = TbcTransaction::where('user_id', $userId)->get();
        if ($transactions->isEmpty() && $tbcTransactions->isEmpty()) {
            return [];
        }
        // Check if TbcTransactions is empty
        if ($tbcTransactions->isEmpty()) {
            // Return only formatted transactions for the user if there are no TbcTransactions
            return $formattedTransactions->all();
        }

        // Format TbcTransactions and merge with formatted transactions
        $formattedTbcTransactions = $tbcTransactions->map(function (
            $tbcTransaction
        ) {
            return [
                'id' => $tbcTransaction->id + 1000,
                'user_id' => $tbcTransaction->user_id,
                'amount' => $tbcTransaction->amount,
                'transaction_id' => $tbcTransaction->order_id,
                'created_at' => $tbcTransaction->created_at,
                'updated_at' => $tbcTransaction->updated_at,
                'type' => 'TBC',
            ];
        });
        if ($transactions->isEmpty()) {
            // Return only formatted transactions for the user if there are no TbcTransactions
            return $formattedTbcTransactions->all();
        }
        // Merge formatted transactions with formatted TbcTransactions
        $combinedTransactions = $formattedTransactions->merge(
            $formattedTbcTransactions
        );

        return $combinedTransactions->all();
    }

    public function perUserTransaction($id)
    {
        // Fetch transactions for the user
        $transactions = Transaction::where('user_id', $id)
            ->where('status', 'Succeeded')
            ->get();

        // Format transactions
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'transaction_id' => $transaction->transaction_id,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'succeeded' => $transaction->status === 'Succeeded',
                'type' => 'TBC ონლაინ გადახდა',
            ];
        });

        // Fetch TbcTransactions for the same user
        $tbcTransactions = TbcTransaction::where('user_id', $id)->get();
        if ($transactions->isEmpty() && $tbcTransactions->isEmpty()) {
            return [];
        }
        // Check if TbcTransactions is empty
        if ($tbcTransactions->isEmpty()) {
            // Return only formatted transactions for the user if there are no TbcTransactions
            return $formattedTransactions->all();
        }

        // Format TbcTransactions and merge with formatted transactions
        $formattedTbcTransactions = $tbcTransactions->map(function (
            $tbcTransaction
        ) {
            return [
                'id' => $tbcTransaction->id + 1000,
                'user_id' => $tbcTransaction->user_id,
                'amount' => $tbcTransaction->amount,
                'transaction_id' => $tbcTransaction->order_id,
                'created_at' => $tbcTransaction->created_at,
                'updated_at' => $tbcTransaction->updated_at,
                'type' => $tbcTransaction->type,
            ];
        });
        if ($transactions->isEmpty()) {
            // Return only formatted transactions for the user if there are no TbcTransactions
            return $formattedTbcTransactions->all();
        }
        // Merge formatted transactions with formatted TbcTransactions
        $combinedTransactions = $formattedTransactions->merge(
            $formattedTbcTransactions
        );

        return $combinedTransactions->all();
    }
    public function createTransaction($amount, $userId)
    {
        if ($amount < 0.5 || $amount > 200) {
            return response()->json(
                ['message' => 'არასწორი თანხაა მიუთითეთ 0.5 დან 200 ლარამდე '],
                422
            );
        }
        $user = User::where('id', $userId)->first();
        $token = $this->getToken();
        $data = $this->makeOrderTransaction($amount, $user, $token);
        Transaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $data['status'],
            'transaction_id' => $data['payId'],
        ]);
        return $data['links'][1]['uri'];
    }

    public function store(Request $request)
    {
        $transaction = Transaction::create($request->all());
        return response()->json($transaction, 201);
    }

    public function show(Transaction $transaction)
    {
        return $transaction;
    }

    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($request->all());
        return response()->json($transaction, 200);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }
    // LBERTY FAST PAY

    public function makeLbrtFastPayOrder(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required',
            'order_id' => 'required',
            'amount' => 'required',
        ]);

        $phone = $validatedData['phone'];
        $order_id = $validatedData['order_id'];
        $amount = $validatedData['amount'];

        //  ვეძებნთ უსერს ტელეფონის ნომრით
        $data = $request->all();

        try {
            $user = User::where('phone', $phone)->first();

            $string = $this->MakeFileId($user->id);


            // ვამოწმებ არსებობს თუ არა მსგავსი order_id ბაზაზე რომ ორჯერ არ მოხდეს დაწერა
            $isOrderExit = $this->checkIfTransactionAlreadyHappend($order_id);
            if ($isOrderExit) {
                return response(['FileId' => $string, 'code' => 215], 400);
            }

            $this->createTransactionFastPay(
                $amount,
                $user->id,
                $order_id,
                $string,
                'LB'
            );

            $this->updateTransactionOrderFastPay($data, $order_id, $amount);

            return response()->json(['FileId' => $string, 'code' => 0], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    //   TBC FAST PAY           //////////////////////////////////////////////////////////////
    public function checkIfUserExists(Request $request)
    {
        $phone = $request->input('phone');

        try {
            $user = User::where('phone', $phone)->first();

            if ($user) {
                return response()->json(
                    ['phone' => $phone, 'user' => $user->name],
                    Response::HTTP_OK
                );
            } else {
                return response()->json(
                    ['code' => 5],
                    Response::HTTP_NOT_FOUND
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function checkIfTransactionAlreadyHappend($order_id)
    {
        $order = Tbctransaction::where('order_id', $order_id)->first();

        if ($order) {
            return true;
        }

        return false;
    }

    // fast pay version of create transaction
    public function updateTransactionOrderFastPay($data, $order_id, $amount)
    {
        try {
            $transaction = Tbctransaction::where(
                'order_id',
                $order_id
            )->first();
            if ($transaction) {
                $data['confirmedAmount'] = $amount;
                $this->updateUserData(
                    $data,
                    $transaction,
                    $order_id,
                    'fast_pay'
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    public function createTransactionFastPay(
        $amount,
        $userId,
        $order_id,
        $FileId,
        $type
    ) {
        Log::debug($type);
        Tbctransaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'FileId' => $FileId,
            'order_id' => $order_id,
            'type' => $type,
        ]);
    }
    //  
    public function makeTbcFastPayOrder(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required',
            'order_id' => 'required',
            'amount' => 'required',
        ]);

        $phone = $validatedData['phone'];
        $order_id = $validatedData['order_id'];
        $amount = $validatedData['amount'];

        //  ვეძებნთ უსერს ტელეფონის ნომრით
        $data = $request->all();
        try {
            $user = User::where('phone', $phone)->first();
            $string = $this->MakeFileId($user->id);

            // ვამოწმებ არსებობს თუ არა მსგავსი order_id ბაზაზე რომ ორჯერ არ მოხდეს დაწერა
            $isOrderExit = $this->checkIfTransactionAlreadyHappend($order_id);
            if ($isOrderExit) {
                return response(['FileId' => $string, 'code' => 215], 400);
            }

            $this->createTransactionFastPay(
                $amount,
                $user->id,
                $order_id,
                $string,
                'TBC'
            );

            $this->updateTransactionOrderFastPay($data, $order_id, $amount);

            return response()->json(['FileId' => $string, 'code' => 0], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // tbc fast pay ends here /////////////////////////////////////////////////////////////////
    public function updateTransactionOrder($data, $order_id)
    {
        $transaction = Transaction::where('transaction_id', $order_id)->first();
        if ($data['status'] === 'Succeeded') {
            $this->updateUserData($data, $transaction, $order_id, 'e_com');
        }
        $transaction->status = $data['status'];
        $transaction->save();
        return $data;
    }

    public function makeOrderTransaction($amount, $user, $token)
    {
        $data = [];
        $string = $this->MakeFileId($user->id);


        $url = 'https://api.tbcbank.ge/v1/tpay/payments';
        $response = Http::withHeaders([
            'Accept-Language' => 'ka',
            'apikey' => 'nQasa4C3Kmk7o9Gac9Y3V3fO1ITrpiOD', // replace 'YourAppKeyHere' with your actual app key
            'Authorization' => 'Bearer ' . $token, // Replace <token> with the actual token you have
            'Content-Type' => 'application/json',
        ])->post($url, [
            'amount' => [
                'currency' => 'GEL',
                'total' => $amount,
                'subTotal' => 0,
                'tax' => 0,
                'shipping' => 0,
            ],
            'description' => $string,
            'extra2' => $string,
            'merchantPaymentId' => $string,
            'extra' => substr($string, 0, 25),
            'returnurl' =>  'https://lift.eideas.io/',
            'installmentProducts' => [
                [
                    'Price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'callbackUrl' =>
            "https://lift.eideas.io/api/bank/transaction/callback",
        ]);
        return json_decode($response->body(), true);
    }

    public function transactionCallback(
        Request $request
    ): \Illuminate\Http\JsonResponse {
        $data = $request->all();
        $log = new Logger('custom');
        $log->pushHandler(
            new StreamHandler(storage_path('logs/custom.log')),
            Logger::INFO
        );
        $log->warning('This is a warning message for myCustomFunction');
        $this->getTransactionDetail($data['PaymentId']);
        // Add records to the log
        $log->info(json_encode($data));
        $log->warning('This is a warning message for myCustomFunction');
        //        $this->updateTransactionOrder($data, $data['payId']);
        return response()->json();
    }

    public function getTransactionDetail($order_id = '')
    {
        $token = $this->getToken();
        //$order_id
        $response = Http::withHeaders([
            'apikey' => 'nQasa4C3Kmk7o9Gac9Y3V3fO1ITrpiOD',
            'Authorization' => 'Bearer ' . $token,
        ])->get("https://api.tbcbank.ge/v1/tpay/payments/$order_id");
        $data = json_decode($response->body(), true);
        return $this->updateTransactionOrder($data, $order_id);
    }
    //  aq aris mtavari problema 
    public function updateUserData($data, $transaction, $order_id, $isFastPay)
    {
        try {
            //  es sachiroa ecomrcialistvis 
            $user = User::where('id', $transaction->user_id)
                ->with('devices')
                ->first();
            $transfer_amount = floatval($data['confirmedAmount']) * 100;
            $sakomisio = 0;
            if ($isFastPay == 'e_com') {
                $sakomisio = $transfer_amount * 0.02;

                $sakomisio = number_format($sakomisio, 2, '.', '');
            }
            $user->balance =
                intval($user->balance) + $transfer_amount - $sakomisio;

            $user->save();
            foreach ($user->devices as $key => $device) {
                if ($device->op_mode == '0') {
                    $this->handleOpMode($device->op_mode, $user, $device);
                }






                // if ($device->op_mode === '0') {
                //     Log::debug('op_mode = 0');

                //     $subscriptionDate = $device->pivot->subscription
                //         ? Carbon::parse($device->pivot->subscription)
                //         : null;
                //     $currentDay = Carbon::now()->day;
                //     if ($currentDay < $device->pay_day) {
                //         $nextMonthPayDay = Carbon::now()

                //             ->startOfMonth()
                //             ->addDays($device->pay_day - 1);
                //         Log::debug('შემდეგი თარიღი>>> 1' . $nextMonthPayDay);
                //     } else {
                //         $nextMonthPayDay = Carbon::now()
                //             ->addMonth()
                //             ->startOfMonth()
                //             ->addDays($device->pay_day - 1);

                //         Log::debug('შემდეგი თარიღი>>> 2' . $nextMonthPayDay);
                //     }
                //     if (
                //         is_null($subscriptionDate) ||
                //         ($subscriptionDate &&
                //             $subscriptionDate->lt($nextMonthPayDay) && $userCardAmount  > 0)
                //     ) {
                //         Log::debug('is_null');

                //         $cardAmount =
                //             $userCardAmount * $user->fixed_card_amount;
                //         if (
                //             $user->balance - $user->freezed_balance >=
                //             $device->tariff_amount + $cardAmount
                //         ) {
                //             DeviceUser::where('device_id', $device->id)
                //                 ->where('user_id', $user->id)
                //                 ->update(['subscription' => $nextMonthPayDay]);

                //             $user->freezed_balance = $device->tariff_amount;
                //         } elseif (
                //             $user->balance >=
                //             $device->tariff_amount + $cardAmount
                //         ) {
                //             DeviceUser::where('device_id', $device->id)
                //                 ->where('user_id', $user->id)
                //                 ->update(['subscription' => $nextMonthPayDay]);
                //             $user->freezed_balance = $device->tariff_amount;
                //         }
                //     }
                // }
                $devices_ids = Device::where(
                    'users_id',
                    $device->users_id
                )->get();
                foreach ($devices_ids as $key2 => $value2) {
                    if ($value2->op_mode == '1') {
                        $lastAmount = LastUserAmount::where(
                            'user_id',
                            $user->id
                        )
                            ->where('device_id', $value2->id)
                            ->first();

                        if (empty($lastAmount->user_id)) {
                            LastUserAmount::insert([
                                'user_id' => $user->id,
                                'device_id' => $value2->id,
                                'last_amount' =>
                                $user->balance - $device->tariff_amount,
                            ]);
                        } else {
                            $lastAmount->last_amount =
                                $user->balance - $device->tariff_amount;
                            $lastAmount->save();
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
                                $user->balance - $device->tariff_amount,
                            ],
                        ]);
                        $this->publishMessage($value2->dev_id, $payload);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    // ხელით დააფდეითება ბალანსის
    public function updateBalanceByHand(Request $request, User $user)
    {
        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'string|max:255',
            'email' => 'email|max:255',
            'balance' => 'integer',
            'phone' => 'string|min:5|max:15',
            'role' => 'string',
            'fixed_card_amount' => 'integer'

        ]);

        $user = User::findOrFail($validated['id']);
        $data = $validated;
        $userUpdateData = $validated;
        unset($userUpdateData['balance']);

        $user->update($userUpdateData);
        $this->updateBalanceByHandUserDataUpdate($data);
    }

    public function updateBalanceByHandUserDataUpdate($data)
    {
        try {
            $transfer_amount = floatval($data['balance']);
            $user = User::where('id', $data['id'])
                ->with('devices')
                ->first();

            // $userCardAmount = Card::where('user_id', $user->id)->count();

            // Log::info("balance", ['info'=>$data ]);
            $user->balance =  $data['balance'];
            $user->save();
            // foreach ($user->devices as $key => $device) {
            //     //ფიქსირებული და არა ფიქსირებული ტარიფების ჰენდლერი

            //     $this->handleOpMode($device->op_mode, $user, $device);
            // }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 300],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    //   tokeni
    private function getToken()
    {
        $response = Http::asForm()
            ->withBasicAuth('7001432', 'RHmjnirq2riDVlht')
            ->withHeaders([
                'apikey' => 'nQasa4C3Kmk7o9Gac9Y3V3fO1ITrpiOD', // replace 'YourAppKeyHere' with your actual app key
            ])
            ->post('https://api.tbcbank.ge/v1/tpay/access-token');
        return json_decode($response->body())->access_token;
    }
}
