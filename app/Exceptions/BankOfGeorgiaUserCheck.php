<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class BankOfGeorgiaUserCheck extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            [
                "code"=> 2,
                "msg" => "Invalid username or password."
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}
