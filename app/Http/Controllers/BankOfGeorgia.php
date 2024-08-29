<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\Company;
use App\Models\DeviceUser;
use Illuminate\Http\Request;
use App\Models\LastUserAmount;
use App\Models\TbcTransaction;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Providers\TransactionProvider;
use App\Exceptions\BankOfGeorgiaUserCheck;
use App\Exceptions\InvalidHashCodeException;
use Symfony\Component\HttpFoundation\Response;

class BankOfGeorgia extends Controller
{

    use TransactionProvider;



    // Endpoint:https://lift.eideas.io/api/ipay/?OP=payUSERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=597571441&SERVICE_ID=dsl&PAY_AMOUNT=500&PAYMENT_ID=123456&HASH_CODE=089423FFE1AACD403CA8A6D60E3F83871&PAY_SRC=internet

    // /api/ipay/?OP=verify&USERNAME=ipay&PASSWORD=ipay123&CUSTO
    // MER_ID=597571441&SERVICE_ID=dsl&PAY_SRC=internet&HASH_CODE=A1638825250CFAD6665EC5
    // DC2631E9AD

    public function  handleIpay(Request $request)
    {
        $OP = $request->query("OP");
        switch ($OP) {
            case "verify":
                return   $this->VerifyUser($request);
                break;
            case "ping":
                return   $this->CheckPing($request);
                break;
            case "pay":
                return $this->handlePayment($request);
                break;
            default:
                throw new \App\Exceptions\BankOfGeorgiaBOException();
        }
    }






