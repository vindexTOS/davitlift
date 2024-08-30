<?php

namespace App\Exceptions;

use Exception;
 
use App\Providers\TransactionProvider;
 
use Illuminate\Http\Response;

class InvalidHashCodeException extends Exception
{

    use TransactionProvider;
    protected $expectedHash;
    protected $actualHash;

    public function __construct(string $expectedHash, string $actualHash)
    {
        parent::__construct("Invalid hash code");
        $this->expectedHash = $expectedHash;
        $this->actualHash = $actualHash;
    }

    public function render():Response
    {
        $data = [
            'status' => [
                'attributes' => [
                    'code' => 3
                ],
                'value' => 'Hash code is invalid'
            ],
             "ex"=> $this->expectedHash,
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