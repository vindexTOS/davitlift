<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use App\Providers\TransactionProvider;
 

class BankOfGeorgiaBOException extends Exception
{
    use TransactionProvider;

    public function render():Response
    {

        $data = [
            'status' => [
                'attributes' => [
                    'code' =>10
                ],
                'value' => 'OP services dose not exist'
            ],
            'timestamp' => now()->timestamp,
        ];
  
    $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
    $this->arrayToXmlWithAttributes($data, $xmlData);
    $xmlContent = $xmlData->asXML();
       
    return response($xmlContent, Response::HTTP_BAD_REQUEST)
    ->header('Content-Type', 'application/xml');
    }
}