    //    /api/ipay/verification/?OP=verify&CUSTOMER_ID=574151953
    //   http://www.service-provider1.ge/payments/ipay.php?OP=verify&USERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=112233&SERVICE_ID=dsl&PAY_AMOUNT=500&PAY_SRC=internet&HASH_CODE=12341234058923958023
    private function VerifyUser(Request $request)
    {


        $OP = $request->query("OP");
        $USERNAME = $request->query("USERNAME");
        $PASSWORD = $request->query("PASSWORD");
        $CUSTOMER_ID = $request->query("CUSTOMER_ID");
        $SERVICE_ID = $request->query("SERVICE_ID");
        $PAY_SRC = $request->query("PAY_SRC");

        $HASH_CODE = $request->query("HASH_CODE");

        try {
            if (!$OP || !$USERNAME || !$PASSWORD || !$CUSTOMER_ID || !$SERVICE_ID || !$PAY_SRC || !$HASH_CODE) {
                return response()->json(['code' => 4], Response::HTTP_BAD_REQUEST); // Missing required parameter

            }
            $this->checkUser($USERNAME, $PASSWORD);

            $this->CheckHashCode($OP . $USERNAME . $PASSWORD . $CUSTOMER_ID . $SERVICE_ID . $PAY_SRC, $HASH_CODE);

            $user = User::where('phone', $CUSTOMER_ID)->first();

            if ($user) {
                return response()->json(
                    ['phone' =>  $user->phone, 'user' => $user->name, "code" => 0],
                    Response::HTTP_OK
                );
            } else {
                return response()->json(
                    ['code' => 6],
                    Response::HTTP_NOT_FOUND
                );
            }
        } catch (InvalidHashCodeException $e) {
            return $e->render();
        } catch (BankOfGeorgiaUserCheck $e) {
            return $e->render();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 99, "msg" => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }




    //    http://www.service-provider1.ge/payments/ipay.php?OP=pay&USERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=112233&SERVICE_ID=dsl&PAY_AMOUNT=500&PAY_SRC=&internet&PAYMENT_ID=123456&EXTRA_INFO=Mikheil%20Kapanadze&HASH_CODE=12341234058923958023

    private function handlePayment(Request $request)
    {





        try {


            $OP = $request->query("OP");
            $USERNAME = $request->query("USERNAME");
            $PASSWORD = $request->query("PASSWORD");
            $SERVICE_ID = $request->query("SERVICE_ID");
            $PAY_SRC = $request->query("PAY_SRC");


            $phone = $request->query('CUSTOMER_ID');
            $amount = $request->query('PAY_AMOUNT');

            $hash = $request->query('HASH_CODE');
            $paymentID = $request->query("PAYMENT_ID");
            // Validate required parameters
            if (!$phone || !$amount || !$hash || !$paymentID || !$USERNAME || !$PASSWORD || !$PAY_SRC || !$OP) {
                return response()->json(['code' => 4], Response::HTTP_BAD_REQUEST); // Missing required parameter
            }

            $this->checkUser($USERNAME, $PASSWORD);

            // Verify hash

            $this->CheckHashCode($OP . $USERNAME . $PASSWORD . $phone . $SERVICE_ID . $amount . $PAY_SRC . $paymentID,   $hash);
            // checking if payment already happend
            if ($this->checkIfTransactionAlreadyHappend($paymentID)) {
                return response()->json(
                    ['code' => 8],
                    Response::HTTP_BAD_REQUEST
                );
            };
            $user = User::where('phone', $phone)->first();
            if ($user) {
                $fileId = $this->MakeFileId($user->id);
                $this->createTransactionFastPay($amount, $user->id,  $paymentID, $fileId, "BO" . "-" . $PAY_SRC);
                $transaction = Tbctransaction::where(
                    'order_id',
                    $paymentID
                )->first();


                if ($transaction) {
                    $this->updateUserData(
                        $amount,
                        $transaction->user_id,

                        'fast_pay'
                    );
                }

                return response()->json(['code' => 0, 'message' => 'Payment successful', 'FileId' => $fileId], Response::HTTP_OK);
            } else {
                return response()->json(['code' => 6], Response::HTTP_NOT_FOUND); // User not found
            }
        } catch (InvalidHashCodeException $e) {
            return $e->render();
        } catch (BankOfGeorgiaUserCheck $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json(['code' => 99], Response::HTTP_INTERNAL_SERVER_ERROR); // General error
        }
    }




    //   payment creation 
    private function createTransactionFastPay(
        $amount,
        $userId,
        $order_id,
        $FileId,
        $type
    ) {

        Tbctransaction::create([
            'user_id' => $userId,
            'amount' =>  number_format($amount / 100, 2),
            'FileId' => $FileId,
            'order_id' => $order_id,
            'type' => $type,
        ]);
    }



    private function checkIfTransactionAlreadyHappend($PAYMENT_ID)
    {

        $order = Tbctransaction::where('order_id', $PAYMENT_ID)->first();

        if ($order) {
            return true;
        }

        return  false;
    }

    // Hash generation function
    private function generateHash($data)
    {
        return md5($data . "someseacret");
    }
    // hash checking function 

    private function CheckHashCode(string $data, string $hash)
    {
        $expectedHash = $this->generateHash($data);

        if (strtoupper($hash) !== strtoupper($expectedHash)) {
            throw new InvalidHashCodeException($expectedHash, $hash);
        }
    }


    // http://www.service-provider1.ge/payments/ipay.php?OP=pay&USERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=112233&SERVICE_ID=dsl&PAY_AMOUNT=500&PAY_SRC=&internet&PAYMENT_ID=123456&EXTRA_INFO=Mikheil%20Kapanadze&HASH_CODE=12341234058923958023
    // testing hash 
    // 829F9F1864B743D5F8D6246FEBD8B905
    // OP=debt&USERNAME=ipay&PASSWORD=ipay123&CUSTOMER_ID=112233&SERVICE_ID=dsl
    public function TestHash(Request $request)
    {
        $OP = $request->query("OP");
        $USERNAME = $request->query("USERNAME");
        $PASSWORD = $request->query("PASSWORD");
        $CUSTOMER_ID = $request->query("CUSTOMER_ID");
        $HASH_CODE = $request->query("HASH_CODE");
        $SERVICE_ID = $request->query("SERVICE_ID");

        $this->CheckHashCode($OP . $USERNAME . $PASSWORD . $CUSTOMER_ID . $SERVICE_ID, $HASH_CODE);
        return response()->json(
            ['code' => 0, 'timestamp' => date('Y-m-d H:i:s')],
            Response::HTTP_OK
        );
    }
    //  http://localhost:8000/api/ipay/ping/?OP=ping&USERNAME=ipay&PASSWORD=ipay123&HASH_CODE=12341234058923958023
    private function CheckPing(Request $request)
    {


        try {


            $OP = $request->query("OP");
            $USERNAME = $request->query("USERNAME");
            $PASSWORD = $request->query("PASSWORD");
            $HASH_CODE = $request->query("HASH_CODE");



            if (!$OP || !$USERNAME || !$PASSWORD || !$HASH_CODE) {
                return response()->json(['code' => 4], Response::HTTP_BAD_REQUEST); // Missing required parameter

            }

            $this->checkUser($USERNAME, $PASSWORD);
            $this->CheckHashCode($OP . $USERNAME . $PASSWORD, $HASH_CODE);

            return response()->json(
                ['code' => 0, 'timestamp' => date('Y-m-d H:i:s')],
                Response::HTTP_OK
            );
        } catch (InvalidHashCodeException $e) {
            return $e->render();
        } catch (BankOfGeorgiaUserCheck $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in CheckPing: ' . $e->getMessage());

            return response()->json(
                ['code' => 99, 'msg' =>  $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }


    private function checkUser($USERNAME, $PASSWORD)
    {
        $expectedUsername = env('BOG_USERNAME', 'ipay');
        $expectedPassword = env('BOG_PASSWORD', 'ipay123');

        if ($USERNAME !== $expectedUsername || $PASSWORD !== $expectedPassword) {
            throw new BankOfGeorgiaUserCheck();
        }
    }
}
