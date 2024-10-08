<?php

namespace App\Providers;

use App\Models\Card;
use App\Models\User;
use App\Models\Device;
use App\Models\Company;
use App\Models\DeviceUser;

use App\Models\LastUserAmount;

use Illuminate\Support\Carbon;
use App\Services\DeviceMessages;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use App\Exceptions\BankOfGeorgiaUserCheck;
use App\Exceptions\InvalidHashCodeException;
use App\Services\TransactionHandlerForOpMode;


trait TransactionProvider
{

    use DeviceMessages;
    use TransactionHandlerForOpMode;
    //   validations for bank of georgia 
    //  user name and password check
    private function checkUser($USERNAME, $PASSWORD)
    {
        $expectedUsername = env('BOG_USERNAME', 'eideas');
        $expectedPassword = env('BOG_PASSWORD', 'e1i2d3e4a5s6STM32');

        if ($USERNAME !== $expectedUsername || $PASSWORD !== $expectedPassword) {
            throw new BankOfGeorgiaUserCheck();
        }
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


    //   უსერ პაე




    //   make FileID 

    public function MakeFileId($userId)
    {

        $deviceId = DeviceUser::where('user_id', $userId)->first();
        $string = '' . $userId;

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
        return $string;
    }
    //     update user info
    public function updateUserData($amount, $transaction, $order_id, $isFastPay)
    {
        try {

            $user = User::where('id', $transaction->user_id)
                ->with('devices')
                ->first();
            $transfer_amount =  $amount;

            $sakomisio = 0;
            if ($isFastPay == 'e_com') {
                $sakomisio = $transfer_amount * 0.02;
                $sakomisio = number_format($sakomisio, 2, '.', '');
            }
            $user->balance =
                intval($user->balance) + $transfer_amount - $sakomisio;
            $user->save();
            foreach ($user->devices as $key => $device) {
                //ფიქსირებული და არა ფიქსირებული ტარიფების ჰენდლერი
                if ($device->op_mode == '0') {
                    Log::info("user inof", ["user info" => $device->op_mode]);

                    $this->handleOpMode($device->op_mode, $user, $device);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error checking user existence: ' . $e->getMessage()
            );

            return response()->json(
                ['code' => 99],

            );
        }
    }


    //  XML processing 
    private function arrayToXml($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }


    private function arrayToXmlWithAttributes($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['attributes'])) {
                    $subnode = $xmlData->addChild($key);
                    foreach ($value['attributes'] as $attrKey => $attrValue) {
                        $subnode->addAttribute($attrKey, $attrValue);
                    }
                    if (isset($value['value'])) {
                        $subnode[0] = htmlspecialchars($value['value']);
                    }
                } else {
                    $subnode = $xmlData->addChild($key);
                    $this->arrayToXmlWithAttributes($value, $subnode);
                }
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }


    private function XmlResponse($data)
    {
        $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
        $this->arrayToXmlWithAttributes($data, $xmlData);
        $xmlContent = $xmlData->asXML();

        return response($xmlContent,  \Illuminate\Http\Response::HTTP_OK)
            ->header('Content-Type', 'application/xml');
    }

    private function HandleErrorCodes(int $code, string $message)
    {
        $data = [
            'status' => [
                'attributes' => [
                    'code' => $code
                ],
                'value' =>  $message
            ],
            'timestamp' => now()->timestamp,
        ];
        $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
        $this->arrayToXmlWithAttributes($data, $xmlData);
        $xmlContent = $xmlData->asXML();
        return response($xmlContent, \Illuminate\Http\Response::HTTP_BAD_REQUEST)
            ->header('Content-Type', 'application/xml');
    }
    private function HandleServerError()
    {
        $data = [
            'status' => [
                'attributes' => [
                    'code' => 10
                ],
                'value' => 'OP services dose not exist'
            ],
            'timestamp' => now()->timestamp,
        ];

        $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
        $this->arrayToXmlWithAttributes($data, $xmlData);
        $xmlContent = $xmlData->asXML();

        return response($xmlContent,  \Illuminate\Http\Response::HTTP_BAD_REQUEST)
            ->header('Content-Type', 'application/xml');
    }
}
