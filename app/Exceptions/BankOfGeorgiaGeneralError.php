<?php

namespace App\Exceptions;

use Exception;
use App\Providers\TransactionProvider;
use Illuminate\Http\Response;

class BankOfGeorgiaGeneralError extends Exception
{
    use TransactionProvider;

    protected $customMessage;

    public function __construct(string $message = null)
    {
        parent::__construct($message);
        $this->customMessage = $message ?: '500 server error';
    }
    public function render(): Response
    {
        $data = [
            'status' => [
                'attributes' => [
                    'code' => 99
                ],
                'value' => $this->customMessage
            ],
            'timestamp' => now()->timestamp,
        ];

        $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><pay-response></pay-response>');
        $this->arrayToXmlWithAttributes($data, $xmlData);
        $xmlContent = $xmlData->asXML();

        return response($xmlContent, Response::HTTP_INTERNAL_SERVER_ERROR)
            ->header('Content-Type', 'application/xml');
    }
}
