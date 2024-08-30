<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use App\Providers\TransactionProvider;
use Symfony\Component\HttpFoundation\JsonResponse;

class BankOfGeorgiaUserCheck extends Exception
{    use TransactionProvider;

    public function render():Response
    {

        $data = [
            'status' => [
                'attributes' => [
                    'code' =>2
                ],
                'value' => 'Invalid username or password.'
            ],
            'timestamp' => now()->timestamp,
        ];
    // Convert array to XML
    $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
    $this->arrayToXmlWithAttributes($data, $xmlData);
    $xmlContent = $xmlData->asXML();
    return response($xmlContent, Response::HTTP_BAD_REQUEST)
    ->header('Content-Type', 'application/xml');
    }
}
