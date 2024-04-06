<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Monolog\Logger;
use App\Models\User;
use App\Models\Device;
use GuzzleHttp\Client;
use App\Models\Company;
use App\Models\DeviceUser;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\TbcTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    private $client_id = '77841';
    private $client_secret = 'OOsQRvWG33n4';

    public function index()
    {
        return Transaction::where('user_id', Auth::id())
            ->where('status', 'Succeeded')
            ->get();
    }

    public function perUserTransaction($id)
    {
        return Transaction::where('user_id', $id)
            ->where('status', 'Succeeded')
            ->get();
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

    //   TBC FAST PAY           //////////////////////////////////////////////////////////////
    public function checkIfUserExists(Request $request)
    {
        $phone = $request->input('phone');

        try {
            $user = User::where('phone', $phone)->first();

            if ($user) {
                return response()->json(['phone' => $phone], Response::HTTP_OK);
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
                $this->updateUserData($data, $transaction, $order_id);
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
        $FileId
    ) {
        Tbctransaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'FileId' => $FileId,
            'order_id' => $order_id,
        ]);
    }
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
            $userId = $user->id;
            $string = '' . $user->id;

            $deviceId = DeviceUser::where('user_id', $userId)->first();
            if ($deviceId) {
                $device = Device::where('id', $deviceId->device_id)->first();
                $manager = User::where('id', $device->users_id)->first();
                $company = Company::where('id', $device->company_id)->first();
                if (
                    isset($manager) &&
                    isset($manager->phone) &&
                    isset($company) &&
                    isset($company->sk_code)
                ) {
                    $string .= '#' . $company->sk_code;
                    $length = strlen($string);

                    // If the length is greater than 30, truncate the string to 30 characters
                    if ($length > 30) {
                        $string = substr($string, 0, 30);
                    }

                    // Calculate the remaining length available for the manager's name
                    $remainingLength = 30 - strlen($string);

                    // Append the portion of the manager's name that fits into the remaining length
                    $string .=
                        '#' . substr($manager->phone, 0, $remainingLength);
                }
            }

            // ვამოწმებ არსებობს თუ არა მსგავსი order_id ბაზაზე რომ ორჯერ არ მოხდეს დაწერა
            $isOrderExit = $this->checkIfTransactionAlreadyHappend($order_id);
            if ($isOrderExit) {
                return response(['FileId' => $string, 'code' => 215], 400);
            }

            $this->createTransactionFastPay(
                $amount,
                $userId,
                $order_id,
                $string
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
        if ($transaction) {
            if ($data['status'] === 'Succeeded') {
                $this->updateUserData($data, $transaction, $order_id);
            }
            $transaction->status = $data['status'];
            $transaction->save();
        }
        return $data;
    }

    public function makeOrderTransaction($amount, $user, $token)
    {
        $data = [];
        $string = '' . $user->id;
        $deviceId = DeviceUser::where('user_id', $user->id)->first();
        if ($deviceId) {
            $device = Device::where('id', $deviceId->device_id)->first();
            $manager = User::where('id', $device->users_id)->first();
            $company = Company::where('id', $device->company_id)->first();
            if (
                isset($manager) &&
                isset($manager->phone) &&
                isset($company) &&
                isset($company->sk_code)
            ) {
                $string .= '#' . $company->sk_code;
                $length = strlen($string);

                // If the length is greater than 30, truncate the string to 30 characters
                if ($length > 30) {
                    $string = substr($string, 0, 30);
                }

                // Calculate the remaining length available for the manager's name
                $remainingLength = 30 - strlen($string);

                // Append the portion of the manager's name that fits into the remaining length
                $string .= '#' . substr($manager->phone, 0, $remainingLength);
            }
        }
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
            'returnurl' => 'https://lift.eideas.io/',
            'installmentProducts' => [
                [
                    'Price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'callbackUrl' =>
                'https://lift.eideas.io/api/bank/transaction/callback',
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

    public function updateUserData($data, $transaction, $order_id)
    {
        try {
            $user = User::where('id', $transaction->user_id)
                ->with('devices')
                ->first();
            $transfer_amount = floatval($data['confirmedAmount']) * 100;
            $sakomisio = $transfer_amount * 0.02;

            $sakomisio = number_format($sakomisio, 2, '.', '');
            $user->balance =
                intval($user->balance) + $transfer_amount - $sakomisio;
            foreach ($user->devices as $key => $device) {
                if ($device->op_mode === '0') {
                    $subscriptionDate = $device->pivot->subscription
                        ? Carbon::parse($device->pivot->subscription)
                        : null;
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
                    if (
                        is_null($subscriptionDate) ||
                        ($subscriptionDate &&
                            $subscriptionDate->lt($nextMonthPayDay))
                    ) {
                        if (
                            $user->balance - $user->freezed_balance >=
                            $device->tariff_amount
                        ) {
                            DeviceUser::where('device_id', $device->id)
                                ->where('user_id', $user->id)
                                ->update(['subscription' => $nextMonthPayDay]);
                            $user->freezed_balance =
                                $user->freezed_balance + $device->tariff_amount;
                        }
                    }
                }
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
                                    $user->balance - $user->freezed_balance,
                            ]);
                        } else {
                            $lastAmount->last_amount =
                                $user->balance - $user->freezed_balance;
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
                                    $user->balance - $user->freezed_balance,
                            ],
                        ]);
                        $this->publishMessage($value2->dev_id, $payload);
                    }
                }
            }
            $user->save();
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

    public function generateHexPayload($command, $payload)
    {
        return [
            'command' => $command,
            'payload' => $payload,
        ];
    }

    public function publishMessage($device_id, $payload)
    {
        $data = [
            'device_id' => $device_id,
            'payload' => $payload,
        ];
        $queryParams = http_build_query($data);
        $response = Http::get(
            'http://localhost:3000/mqtt/general?' . $queryParams
        );
        return $response->json(['data' => ['dasd']]);
    }
}
