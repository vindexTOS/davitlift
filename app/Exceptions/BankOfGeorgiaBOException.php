<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class BankOfGeorgiaBOException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            [
                "code"=> 10,
                "msg" => "OP services dose not exist"
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}
